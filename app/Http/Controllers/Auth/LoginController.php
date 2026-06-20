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
        $rules = [
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'phone'    => 'required|string|max:15|unique:users',
            'password' => 'required|confirmed|min:8',
            'role'     => 'required|in:user,hostel_owner,mess_owner',
        ];
        if (in_array($request->role, ['hostel_owner','mess_owner'])) {
            $rules['identity_type']   = 'required|in:aadhaar,passport';
            $rules['identity_number'] = 'required|string';
            $rules['identity_front']  = 'required|file|mimes:jpg,jpeg,png,pdf|max:5120';
        }
        $request->validate($rules);

        $disk = config('filesystems.default');
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => '91'.$request->phone,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
            'status'   => 'pending_verification',
            'identity_type'   => $request->identity_type,
            'identity_number' => $request->identity_number,
            'identity_status' => $request->role !== 'user' ? 'pending' : null,
        ]);

        if ($request->hasFile('identity_front')) {
            $user->identity_document_front = $request->file('identity_front')->store('identity/front', $disk);
        }
        if ($request->hasFile('identity_back')) {
            $user->identity_document_back = $request->file('identity_back')->store('identity/back', $disk);
        }

        $otp = str_pad(rand(0,999999),6,'0',STR_PAD_LEFT);
        $user->email_otp = $otp;
        $user->email_otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        \Mail::raw("Hello {$user->name},\n\nYour OTP: {$otp}\n\nExpires in 15 minutes.\n\n— SolMate", function($m) use($user) {
            $m->to($user->email)->subject('Verify Your Email — SolMate');
        });

        session(['pending_user_id'=>$user->id,'otp_email'=>$user->email]);
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
