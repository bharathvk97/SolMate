<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MenuImage extends Model
{
    protected $fillable = ['menu_id','image_path','disk'];

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?? config('filesystems.default'))->url($this->image_path);
    }
}
