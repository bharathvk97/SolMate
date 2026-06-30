<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\HostelBooking;
use App\Models\HostelImage;
use App\Models\Room;
use App\Models\RoomImage;
use App\Models\HostelMember;
use App\Models\RoomAsset;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        // Residents = people with an approved stay (exclude pending requests, rejected, cancelled)
        $base = HostelBooking::whereIn('hostel_id', $hostelIds)
            ->whereIn('status', ['confirmed', 'checked_in', 'checked_out']);

        $stats = [
            'total'        => (clone $base)->count(),
            'pending'      => (clone $base)->where('rent_status', 'pending')->count(),
            'advance_paid' => (clone $base)->where('rent_status', 'advance_paid')->count(),
            'fully_paid'   => (clone $base)->where('rent_status', 'fully_paid')->count(),
        ];

        $query = (clone $base)->with(['user', 'hostel', 'room']);

        // Filter by rent status category
        if (in_array($request->rent, ['pending', 'advance_paid', 'fully_paid'], true)) {
            $query->where('rent_status', $request->rent);
        }

        // Date range filter — residents whose stay overlaps the chosen window
        if ($request->filled('start_date')) {
            $query->whereDate('check_out', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('check_in', '<=', $request->end_date);
        }

        $bookings = $query->orderByDesc('check_in')->paginate(15)->withQueryString();

        $pendingBookings = HostelBooking::whereIn('hostel_id', $hostelIds)->where('status', 'pending')->count();

        return view('owner.hostel.bookings', compact('bookings', 'stats', 'pendingBookings'));
    }

    public function updateBookingStatus(Request $request, int $id)
    {
        $booking = HostelBooking::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        $booking->update(['status' => $request->status, 'owner_note' => $request->owner_note]);
        return back()->with('success', 'Booking status updated.');
    }

    public function updateRentStatus(Request $request, int $id)
    {
        $request->validate(['rent_status' => 'required|in:pending,advance_paid,fully_paid']);

        $booking = HostelBooking::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($id);
        $booking->update(['rent_status' => $request->rent_status]);

        return back()->with('success', 'Rent status updated.');
    }

    public function reviews()
    {
        $hostelIds = Hostel::where('owner_id', auth()->id())->pluck('id');

        $reviews = Review::where('reviewable_type', Hostel::class)
            ->whereIn('reviewable_id', $hostelIds)
            ->with(['user', 'reviewable'])
            ->latest()
            ->paginate(15);

        $base = Review::where('reviewable_type', Hostel::class)->whereIn('reviewable_id', $hostelIds);
        $stats = [
            'total'   => (clone $base)->count(),
            'average' => round((clone $base)->avg('rating') ?? 0, 1),
            'replied' => (clone $base)->whereNotNull('owner_reply')->count(),
        ];

        $pendingBookings = HostelBooking::whereIn('hostel_id', $hostelIds)->where('status', 'pending')->count();

        return view('owner.hostel.reviews', compact('reviews', 'stats', 'pendingBookings'));
    }

    public function replyReview(Request $request, int $id)
    {
        $request->validate(['owner_reply' => 'required|string|max:1000']);

        // Only allow replying to reviews that belong to this owner's hostels
        $hostelIds = Hostel::where('owner_id', auth()->id())->pluck('id');
        $review = Review::where('reviewable_type', Hostel::class)
            ->whereIn('reviewable_id', $hostelIds)
            ->findOrFail($id);

        $review->update([
            'owner_reply'      => $request->owner_reply,
            'owner_replied_at' => now(),
        ]);

        return back()->with('success', 'Your reply has been posted.');
    }

    // ── MEMBERS (Subscription) ─────────────────────────────

    private function ownerHostelIds()
    {
        return Hostel::where('owner_id', auth()->id())->pluck('id');
    }

    private function sidebarPendingCount(): int
    {
        return HostelBooking::whereIn('hostel_id', $this->ownerHostelIds())
            ->where('status', 'pending')->count();
    }

    public function membersPage(Request $request)
    {
        $hostelIds = $this->ownerHostelIds();
        $base      = HostelMember::whereIn('hostel_id', $hostelIds);

        $stats = [
            'total'        => (clone $base)->count(),
            'pending'      => (clone $base)->where('rent_status', 'pending')->count(),
            'advance_paid' => (clone $base)->where('rent_status', 'advance_paid')->count(),
            'fully_paid'   => (clone $base)->where('rent_status', 'fully_paid')->count(),
        ];

        $query = (clone $base)->with(['hostel', 'room']);

        // Search across name, phone, email, place, room number, ID and id-proof number.
        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('place', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%")
                  ->orWhere('id_proof_number', 'like', "%{$search}%");
                if (ctype_digit($search)) {
                    $w->orWhere('id', (int) $search);
                }
            });
        }

        // Rent status filter (paid / unpaid / advance).
        if (in_array($request->rent, ['pending', 'advance_paid', 'fully_paid'], true)) {
            $query->where('rent_status', $request->rent);
        }

        // Optional hostel filter (only the owner's hostels).
        if ($request->filled('hostel_id') && $hostelIds->contains((int) $request->hostel_id)) {
            $query->where('hostel_id', (int) $request->hostel_id);
        }

        $members = $query->orderByDesc('id')->paginate(12)->withQueryString();

        $hostels = Hostel::where('owner_id', auth()->id())
            ->with(['rooms' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')->get();

        $pendingBookings = $this->sidebarPendingCount();

        return view('owner.hostel.members', compact('members', 'stats', 'hostels', 'pendingBookings'));
    }

    public function storeMember(Request $request)
    {
        $data   = $this->validateMember($request);
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($data['hostel_id']);

        $payload = $this->memberPayload($data, $hostel);

        if ($request->hasFile('photo')) {
            $disk = config('filesystems.default');
            $payload['photo_path'] = $request->file('photo')->store('members', $disk);
            $payload['photo_disk'] = $disk;
        }

        HostelMember::create($payload);

        return redirect()->route('owner.hostel.members')
            ->with('success', "Member '{$data['name']}' added.");
    }

    public function updateMember(Request $request, int $id)
    {
        $member = HostelMember::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))
            ->findOrFail($id);

        $data   = $this->validateMember($request);
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($data['hostel_id']);

        $payload = $this->memberPayload($data, $hostel);

        if ($request->hasFile('photo')) {
            $disk = config('filesystems.default');
            if ($member->photo_path) {
                Storage::disk($member->photo_disk ?: $disk)->delete($member->photo_path);
            }
            $payload['photo_path'] = $request->file('photo')->store('members', $disk);
            $payload['photo_disk'] = $disk;
        }

        $member->update($payload);

        return redirect()->route('owner.hostel.members')
            ->with('success', "Member '{$member->name}' updated.");
    }

    public function deleteMember(int $id)
    {
        $member = HostelMember::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))
            ->findOrFail($id);

        if ($member->photo_path) {
            Storage::disk($member->photo_disk ?: config('filesystems.default'))->delete($member->photo_path);
        }
        $member->delete();

        return back()->with('success', 'Member removed.');
    }

    private function validateMember(Request $request): array
    {
        return $request->validate([
            'hostel_id'       => 'required|integer',
            'room_id'         => 'nullable|integer',
            'room_number'     => 'nullable|string|max:50',
            'name'            => 'required|string|max:150',
            'age'             => 'nullable|integer|min:1|max:120',
            'email'           => 'nullable|email|max:150',
            'phone'           => 'nullable|string|max:20',
            'id_proof_type'   => 'nullable|in:aadhaar,pan,passport',
            'id_proof_number' => 'nullable|string|max:50',
            'place'           => 'nullable|string|max:150',
            'date_of_join'    => 'nullable|date',
            'date_of_left'    => 'nullable|date|after_or_equal:date_of_join',
            'monthly_rent'    => 'nullable|numeric|min:0',
            'rent_status'     => 'required|in:pending,advance_paid,fully_paid',
            'notes'           => 'nullable|string|max:1000',
            'photo'           => 'nullable|image|max:4096',
        ]);
    }

    /** Build the writable column set, making sure a chosen room actually belongs to the hostel. */
    private function memberPayload(array $data, Hostel $hostel): array
    {
        $roomId = null;
        if (!empty($data['room_id'])) {
            $roomId = Room::where('hostel_id', $hostel->id)->whereKey($data['room_id'])->value('id');
        }

        return [
            'hostel_id'       => $hostel->id,
            'room_id'         => $roomId,
            'room_number'     => $data['room_number'] ?? null,
            'name'            => $data['name'],
            'age'             => $data['age'] ?? null,
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'id_proof_type'   => $data['id_proof_type'] ?? null,
            'id_proof_number' => $data['id_proof_number'] ?? null,
            'place'           => $data['place'] ?? null,
            'date_of_join'    => $data['date_of_join'] ?? null,
            'date_of_left'    => $data['date_of_left'] ?? null,
            'monthly_rent'    => $data['monthly_rent'] ?? null,
            'rent_status'     => $data['rent_status'],
            'notes'           => $data['notes'] ?? null,
        ];
    }

    // ── ASSETS (room item counts) ──────────────────────────

    public function assetsPage(Request $request)
    {
        $hostelIds = $this->ownerHostelIds();
        $base      = RoomAsset::whereIn('hostel_id', $hostelIds);

        $stats = [
            'items' => (clone $base)->count(),
            'units' => (int) (clone $base)->sum('quantity'),
        ];

        $query = (clone $base)->with(['hostel', 'room']);

        $search = trim((string) $request->input('search', ''));
        if ($search !== '') {
            $query->where(function ($w) use ($search) {
                $w->where('item_name', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('hostel_id') && $hostelIds->contains((int) $request->hostel_id)) {
            $query->where('hostel_id', (int) $request->hostel_id);
        }
        if ($request->filled('room_id')) {
            $query->where('room_id', (int) $request->room_id);
        }

        $assets = $query->orderBy('hostel_id')->orderBy('room_number')->orderBy('item_name')
            ->paginate(15)->withQueryString();

        $hostels = Hostel::where('owner_id', auth()->id())
            ->with(['rooms' => fn($q) => $q->orderBy('name')])
            ->orderBy('name')->get();

        $pendingBookings = $this->sidebarPendingCount();

        return view('owner.hostel.assets', compact('assets', 'stats', 'hostels', 'pendingBookings'));
    }

    public function storeAsset(Request $request)
    {
        $data   = $this->validateAsset($request);
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($data['hostel_id']);

        RoomAsset::create($this->assetPayload($data, $hostel));

        return redirect()->route('owner.hostel.assets')
            ->with('success', "Asset '{$data['item_name']}' added.");
    }

    public function updateAsset(Request $request, int $id)
    {
        $asset = RoomAsset::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))
            ->findOrFail($id);

        $data   = $this->validateAsset($request);
        $hostel = Hostel::where('owner_id', auth()->id())->findOrFail($data['hostel_id']);

        $asset->update($this->assetPayload($data, $hostel));

        return redirect()->route('owner.hostel.assets')->with('success', 'Asset updated.');
    }

    public function deleteAsset(int $id)
    {
        $asset = RoomAsset::whereHas('hostel', fn($q) => $q->where('owner_id', auth()->id()))
            ->findOrFail($id);
        $asset->delete();

        return back()->with('success', 'Asset removed.');
    }

    private function validateAsset(Request $request): array
    {
        return $request->validate([
            'hostel_id'   => 'required|integer',
            'room_id'     => 'nullable|integer',
            'room_number' => 'nullable|string|max:50',
            'item_name'   => 'required|string|max:100',
            'quantity'    => 'required|integer|min:0|max:100000',
            'notes'       => 'nullable|string|max:255',
        ]);
    }

    private function assetPayload(array $data, Hostel $hostel): array
    {
        $roomId = null;
        if (!empty($data['room_id'])) {
            $roomId = Room::where('hostel_id', $hostel->id)->whereKey($data['room_id'])->value('id');
        }

        return [
            'hostel_id'   => $hostel->id,
            'room_id'     => $roomId,
            'room_number' => $data['room_number'] ?? null,
            'item_name'   => $data['item_name'],
            'quantity'    => $data['quantity'],
            'notes'       => $data['notes'] ?? null,
        ];
    }
}
