<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MessBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_ref','user_id','mess_id','plan_id','selected_slots',
        'start_date','end_date','amount','status','auto_renew',
        'razorpay_order_id','razorpay_payment_id','razorpay_signature',
        'payment_status','paused_at','resumed_at',
    ];
    protected $casts = [
        'selected_slots'=>'array','start_date'=>'date','end_date'=>'date',
        'auto_renew'=>'boolean','paused_at'=>'datetime','resumed_at'=>'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->booking_ref)) $m->booking_ref = 'MB-'.strtoupper(Str::random(8));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function mess() { return $this->belongsTo(Mess::class); }
    public function plan() { return $this->belongsTo(MessSubscriptionPlan::class); }
}
