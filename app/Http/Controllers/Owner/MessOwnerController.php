<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Mess;
use App\Models\MessBooking;
use App\Models\MessImage;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MessOwnerController extends Controller
{
    public function dashboard()
    {
        $user    = auth()->user();
        $messIds = Mess::where('owner_id', $user->id)->pluck('id');
        return view('owner.mess.dashboard', [
            'messes'         => Mess::where('owner_id', $user->id)->with(['images', 'menus'])->latest()->get(),
            'recentBookings' => MessBooking::whereIn('mess_id', $messIds)->with(['user', 'mess', 'plan'])->latest()->limit(5)->get(),
            'pendingBookings'=> 0,
            'stats' => [
                'total_messes'      => Mess::where('owner_id', $user->id)->count(),
                'active_messes'     => Mess::where('owner_id', $user->id)->where('status', 'active')->count(),
                'total_subscribers' => MessBooking::whereIn('mess_id', $messIds)->where('payment_status', 'paid')->count(),
                'total_menus'       => Menu::whereIn('mess_id', $messIds)->count(),
            ],
        ]);
    }

    public function createForm()
    {
        return view('owner.mess.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:200',
            'food_type' => 'required|in:veg,non_veg,both',
            'address'   => 'required|string',
            'city'      => 'required|string',
            'state'     => 'required|string',
        ]);

        $user = auth()->user();
        $disk = config('filesystems.default');

        $mess = Mess::create([
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
            'food_type'       => $request->food_type,
            'has_delivery'    => $request->boolean('has_delivery'),
            'is_pure_veg'     => $request->boolean('is_pure_veg'),
            'morning_open'    => $request->morning_enabled  ? $request->morning_open   : null,
            'morning_close'   => $request->morning_enabled  ? $request->morning_close  : null,
            'afternoon_open'  => $request->afternoon_enabled? $request->afternoon_open  : null,
            'afternoon_close' => $request->afternoon_enabled? $request->afternoon_close : null,
            'evening_open'    => $request->evening_enabled  ? $request->evening_open   : null,
            'evening_close'   => $request->evening_enabled  ? $request->evening_close  : null,
            'night_open'      => $request->night_enabled    ? $request->night_open     : null,
            'night_close'     => $request->night_enabled    ? $request->night_close    : null,
            'status'          => 'pending',
        ]);

        if ($request->hasFile('images')) {
            $isCover = true;
            foreach ($request->file('images') as $img) {
                $path = $img->store('messes/' . $mess->id, $disk);
                MessImage::create([
                    'mess_id'    => $mess->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => $isCover,
                    'sort_order' => 0,
                ]);
                $isCover = false;
            }
        }

        return redirect()->route('owner.mess.dashboard')
            ->with('success', 'Mess submitted for review! We will activate it shortly.');
    }

    public function editForm(int $id)
    {
        $mess = Mess::where('owner_id', auth()->id())->with(['images', 'menus'])->findOrFail($id);
        return view('owner.mess.edit', compact('mess'));
    }

    public function update(Request $request, int $id)
    {
        $mess = Mess::where('owner_id', auth()->id())->findOrFail($id);
        $disk = config('filesystems.default');

        $mess->update([
            'name'            => $request->name,
            'description'     => $request->description,
            'address'         => $request->address,
            'city'            => $request->city,
            'state'           => $request->state,
            'pincode'         => $request->pincode ?: $mess->pincode,
            'lat'             => $request->latitude  ?: $mess->lat,
            'lng'             => $request->longitude ?: $mess->lng,
            'phone'           => $request->phone,
            'food_type'       => $request->food_type,
            'has_delivery'    => $request->boolean('has_delivery'),
            'is_pure_veg'     => $request->boolean('is_pure_veg'),
            'morning_open'    => $request->morning_open,
            'morning_close'   => $request->morning_close,
            'afternoon_open'  => $request->afternoon_open,
            'afternoon_close' => $request->afternoon_close,
            'evening_open'    => $request->evening_open,
            'evening_close'   => $request->evening_close,
            'night_open'      => $request->night_open,
            'night_close'     => $request->night_close,
        ]);

        if ($request->hasFile('images')) {
            $hasCover = $mess->images()->where('is_cover', true)->exists();
            foreach ($request->file('images') as $img) {
                $path = $img->store('messes/' . $mess->id, $disk);
                MessImage::create([
                    'mess_id'    => $mess->id,
                    'image_path' => $path,
                    'disk'       => $disk,
                    'is_cover'   => !$hasCover,
                    'sort_order' => 0,
                ]);
                $hasCover = true;
            }
        }

        return redirect()->route('owner.mess.dashboard')
            ->with('success', 'Mess updated successfully!');
    }

    public function toggleMenu(int $menuId)
    {
        $menu = Menu::whereHas('mess', fn($q) => $q->where('owner_id', auth()->id()))->findOrFail($menuId);
        $menu->update(['status' => $menu->status === 'open' ? 'closed' : 'open']);
        return back()->with('success', "Menu slot marked as {$menu->status}.");
    }

    public function bookingsPage(Request $request)
    {
        $messIds  = Mess::where('owner_id', auth()->id())->pluck('id');
        $bookings = MessBooking::whereIn('mess_id', $messIds)
            ->with(['user', 'mess', 'plan'])->latest()->paginate(20);
        return view('owner.mess.bookings', compact('bookings'));
    }
}
