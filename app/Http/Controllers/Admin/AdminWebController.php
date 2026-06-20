<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Hostel;
use App\Models\Mess;
use App\Models\HostelBooking;
use App\Models\MessBooking;
use App\Models\Review;
use App\Models\OwnerSubscription;
use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard', [
            'stats' => [
                'total_users'                    => User::where('role','user')->count(),
                'total_hostel_owners'            => User::where('role','hostel_owner')->count(),
                'total_mess_owners'              => User::where('role','mess_owner')->count(),
                'active_hostels'                 => Hostel::where('status','active')->count(),
                'active_messes'                  => Mess::where('status','active')->count(),
                'revenue_this_month'             => OwnerSubscription::where('payment_status','paid')->whereMonth('created_at',now()->month)->sum('amount_paid'),
                'pending_hostels'                => Hostel::where('status','pending')->count(),
                'pending_identity_verifications' => User::where('identity_status','pending')->count(),
            ],
            'pendingHostelsList'   => Hostel::where('status','pending')->with('owner:id,name')->latest()->limit(5)->get(),
            'recentSubscriptions'  => OwnerSubscription::with(['user:id,name,role','plan:id,name'])->latest()->limit(8)->get(),
            'monthlyRevenue'       => OwnerSubscription::where('payment_status','paid')->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount_paid) as total')->groupByRaw('YEAR(created_at), MONTH(created_at)')->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')->limit(12)->get(),
            'userGrowth'           => User::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as total')->groupByRaw('YEAR(created_at), MONTH(created_at)')->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')->limit(12)->get(),
        ]);
    }

    public function users(Request $request)
    {
        $q = User::query();
        if ($request->role)   $q->where('role',$request->role);
        if ($request->status) $q->where('status',$request->status);
        if ($request->q)      $q->where(fn($sq)=>$sq->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%'));
        return view('admin.users.index', ['users'=>$q->latest()->paginate(25)]);
    }

    public function updateUserStatus(Request $request, int $id)
    {
        User::findOrFail($id)->update(['status'=>$request->status]);
        return back()->with('success','User status updated.');
    }

    public function identityVerification(Request $request)
    {
        $users = User::where('identity_status','pending')->with([])->latest()->paginate(20);
        return view('admin.users.identity', compact('users'));
    }

    public function verifyIdentity(Request $request, int $id)
    {
        User::findOrFail($id)->update(['identity_status'=>$request->status]);
        return back()->with('success','Identity '.($request->status==='verified'?'verified':'rejected').'.');
    }

    public function hostels(Request $request)
    {
        $q = Hostel::withTrashed()->with('owner:id,name');
        if ($request->status) $q->where('status',$request->status);
        return view('admin.hostels.index', ['hostels'=>$q->latest()->paginate(20)]);
    }

    public function updateHostelStatus(Request $request, int $id)
    {
        Hostel::findOrFail($id)->update(['status'=>$request->status,'admin_reviewed_at'=>now(),'admin_rejection_reason'=>$request->rejection_reason,'is_featured'=>$request->boolean('is_featured')]);
        return back()->with('success','Hostel '.$request->status.'.');
    }

    public function messes(Request $request)
    {
        $q = Mess::withTrashed()->with('owner:id,name');
        if ($request->status) $q->where('status',$request->status);
        return view('admin.messes.index', ['messes'=>$q->latest()->paginate(20)]);
    }

    public function updateMessStatus(Request $request, int $id)
    {
        Mess::findOrFail($id)->update(['status'=>$request->status,'admin_reviewed_at'=>now()]);
        return back()->with('success','Mess '.$request->status.'.');
    }

    public function subscriptions(Request $request)
    {
        $subs = OwnerSubscription::with(['user:id,name,email,role','plan:id,name,price'])->latest()->paginate(25);
        return view('admin.subscriptions', compact('subs'));
    }

    public function expireAccounts()
    {
        $expired = User::whereIn('role',['hostel_owner','mess_owner'])->where('subscription_status','active')->where('subscription_expires_at','<',now())->update(['subscription_status'=>'expired','status'=>'inactive']);
        return back()->with('success',"{$expired} expired owner accounts deactivated.");
    }

    public function reviews(Request $request)
    {
        $reviews = Review::with(['user:id,name','reviewable'])->when($request->has('hidden'),fn($q)=>$q->where('is_hidden',true))->latest()->paginate(25);
        return view('admin.reviews', compact('reviews'));
    }

    public function toggleReview(int $id)
    {
        $r = Review::findOrFail($id);
        $r->update(['is_hidden'=>!$r->is_hidden]);
        $r->reviewable?->updateRating();
        return back()->with('success',$r->is_hidden?'Review hidden.':'Review shown.');
    }

    public function bookings(Request $request)
    {
        $bookings = HostelBooking::with(['user:id,name','hostel:id,name','room:id,name'])->latest()->paginate(25);
        return view('admin.bookings', compact('bookings'));
    }
}
