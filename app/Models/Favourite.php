<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favourite extends Model
{
    protected $fillable = ['user_id','favourable_type','favourable_id'];

    public function user()       { return $this->belongsTo(User::class); }
    public function favourable() { return $this->morphTo(); }
}
