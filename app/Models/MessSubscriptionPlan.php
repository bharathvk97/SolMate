<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessSubscriptionPlan extends Model
{
    protected $fillable = ['mess_id','name','slots','duration_days','price','description','is_active'];
    protected $casts = ['slots'=>'array','is_active'=>'boolean'];

    public function mess()     { return $this->belongsTo(Mess::class); }
    public function bookings() { return $this->hasMany(MessBooking::class, 'plan_id'); }
}
