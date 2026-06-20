<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{User, Hostel, Mess, HostelBooking, MessBooking, Review, OwnerSubscription};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ── Dashboard Analytics ───────────────────────────────────
    public function dashboard(): JsonResponse
    {
        $stats = [
            'total_users'         => User::where('role','user')->count(),
            'total_hostel_owners' => User::where('role','hostel_owner')->count(),
            'total_mess_owners'   => User::where('role','mess_owner')->count(),
            'total_hostels'       => Hostel::count(),
            'active_hostels'      => Hostel::where('status','active')->count(),
            'pending_hostels'     => Hostel::where('status','pending')->count(),
            'total_messes'        => Mess::count(),
            'active_messes'       => Mess::where('status','active')->count(),
            'pending_messes'      => Mess::where('status','pending')->count(),
            'total_hostel_bookings'  => HostelBooking::count(),
            'total_mess_bookings'    => MessBooking::count(),
            'revenue_this_month'     => OwnerSubscription::where('payment_status','paid')->whereMonth('created_at',now()->month)->sum('amount_paid'),
            'bookings_this_month'    => HostelBooking::whereMonth('created_at',now()->month)->count() + MessBooking::whereMonth('created_at',now()->month)->count(),
            'pending_identity_verifications' => User::where('identity_status','pending')->count(),
        ];

        $monthlyRevenue = OwnerSubscription::where('payment_status','paid')
            ->selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(amount_paid) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')
            ->limit(12)->get();

        $userGrowth = User::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, COUNT(*) as total')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')
            ->limit(12)->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'stats'          => $stats,
                'monthly_revenue'=> $monthlyRevenue,
                'user_growth'    => $userGrowth,
            ],
        ]);
    }

    // ── User Management ───────────────────────────────────────
    public function users(Request $request): JsonResponse
    {
        $q = User::query();
        if ($request->role)   $q->where('role', $request->role);
        if ($request->status) $q->where('status', $request->status);
        if ($request->q)      $q->where(fn($sq)=>$sq->where('name','like','%'.$request->q.'%')->orWhere('email','like','%'.$request->q.'%')->orWhere('phone','like','%'.$request->q.'%'));
        if ($request->identity_status) $q->where('identity_status', $request->identity_status);

        return response()->json(['status'=>true,'data'=>$q->latest()->paginate(20)]);
    }

    public function updateUserStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status'=>'required|in:active,inactive,suspended']);
        $user = User::findOrFail($id);
        $user->update(['status'=>$request->status]);
        return response()->json(['status'=>true,'message'=>"User status updated to {$request->status}."]);
    }

    public function verifyIdentity(Request $request, int $id): JsonResponse
    {
        $request->validate(['status'=>'required|in:verified,rejected','rejection_reason'=>'nullable|string']);
        $user = User::findOrFail($id);
        $user->update(['identity_status'=>$request->status]);
        // TODO: send notification email
        return response()->json(['status'=>true,'message'=>'Identity status updated.']);
    }

    public function deleteUser(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        if ($user->isAdmin()) return response()->json(['status'=>false,'message'=>'Cannot delete admin.'], 403);
        $user->delete();
        return response()->json(['status'=>true,'message'=>'User deleted.']);
    }

    // ── Hostel Moderation ─────────────────────────────────────
    public function hostels(Request $request): JsonResponse
    {
        $q = Hostel::withTrashed()->with('owner:id,name,email');
        if ($request->status) $q->where('status',$request->status);
        if ($request->q)      $q->where('name','like','%'.$request->q.'%');
        return response()->json(['status'=>true,'data'=>$q->latest()->paginate(20)]);
    }

    public function updateHostelStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status'=>'required|in:active,inactive,rejected','rejection_reason'=>'nullable|string','is_featured'=>'nullable|boolean']);
        $hostel = Hostel::findOrFail($id);
        $data   = ['status'=>$request->status,'admin_reviewed_at'=>now()];
        if ($request->has('is_featured'))    $data['is_featured']           = $request->is_featured;
        if ($request->rejection_reason)      $data['admin_rejection_reason']= $request->rejection_reason;
        $hostel->update($data);
        return response()->json(['status'=>true,'message'=>"Hostel {$request->status}."]);
    }

    // ── Mess Moderation ───────────────────────────────────────
    public function messes(Request $request): JsonResponse
    {
        $q = Mess::withTrashed()->with('owner:id,name,email');
        if ($request->status) $q->where('status',$request->status);
        if ($request->q)      $q->where('name','like','%'.$request->q.'%');
        return response()->json(['status'=>true,'data'=>$q->latest()->paginate(20)]);
    }

    public function updateMessStatus(Request $request, int $id): JsonResponse
    {
        $request->validate(['status'=>'required|in:active,inactive,rejected']);
        $mess = Mess::findOrFail($id);
        $mess->update(['status'=>$request->status,'admin_reviewed_at'=>now(),'admin_rejection_reason'=>$request->rejection_reason]);
        return response()->json(['status'=>true,'message'=>"Mess {$request->status}."]);
    }

    // ── Review Moderation ─────────────────────────────────────
    public function reviews(Request $request): JsonResponse
    {
        $q = Review::with(['user:id,name','reviewable'])->latest();
        if ($request->is_hidden !== null) $q->where('is_hidden',(bool)$request->is_hidden);
        return response()->json(['status'=>true,'data'=>$q->paginate(20)]);
    }

    public function toggleReviewVisibility(int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update(['is_hidden'=>!$review->is_hidden]);
        $review->reviewable?->updateRating();
        return response()->json(['status'=>true,'message'=>$review->is_hidden?'Review hidden.':'Review restored.']);
    }

    // ── Subscriptions ─────────────────────────────────────────
    public function subscriptions(Request $request): JsonResponse
    {
        $q = OwnerSubscription::with(['user:id,name,email,role','plan:id,name,price']);
        if ($request->status)       $q->where('status',$request->status);
        if ($request->payment_status)$q->where('payment_status',$request->payment_status);
        return response()->json(['status'=>true,'data'=>$q->latest()->paginate(20)]);
    }

    public function expireOwnerAccounts(): JsonResponse
    {
        $expired = User::whereIn('role',['hostel_owner','mess_owner'])
            ->where('subscription_status','active')
            ->where('subscription_expires_at','<',now())
            ->update(['subscription_status'=>'expired','status'=>'inactive']);

        return response()->json(['status'=>true,'message'=>"{$expired} owner accounts set to inactive due to expired subscriptions."]);
    }

    // ── Platform Stats ────────────────────────────────────────
    public function bookings(Request $request): JsonResponse
    {
        $q = HostelBooking::with(['user:id,name','hostel:id,name','room:id,name'])->latest();
        if ($request->status) $q->where('status',$request->status);
        return response()->json(['status'=>true,'data'=>$q->paginate(20)]);
    }
}
