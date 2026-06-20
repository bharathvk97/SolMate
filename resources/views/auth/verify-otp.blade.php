@extends('layouts.app')
@section('title', 'Verify OTP')

@push('styles')
<style>
.auth-page { background:var(--bg-base); min-height:calc(100vh - 65px); display:flex; align-items:center; justify-content:center; padding:2rem; }
.auth-card { background:var(--bg-surface); border-radius:24px; padding:2.5rem; width:100%; max-width:420px; box-shadow:var(--card-shadow); border:1px solid var(--border-color); text-align:center; }
.otp-icon { width:72px;height:72px;border-radius:50%;background:rgba(92,95,239,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:2rem; }
.otp-inputs { display:flex; gap:10px; justify-content:center; margin:1.5rem 0; }
.otp-input { width:50px;height:56px;text-align:center;font-size:1.4rem;font-weight:700; border:2px solid var(--input-border);border-radius:12px;background:var(--input-bg);color:var(--text-primary);transition:all 0.2s; }
.otp-input:focus { border-color:var(--brand-primary); box-shadow:0 0 0 3px rgba(92,95,239,0.12); outline:none; }
.otp-input.filled { border-color:var(--brand-primary); background:rgba(92,95,239,0.05); }
</style>
@endpush

@section('content')
<div class="auth-page">
<div class="auth-card">
    <div class="otp-icon"><i class="bi bi-envelope-check" style="color:var(--brand-primary);"></i></div>
    <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:0.5rem;">Check your email</h2>
    <p style="color:var(--text-muted);font-size:0.88rem;margin-bottom:0;">
        We sent a 6-digit OTP to<br>
        <strong style="color:var(--text-primary);">{{ session('otp_email', 'your email') }}</strong>
    </p>

    @if(session('error'))
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.75rem 1rem;margin-top:1rem;font-size:0.85rem;color:var(--danger);">
        <i class="bi bi-x-circle me-2"></i>{{ session('error') }}
    </div>
    @endif

    <form method="POST" action="{{ route('verify.otp') }}" id="otpForm">
        @csrf
        <input type="hidden" name="user_id" value="{{ session('pending_user_id') }}">
        <input type="hidden" name="otp" id="otpHidden">

        <div class="otp-inputs">
            @for($i=0; $i<6; $i++)
            <input type="text" class="otp-input" maxlength="1" inputmode="numeric" pattern="[0-9]"
                data-index="{{ $i }}" autocomplete="off">
            @endfor
        </div>

        <button type="submit" class="btn-primary-findr w-100" style="padding:0.75rem;font-size:0.95rem;" id="verifyBtn">
            <i class="bi bi-check2-circle me-2"></i>Verify OTP
        </button>
    </form>

    <div style="margin-top:1.5rem;">
        <p style="font-size:0.85rem;color:var(--text-muted);">Didn't receive the code?</p>
        <form method="POST" action="{{ route('resend.otp') }}" id="resendForm">
            @csrf
            <input type="hidden" name="user_id" value="{{ session('pending_user_id') }}">
            <input type="hidden" name="type" value="email">
            <button type="submit" id="resendBtn" class="btn-outline-findr" style="padding:0.5rem 1.25rem;font-size:0.85rem;" disabled>
                Resend OTP <span id="timer" style="color:var(--text-muted);">(30s)</span>
            </button>
        </form>
    </div>

    <p style="margin-top:1.25rem;font-size:0.82rem;color:var(--text-muted);">
        <a href="{{ route('login') }}" style="color:var(--brand-primary);">← Back to sign in</a>
    </p>
</div>
</div>
@endsection

@push('scripts')
<script>
// OTP input auto-advance
const inputs = document.querySelectorAll('.otp-input');
inputs.forEach((input, i) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g,'');
        if (input.value) {
            input.classList.add('filled');
            if (i < inputs.length-1) inputs[i+1].focus();
        } else { input.classList.remove('filled'); }
        updateHidden();
    });
    input.addEventListener('keydown', e => {
        if (e.key === 'Backspace' && !input.value && i > 0) { inputs[i-1].focus(); inputs[i-1].value=''; inputs[i-1].classList.remove('filled'); updateHidden(); }
    });
    input.addEventListener('paste', e => {
        e.preventDefault();
        const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').slice(0,6);
        pasted.split('').forEach((ch, j) => { if (inputs[j]) { inputs[j].value=ch; inputs[j].classList.add('filled'); } });
        if (inputs[pasted.length-1]) inputs[pasted.length-1].focus();
        updateHidden();
    });
});
inputs[0].focus();

function updateHidden() {
    document.getElementById('otpHidden').value = Array.from(inputs).map(i=>i.value).join('');
}

// Countdown timer for resend
let seconds = 30;
const timerEl = document.getElementById('timer');
const resendBtn = document.getElementById('resendBtn');
const interval = setInterval(() => {
    seconds--;
    timerEl.textContent = seconds > 0 ? `(${seconds}s)` : '';
    if (seconds <= 0) {
        clearInterval(interval);
        resendBtn.disabled = false;
        timerEl.style.display = 'none';
    }
}, 1000);
</script>
@endpush
