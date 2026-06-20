<?php

namespace App\Http\Controllers\API\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OwnerSubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Carbon\Carbon;

class AuthController extends Controller
{
    // ── Register ──────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $rules = [
            'name'          => 'required|string|max:100',
            'email'         => 'required|email|unique:users',
            'phone'         => 'required|string|max:15|unique:users',
            'password'      => ['required', Password::min(8)->letters()->numbers()->symbols()],
            'role'          => 'required|in:user,hostel_owner,mess_owner',
        ];

        if (in_array($request->role, ['hostel_owner','mess_owner'])) {
            $rules['identity_type']   = 'required|in:aadhaar,passport';
            $rules['identity_number'] = 'required|string';
            $rules['identity_front']  = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
            $rules['identity_back']   = 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }

        $validated = $request->validate($rules);

        $user = new User([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role'     => $validated['role'],
            'status'   => 'pending_verification',
        ]);

        // Identity docs upload
        if (in_array($request->role, ['hostel_owner','mess_owner'])) {
            $user->identity_type   = $validated['identity_type'];
            $user->identity_number = $validated['identity_number'];
            $user->identity_status = 'pending';

            $disk = config('filesystems.default');
            if ($request->hasFile('identity_front')) {
                $user->identity_document_front = $request->file('identity_front')
                    ->store('identity/front', $disk);
            }
            if ($request->hasFile('identity_back')) {
                $user->identity_document_back = $request->file('identity_back')
                    ->store('identity/back', $disk);
            }
        }

        // Generate email OTP
        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->email_otp            = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        // Send OTP email
        $this->sendOtpEmail($user, $otp);

        return response()->json([
            'status'  => true,
            'message' => 'Registration successful. Please verify your email with the OTP sent.',
            'data'    => ['user_id' => $user->id, 'email' => $user->email],
        ], 201);
    }

    // ── Verify Email OTP ──────────────────────────────────────
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'otp'     => 'required|string|size:6',
        ]);

        $user = User::findOrFail($request->user_id);

        if ($user->email_otp !== $request->otp) {
            return response()->json(['status'=>false,'message'=>'Invalid OTP.'], 422);
        }
        if (Carbon::now()->gt($user->email_otp_expires_at)) {
            return response()->json(['status'=>false,'message'=>'OTP has expired. Please request a new one.'], 422);
        }

        $user->email_verified_at    = now();
        $user->email_otp            = null;
        $user->email_otp_expires_at = null;
        $user->status               = 'active';
        $user->save();

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Email verified successfully.',
            'data'    => ['token' => $token, 'user' => $this->userResource($user)],
        ]);
    }

    // ── Resend OTP ────────────────────────────────────────────
    public function resendOtp(Request $request): JsonResponse
    {
        $request->validate(['user_id'=>'required|exists:users,id','type'=>'required|in:email,phone']);

        $user = User::findOrFail($request->user_id);
        $otp  = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        if ($request->type === 'email') {
            $user->email_otp            = $otp;
            $user->email_otp_expires_at = Carbon::now()->addMinutes(15);
            $user->save();
            $this->sendOtpEmail($user, $otp);
        }

        return response()->json(['status'=>true,'message'=>'OTP resent successfully.']);
    }

    // ── Login ─────────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['status'=>false,'message'=>'Invalid credentials.'], 401);
        }
        if (!$user->email_verified_at) {
            return response()->json(['status'=>false,'message'=>'Please verify your email first.', 'requires_verification'=>true, 'user_id'=>$user->id], 403);
        }
        if ($user->status === 'suspended') {
            return response()->json(['status'=>false,'message'=>'Your account has been suspended.'], 403);
        }
        if ($user->isOwner() && !$user->hasActiveSubscription()) {
            // Allow login but flag subscription required
            $token = $user->createToken('api-token')->plainTextToken;
            return response()->json([
                'status'  => true,
                'message' => 'Login successful. Your subscription has expired.',
                'data'    => [
                    'token'                  => $token,
                    'user'                   => $this->userResource($user),
                    'subscription_required'  => true,
                ],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Login successful.',
            'data'    => ['token' => $token, 'user' => $this->userResource($user)],
        ]);
    }

    // ── Logout ────────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['status'=>true,'message'=>'Logged out successfully.']);
    }

    // ── Me ────────────────────────────────────────────────────
    public function me(Request $request): JsonResponse
    {
        return response()->json(['status'=>true,'data'=>$this->userResource($request->user())]);
    }

    // ── Update Profile ────────────────────────────────────────
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:100',
            'phone'            => 'sometimes|string|max:15|unique:users,phone,'.$user->id,
            'theme_preference' => 'sometimes|in:light,dark,system',
            'city'             => 'sometimes|string',
            'state'            => 'sometimes|string',
            'lat'              => 'sometimes|numeric',
            'lng'              => 'sometimes|numeric',
        ]);
        if ($request->hasFile('avatar')) {
            $request->validate(['avatar'=>'image|max:2048']);
            $path = $request->file('avatar')->store('avatars', config('filesystems.default'));
            $validated['avatar'] = $path;
        }
        $user->update($validated);
        return response()->json(['status'=>true,'message'=>'Profile updated.','data'=>$this->userResource($user->fresh())]);
    }

    // ── Forgot Password ───────────────────────────────────────
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email'=>'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) return response()->json(['status'=>false,'message'=>'No account with that email.'], 404);

        $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();
        $this->sendOtpEmail($user, $otp, 'Password Reset OTP');

        return response()->json(['status'=>true,'message'=>'OTP sent to your email.','data'=>['user_id'=>$user->id]]);
    }

    // ── Reset Password ────────────────────────────────────────
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'user_id'  => 'required|exists:users,id',
            'otp'      => 'required|size:6',
            'password' => ['required', Password::min(8)->letters()->numbers()->symbols()],
        ]);
        $user = User::findOrFail($request->user_id);
        if ($user->email_otp !== $request->otp || Carbon::now()->gt($user->email_otp_expires_at)) {
            return response()->json(['status'=>false,'message'=>'Invalid or expired OTP.'], 422);
        }
        $user->password = Hash::make($request->password);
        $user->email_otp = null;
        $user->email_otp_expires_at = null;
        $user->save();

        return response()->json(['status'=>true,'message'=>'Password reset successfully.']);
    }

    // ── Change Password ───────────────────────────────────────
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => ['required', Password::min(8)->letters()->numbers()->symbols()],
        ]);
        $user = $request->user();
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['status'=>false,'message'=>'Current password is incorrect.'], 422);
        }
        $user->update(['password' => Hash::make($request->password)]);
        return response()->json(['status'=>true,'message'=>'Password changed successfully.']);
    }

    // ── Helpers ───────────────────────────────────────────────
    private function sendOtpEmail(User $user, string $otp, string $subject = 'Email Verification OTP'): void
    {
        try {
            Mail::raw(
                "Hello {$user->name},\n\nYour OTP is: {$otp}\n\nThis OTP expires in 15 minutes.\n\nDo not share this OTP with anyone.\n\n— Hostel & Mess Finder Team",
                function ($msg) use ($user, $subject) {
                    $msg->to($user->email)->subject($subject);
                }
            );
        } catch (\Exception $e) {
            \Log::error('OTP Email failed: '.$e->getMessage());
        }
    }

    private function userResource(User $user): array
    {
        return [
            'id'                     => $user->id,
            'name'                   => $user->name,
            'email'                  => $user->email,
            'phone'                  => $user->phone,
            'role'                   => $user->role,
            'status'                 => $user->status,
            'avatar_url'             => $user->avatar_url,
            'email_verified'         => (bool)$user->email_verified_at,
            'identity_type'          => $user->identity_type,
            'identity_status'        => $user->identity_status,
            'subscription_status'    => $user->subscription_status,
            'subscription_expires_at'=> $user->subscription_expires_at?->toDateString(),
            'has_active_subscription'=> $user->hasActiveSubscription(),
            'theme_preference'       => $user->theme_preference,
            'city'                   => $user->city,
            'state'                  => $user->state,
            'lat'                    => $user->lat,
            'lng'                    => $user->lng,
        ];
    }
}
