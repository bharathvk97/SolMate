<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function showLogin()   { return view('auth.login'); }
    public function showRegister(){ return view('auth.register'); }

    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);
        $user = User::where('email',$request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->with('error','Invalid credentials.')->withInput();
        }
        if (!$user->email_verified_at) {
            session(['pending_user_id'=>$user->id,'otp_email'=>$user->email]);
            return redirect()->route('verify.otp.page')->with('info','Please verify your email first.');
        }
        if ($user->status === 'suspended') {
            return back()->with('error','Your account has been suspended.');
        }
        Auth::login($user, $request->boolean('remember'));
        return $this->redirectByRole($user);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'required|digits:10',
            'password' => 'required|confirmed|min:8',
            'role'     => 'required|in:user,hostel_owner,mess_owner',
            'terms'    => 'accepted',
        ], [
            'terms.accepted' => 'Please accept the Terms of Service and Privacy Policy.',
        ]);

        // Check phone uniqueness with 91 prefix
        $phoneExists = \App\Models\User::where('phone', '91' . $request->phone)
            ->orWhere('phone', $request->phone)
            ->exists();

        if ($phoneExists) {
            return back()->withErrors(['phone' => 'This phone number is already registered.'])->withInput();
        }

        $otp  = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user = \App\Models\User::create([
            'name'                 => $request->name,
            'email'                => $request->email,
            'phone'                => '91' . $request->phone,
            'password'             => \Illuminate\Support\Facades\Hash::make($request->password),
            'role'                 => $request->role,
            'status'               => 'pending_verification',
            'email_otp'            => $otp,
            'email_otp_expires_at' => \Carbon\Carbon::now()->addMinutes(15),
        ]);

        \Illuminate\Support\Facades\Mail::raw(
            "Hello {$user->name},\n\nYour SolMate OTP: {$otp}\n\nExpires in 15 minutes.",
            fn($m) => $m->to($user->email)->subject('Verify Your Email — SolMate')
        );

        session(['pending_user_id' => $user->id, 'otp_email' => $user->email]);
        return redirect()->route('verify.otp.page');
    }

    public function showVerifyOtp(){ return view('auth.verify-otp'); }

    public function verifyOtp(Request $request)
    {
        $request->validate(['user_id'=>'required|exists:users,id','otp'=>'required|size:6']);
        $user = User::findOrFail($request->user_id);

        if ($user->email_otp !== $request->otp || Carbon::now()->gt($user->email_otp_expires_at)) {
            return back()->with('error','Invalid or expired OTP.');
        }
        $user->email_verified_at = now();
        $user->email_otp = null;
        $user->email_otp_expires_at = null;
        $user->status = 'active';
        $user->save();

        Auth::login($user);
        return $this->redirectByRole($user)->with('success','Email verified! Welcome to SolMate.');
    }

    public function resendOtp(Request $request)
    {
        $user = User::findOrFail($request->user_id);
        $otp  = str_pad(rand(0,999999),6,'0',STR_PAD_LEFT);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();
        \Mail::raw("Your new OTP: {$otp}", fn($m) => $m->to($user->email)->subject('New OTP — SolMate'));
        return back()->with('success','OTP resent.');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('success','Logged out.');
    }

    private function redirectByRole(User $user)
    {
        return match($user->role) {
            'admin'        => redirect()->route('admin.dashboard'),
            'hostel_owner' => redirect()->route('owner.hostel.dashboard'),
            'mess_owner'   => redirect()->route('owner.mess.dashboard'),
            default        => redirect()->route('home'),
        };
    }
}
