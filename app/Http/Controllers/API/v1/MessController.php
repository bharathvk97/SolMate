<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\Mess;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MessController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Mess::active()->with([
            'images' => fn($q) => $q->where('is_cover', true),
            'menus',
        ]);

        if ($request->city)      $q->where('city', $request->city);
        if ($request->food_type) $q->where(fn($s) => $s->where('food_type', $request->food_type)->orWhere('food_type', 'both'));
        if ($request->q)         $q->where('name', 'like', '%' . $request->q . '%');
        if ($request->featured)  $q->where('is_featured', true);

        return response()->json(['status' => true, 'data' => $q->orderByDesc('average_rating')->paginate(12)]);
    }

    public function show(string $slug): JsonResponse
    {
        $mess = Mess::active()
            ->with([
                'owner:id,name,phone',
                'images',
                'menus.images',
                'subscriptionPlans' => fn($q) => $q->where('is_active', true),
                'reviews'           => fn($q) => $q->with('user:id,name,avatar')
                    ->where('is_hidden', false)->latest()->limit(10),
            ])
            ->where('slug', $slug)->firstOrFail();

        $data                   = $mess->toArray();
        $data['share_url']      = url('/messes/' . $mess->slug);
        $data['whatsapp_share'] = 'https://wa.me/?text=' . urlencode("Check out {$mess->name}! " . url('/messes/' . $mess->slug));

        return response()->json(['status' => true, 'data' => $data]);
    }
}
