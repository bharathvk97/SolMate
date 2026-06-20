<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelBooking;
use App\Models\OwnerSubscription;
use App\Models\OwnerSubscriptionPlan;
use App\Models\Room;
use Illuminate\Http\Request;

class HostelOwnerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $hostelIds = Hostel::where('owner_id',$user->id)->pluck('id');
        return view('owner.hostel.dashboard', [
            'hostels'        => Hostel::where('owner_id',$user->id)->with(['rooms','images'])->latest()->get(),
            'recentBookings' => HostelBooking::whereIn('hostel_id',$hostelIds)->with(['user','room','hostel'])->latest()->limit(5)->get(),
            'stats' => [
                'total_hostels'   => Hostel::where('owner_id',$user->id)->count(),
                'active_hostels'  => Hostel::where('owner_id',$user->id)->where('status','active')->count(),
                'total_bookings'  => HostelBooking::whereIn('hostel_id',$hostelIds)->count(),
                'pending_bookings'=> HostelBooking::whereIn('hostel_id',$hostelIds)->where('status','pending')->count(),
            ],
        ]);
    }

    public function createForm()
    {
        return view('owner.hostel.create');
    }

    public function editForm(int $id)
    {
        $hostel = Hostel::where('owner_id',auth()->id())->with('rooms','images','amenities')->findOrFail($id);
        return view('owner.hostel.edit', compact('hostel'));
    }

    public function roomsPage(int $id)
    {
        $hostel = Hostel::where('owner_id',auth()->id())->with(['rooms.images'])->findOrFail($id);
        return view('owner.hostel.rooms', compact('hostel'));
    }

    public function bookingsPage(Request $request)
    {
        $hostelIds = Hostel::where('owner_id',auth()->id())->pluck('id');
        $bookings  = HostelBooking::whereIn('hostel_id',$hostelIds)
            ->with(['user','hostel','room'])
            ->when($request->status, fn($q) => $q->where('status',$request->status))
            ->latest()->paginate(20);
        return view('owner.hostel.bookings', compact('bookings'));
    }

    public function updateBookingStatus(Request $request, int $id)
    {
        $booking = HostelBooking::whereHas('hostel',fn($q)=>$q->where('owner_id',auth()->id()))->findOrFail($id);
        $booking->update(['status'=>$request->status,'owner_note'=>$request->owner_note]);
        return back()->with('success','Booking status updated.');
    }
}
