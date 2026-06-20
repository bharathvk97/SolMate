<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HostelImage extends Model
{
    protected $fillable = ['hostel_id','image_path','disk','is_cover','sort_order','caption'];
    protected $casts = ['is_cover' => 'boolean'];

    public function hostel() { return $this->belongsTo(Hostel::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk ?? config('filesystems.default'))->url($this->image_path);
    }
}
