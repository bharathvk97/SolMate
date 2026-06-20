<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hostel_id','name','type','is_ac','price_per_month','price_per_day',
        'security_deposit','capacity','total_count','available_count',
        'has_attached_bathroom','has_balcony','has_study_table','has_wardrobe',
        'has_tv','has_fridge','floor_number','description','is_available',
    ];
    protected $casts = [
        'is_ac'=>'boolean','has_attached_bathroom'=>'boolean','has_balcony'=>'boolean',
        'has_study_table'=>'boolean','has_wardrobe'=>'boolean','has_tv'=>'boolean',
        'has_fridge'=>'boolean','is_available'=>'boolean',
    ];

    public function hostel()   { return $this->belongsTo(Hostel::class); }
    public function images()   { return $this->hasMany(RoomImage::class)->orderBy('sort_order'); }
    public function bookings() { return $this->hasMany(HostelBooking::class); }

    public function scopeAvailable($q) { return $q->where('is_available', true)->where('available_count', '>', 0); }
}
