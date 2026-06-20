@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div style="background:var(--bg-base);min-height:calc(100vh - 65px);display:flex;align-items:center;justify-content:center;padding:2rem;">
<div style="background:var(--bg-surface);border-radius:24px;padding:2.5rem;width:100%;max-width:420px;box-shadow:var(--card-shadow);border:1px solid var(--border-color);text-align:center;">
  <div style="width:68px;height:68px;border-radius:50%;background:rgba(92,95,239,.1);display:flex;align-items:center;justify-content:center;margin:0 auto 1.5rem;font-size:1.8rem;">🔐</div>
  <h2 style="font-size:1.4rem;font-weight:800;margin-bottom:.5rem;">Forgot your password?</h2>
  <p style="color:var(--text-muted);font-size:.88rem;margin-bottom:1.75rem;">Enter your email and we'll send you a reset link.</p>

  @if(session('success'))
  <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;font-size:.85rem;color:var(--brand-accent);">
    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
  </div>
  @endif

  <form method="POST" action="{{ route('password.forgot') }}" id="fpForm">
    @csrf
    <div class="mb-3 text-start">
      <label class="form-label">Email Address</label>
      <div style="position:relative;">
        <i class="bi bi-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
        <input type="email" name="email" class="form-control" style="padding-left:2.2rem;" placeholder="you@email.com" required value="{{ old('email') }}">
      </div>
      @error('email')<p style="color:var(--danger);font-size:.8rem;margin:4px 0 0;">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="btn-primary-findr w-100" style="padding:.75rem;">
      <i class="bi bi-send me-2"></i>Send Reset Link
    </button>
  </form>

  <p style="margin-top:1.5rem;font-size:.85rem;color:var(--text-secondary);">
    <a href="{{ route('login') }}" style="color:var(--brand-primary);">← Back to sign in</a>
  </p>
</div>
</div>
@endsection
