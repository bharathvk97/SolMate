<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','reviewable_type','reviewable_id','rating',
        'cleanliness_rating','food_rating','value_rating','staff_rating','location_rating',
        'body','is_verified','is_hidden','helpful_count','owner_reply','owner_replied_at',
    ];
    protected $casts = ['is_verified'=>'boolean','is_hidden'=>'boolean','owner_replied_at'=>'datetime'];

    public function user()       { return $this->belongsTo(User::class); }
    public function reviewable() { return $this->morphTo(); }
}
