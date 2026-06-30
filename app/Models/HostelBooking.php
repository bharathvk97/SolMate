<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HostelBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_ref','user_id','hostel_id','room_id','check_in','check_out',
        'occupants','monthly_rate','security_deposit','total_amount','status',
        'user_note','owner_note','razorpay_order_id','razorpay_payment_id',
        'razorpay_signature','payment_status','rent_status','confirmed_at','cancelled_at','cancellation_reason',
    ];
    protected $casts = ['check_in'=>'date','check_out'=>'date','confirmed_at'=>'datetime','cancelled_at'=>'datetime'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->booking_ref)) $m->booking_ref = 'HB-'.strtoupper(Str::random(8));
        });
    }

    public function user()   { return $this->belongsTo(User::class); }
    public function hostel() { return $this->belongsTo(Hostel::class); }
    public function room()   { return $this->belongsTo(Room::class); }
}
