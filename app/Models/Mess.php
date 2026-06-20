<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Mess extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id','name','slug','description','address','city','state','pincode',
        'lat','lng','phone','email','food_type','status','is_featured',
        'has_delivery','has_tiffin','has_dine_in','cover_image','disk',
        'morning_open','morning_close','afternoon_open','afternoon_close',
        'evening_open','evening_close','night_open','night_close',
        'average_rating','total_reviews','admin_reviewed_at','admin_rejection_reason',
    ];
    protected $casts = [
        'is_featured'=>'boolean','has_delivery'=>'boolean',
        'has_tiffin'=>'boolean','has_dine_in'=>'boolean','average_rating'=>'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->slug)) $m->slug = Str::slug($m->name).'-'.Str::random(5);
        });
    }

    public function owner()             { return $this->belongsTo(User::class, 'owner_id'); }
    public function menus()             { return $this->hasMany(Menu::class); }
    public function images()            { return $this->hasMany(MessImage::class)->orderBy('sort_order'); }
    public function subscriptionPlans() { return $this->hasMany(MessSubscriptionPlan::class); }
    public function reviews()           { return $this->morphMany(Review::class, 'reviewable'); }
    public function favourites()        { return $this->morphMany(Favourite::class, 'favourable'); }
    public function bookings()          { return $this->hasMany(MessBooking::class); }

    public function scopeActive($q)   { return $q->where('status', 'active'); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) return Storage::disk($this->disk ?? config('filesystems.default'))->url($this->cover_image);
        return asset('images/mess-placeholder.jpg');
    }

    public function isSlotOpen(string $slot): bool
    {
        $now = now()->format('H:i:s');
        return match($slot) {
            'morning'   => $now >= $this->morning_open   && $now <= $this->morning_close,
            'afternoon' => $now >= $this->afternoon_open && $now <= $this->afternoon_close,
            'evening'   => $now >= $this->evening_open   && $now <= $this->evening_close,
            'night'     => $now >= $this->night_open     && $now <= $this->night_close,
            default     => false,
        };
    }

    public function updateRating(): void
    {
        $avg = $this->reviews()->where('is_hidden', false)->avg('rating') ?? 0;
        $count = $this->reviews()->where('is_hidden', false)->count();
        $this->update(['average_rating' => round($avg, 2), 'total_reviews' => $count]);
    }
}
