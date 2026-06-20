{{-- resources/views/admin/subscriptions.blade.php --}}
@extends('layouts.admin')
@section('title', 'Subscriptions')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Subscriptions</h1><p>All owner subscription payments.</p></div>
  <div>
    <form method="POST" action="{{ route('admin.expire-accounts') }}" style="margin:0;">
      @csrf
      <button type="submit" class="btn-outline-findr" style="font-size:.82rem;" onclick="return confirm('Deactivate all expired accounts?')">
        <i class="bi bi-hourglass-split me-1"></i>Run Expiry Check
      </button>
    </form>
  </div>
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>Owner</th><th>Role</th><th>Plan</th><th>Amount</th><th>Paid On</th><th>Expires</th><th>Status</th></tr></thead>
      <tbody>
        @forelse($subs as $s)
        <tr>
          <td>
            <p style="font-weight:600;font-size:.88rem;margin:0;">{{ $s->user->name }}</p>
            <p style="font-size:.75rem;color:var(--text-muted);margin:0;">{{ $s->user->email }}</p>
          </td>
          <td>
            <span style="background:var(--bg-subtle);border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;color:var(--text-secondary);">
              {{ $s->user->role==='hostel_owner'?'Hostel':'Mess' }} Owner
            </span>
          </td>
          <td style="font-size:.85rem;font-weight:600;">{{ $s->plan->name }}</td>
          <td style="font-size:.88rem;font-weight:700;color:var(--brand-primary);">₹{{ number_format($s->amount_paid) }}</td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $s->created_at->format('d M Y') }}</td>
          <td style="font-size:.82rem;">
            <span style="color:{{ $s->expires_at->isPast()?'var(--danger)':'var(--brand-accent)' }};">
              {{ $s->expires_at->format('d M Y') }}
              {{ $s->expires_at->isPast() ? '(Expired)' : '' }}
            </span>
          </td>
          <td><span class="badge-status badge-{{ $s->payment_status==='paid'?'active':'pending' }}">{{ ucfirst($s->payment_status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No subscriptions yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $subs->links() }}</div>
</div>
@endsection
