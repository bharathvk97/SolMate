{{-- admin/settings.blade.php --}}
@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
<div class="page-header"><h1>Platform Settings</h1><p>Configure global platform parameters.</p></div>

<div class="row g-4">
  <div class="col-lg-8">

    <div class="card-findr p-4 mb-3">
      <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);">Subscription Plans</h6>
      <p style="font-size:.85rem;color:var(--text-muted);">Manage plans via database seeders or direct DB edits. Current active plans:</p>
      <div class="table-responsive">
        <table class="table-findr">
          <thead><tr><th>Plan</th><th>Price</th><th>Duration</th><th>Type</th><th>Status</th></tr></thead>
          <tbody>
            @foreach(\App\Models\OwnerSubscriptionPlan::all() as $p)
            <tr>
              <td style="font-weight:600;font-size:.85rem;">{{ $p->name }}</td>
              <td style="font-size:.85rem;">₹{{ number_format($p->price) }}</td>
              <td style="font-size:.85rem;color:var(--text-muted);">{{ $p->duration_days }} days</td>
              <td style="font-size:.78rem;">{{ ucfirst(str_replace('_',' ',$p->owner_type)) }}</td>
              <td><span class="badge-status badge-{{ $p->is_active?'active':'inactive' }}">{{ $p->is_active?'Active':'Inactive' }}</span></td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <div class="card-findr p-4 mb-3">
      <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);">Maintenance Actions</h6>
      <div class="d-flex flex-wrap gap-3">
        <form method="POST" action="{{ route('admin.expire-accounts') }}" style="margin:0;">
          @csrf
          <button type="submit" class="btn-outline-findr" onclick="return confirm('Run expiry check now?')">
            <i class="bi bi-hourglass-split me-2"></i>Expire Overdue Owners
          </button>
        </form>
      </div>
    </div>

    <div class="card-findr p-4">
      <h6 style="font-weight:700;margin-bottom:1rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);">Payment Gateway</h6>
      <div style="background:var(--bg-subtle);border-radius:10px;padding:1rem;">
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Gateway</span>
          <strong>Razorpay</strong>
        </div>
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Key ID</span>
          <code style="font-size:.78rem;">{{ substr(config('services.razorpay.key_id'),0,12).'…' }}</code>
        </div>
        <div class="d-flex justify-content-between" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Environment</span>
          <span style="color:{{ app()->isProduction()?'var(--brand-accent)':'var(--warning)' }};font-weight:600;">{{ app()->isProduction()?'Production':'Test Mode' }}</span>
        </div>
      </div>
    </div>

  </div>

  <div class="col-lg-4">
    <div class="card-findr p-4">
      <h6 style="font-weight:700;margin-bottom:1rem;">File Storage</h6>
      <div style="background:var(--bg-subtle);border-radius:10px;padding:1rem;">
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Driver</span>
          <strong>{{ ucfirst(config('filesystems.default')) }}</strong>
        </div>
        @if(config('filesystems.default') === 's3')
        <div class="d-flex justify-content-between mb-2" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Bucket</span>
          <code style="font-size:.75rem;">{{ config('filesystems.disks.s3.bucket') }}</code>
        </div>
        <div class="d-flex justify-content-between" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Region</span>
          <code style="font-size:.75rem;">{{ config('filesystems.disks.s3.region') }}</code>
        </div>
        @else
        <div class="d-flex justify-content-between" style="font-size:.85rem;">
          <span style="color:var(--text-muted);">Path</span>
          <code style="font-size:.75rem;">storage/app/public</code>
        </div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection
