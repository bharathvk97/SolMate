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
}
