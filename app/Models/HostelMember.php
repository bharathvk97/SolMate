<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HostelMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hostel_id', 'room_id', 'room_number', 'name', 'age', 'email', 'phone',
        'id_proof_type', 'id_proof_number', 'place', 'photo_path', 'photo_disk',
        'date_of_join', 'date_of_left', 'monthly_rent', 'rent_status', 'notes',
    ];

    protected $casts = [
        'date_of_join' => 'date',
        'date_of_left' => 'date',
        'monthly_rent' => 'decimal:2',
    ];

    protected $appends = ['photo_url'];

    public function hostel() { return $this->belongsTo(Hostel::class); }
    public function room()   { return $this->belongsTo(Room::class); }

    /** Room label shown in lists — free-text number first, else the linked room's name. */
    public function getRoomLabelAttribute(): ?string
    {
        return $this->room_number ?: $this->room?->name;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
