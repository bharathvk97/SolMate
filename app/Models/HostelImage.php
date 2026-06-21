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
        $disk = $this->disk ?? config('filesystems.default', 'local');

        // For local disk, use asset() to generate proper URL with current port
        if ($disk === 'local' || $disk === 'public') {
            return asset('storage/' . $this->image_path);
        }

        // For S3
        return Storage::disk($disk)->url($this->image_path);
    }
}