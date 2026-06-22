<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['mess_id', 'slot', 'title', 'items', 'price', 'notes', 'status', 'is_available'];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function mess()   { return $this->belongsTo(Mess::class); }
    public function images() { return $this->hasMany(MenuImage::class); }

    // Always return items as array regardless of how it's stored
    public function getItemsAttribute($value): array
    {
        if (is_array($value)) return $value;
        if (empty($value))    return [];

        $decoded = json_decode($value, true);

        // Handle double-encoded JSON
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}