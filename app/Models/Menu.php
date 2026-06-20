<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['mess_id','slot','day_type','days_of_week','title','items','price','is_available','status','notes'];
    protected $casts = ['items'=>'array','days_of_week'=>'array','is_available'=>'boolean'];

    public function mess()   { return $this->belongsTo(Mess::class); }
    public function images() { return $this->hasMany(MenuImage::class); }
}
