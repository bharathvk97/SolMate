<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MessMember extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mess_id', 'name', 'phone', 'address', 'age', 'location', 'gender',
        'id_proof_number', 'photo_path', 'photo_disk', 'join_date',
        'monthly_fee', 'payment_status', 'notes',
    ];

    protected $casts = [
        'join_date'   => 'date',
        'monthly_fee' => 'decimal:2',
    ];

    protected $appends = ['photo_url'];

    public function mess() { return $this->belongsTo(Mess::class); }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }
}
