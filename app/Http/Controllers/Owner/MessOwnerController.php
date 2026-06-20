<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Mess;
use App\Models\MessBooking;
use App\Models\Menu;
use Illuminate\Http\Request;

class MessOwnerController extends Controller
{
    public function dashboard()
    {
        $user    = auth()->user();
        $messIds = Mess::where('owner_id',$user->id)->pluck('id');
        return view('owner.mess.dashboard', [
            'messes'        => Mess::where('owner_id',$user->id)->with(['images','menus'])->latest()->get(),
            'recentBookings'=> MessBooking::whereIn('mess_id',$messIds)->with(['user','mess','plan'])->latest()->limit(5)->get(),
            'stats' => [
                'total_messes'      => Mess::where('owner_id',$user->id)->count(),
                'active_messes'     => Mess::where('owner_id',$user->id)->where('status','active')->count(),
                'total_subscribers' => MessBooking::whereIn('mess_id',$messIds)->where('payment_status','paid')->count(),
                'total_menus'       => Menu::whereIn('mess_id',$messIds)->count(),
            ],
        ]);
    }

    public function toggleMenu(int $menuId)
    {
        $menu = Menu::whereHas('mess',fn($q)=>$q->where('owner_id',auth()->id()))->findOrFail($menuId);
        $menu->update(['status' => $menu->status==='open' ? 'closed' : 'open']);
        return back()->with('success',"Menu slot marked as {$menu->status}.");
    }
}
