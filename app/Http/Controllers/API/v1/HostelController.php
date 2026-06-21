<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HostelController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Hostel::active()->with([
            'images' => fn($q) => $q->where('is_cover', true),
            'rooms'  => fn($q) => $q->where('is_available', true),
        ]);

        if ($request->city)        $q->where('city', $request->city);
        if ($request->gender_type) $q->where('gender_type', $request->gender_type);
        if ($request->q)           $q->where('name', 'like', '%' . $request->q . '%');
        if ($request->featured)    $q->where('is_featured', true);

        $q->orderByDesc('is_featured')->orderByDesc('average_rating');

        return response()->json(['status' => true, 'data' => $q->paginate(12)]);
    }

    public function show(string $slug): JsonResponse
    {
        $hostel = Hostel::active()
            ->with([
                'owner:id,name,phone',
                'rooms.images',
                'images',
                'amenities',
                'reviews' => fn($q) => $q->with('user:id,name,avatar')
                    ->where('is_hidden', false)->latest()->limit(10),
            ])
            ->where('slug', $slug)->firstOrFail();

        $data                   = $hostel->toArray();
        $data['share_url']      = url('/hostels/' . $hostel->slug);
        $data['whatsapp_share'] = 'https://wa.me/?text=' . urlencode("Check out {$hostel->name}! " . url('/hostels/' . $hostel->slug));

        return response()->json(['status' => true, 'data' => $data]);
    }
}
