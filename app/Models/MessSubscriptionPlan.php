<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessSubscriptionPlan extends Model
{
    protected $fillable = ['mess_id', 'name', 'slots', 'duration_days', 'price', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function mess() { return $this->belongsTo(Mess::class); }

    // Always return slots as array
    public function getSlotsAttribute($value): array
    {
        if (is_array($value))  return $value;
        if (empty($value))     return [];
        $decoded = json_decode($value, true);
        if (is_string($decoded)) $decoded = json_decode($decoded, true);
        return is_array($decoded) ? $decoded : [];
    }
}