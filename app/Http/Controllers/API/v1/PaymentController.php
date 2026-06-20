<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\{OwnerSubscriptionPlan, OwnerSubscription, HostelBooking, MessBooking, Room, Mess, MessSubscriptionPlan};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Carbon\Carbon;

// ═══════════════════════════════════════════════════════════
// PaymentController — Owner Subscription Payments
// ═══════════════════════════════════════════════════════════

class PaymentController extends Controller
{
    /**
     * Get available subscription plans
     * GET /api/v1/plans
     */
    public function plans(Request $request): JsonResponse
    {
        $plans = OwnerSubscriptionPlan::where('is_active', true)
            ->where(fn($q)=>$q->where('owner_type',$request->user()->role)->orWhere('owner_type','both'))
            ->get();

        return response()->json(['status'=>true,'data'=>$plans]);
    }

    /**
     * Create Razorpay order for owner subscription
     * POST /api/v1/subscription/create-order
     */
    public function createSubscriptionOrder(Request $request): JsonResponse
    {
        $request->validate(['plan_id'=>'required|exists:owner_subscription_plans,id']);

        $plan = OwnerSubscriptionPlan::findOrFail($request->plan_id);
        $user = $request->user();

        // Create Razorpay order
        $razorpay  = $this->getRazorpayInstance();
        $orderData = [
            'amount'   => $plan->price * 100, // paise
            'currency' => 'INR',
            'receipt'  => 'sub_'.Str::random(8),
            'notes'    => ['user_id'=>$user->id,'plan_id'=>$plan->id,'type'=>'owner_subscription'],
        ];

        try {
            $order = $razorpay->order->create($orderData);
        } catch (\Exception $e) {
            return response()->json(['status'=>false,'message'=>'Payment gateway error: '.$e->getMessage()], 500);
        }

        // Store pending subscription
        $subscription = OwnerSubscription::create([
            'user_id'          => $user->id,
            'plan_id'          => $plan->id,
            'razorpay_order_id'=> $order->id,
            'amount_paid'      => $plan->price,
            'payment_status'   => 'pending',
            'status'           => 'active',
            'starts_at'        => now(),
            'expires_at'       => now()->addDays($plan->duration_days),
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'order_id'       => $order->id,
                'subscription_id'=> $subscription->id,
                'amount'         => $plan->price,
                'currency'       => 'INR',
                'key'            => config('services.razorpay.key_id'),
                'name'           => 'Hostel & Mess Finder',
                'description'    => $plan->name,
                'prefill'        => ['name'=>$user->name,'email'=>$user->email,'contact'=>$user->phone],
            ],
        ]);
    }

    /**
     * Verify and activate subscription after payment
     * POST /api/v1/subscription/verify
     */
    public function verifySubscriptionPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
            'subscription_id'     => 'required|exists:owner_subscriptions,id',
        ]);

        $subscription = OwnerSubscription::findOrFail($request->subscription_id);

        // Verify signature
        $generated = hash_hmac('sha256', $request->razorpay_order_id.'|'.$request->razorpay_payment_id, config('services.razorpay.key_secret'));
        if ($generated !== $request->razorpay_signature) {
            return response()->json(['status'=>false,'message'=>'Payment verification failed.'], 422);
        }

        $subscription->update([
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature,
            'payment_status'      => 'paid',
            'status'              => 'active',
        ]);

        $user = $request->user();
        $user->update([
            'subscription_status'    => 'active',
            'subscription_expires_at'=> $subscription->expires_at,
        ]);

        return response()->json(['status'=>true,'message'=>'Subscription activated successfully!','data'=>$subscription]);
    }

    /**
     * Create Razorpay order for hostel booking
     * POST /api/v1/bookings/hostel/create-order
     */
    public function createHostelBookingOrder(Request $request): JsonResponse
    {
        $request->validate([
            'room_id'    => 'required|exists:rooms,id',
            'check_in'   => 'required|date|after_or_equal:today',
            'check_out'  => 'required|date|after:check_in',
            'occupants'  => 'sometimes|integer|min:1',
            'user_note'  => 'nullable|string|max:500',
        ]);

        $room   = Room::with('hostel')->findOrFail($request->room_id);
        $user   = $request->user();

        if (!$room->is_available || $room->available_count < 1) {
            return response()->json(['status'=>false,'message'=>'Room is not available.'], 422);
        }

        $checkIn  = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        $months   = max(1, $checkIn->diffInMonths($checkOut));
        $total    = ($room->price_per_month * $months) + $room->security_deposit;

        $razorpay  = $this->getRazorpayInstance();
        $order     = $razorpay->order->create([
            'amount'   => $total * 100,
            'currency' => 'INR',
            'receipt'  => 'hb_'.Str::random(8),
        ]);

        $booking = HostelBooking::create([
            'user_id'           => $user->id,
            'hostel_id'         => $room->hostel_id,
            'room_id'           => $room->id,
            'check_in'          => $request->check_in,
            'check_out'         => $request->check_out,
            'occupants'         => $request->get('occupants', 1),
            'monthly_rate'      => $room->price_per_month,
            'security_deposit'  => $room->security_deposit,
            'total_amount'      => $total,
            'status'            => 'pending',
            'user_note'         => $request->user_note,
            'razorpay_order_id' => $order->id,
            'payment_status'    => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'booking_id' => $booking->id,
                'booking_ref'=> $booking->booking_ref,
                'order_id'   => $order->id,
                'amount'     => $total,
                'key'        => config('services.razorpay.key_id'),
                'prefill'    => ['name'=>$user->name,'email'=>$user->email,'contact'=>$user->phone],
            ],
        ]);
    }

    /**
     * Verify hostel booking payment
     * POST /api/v1/bookings/hostel/verify
     */
    public function verifyHostelBooking(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'          => 'required|exists:hostel_bookings,id',
            'razorpay_order_id'   => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature'  => 'required|string',
        ]);

        $booking = HostelBooking::findOrFail($request->booking_id);
        $generated = hash_hmac('sha256', $request->razorpay_order_id.'|'.$request->razorpay_payment_id, config('services.razorpay.key_secret'));

        if ($generated !== $request->razorpay_signature) {
            return response()->json(['status'=>false,'message'=>'Payment verification failed.'], 422);
        }

        $booking->update([
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature,
            'payment_status'      => 'paid',
            'status'              => 'confirmed',
            'confirmed_at'        => now(),
        ]);

        // Reduce available count
        $booking->room()->decrement('available_count');

        // Send notification
        \Notification::send($request->user(), new \App\Notifications\BookingConfirmed($booking));

        return response()->json(['status'=>true,'message'=>'Booking confirmed!','data'=>$booking->load('hostel','room')]);
    }

    /**
     * Create Razorpay order for mess subscription
     * POST /api/v1/bookings/mess/create-order
     */
    public function createMessBookingOrder(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id'       => 'required|exists:mess_subscription_plans,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'auto_renew'    => 'boolean',
        ]);

        $plan = MessSubscriptionPlan::with('mess')->findOrFail($request->plan_id);
        $user = $request->user();
        $end  = Carbon::parse($request->start_date)->addDays($plan->duration_days);

        $razorpay = $this->getRazorpayInstance();
        $order    = $razorpay->order->create([
            'amount'  => $plan->price * 100,
            'currency'=> 'INR',
            'receipt' => 'mb_'.Str::random(8),
        ]);

        $booking = MessBooking::create([
            'user_id'           => $user->id,
            'mess_id'           => $plan->mess_id,
            'plan_id'           => $plan->id,
            'selected_slots'    => $plan->slots,
            'start_date'        => $request->start_date,
            'end_date'          => $end->toDateString(),
            'amount'            => $plan->price,
            'status'            => 'active',
            'auto_renew'        => $request->get('auto_renew', false),
            'razorpay_order_id' => $order->id,
            'payment_status'    => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'booking_id' => $booking->id,
                'booking_ref'=> $booking->booking_ref,
                'order_id'   => $order->id,
                'amount'     => $plan->price,
                'key'        => config('services.razorpay.key_id'),
            ],
        ]);
    }

    /**
     * Verify mess booking payment
     * POST /api/v1/bookings/mess/verify
     */
    public function verifyMessBooking(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id'          => 'required|exists:mess_bookings,id',
            'razorpay_order_id'   => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature'  => 'required',
        ]);

        $booking   = MessBooking::findOrFail($request->booking_id);
        $generated = hash_hmac('sha256', $request->razorpay_order_id.'|'.$request->razorpay_payment_id, config('services.razorpay.key_secret'));

        if ($generated !== $request->razorpay_signature) {
            return response()->json(['status'=>false,'message'=>'Payment verification failed.'], 422);
        }

        $booking->update([
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_signature'  => $request->razorpay_signature,
            'payment_status'      => 'paid',
        ]);

        return response()->json(['status'=>true,'message'=>'Mess subscription confirmed!','data'=>$booking->load('mess','plan')]);
    }

    /**
     * Get user bookings
     * GET /api/v1/bookings
     */
    public function myBookings(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'status' => true,
            'data'   => [
                'hostel_bookings' => HostelBooking::where('user_id',$user->id)->with(['hostel:id,name,cover_image','room:id,name,type'])->latest()->paginate(10),
                'mess_bookings'   => MessBooking::where('user_id',$user->id)->with(['mess:id,name,cover_image','plan:id,name,price'])->latest()->paginate(10),
            ],
        ]);
    }

    /**
     * Cancel hostel booking
     * DELETE /api/v1/bookings/hostel/{id}
     */
    public function cancelHostelBooking(Request $request, int $id): JsonResponse
    {
        $booking = HostelBooking::where('user_id',$request->user()->id)->findOrFail($id);
        if (in_array($booking->status, ['cancelled','checked_out'])) {
            return response()->json(['status'=>false,'message'=>'Booking cannot be cancelled.'], 422);
        }
        $booking->update(['status'=>'cancelled','cancelled_at'=>now(),'cancellation_reason'=>$request->reason]);
        $booking->room()->increment('available_count');
        return response()->json(['status'=>true,'message'=>'Booking cancelled.']);
    }

    private function getRazorpayInstance()
    {
        return new \Razorpay\Api\Api(config('services.razorpay.key_id'), config('services.razorpay.key_secret'));
    }
}

// ═══════════════════════════════════════════════════════════
// ReviewController
// ═══════════════════════════════════════════════════════════

namespace App\Http\Controllers\API\v1;

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

// ═══════════════════════════════════════════════════════════
// FavouriteController
// ═══════════════════════════════════════════════════════════

namespace App\Http\Controllers\API\v1;

use App\Models\{Favourite, Hostel, Mess};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavouriteController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $request->validate(['type'=>'required|in:hostel,mess','id'=>'required|integer']);
        $type  = $request->type === 'hostel' ? Hostel::class : Mess::class;
        $model = $type::findOrFail($request->id);
        $user  = $request->user();

        $fav = Favourite::where('user_id',$user->id)->where('favourable_type',$type)->where('favourable_id',$request->id)->first();
        if ($fav) {
            $fav->delete();
            return response()->json(['status'=>true,'saved'=>false,'message'=>'Removed from favourites.']);
        }
        Favourite::create(['user_id'=>$user->id,'favourable_type'=>$type,'favourable_id'=>$request->id]);
        return response()->json(['status'=>true,'saved'=>true,'message'=>'Saved to favourites.']);
    }

    public function index(Request $request): JsonResponse
    {
        $favs = Favourite::where('user_id',$request->user()->id)->with('favourable')->latest()->paginate(20);
        return response()->json(['status'=>true,'data'=>$favs]);
    }
}
