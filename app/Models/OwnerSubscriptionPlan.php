<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerSubscriptionPlan extends Model
{
    protected $fillable = ['name','slug','owner_type','price','duration_days','max_listings','allow_image_upload','max_images_per_listing','featured_listing','features','is_active'];
    protected $casts = ['features'=>'array','allow_image_upload'=>'boolean','featured_listing'=>'boolean','is_active'=>'boolean'];

    public function subscriptions() { return $this->hasMany(OwnerSubscription::class, 'plan_id'); }
}
