<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{Hostel, HostelImage, Room, RoomImage, Mess, MessImage, Menu, MenuImage, MessSubscriptionPlan};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// ═══════════════════════════════════════════════════════════
// OwnerHostelController
// ═══════════════════════════════════════════════════════════
class OwnerHostelController extends Controller
{
    private function disk(): string { return config('filesystems.default', 'local'); }

    public function index(Request $request): JsonResponse
    {
        $hostels = Hostel::where('owner_id',$request->user()->id)->with(['images','rooms'])->latest()->get();
        return response()->json(['status'=>true,'data'=>$hostels]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'description'   => 'nullable|string',
            'address'       => 'required|string',
            'city'          => 'required|string',
            'state'         => 'required|string',
            'pincode'       => 'required|string|max:10',
            'lat'           => 'required|numeric',
            'lng'           => 'required|numeric',
            'phone'         => 'nullable|string|max:15',
            'gender_type'   => 'required|in:boys,girls,coed',
            'curfew_time'   => 'nullable|string',
            'has_wifi'      => 'boolean',
            'has_ac'        => 'boolean',
            'has_cctv'      => 'boolean',
            'has_parking'   => 'boolean',
            'has_laundry'   => 'boolean',
            'has_power_backup'=>'boolean',
            'has_gym'       => 'boolean',
            'has_mess'      => 'boolean',
            'has_security'  => 'boolean',
            'allow_guests'  => 'boolean',
            'house_rules'   => 'nullable|string',
            'cover_image'   => 'nullable|image|max:5120',
        ]);

        $data = $request->except('cover_image','_method');
        $data['owner_id'] = $request->user()->id;
        $data['slug']     = Str::slug($request->name).'-'.Str::random(5);
        $data['status']   = 'pending';

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('hostels/covers', $this->disk());
        }

        $hostel = Hostel::create($data);
        return response()->json(['status'=>true,'message'=>'Hostel created. Pending admin approval.','data'=>$hostel], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $hostel = Hostel::where('owner_id',$request->user()->id)->findOrFail($id);
        $data   = $request->except('cover_image','_method','owner_id');

        if ($request->hasFile('cover_image')) {
            if ($hostel->cover_image) Storage::disk($this->disk())->delete($hostel->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('hostels/covers', $this->disk());
        }

        $hostel->update($data);
        return response()->json(['status'=>true,'message'=>'Hostel updated.','data'=>$hostel->fresh()]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $hostel = Hostel::where('owner_id',$request->user()->id)->findOrFail($id);
        $hostel->delete();
        return response()->json(['status'=>true,'message'=>'Hostel deleted.']);
    }

    // ── Images ───────────────────────────────────────────────
    public function uploadImages(Request $request, int $hostelId): JsonResponse
    {
        $request->validate(['images'   =>'required|array','images.*'=>'image|max:5120','is_cover'=>'nullable|integer']);
        $hostel = Hostel::where('owner_id',$request->user()->id)->findOrFail($hostelId);
        $disk   = $this->disk();
        $saved  = [];

        foreach ($request->file('images') as $idx => $file) {
            $path = $file->store("hostels/{$hostelId}/images", $disk);
            $isCover = ($request->is_cover === $idx) || ($idx === 0 && !$hostel->images()->where('is_cover',true)->exists());
            if ($isCover) {
                $hostel->images()->update(['is_cover'=>false]);
                if (!$hostel->cover_image) $hostel->update(['cover_image'=>$path]);
            }
            $saved[] = HostelImage::create(['hostel_id'=>$hostelId,'image_path'=>$path,'disk'=>$disk,'is_cover'=>$isCover,'sort_order'=>$hostel->images()->count()]);
        }

        return response()->json(['status'=>true,'message'=>count($saved).' image(s) uploaded.','data'=>collect($saved)->map(fn($i)=>['id'=>$i->id,'url'=>$i->url,'is_cover'=>$i->is_cover])]);
    }

    public function deleteImage(Request $request, int $imageId): JsonResponse
    {
        $image  = HostelImage::findOrFail($imageId);
        $hostel = Hostel::where('owner_id',$request->user()->id)->findOrFail($image->hostel_id);
        Storage::disk($image->disk)->delete($image->image_path);
        $image->delete();
        return response()->json(['status'=>true,'message'=>'Image deleted.']);
    }

    // ── Rooms ────────────────────────────────────────────────
    public function storeRoom(Request $request, int $hostelId): JsonResponse
    {
        $hostel = Hostel::where('owner_id',$request->user()->id)->findOrFail($hostelId);
        $request->validate([
            'name'                 => 'required|string',
            'type'                 => 'required|in:single,double,triple,shared,dormitory',
            'is_ac'                => 'boolean',
            'price_per_month'      => 'required|numeric|min:0',
            'price_per_day'        => 'nullable|numeric|min:0',
            'security_deposit'     => 'numeric|min:0',
            'capacity'             => 'integer|min:1',
            'total_count'          => 'required|integer|min:1',
            'has_attached_bathroom'=> 'boolean',
            'has_balcony'          => 'boolean',
            'has_study_table'      => 'boolean',
            'has_wardrobe'         => 'boolean',
        ]);
        $data = $request->all();
        $data['hostel_id']       = $hostelId;
        $data['available_count'] = $request->get('total_count');
        $room = Room::create($data);
        return response()->json(['status'=>true,'message'=>'Room added.','data'=>$room], 201);
    }

    public function updateRoom(Request $request, int $roomId): JsonResponse
    {
        $room = Room::whereHas('hostel',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($roomId);
        $room->update($request->except('hostel_id'));
        return response()->json(['status'=>true,'data'=>$room->fresh()]);
    }

    public function deleteRoom(Request $request, int $roomId): JsonResponse
    {
        $room = Room::whereHas('hostel',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($roomId);
        $room->delete();
        return response()->json(['status'=>true,'message'=>'Room deleted.']);
    }

    public function uploadRoomImages(Request $request, int $roomId): JsonResponse
    {
        $room = Room::whereHas('hostel',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($roomId);
        $request->validate(['images'=>'required|array','images.*'=>'image|max:5120']);
        $disk  = $this->disk();
        $saved = [];
        foreach ($request->file('images') as $idx => $file) {
            $path  = $file->store("rooms/{$roomId}/images", $disk);
            $saved[] = RoomImage::create(['room_id'=>$roomId,'image_path'=>$path,'disk'=>$disk,'is_cover'=>$idx===0 && !$room->images()->exists(),'sort_order'=>$room->images()->count()]);
        }
        return response()->json(['status'=>true,'data'=>collect($saved)->map(fn($i)=>['id'=>$i->id,'url'=>$i->url])]);
    }

    // ── Owner Bookings ────────────────────────────────────────
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = \App\Models\HostelBooking::whereHas('hostel',fn($q)=>$q->where('owner_id',$request->user()->id))
            ->with(['user:id,name,phone,email','hostel:id,name','room:id,name,type'])
            ->when($request->status,fn($q)=>$q->where('status',$request->status))
            ->latest()->paginate(15);
        return response()->json(['status'=>true,'data'=>$bookings]);
    }

    public function updateBookingStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status'=>'required|in:confirmed,rejected','owner_note'=>'nullable|string']);
        $booking = \App\Models\HostelBooking::whereHas('hostel',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($id);
        $booking->update(['status'=>$request->status,'owner_note'=>$request->owner_note]);
        return response()->json(['status'=>true,'message'=>'Booking status updated.']);
    }

    public function replyToReview(Request $request, int $reviewId): JsonResponse
    {
        $request->validate(['reply'=>'required|string|max:1000']);
        $review = \App\Models\Review::whereHasMorph('reviewable',Hostel::class,fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($reviewId);
        $review->update(['owner_reply'=>$request->reply,'owner_replied_at'=>now()]);
        return response()->json(['status'=>true,'message'=>'Reply posted.']);
    }
}
