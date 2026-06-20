<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name','email','phone','password','role','status','avatar',
        'email_otp','email_otp_expires_at','email_verified_at',
        'phone_otp','phone_otp_expires_at','phone_verified_at',
        'identity_type','identity_number','identity_document_front',
        'identity_document_back','identity_status',
        'subscription_status','subscription_expires_at','last_subscription_reminder_at',
        'lat','lng','city','state','country','theme_preference',
    ];

    protected $hidden = ['password','remember_token','email_otp','phone_otp'];

    protected $casts = [
        'email_verified_at'       => 'datetime',
        'phone_verified_at'       => 'datetime',
        'email_otp_expires_at'    => 'datetime',
        'phone_otp_expires_at'    => 'datetime',
        'subscription_expires_at' => 'datetime',
        'password'                => 'hashed',
    ];

    // ── Scopes ──────────────────────────────────────────────
    public function scopeActive($q)        { return $q->where('status', 'active'); }
    public function scopeOwners($q)        { return $q->whereIn('role', ['hostel_owner','mess_owner']); }
    public function scopeHostelOwners($q)  { return $q->where('role', 'hostel_owner'); }
    public function scopeMessOwners($q)    { return $q->where('role', 'mess_owner'); }

    // ── Role Helpers ─────────────────────────────────────────
    public function isAdmin()       { return $this->role === 'admin'; }
    public function isHostelOwner() { return $this->role === 'hostel_owner'; }
    public function isMessOwner()   { return $this->role === 'mess_owner'; }
    public function isUser()        { return $this->role === 'user'; }
    public function isOwner()       { return in_array($this->role, ['hostel_owner','mess_owner']); }

    public function hasActiveSubscription(): bool
    {
        return $this->isOwner()
            && $this->subscription_status === 'active'
            && $this->subscription_expires_at
            && $this->subscription_expires_at->isFuture();
    }

    // ── Relationships ────────────────────────────────────────
    public function hostels()
    {
        return $this->hasMany(Hostel::class, 'owner_id');
    }

    public function messes()
    {
        return $this->hasMany(Mess::class, 'owner_id');
    }

    public function hostelBookings()
    {
        return $this->hasMany(HostelBooking::class);
    }

    public function messBookings()
    {
        return $this->hasMany(MessBooking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function favourites()
    {
        return $this->hasMany(Favourite::class);
    }

    public function ownerSubscriptions()
    {
        return $this->hasMany(OwnerSubscription::class);
    }

    public function activeSubscription()
    {
        return $this->hasOne(OwnerSubscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest();
    }

    // ── Avatar URL ───────────────────────────────────────────
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return \Storage::disk(config('filesystems.default'))->url($this->avatar);
        }
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=fff';
    }
}
