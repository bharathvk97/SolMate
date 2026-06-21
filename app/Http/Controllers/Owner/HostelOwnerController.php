<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelBooking;
use App\Models\HostelImage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HostelOwnerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $hostelIds = Hostel::where('owner_id', $user->id)->pluck('id');
        return view('owner.hostel.dashboard', [
            'hostels'         => Hostel::where('owner_id', $user->id)->with(['rooms', 'images'])->latest()->get(),
            'recentBookings'  => HostelBooking::whereIn('hostel_id', $hostelIds)->with(['user', 'room', 'hostel'])->latest()->limit(5)->get(),
            'pendingBookings' => HostelBooking::whereIn('hostel_id', $hostelIds)->where('status', 'pending')->count(),
            'stats' => [
                'total_hostels'    => Hostel::where('owner_id', $user->id)->count(),
                'active_hostels'   => Hostel::where('owner_id', $user->id)->where('status', 'active')->count(),
                'total_bookings'   => HostelBooking::whereIn('hostel_id', $hostelIds)->count(),
                'pending_bookings' => HostelBooking::whereIn('hostel_id', $hostelIds)->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function createForm()
    {
        return view('owner.hostel.create');
    }

    public function editForm(int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())
            ->with(['rooms', 'images', 'amenities'])
            ->findOrFail($id);
        return view('owner.hostel.edit', compact('hostel'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:200',
            'address' => 'required|string',
            'city'    => 'required|string',
            'state'   => 'required|string',
        ]);

        $user = auth()->user();
        $disk = config('filesystems.default');

        $hostel = Hostel::create([
            'owner_id'        => $user->id,
            'name'            => $request->name,
            'slug'            => Str::slug($request->name) . '-' . Str::random(6),
            'description'     => $request->description,
            'address'         => $request->address,
            'city'            => $request->city,
            'state'           => $request->state,
            'pincode'         => $request->pincode ?: '000000',
            'lat'             => $request->latitude  ?: null,
            'lng'             => $request->longitude ?: null,
            'phone'           => $request->phone,
            'gender_type'     => $request->gender_type ?? 'coed',
            'curfew_time'     => $request->curfew_time ?: null,
            'allow_guests'    => $request->boolean('allow_guests'),
            'house_rules'     => $request->house_rules,
            'has_wifi'        => $request->boolean('has_wifi'),
            'has_ac'          => $request->boolean('has_ac'),
            'has_cctv'        => $request->boolean('has_cctv'),
            'has_parking'     => $request->boolean('has_parking'),
            'has_laundry'     => $request->boolean('has_laundry'),
            'has_power_backup'=> $request->boolean('has_power_backup'),
            'has_gym'         => $request->boolean('has_gym'),
            'has_mess'        => $request->boolean('has_mess'),
            'has_security'    => $request->boolean('has_security'),
            'status'          => 'pending',
        ]);

        // Upload images
        if ($request->hasFile('images')) {
            $isCover = true;
            foreach ($request->file('images') as $img) {
                $path = $img->store('hostels/' . $hostel->id, $disk);
                HostelImage::create([
                    'hostel_id'  => $hostel->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => $isCover,
                    'sort_order' => 0,
                ]);
                $isCover = false;
            }
        }

        return redirect()->route('owner.hostel.dashboard')
            ->with('success', 'Hostel submitted for review! We will activate it shortly.');
    }

    public function update(Request $request, int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($id);
        $disk   = config('filesystems.default');

        $hostel->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'address'         => $request->address,
            'city'            => $request->city,
            'state'           => $request->state,
            'pincode'         => $request->pincode ?: $hostel->pincode,
            'lat'             => $request->latitude  ?: $hostel->lat,
            'lng'             => $request->longitude ?: $hostel->lng,
            'phone'           => $request->phone,
            'gender_type'     => $request->gender_type,
            'curfew_time'     => $request->curfew_time ?: null,
            'allow_guests'    => $request->boolean('allow_guests'),
            'house_rules'     => $request->house_rules,
            'has_wifi'        => $request->boolean('has_wifi'),
            'has_ac'          => $request->boolean('has_ac'),
            'has_cctv'        => $request->boolean('has_cctv'),
            'has_parking'     => $request->boolean('has_parking'),
            'has_laundry'     => $request->boolean('has_laundry'),
            'has_power_backup'=> $request->boolean('has_power_backup'),
            'has_gym'         => $request->boolean('has_gym'),
            'has_mess'        => $request->boolean('has_mess'),
            'has_security'    => $request->boolean('has_security'),
        ]);

        // Upload new images
        if ($request->hasFile('images')) {
            $hasCover = $hostel->images()->where('is_cover', true)->exists();
            foreach ($request->file('images') as $img) {
                $path = $img->store('hostels/' . $hostel->id, $disk);
                HostelImage::create([
                    'hostel_id'  => $hostel->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => !$hasCover,
                    'sort_order' => 0,
                ]);
                $hasCover = true;
            }
        }

        return redirect()->route('owner.hostel.dashboard')
            ->with('success', 'Hostel updated successfully!');
    }

    public function roomsPage(int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->with(['rooms.images'])->findOrFail($id);
        return view('owner.hostel.rooms', compact('hostel'));
    }

    public function bookingsPage(Request $request)
    {
        $hostelIds = Hostel::where('owner_id', auth()->id())->pluck('id');
        $bookings  = HostelBooking::whereIn('hostel_id', $hostelIds)
            ->with(['user', 'hostel', 'room'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()->paginate(20);
        return view('owner.hostel.bookings', compact('bookings'));
    }

    public function updateBookingStatus(Request $request, int $id)
    {
        $booking = HostelBooking::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        $booking->update(['status' => $request->status, 'owner_note' => $request->owner_note]);
        return back()->with('success', 'Booking status updated.');
    }
}
