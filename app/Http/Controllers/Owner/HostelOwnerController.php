<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelBooking;
use App\Models\HostelImage;
use App\Models\Room;
use App\Models\RoomImage;
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
            'owner_id'         => $user->id,
            'name'             => $request->name,
            'slug'             => Str::slug($request->name) . '-' . Str::random(6),
            'description'      => $request->description,
            'address'          => $request->address,
            'city'             => $request->city,
            'state'            => $request->state,
            'pincode'          => $request->pincode ?: '000000',
            'lat'              => $request->latitude  ?: null,
            'lng'              => $request->longitude ?: null,
            'phone'            => $request->phone,
            'gender_type'      => $request->gender_type ?? 'coed',
            'curfew_time'      => $request->curfew_time ?: null,
            'allow_guests'     => $request->boolean('allow_guests'),
            'house_rules'      => $request->house_rules,
            'has_wifi'         => $request->boolean('has_wifi'),
            'has_ac'           => $request->boolean('has_ac'),
            'has_cctv'         => $request->boolean('has_cctv'),
            'has_parking'      => $request->boolean('has_parking'),
            'has_laundry'      => $request->boolean('has_laundry'),
            'has_power_backup' => $request->boolean('has_power_backup'),
            'has_gym'          => $request->boolean('has_gym'),
            'has_mess'         => $request->boolean('has_mess'),
            'has_security'     => $request->boolean('has_security'),
            'status'           => 'pending',
        ]);

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
            ->with('success', 'Hostel submitted for review!');
    }

    public function editForm(int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())
            ->with(['rooms', 'images', 'amenities'])
            ->findOrFail($id);
        return view('owner.hostel.edit', compact('hostel'));
    }

    public function update(Request $request, int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($id);
        $disk   = config('filesystems.default');

        $hostel->update([
            'name'             => $request->name,
            'description'      => $request->description,
            'address'          => $request->address,
            'city'             => $request->city,
            'state'            => $request->state,
            'pincode'          => $request->pincode ?: $hostel->pincode,
            'lat'              => $request->latitude  ?: $hostel->lat,
            'lng'              => $request->longitude ?: $hostel->lng,
            'phone'            => $request->phone,
            'gender_type'      => $request->gender_type,
            'curfew_time'      => $request->curfew_time ?: null,
            'allow_guests'     => $request->boolean('allow_guests'),
            'house_rules'      => $request->house_rules,
            'has_wifi'         => $request->boolean('has_wifi'),
            'has_ac'           => $request->boolean('has_ac'),
            'has_cctv'         => $request->boolean('has_cctv'),
            'has_parking'      => $request->boolean('has_parking'),
            'has_laundry'      => $request->boolean('has_laundry'),
            'has_power_backup' => $request->boolean('has_power_backup'),
            'has_gym'          => $request->boolean('has_gym'),
            'has_mess'         => $request->boolean('has_mess'),
            'has_security'     => $request->boolean('has_security'),
        ]);

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

    // ── ROOMS ──────────────────────────────────────────────

    public function roomsPage(int $id)
    {
        $hostel = Hostel::where('owner_id', auth()->id())
            ->with(['rooms' => fn($q) => $q->withTrashed(false), 'rooms.images'])
            ->findOrFail($id);
        return view('owner.hostel.rooms', compact('hostel'));
    }

    public function storeRoom(Request $request, int $hostelId)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($hostelId);
        $disk   = config('filesystems.default');

        $request->validate([
            'name'            => 'required|string|max:100',
            'type'            => 'required|in:single,double,triple,shared,dormitory',
            'price_per_month' => 'required|numeric|min:0',
        ]);

        $room = Room::create([
            'hostel_id'             => $hostel->id,
            'name'                  => $request->name,
            'type'                  => $request->type,
            'is_ac'                 => $request->boolean('is_ac'),
            'price_per_month'       => $request->price_per_month,
            'price_per_day'         => $request->price_per_day ?: null,
            'security_deposit'      => $request->security_deposit ?? 0,
            'capacity'              => $request->capacity ?? 1,
            'total_count'           => $request->total_count ?? 1,
            'available_count'       => $request->available_count ?? 1,
            'floor_number'          => $request->floor_number,
            'description'           => $request->description,
            'is_available'          => $request->boolean('is_available'),
            'has_attached_bathroom' => $request->boolean('has_attached_bathroom'),
            'has_balcony'           => $request->boolean('has_balcony'),
            'has_study_table'       => $request->boolean('has_study_table'),
            'has_wardrobe'          => $request->boolean('has_wardrobe'),
            'has_tv'                => $request->boolean('has_tv'),
            'has_fridge'            => $request->boolean('has_fridge'),
        ]);

        if ($request->hasFile('images')) {
            $isCover = true;
            foreach ($request->file('images') as $img) {
                $path = $img->store('rooms/' . $room->id, $disk);
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => $isCover,
                    'sort_order' => 0,
                ]);
                $isCover = false;
            }
        }

        return redirect()->route('owner.hostel.rooms', $hostelId)
            ->with('success', "Room '{$room->name}' added successfully!");
    }

    public function updateRoom(Request $request, int $hostelId, int $roomId)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($hostelId);
        $room   = Room::where('hostel_id', $hostel->id)->findOrFail($roomId);
        $disk   = config('filesystems.default');

        $room->update([
            'name'                  => $request->name,
            'type'                  => $request->type,
            'is_ac'                 => $request->boolean('is_ac'),
            'price_per_month'       => $request->price_per_month,
            'price_per_day'         => $request->price_per_day ?: null,
            'security_deposit'      => $request->security_deposit ?? 0,
            'capacity'              => $request->capacity ?? 1,
            'total_count'           => $request->total_count ?? 1,
            'available_count'       => $request->available_count ?? 1,
            'floor_number'          => $request->floor_number,
            'description'           => $request->description,
            'is_available'          => $request->boolean('is_available'),
            'has_attached_bathroom' => $request->boolean('has_attached_bathroom'),
            'has_balcony'           => $request->boolean('has_balcony'),
            'has_study_table'       => $request->boolean('has_study_table'),
            'has_wardrobe'          => $request->boolean('has_wardrobe'),
            'has_tv'                => $request->boolean('has_tv'),
            'has_fridge'            => $request->boolean('has_fridge'),
        ]);

        if ($request->hasFile('images')) {
            $hasCover = $room->images()->where('is_cover', true)->exists();
            foreach ($request->file('images') as $img) {
                $path = $img->store('rooms/' . $room->id, $disk);
                RoomImage::create([
                    'room_id'    => $room->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => !$hasCover,
                    'sort_order' => 0,
                ]);
                $hasCover = true;
            }
        }

        return redirect()->route('owner.hostel.rooms', $hostelId)
            ->with('success', "Room '{$room->name}' updated!");
    }

    public function toggleRoom(int $hostelId, int $roomId)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($hostelId);
        $room   = Room::where('hostel_id', $hostel->id)->findOrFail($roomId);
        $room->update(['is_available' => !$room->is_available]);
        return back()->with('success', "Room marked as " . ($room->is_available ? 'available' : 'unavailable') . ".");
    }

    public function deleteRoom(int $hostelId, int $roomId)
    {
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($hostelId);
        $room   = Room::where('hostel_id', $hostel->id)->findOrFail($roomId);
        $room->delete();
        return back()->with('success', "Room deleted.");
    }

    // ── BOOKINGS ───────────────────────────────────────────

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
