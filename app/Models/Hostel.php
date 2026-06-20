<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class Hostel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id','name','slug','description','address','city','state','pincode',
        'lat','lng','phone','email','website','gender_type','status','is_featured',
        'cover_image','has_wifi','has_ac','has_parking','has_laundry','has_cctv',
        'has_power_backup','has_gym','has_mess','has_security','curfew_time',
        'allow_guests','allow_smoking','allow_alcohol','house_rules',
        'average_rating','total_reviews','nearby_landmarks',
        'admin_reviewed_at','admin_rejection_reason',
    ];

    protected $casts = [
        'nearby_landmarks'  => 'array',
        'is_featured'       => 'boolean',
        'has_wifi'          => 'boolean',
        'has_ac'            => 'boolean',
        'has_parking'       => 'boolean',
        'has_laundry'       => 'boolean',
        'has_cctv'          => 'boolean',
        'has_power_backup'  => 'boolean',
        'has_gym'           => 'boolean',
        'has_mess'          => 'boolean',
        'has_security'      => 'boolean',
        'allow_guests'      => 'boolean',
        'allow_smoking'     => 'boolean',
        'allow_alcohol'     => 'boolean',
        'average_rating'    => 'float',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name).'-'.Str::random(5);
            }
        });
    }

    public function owner()       { return $this->belongsTo(User::class, 'owner_id'); }
    public function rooms()       { return $this->hasMany(Room::class); }
    public function images()      { return $this->hasMany(HostelImage::class)->orderBy('sort_order'); }
    public function coverImage()  { return $this->hasOne(HostelImage::class)->where('is_cover', true); }
    public function amenities()   { return $this->belongsToMany(Amenity::class, 'hostel_amenities'); }
    public function reviews()     { return $this->morphMany(Review::class, 'reviewable'); }
    public function favourites()  { return $this->morphMany(Favourite::class, 'favourable'); }
    public function bookings()    { return $this->hasMany(HostelBooking::class); }

    public function scopeActive($q)   { return $q->where('status', 'active'); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    public function getCoverImageUrlAttribute(): string
    {
        if ($this->cover_image) {
            return Storage::disk(config('filesystems.default'))->url($this->cover_image);
        }
        return asset('images/hostel-placeholder.jpg');
    }

    public function getDistanceAttribute() { return $this->attributes['distance'] ?? null; }

    public function updateRating(): void
    {
        $avg = $this->reviews()->where('is_hidden', false)->avg('rating') ?? 0;
        $count = $this->reviews()->where('is_hidden', false)->count();
        $this->update(['average_rating' => round($avg, 2), 'total_reviews' => $count]);
    }
}
