<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Mess;
use App\Models\HostelBooking;
use App\Models\MessBooking;
use App\Models\Favourite;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('user.home', [
            'stats' => [
                'hostels' => Hostel::where('status','active')->count(),
                'messes'  => Mess::where('status','active')->count(),
                'cities'  => Hostel::where('status','active')->distinct('city')->count('city'),
            ],
        ]);
    }

    public function hostelDetail(string $slug)
    {
        $hostel = Hostel::active()
            ->with(['owner:id,name,phone,avatar','rooms.images','images','amenities','reviews'=>fn($q)=>$q->with('user:id,name,avatar')->where('is_hidden',false)->latest()->limit(15)])
            ->where('slug',$slug)->firstOrFail();

        $hostel->whatsapp_share = 'https://wa.me/?text='.urlencode("Check out {$hostel->name}! ".url()->current());

        return view('user.hostel-detail', compact('hostel'));
    }

    public function messDetail(string $slug)
    {
        $mess = Mess::active()
            ->with(['owner:id,name,phone','images','menus.images','subscriptionPlans'=>fn($q)=>$q->where('is_active',true),'reviews'=>fn($q)=>$q->with('user:id,name,avatar')->where('is_hidden',false)->latest()->limit(15)])
            ->where('slug',$slug)->firstOrFail();

        $mess->whatsapp_share = 'https://wa.me/?text='.urlencode("Check out {$mess->name}! ".url()->current());

        return view('user.mess-detail', compact('mess'));
    }

    public function hostels(Request $request)
    {
        $q = Hostel::active()->with(['images'=>fn($q)=>$q->where('is_cover',true),'rooms'=>fn($q)=>$q->available()]);
        if ($request->q) $q->where('name','like','%'.$request->q.'%');
        if ($request->city) $q->where('city',$request->city);
        return view('user.hostels-list', ['hostels'=>$q->orderByDesc('is_featured')->paginate(12)]);
    }

    public function messes(Request $request)
    {
        $q = Mess::active()->with(['images'=>fn($q)=>$q->where('is_cover',true),'menus']);
        if ($request->q) $q->where('name','like','%'.$request->q.'%');
        return view('user.messes-list', ['messes'=>$q->orderByDesc('is_featured')->paginate(12)]);
    }

    public function bookings()
    {
        $user = auth()->user();
        return view('user.bookings', [
            'hostelBookings' => HostelBooking::where('user_id',$user->id)->with(['hostel','room'])->latest()->paginate(10),
            'messBookings'   => MessBooking::where('user_id',$user->id)->with(['mess','plan'])->latest()->paginate(10),
        ]);
    }

    public function favourites()
    {
        $favs = Favourite::where('user_id',auth()->id())->with('favourable')->latest()->paginate(20);
        return view('user.favourites', compact('favs'));
    }

    public function profile()
    {
        return view('user.profile', ['user'=>auth()->user()]);
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|in:hostel,mess',
            'reviewable_id'   => 'required|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'body'            => 'required|string|min:10',
        ]);

        $user = auth()->user();

        // Map type string to model class
        $modelClass = $request->reviewable_type === 'hostel'
            ? \App\Models\Hostel::class
            : \App\Models\Mess::class;

        $reviewable = $modelClass::findOrFail($request->reviewable_id);

        // Check if user already reviewed this
        $existing = \App\Models\Review::where('user_id', $user->id)
            ->where('reviewable_type', $modelClass)
            ->where('reviewable_id', $request->reviewable_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already reviewed this listing.');
        }

        // Check if verified booking exists
        $isVerified = false;
        if ($request->reviewable_type === 'hostel') {
            $isVerified = \App\Models\HostelBooking::where('user_id', $user->id)
                ->where('hostel_id', $request->reviewable_id)
                ->where('payment_status', 'paid')
                ->exists();
        } else {
            $isVerified = \App\Models\MessBooking::where('user_id', $user->id)
                ->where('mess_id', $request->reviewable_id)
                ->where('payment_status', 'paid')
                ->exists();
        }

        \App\Models\Review::create([
            'user_id'          => $user->id,
            'reviewable_type'  => $modelClass,
            'reviewable_id'    => $request->reviewable_id,
            'rating'           => $request->rating,
            'body'             => $request->body,
            'is_verified'      => $isVerified,
            'is_hidden'        => false,
        ]);

        // Update average rating on the listing
        $avg = \App\Models\Review::where('reviewable_type', $modelClass)
            ->where('reviewable_id', $request->reviewable_id)
            ->where('is_hidden', false)
            ->avg('rating');

        $count = \App\Models\Review::where('reviewable_type', $modelClass)
            ->where('reviewable_id', $request->reviewable_id)
            ->where('is_hidden', false)
            ->count();

        $reviewable->update([
            'average_rating' => round($avg, 2),
            'total_reviews'  => $count,
        ]);

        return back()->with('success', 'Review submitted successfully!');
    }

    public function toggleFavourite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:hostel,mess',
            'id'   => 'required|integer',
        ]);

        $user       = auth()->user();
        $modelClass = $request->type === 'hostel'
            ? \App\Models\Hostel::class
            : \App\Models\Mess::class;

        $existing = \App\Models\Favourite::where('user_id', $user->id)
            ->where('favourable_type', $modelClass)
            ->where('favourable_id', $request->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved   = false;
            $message = 'Removed from favourites.';
        } else {
            \App\Models\Favourite::create([
                'user_id'         => $user->id,
                'favourable_type' => $modelClass,
                'favourable_id'   => $request->id,
            ]);
            $saved   = true;
            $message = 'Added to favourites!';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['saved' => $saved, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
