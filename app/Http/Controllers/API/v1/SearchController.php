<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Mess;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat'    => 'required|numeric|between:-90,90',
            'lng'    => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.5|max:50',
            'type'   => 'nullable|in:hostel,mess,both',
        ]);

        $lat    = (float) $request->lat;
        $lng    = (float) $request->lng;
        $radius = (float) $request->get('radius', 10);
        $type   = $request->get('type', 'both');

        $results = [];

        if ($type !== 'mess') {
            $results['hostels'] = $this->nearbyHostels($lat, $lng, $radius, $request);
        }
        if ($type !== 'hostel') {
            $results['messes'] = $this->nearbyMesses($lat, $lng, $radius, $request);
        }

        return response()->json(['status' => true, 'data' => $results]);
    }

    private function nearbyHostels(float $lat, float $lng, float $radius, Request $request): array
    {
        $q = Hostel::active()
            ->select('hostels.*', DB::raw(
                "(6371 * acos(
                    cos(radians({$lat})) * cos(radians(lat)) *
                    cos(radians(lng) - radians({$lng})) +
                    sin(radians({$lat})) * sin(radians(lat))
                )) AS distance"
            ))
            ->with([
                'images' => fn($q) => $q->where('is_cover', true),
                'rooms'  => fn($q) => $q->where('is_available', true),
            ])
            ->having('distance', '<=', $radius);

        if ($request->gender_type) $q->where('gender_type', $request->gender_type);
        if ($request->min_price)   $q->whereHas('rooms', fn($r) => $r->where('price_per_month', '>=', $request->min_price));
        if ($request->max_price)   $q->whereHas('rooms', fn($r) => $r->where('price_per_month', '<=', $request->max_price));
        if ($request->has_ac)      $q->where('has_ac', true);
        if ($request->has_wifi)    $q->where('has_wifi', true);
        if ($request->min_rating)  $q->where('average_rating', '>=', $request->min_rating);
        if ($request->q)           $q->where(fn($s) => $s->where('name', 'like', '%'.$request->q.'%')->orWhere('address', 'like', '%'.$request->q.'%'));

        $sort = $request->get('sort', 'distance');
        if ($sort === 'rating')     $q->orderByDesc('average_rating');
        elseif ($sort === 'price_asc') $q->orderBy('average_rating');
        else                        $q->orderBy('distance');

        return $q->paginate(10)->through(fn($h) => $this->hostelCard($h))->toArray();
    }

    private function nearbyMesses(float $lat, float $lng, float $radius, Request $request): array
    {
        $q = Mess::active()
            ->select('messes.*', DB::raw(
                "(6371 * acos(
                    cos(radians({$lat})) * cos(radians(lat)) *
                    cos(radians(lng) - radians({$lng})) +
                    sin(radians({$lat})) * sin(radians(lat))
                )) AS distance"
            ))
            ->with([
                'images' => fn($q) => $q->where('is_cover', true),
                'menus'  => fn($q) => $q->where('is_available', true),
            ])
            ->having('distance', '<=', $radius);

        if ($request->food_type)    $q->where(fn($s) => $s->where('food_type', $request->food_type)->orWhere('food_type', 'both'));
        if ($request->has_delivery) $q->where('has_delivery', true);
        if ($request->min_rating)   $q->where('average_rating', '>=', $request->min_rating);
        if ($request->q)            $q->where(fn($s) => $s->where('name', 'like', '%'.$request->q.'%')->orWhere('address', 'like', '%'.$request->q.'%'));

        $q->orderBy('distance');

        return $q->paginate(10)->through(fn($m) => $this->messCard($m))->toArray();
    }

    private function hostelCard(Hostel $h): array
    {
        $cheapest = $h->rooms->sortBy('price_per_month')->first();
        return [
            'id'             => $h->id,
            'type'           => 'hostel',
            'name'           => $h->name,
            'slug'           => $h->slug,
            'address'        => $h->address,
            'city'           => $h->city,
            'lat'            => $h->lat,
            'lng'            => $h->lng,
            'gender_type'    => $h->gender_type,
            'cover_image'    => $h->cover_image_url,
            'rating'         => $h->average_rating,
            'total_reviews'  => $h->total_reviews,
            'distance_km'    => round($h->distance ?? 0, 2),
            'starting_price' => $cheapest?->price_per_month,
            'has_ac'         => $h->has_ac,
            'has_wifi'       => $h->has_wifi,
            'is_featured'    => $h->is_featured,
            'share_url'      => url('/hostels/' . $h->slug),
            'whatsapp_share' => 'https://wa.me/?text=' . urlencode("Check out {$h->name}! " . url('/hostels/' . $h->slug)),
        ];
    }

    private function messCard(Mess $m): array
    {
        $cheapestMenu = $m->menus->sortBy('price')->first();
        return [
            'id'             => $m->id,
            'type'           => 'mess',
            'name'           => $m->name,
            'slug'           => $m->slug,
            'address'        => $m->address,
            'city'           => $m->city,
            'lat'            => $m->lat,
            'lng'            => $m->lng,
            'food_type'      => $m->food_type,
            'cover_image'    => $m->cover_image_url,
            'rating'         => $m->average_rating,
            'total_reviews'  => $m->total_reviews,
            'distance_km'    => round($m->distance ?? 0, 2),
            'has_delivery'   => $m->has_delivery,
            'is_featured'    => $m->is_featured,
            'slots_open'     => [
                'morning'   => $m->isSlotOpen('morning'),
                'afternoon' => $m->isSlotOpen('afternoon'),
                'evening'   => $m->isSlotOpen('evening'),
                'night'     => $m->isSlotOpen('night'),
            ],
            'cheapest_meal'  => $cheapestMenu?->price,
            'share_url'      => url('/messes/' . $m->slug),
            'whatsapp_share' => 'https://wa.me/?text=' . urlencode("Check out {$m->name}! " . url('/messes/' . $m->slug)),
        ];
    }
}
