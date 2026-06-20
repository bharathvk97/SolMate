@extends('layouts.app')
@section('title', 'My Bookings')

@push('styles')
<style>
.booking-tabs { display:flex; gap:0; border-bottom:2px solid var(--border-color); margin-bottom:1.5rem; }
.booking-tab  { padding:.65rem 1.4rem; font-size:.88rem; font-weight:600; color:var(--text-muted); border:none; background:none; cursor:pointer; border-bottom:2px solid transparent; margin-bottom:-2px; transition:all .15s; }
.booking-tab.active { color:var(--brand-primary); border-bottom-color:var(--brand-primary); }
.booking-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; overflow:hidden; margin-bottom:1rem; transition:box-shadow .2s; }
.booking-card:hover { box-shadow:var(--card-shadow); }
.booking-banner { height:6px; }
.booking-banner.confirmed { background:linear-gradient(90deg,var(--brand-accent),#6EE7B7); }
.booking-banner.pending   { background:linear-gradient(90deg,var(--warning),#FCD34D); }
.booking-banner.rejected,.booking-banner.cancelled { background:linear-gradient(90deg,var(--danger),#FCA5A5); }
</style>
@endpush

@section('content')
<div class="container py-5" style="max-width:800px;">
  <div class="page-header">
    <h1>My Bookings</h1>
    <p>Track all your hostel stays and mess subscriptions.</p>
  </div>

  <!-- Tabs -->
  <div class="booking-tabs">
    <button class="booking-tab active" onclick="switchTab('hostel',this)">🏠 Hostel Bookings ({{ $hostelBookings->total() }})</button>
    <button class="booking-tab"        onclick="switchTab('mess',this)">🍽️ Mess Subscriptions ({{ $messBookings->total() }})</button>
  </div>

  <!-- Hostel Bookings -->
  <div id="tab-hostel">
    @forelse($hostelBookings as $b)
    <div class="booking-card">
      <div class="booking-banner {{ $b->status }}"></div>
      <div style="padding:1.2rem;">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
          <div>
            <h6 style="font-weight:700;margin:0;">{{ $b->hostel->name }}</h6>
            <p style="font-size:.8rem;color:var(--text-muted);margin:3px 0 0;">{{ $b->room->name }} · {{ $b->hostel->city }}</p>
          </div>
          <span class="badge-status badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span>
        </div>
        <div class="row g-2 mt-2">
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Check-in</p>
            <strong style="font-size:.85rem;">{{ $b->check_in->format('d M Y') }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Check-out</p>
            <strong style="font-size:.85rem;">{{ $b->check_out->format('d M Y') }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Amount Paid</p>
            <strong style="font-size:.85rem;color:var(--brand-primary);">₹{{ number_format($b->total_amount) }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Booking Ref</p>
            <strong style="font-size:.78rem;font-family:monospace;">{{ $b->booking_reference }}</strong>
          </div>
        </div>
        <div class="d-flex gap-2 mt-3">
          <a href="/hostels/{{ $b->hostel->slug }}" class="btn-outline-findr" style="padding:.4rem .9rem;font-size:.8rem;">View Hostel</a>
          @if($b->status === 'pending')
          <form method="POST" action="/api/v1/bookings/hostel/{{ $b->id }}/cancel" style="margin:0;">
            @csrf
            <button type="submit" style="padding:.4rem .9rem;font-size:.8rem;background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:8px;cursor:pointer;" onclick="return confirm('Cancel this booking?')">Cancel</button>
          </form>
          @endif
        </div>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">🏠</div>
      <h5 style="font-weight:700;margin-bottom:.5rem;">No hostel bookings yet</h5>
      <p style="font-size:.88rem;">Find a hostel near you and book your stay.</p>
      <a href="/" class="btn-primary-findr" style="display:inline-block;margin-top:1rem;">Explore Hostels</a>
    </div>
    @endforelse
    {{ $hostelBookings->links() }}
  </div>

  <!-- Mess Subscriptions -->
  <div id="tab-mess" style="display:none;">
    @forelse($messBookings as $b)
    <div class="booking-card">
      <div class="booking-banner {{ $b->payment_status==='paid'?'confirmed':'pending' }}"></div>
      <div style="padding:1.2rem;">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
          <div>
            <h6 style="font-weight:700;margin:0;">{{ $b->mess->name }}</h6>
            <p style="font-size:.8rem;color:var(--text-muted);margin:3px 0 0;">{{ $b->plan->name }} · {{ $b->mess->city }}</p>
          </div>
          <span class="badge-status badge-{{ $b->payment_status==='paid'?'active':'pending' }}">{{ $b->payment_status==='paid'?'Active':'Pending' }}</span>
        </div>
        <div class="row g-2 mt-2">
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Start Date</p>
            <strong style="font-size:.85rem;">{{ $b->start_date->format('d M Y') }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">End Date</p>
            <strong style="font-size:.85rem;">{{ $b->end_date->format('d M Y') }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Amount</p>
            <strong style="font-size:.85rem;color:var(--brand-primary);">₹{{ number_format($b->amount_paid) }}</strong>
          </div>
          <div class="col-6 col-md-3">
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">Days Left</p>
            <strong style="font-size:.85rem;color:{{ $b->end_date->isPast()?'var(--danger)':'var(--brand-accent)' }};">
              {{ $b->end_date->isPast() ? 'Expired' : $b->end_date->diffInDays(now()).' days' }}
            </strong>
          </div>
        </div>
        <div class="mt-3">
          <a href="/messes/{{ $b->mess->slug }}" class="btn-outline-findr" style="padding:.4rem .9rem;font-size:.8rem;">View Mess</a>
        </div>
      </div>
    </div>
    @empty
    <div style="text-align:center;padding:4rem 2rem;color:var(--text-muted);">
      <div style="font-size:3rem;margin-bottom:1rem;">🍽️</div>
      <h5 style="font-weight:700;margin-bottom:.5rem;">No mess subscriptions yet</h5>
      <p style="font-size:.88rem;">Subscribe to a mess near you for daily meals.</p>
      <a href="/?type=mess" class="btn-primary-findr" style="display:inline-block;margin-top:1rem;">Explore Messes</a>
    </div>
    @endforelse
    {{ $messBookings->links() }}
  </div>
</div>
@endsection

@push('scripts')
<script>
function switchTab(name, btn) {
  document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display='none');
  document.querySelectorAll('.booking-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('tab-'+name).style.display = 'block';
  btn.classList.add('active');
}
</script>
@endpush
