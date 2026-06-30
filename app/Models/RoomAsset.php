<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomAsset extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'hostel_id', 'room_id', 'room_number', 'item_name', 'quantity', 'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function hostel() { return $this->belongsTo(Hostel::class); }
    public function room()   { return $this->belongsTo(Room::class); }

    public function getRoomLabelAttribute(): ?string
    {
        return $this->room_number ?: $this->room?->name;
    }
}
