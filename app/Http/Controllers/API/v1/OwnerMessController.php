<?php
namespace App\Http\Controllers\API\v1;
use App\Http\Controllers\Controller;

class OwnerMessController extends Controller
{
    private function disk(): string { return config('filesystems.default', 'local'); }

    public function index(Request $request): JsonResponse
    {
        $messes = Mess::where('owner_id',$request->user()->id)->with(['images','menus','subscriptionPlans'])->latest()->get();
        return response()->json(['status'=>true,'data'=>$messes]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'description'    => 'nullable|string',
            'address'        => 'required|string',
            'city'           => 'required|string',
            'state'          => 'required|string',
            'pincode'        => 'required|string',
            'lat'            => 'required|numeric',
            'lng'            => 'required|numeric',
            'phone'          => 'nullable|string',
            'food_type'      => 'required|in:veg,non_veg,both',
            'has_delivery'   => 'boolean',
            'has_tiffin'     => 'boolean',
            'morning_open'   => 'nullable|date_format:H:i',
            'morning_close'  => 'nullable|date_format:H:i',
            'afternoon_open' => 'nullable|date_format:H:i',
            'afternoon_close'=> 'nullable|date_format:H:i',
            'evening_open'   => 'nullable|date_format:H:i',
            'evening_close'  => 'nullable|date_format:H:i',
            'night_open'     => 'nullable|date_format:H:i',
            'night_close'    => 'nullable|date_format:H:i',
        ]);
        $data = $request->except('cover_image','_method');
        $data['owner_id'] = $request->user()->id;
        $data['status']   = 'pending';

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('messes/covers', $this->disk());
            $data['disk']        = $this->disk();
        }
        $mess = Mess::create($data);
        return response()->json(['status'=>true,'message'=>'Mess created. Pending admin approval.','data'=>$mess], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $mess = Mess::where('owner_id',$request->user()->id)->findOrFail($id);
        $data = $request->except('cover_image','_method','owner_id');
        if ($request->hasFile('cover_image')) {
            if ($mess->cover_image) Storage::disk($mess->disk)->delete($mess->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('messes/covers', $this->disk());
            $data['disk']        = $this->disk();
        }
        $mess->update($data);
        return response()->json(['status'=>true,'data'=>$mess->fresh()]);
    }

    public function uploadImages(Request $request, int $messId): JsonResponse
    {
        $request->validate(['images'=>'required|array','images.*'=>'image|max:5120']);
        $mess  = Mess::where('owner_id',$request->user()->id)->findOrFail($messId);
        $disk  = $this->disk();
        $saved = [];
        foreach ($request->file('images') as $idx => $file) {
            $path    = $file->store("messes/{$messId}/images", $disk);
            $isCover = $idx === 0 && !$mess->images()->where('is_cover',true)->exists();
            $saved[] = MessImage::create(['mess_id'=>$messId,'image_path'=>$path,'disk'=>$disk,'is_cover'=>$isCover,'sort_order'=>$mess->images()->count()]);
        }
        return response()->json(['status'=>true,'data'=>collect($saved)->map(fn($i)=>['id'=>$i->id,'url'=>$i->url])]);
    }

    // ── Menus ────────────────────────────────────────────────
    public function storeMenu(Request $request, int $messId): JsonResponse
    {
        $mess = Mess::where('owner_id',$request->user()->id)->findOrFail($messId);
        $request->validate([
            'slot'         => 'required|in:morning,afternoon,evening,night',
            'title'        => 'nullable|string',
            'items'        => 'required|array|min:1',
            'items.*.name' => 'required|string',
            'items.*.qty'  => 'nullable|string',
            'price'        => 'required|numeric|min:0',
            'is_available' => 'boolean',
            'status'       => 'in:open,closed',
        ]);
        $menu = Menu::create([
            'mess_id'      => $messId,
            'slot'         => $request->slot,
            'title'        => $request->title,
            'items'        => $request->items,
            'price'        => $request->price,
            'is_available' => $request->get('is_available',true),
            'status'       => $request->get('status','open'),
            'notes'        => $request->notes,
        ]);
        return response()->json(['status'=>true,'data'=>$menu], 201);
    }

    public function updateMenu(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::whereHas('mess',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($menuId);
        $menu->update($request->except('mess_id'));
        return response()->json(['status'=>true,'data'=>$menu->fresh()]);
    }

    public function toggleMenuStatus(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::whereHas('mess',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($menuId);
        $menu->update(['status'=>$menu->status==='open'?'closed':'open']);
        return response()->json(['status'=>true,'message'=>"Menu slot marked as {$menu->status}.",'data'=>['status'=>$menu->status]]);
    }

    public function uploadMenuImage(Request $request, int $menuId): JsonResponse
    {
        $request->validate(['image'=>'required|image|max:5120']);
        $menu  = Menu::whereHas('mess',fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($menuId);
        $disk  = $this->disk();
        $path  = $request->file('image')->store("menus/{$menuId}", $disk);
        $image = MenuImage::create(['menu_id'=>$menuId,'image_path'=>$path,'disk'=>$disk]);
        return response()->json(['status'=>true,'data'=>['id'=>$image->id,'url'=>$image->url]]);
    }

    // ── Subscription Plans ───────────────────────────────────
    public function storePlan(Request $request, int $messId): JsonResponse
    {
        $mess = Mess::where('owner_id',$request->user()->id)->findOrFail($messId);
        $request->validate(['name'=>'required|string','slots'=>'required|array','slots.*'=>'in:morning,afternoon,evening,night','duration_days'=>'required|integer|min:1','price'=>'required|numeric|min:0']);
        $plan = MessSubscriptionPlan::create(['mess_id'=>$messId,...$request->only('name','slots','duration_days','price','description'),'is_active'=>true]);
        return response()->json(['status'=>true,'data'=>$plan], 201);
    }

    public function myBookings(Request $request): JsonResponse
    {
        $bookings = \App\Models\MessBooking::whereHas('mess',fn($q)=>$q->where('owner_id',$request->user()->id))
            ->with(['user:id,name,phone','mess:id,name','plan:id,name'])
            ->latest()->paginate(15);
        return response()->json(['status'=>true,'data'=>$bookings]);
    }

    public function replyToReview(Request $request, int $reviewId): JsonResponse
    {
        $request->validate(['reply'=>'required|string|max:1000']);
        $review = \App\Models\Review::whereHasMorph('reviewable',Mess::class,fn($q)=>$q->where('owner_id',$request->user()->id))->findOrFail($reviewId);
        $review->update(['owner_reply'=>$request->reply,'owner_replied_at'=>now()]);
        return response()->json(['status'=>true,'message'=>'Reply posted.']);
    }
}
