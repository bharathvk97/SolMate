<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = ['name','icon','category'];

    public function hostels() { return $this->belongsToMany(Hostel::class, 'hostel_amenities'); }
}
