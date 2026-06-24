<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{Review, Hostel, Mess};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'reviewable_type'   => 'required|in:hostel,mess',
            'reviewable_id'     => 'required|integer',
            'rating'            => 'required|integer|between:1,5',
            'cleanliness_rating'=> 'nullable|integer|between:1,5',
            'food_rating'       => 'nullable|integer|between:1,5',
            'value_rating'      => 'nullable|integer|between:1,5',
            'staff_rating'      => 'nullable|integer|between:1,5',
            'location_rating'   => 'nullable|integer|between:1,5',
            'body'              => 'required|string|min:20|max:2000',
        ]);

        $type  = $request->reviewable_type === 'hostel' ? Hostel::class : Mess::class;
        $model = $type::findOrFail($request->reviewable_id);

        // Check duplicate
        $exists = Review::where('user_id',$request->user()->id)->where('reviewable_type',$type)->where('reviewable_id',$request->reviewable_id)->exists();
        if ($exists) return response()->json(['status'=>false,'message'=>'You have already reviewed this listing.'], 422);

        // Verify booking
        $hasBooking = false;
        if ($request->reviewable_type === 'hostel') {
            $hasBooking = \App\Models\HostelBooking::where('user_id',$request->user()->id)->where('hostel_id',$request->reviewable_id)->where('status','confirmed')->exists();
        } else {
            $hasBooking = \App\Models\MessBooking::where('user_id',$request->user()->id)->where('mess_id',$request->reviewable_id)->where('payment_status','paid')->exists();
        }

        $review = Review::create([
            'user_id'           => $request->user()->id,
            'reviewable_type'   => $type,
            'reviewable_id'     => $request->reviewable_id,
            'rating'            => $request->rating,
            'cleanliness_rating'=> $request->cleanliness_rating,
            'food_rating'       => $request->food_rating,
            'value_rating'      => $request->value_rating,
            'staff_rating'      => $request->staff_rating,
            'location_rating'   => $request->location_rating,
            'body'              => $request->body,
            'is_verified'       => $hasBooking,
        ]);

        $model->updateRating();

        return response()->json(['status'=>true,'message'=>'Review submitted.','data'=>$review->load('user:id,name,avatar')], 201);
    }

    public function markHelpful(Request $request, int $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $userId = $request->user()->id;
        $exists = \DB::table('review_helpful')->where('review_id',$id)->where('user_id',$userId)->exists();
        if ($exists) {
            \DB::table('review_helpful')->where('review_id',$id)->where('user_id',$userId)->delete();
            $review->decrement('helpful_count');
            return response()->json(['status'=>true,'message'=>'Removed helpful vote.']);
        }
        \DB::table('review_helpful')->insert(['review_id'=>$id,'user_id'=>$userId]);
        $review->increment('helpful_count');
        return response()->json(['status'=>true,'message'=>'Marked as helpful.']);
    }
}
