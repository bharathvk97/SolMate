<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{Hostel, Mess, Room, Review};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Unified location-based search for hostels and messes
     * GET /api/v1/search/nearby
     */
    public function nearby(Request $request): JsonResponse
    {
        $request->validate([
            'lat'    => 'required|numeric|between:-90,90',
            'lng'    => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:0.5|max:50',
            'type'   => 'nullable|in:hostel,mess,both',
        ]);

        $lat    = $request->lat;
        $lng    = $request->lng;
        $radius = $request->get('radius', 5); // km
        $type   = $request->get('type', 'both');

        $results = [];

        if ($type !== 'mess') {
            $results['hostels'] = $this->nearbyHostels($lat, $lng, $radius, $request);
        }
        if ($type !== 'hostel') {
            $results['messes'] = $this->nearbyMesses($lat, $lng, $radius, $request);
        }

        return response()->json(['status'=>true,'data'=>$results]);
    }

    private function nearbyHostels(float $lat, float $lng, float $radius, Request $request): array
    {
        $q = Hostel::active()
            ->select('hostels.*', DB::raw(
                "(6371 * acos(cos(radians({$lat})) * cos(radians(lat)) * cos(radians(lng) - radians({$lng})) + sin(radians({$lat})) * sin(radians(lat)))) AS distance"
            ))
            ->with(['images'=>fn($q)=>$q->where('is_cover',true), 'rooms'=>fn($q)=>$q->available()])
            ->having('distance', '<=', $radius);

        // Filters
        if ($request->gender_type)       $q->where('gender_type', $request->gender_type);
        if ($request->min_price)         $q->whereHas('rooms', fn($rq)=>$rq->where('price_per_month','>=',$request->min_price));
        if ($request->max_price)         $q->whereHas('rooms', fn($rq)=>$rq->where('price_per_month','<=',$request->max_price));
        if ($request->has_ac)            $q->where('has_ac', true);
        if ($request->has_wifi)          $q->where('has_wifi', true);
        if ($request->min_rating)        $q->where('average_rating', '>=', $request->min_rating);
        if ($request->q)                 $q->where(fn($sq)=>$sq->where('name','like','%'.$request->q.'%')->orWhere('address','like','%'.$request->q.'%'));

        $sort = $request->get('sort','distance');
        match($sort) {
            'rating'    => $q->orderByDesc('average_rating'),
            'price_asc' => $q->orderBy('rooms.price_per_month'),
            default     => $q->orderBy('distance'),
        };

        return $q->paginate(10)->through(fn($h) => $this->hostelCard($h))->toArray();
    }

    private function nearbyMesses(float $lat, float $lng, float $radius, Request $request): array
    {
        $q = Mess::active()
            ->select('messes.*', DB::raw(
                "(6371 * acos(cos(radians({$lat})) * cos(radians(lat)) * cos(radians(lng) - radians({$lng})) + sin(radians({$lat})) * sin(radians(lat)))) AS distance"
            ))
            ->with(['images'=>fn($q)=>$q->where('is_cover',true), 'menus'=>fn($q)=>$q->where('is_available',true)])
            ->having('distance', '<=', $radius);

        if ($request->food_type)   $q->where(fn($sq)=>$sq->where('food_type',$request->food_type)->orWhere('food_type','both'));
        if ($request->has_delivery)$q->where('has_delivery', true);
        if ($request->min_rating)  $q->where('average_rating', '>=', $request->min_rating);
        if ($request->q)           $q->where(fn($sq)=>$sq->where('name','like','%'.$request->q.'%')->orWhere('address','like','%'.$request->q.'%'));

        $q->orderBy('distance');

        return $q->paginate(10)->through(fn($m) => $this->messCard($m))->toArray();
    }

    private function hostelCard(Hostel $h): array
    {
        $cheapest = $h->rooms->sortBy('price_per_month')->first();
        return [
            'id'            => $h->id,
            'type'          => 'hostel',
            'name'          => $h->name,
            'slug'          => $h->slug,
            'address'       => $h->address,
            'city'          => $h->city,
            'lat'           => $h->lat,
            'lng'           => $h->lng,
            'gender_type'   => $h->gender_type,
            'cover_image'   => $h->cover_image_url,
            'rating'        => $h->average_rating,
            'total_reviews' => $h->total_reviews,
            'distance_km'   => round($h->distance, 2),
            'starting_price'=> $cheapest?->price_per_month,
            'has_ac'        => $h->has_ac,
            'has_wifi'      => $h->has_wifi,
            'share_url'     => url('/hostels/'.$h->slug),
            'whatsapp_share'=> 'https://wa.me/?text='.urlencode("Check out {$h->name} on Hostel & Mess Finder! ".url('/hostels/'.$h->slug)),
        ];
    }

    private function messCard(Mess $m): array
    {
        $cheapestPlan = $m->menus->sortBy('price')->first();
        return [
            'id'            => $m->id,
            'type'          => 'mess',
            'name'          => $m->name,
            'slug'          => $m->slug,
            'address'       => $m->address,
            'city'          => $m->city,
            'lat'           => $m->lat,
            'lng'           => $m->lng,
            'food_type'     => $m->food_type,
            'cover_image'   => $m->cover_image_url,
            'rating'        => $m->average_rating,
            'total_reviews' => $m->total_reviews,
            'distance_km'   => round($m->distance, 2),
            'has_delivery'  => $m->has_delivery,
            'has_tiffin'    => $m->has_tiffin,
            'slots_open'    => [
                'morning'   => $m->isSlotOpen('morning'),
                'afternoon' => $m->isSlotOpen('afternoon'),
                'evening'   => $m->isSlotOpen('evening'),
                'night'     => $m->isSlotOpen('night'),
            ],
            'cheapest_meal' => $cheapestPlan?->price,
            'share_url'     => url('/messes/'.$m->slug),
            'whatsapp_share'=> 'https://wa.me/?text='.urlencode("Check out {$m->name} on Hostel & Mess Finder! ".url('/messes/'.$m->slug)),
        ];
    }
}

// ═══════════════════════════════════════════════════════════
// HostelController (public-facing API)
// ═══════════════════════════════════════════════════════════

namespace App\Http\Controllers\API\v1;

use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HostelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Hostel::active()->with(['images'=>fn($q)=>$q->where('is_cover',true),'rooms'=>fn($q)=>$q->available()]);

        if ($request->city)       $q->where('city', $request->city);
        if ($request->gender_type)$q->where('gender_type', $request->gender_type);
        if ($request->q)          $q->where('name','like','%'.$request->q.'%');
        if ($request->featured)   $q->featured();

        $q->orderByDesc('is_featured')->orderByDesc('average_rating');

        return response()->json(['status'=>true,'data'=>$q->paginate(12)]);
    }

    public function show(string $slug): JsonResponse
    {
        $hostel = Hostel::active()
            ->with(['owner:id,name,phone','rooms.images','images','amenities','reviews'=>fn($q)=>$q->with('user:id,name,avatar')->where('is_hidden',false)->latest()->limit(10)])
            ->where('slug', $slug)->firstOrFail();

        $data = $hostel->toArray();
        $data['share_url']      = url('/hostels/'.$hostel->slug);
        $data['whatsapp_share'] = 'https://wa.me/?text='.urlencode("Check out {$hostel->name}! ".url('/hostels/'.$hostel->slug));
        $data['rooms']          = $hostel->rooms->map(fn($r) => [
            ...$r->toArray(),
            'images' => $r->images->map(fn($i)=>['id'=>$i->id,'url'=>$i->url]),
        ]);

        return response()->json(['status'=>true,'data'=>$data]);
    }
}

// ═══════════════════════════════════════════════════════════
// MessController (public-facing API)
// ═══════════════════════════════════════════════════════════

namespace App\Http\Controllers\API\v1;

use App\Models\Mess;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Mess::active()->with(['images'=>fn($q)=>$q->where('is_cover',true),'menus']);
        if ($request->city)      $q->where('city', $request->city);
        if ($request->food_type) $q->where(fn($sq)=>$sq->where('food_type',$request->food_type)->orWhere('food_type','both'));
        if ($request->q)         $q->where('name','like','%'.$request->q.'%');
        if ($request->featured)  $q->featured();

        return response()->json(['status'=>true,'data'=>$q->orderByDesc('average_rating')->paginate(12)]);
    }

    public function show(string $slug): JsonResponse
    {
        $mess = Mess::active()
            ->with([
                'owner:id,name,phone',
                'images',
                'menus.images',
                'subscriptionPlans'=>fn($q)=>$q->where('is_active',true),
                'reviews'=>fn($q)=>$q->with('user:id,name,avatar')->where('is_hidden',false)->latest()->limit(10),
            ])
            ->where('slug', $slug)->firstOrFail();

        $data = $mess->toArray();
        $data['slots_status'] = [
            'morning'   => ['open'=>$mess->isSlotOpen('morning'),  'opens'=>$mess->morning_open,  'closes'=>$mess->morning_close],
            'afternoon' => ['open'=>$mess->isSlotOpen('afternoon'),'opens'=>$mess->afternoon_open,'closes'=>$mess->afternoon_close],
            'evening'   => ['open'=>$mess->isSlotOpen('evening'),  'opens'=>$mess->evening_open,  'closes'=>$mess->evening_close],
            'night'     => ['open'=>$mess->isSlotOpen('night'),    'opens'=>$mess->night_open,    'closes'=>$mess->night_close],
        ];
        $data['share_url']      = url('/messes/'.$mess->slug);
        $data['whatsapp_share'] = 'https://wa.me/?text='.urlencode("Check out {$mess->name}! ".url('/messes/'.$mess->slug));

        return response()->json(['status'=>true,'data'=>$data]);
    }
}
