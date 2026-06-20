<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\OwnerSubscriptionPlan;
use App\Models\OwnerSubscription;

class SubscriptionController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $plans = OwnerSubscriptionPlan::where('is_active',true)
            ->where(fn($q)=>$q->where('owner_type',$user->role)->orWhere('owner_type','both'))
            ->get();
        $history = OwnerSubscription::where('user_id',$user->id)->with('plan')->latest()->get();
        return view('owner.subscription', compact('plans','history'));
    }
}
