<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RoomImage extends Model
{
    protected $fillable = ['room_id','image_path','disk','is_cover','sort_order'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?? config('filesystems.default'))->url($this->image_path);
    }
}
