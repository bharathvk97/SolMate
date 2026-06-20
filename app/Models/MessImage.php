<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MessImage extends Model
{
    protected $fillable = ['mess_id','image_path','disk','is_cover','sort_order','caption'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?? config('filesystems.default'))->url($this->image_path);
    }
}
