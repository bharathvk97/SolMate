<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerSubscription extends Model
{
    protected $fillable = ['user_id','plan_id','razorpay_order_id','razorpay_payment_id','razorpay_signature','amount_paid','payment_status','status','starts_at','expires_at'];
    protected $casts = ['starts_at'=>'datetime','expires_at'=>'datetime'];

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(OwnerSubscriptionPlan::class); }
}
