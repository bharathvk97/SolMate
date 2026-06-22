<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Mess;
use App\Models\HostelBooking;
use App\Models\MessBooking;
use App\Models\Favourite;
use App\Models\Review;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return view('user.home', [
            'stats' => [
                'hostels' => Hostel::where('status', 'active')->count(),
                'messes'  => Mess::where('status', 'active')->count(),
                'cities'  => Hostel::where('status', 'active')->distinct('city')->count('city'),
            ],
        ]);
    }

    public function hostels(Request $request)
    {
        $q = Hostel::active()->with([
            'images' => fn($q) => $q->where('is_cover', true),
            'rooms',
        ]);

        if ($request->q)           $q->where('name', 'like', '%' . $request->q . '%');
        if ($request->city)        $q->where('city', $request->city);
        if ($request->gender_type) $q->where('gender_type', $request->gender_type);

        $sort = $request->get('sort', 'featured');
        if ($sort === 'rating')  $q->orderByDesc('average_rating');
        elseif ($sort === 'newest') $q->latest();
        else $q->orderByDesc('is_featured')->orderByDesc('average_rating');

        return view('user.hostels-list', ['hostels' => $q->paginate(12)]);
    }

    public function messes(Request $request)
    {
        $q = Mess::active()->with([
            'images' => fn($q) => $q->where('is_cover', true),
            'menus',
            'subscriptionPlans' => fn($q) => $q->where('is_active', true),
        ]);

        if ($request->q)           $q->where('name', 'like', '%' . $request->q . '%');
        if ($request->city)        $q->where('city', $request->city);
        if ($request->food_type)   $q->where(fn($s) => $s->where('food_type', $request->food_type)->orWhere('food_type', 'both'));
        if ($request->has_delivery)$q->where('has_delivery', true);

        // Filter by open slot
        if ($request->slot) {
            $slot = $request->slot;
            $q->whereNotNull($slot . '_open')->whereNotNull($slot . '_close');
        }

        $q->orderByDesc('is_featured')->orderByDesc('average_rating');

        return view('user.messes-list', ['messes' => $q->paginate(12)]);
    }

    public function hostelDetail(string $slug)
    {
        $hostel = Hostel::active()
            ->with([
                'owner:id,name,phone,avatar',
                'rooms.images',
                'images',
                'amenities',
                'reviews' => fn($q) => $q->with('user:id,name,avatar')
                    ->where('is_hidden', false)->latest()->limit(15),
            ])
            ->where('slug', $slug)->firstOrFail();

        return view('user.hostel-detail', compact('hostel'));
    }

    public function messDetail(string $slug)
    {
        $mess = Mess::active()
            ->with([
                'owner:id,name,phone',
                'images',
                'menus.images',
                'subscriptionPlans' => fn($q) => $q->where('is_active', true),
                'reviews' => fn($q) => $q->with('user:id,name,avatar')
                    ->where('is_hidden', false)->latest()->limit(15),
            ])
            ->where('slug', $slug)->firstOrFail();

        return view('user.mess-detail', compact('mess'));
    }

    public function bookings()
    {
        $user = auth()->user();
        return view('user.bookings', [
            'hostelBookings' => HostelBooking::where('user_id', $user->id)
                ->with(['hostel', 'room'])->latest()->paginate(10),
            'messBookings'   => MessBooking::where('user_id', $user->id)
                ->with(['mess', 'plan'])->latest()->paginate(10),
        ]);
    }

    public function favourites()
    {
        $favs = Favourite::where('user_id', auth()->id())
            ->with('favourable')->latest()->paginate(20);
        return view('user.favourites', compact('favs'));
    }

    public function profile()
    {
        return view('user.profile', ['user' => auth()->user()]);
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'reviewable_type' => 'required|in:hostel,mess',
            'reviewable_id'   => 'required|integer',
            'rating'          => 'required|integer|min:1|max:5',
            'body'            => 'required|string|min:10',
        ]);

        $user       = auth()->user();
        $modelClass = $request->reviewable_type === 'hostel'
            ? Hostel::class
            : Mess::class;

        $reviewable = $modelClass::findOrFail($request->reviewable_id);

        // Prevent duplicate reviews
        $existing = Review::where('user_id', $user->id)
            ->where('reviewable_type', $modelClass)
            ->where('reviewable_id', $request->reviewable_id)
            ->first();

        if ($existing) {
            return back()->with('error', 'You have already reviewed this listing.');
        }

        // Verify booking
        $isVerified = false;
        if ($request->reviewable_type === 'hostel') {
            $isVerified = HostelBooking::where('user_id', $user->id)
                ->where('hostel_id', $request->reviewable_id)
                ->where('payment_status', 'paid')->exists();
        } else {
            $isVerified = MessBooking::where('user_id', $user->id)
                ->where('mess_id', $request->reviewable_id)
                ->where('payment_status', 'paid')->exists();
        }

        Review::create([
            'user_id'         => $user->id,
            'reviewable_type' => $modelClass,
            'reviewable_id'   => $request->reviewable_id,
            'rating'          => $request->rating,
            'body'            => $request->body,
            'is_verified'     => $isVerified,
            'is_hidden'       => false,
        ]);

        // Update average rating
        $avg   = Review::where('reviewable_type', $modelClass)->where('reviewable_id', $request->reviewable_id)->where('is_hidden', false)->avg('rating');
        $count = Review::where('reviewable_type', $modelClass)->where('reviewable_id', $request->reviewable_id)->where('is_hidden', false)->count();
        $reviewable->update(['average_rating' => round($avg, 2), 'total_reviews' => $count]);

        return back()->with('success', 'Review submitted successfully!');
    }

    public function toggleFavourite(Request $request)
    {
        $request->validate([
            'type' => 'required|in:hostel,mess',
            'id'   => 'required|integer',
        ]);

        $user       = auth()->user();
        $modelClass = $request->type === 'hostel' ? Hostel::class : Mess::class;

        $existing = Favourite::where('user_id', $user->id)
            ->where('favourable_type', $modelClass)
            ->where('favourable_id', $request->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $saved   = false;
            $message = 'Removed from favourites.';
        } else {
            Favourite::create([
                'user_id'         => $user->id,
                'favourable_type' => $modelClass,
                'favourable_id'   => $request->id,
            ]);
            $saved   = true;
            $message = 'Added to favourites! ❤️';
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['saved' => $saved, 'message' => $message]);
        }

        return back()->with('success', $message);
    }
}
