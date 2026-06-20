@extends('layouts.admin')
@section('title', 'All Bookings')

@section('content')
<div class="page-header"><h1>Bookings</h1><p>All hostel and mess bookings across the platform.</p></div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>Guest</th><th>Hostel / Room</th><th>Check-in</th><th>Check-out</th><th>Amount</th><th>Status</th><th>Payment</th></tr></thead>
      <tbody>
        @forelse($bookings as $b)
        <tr>
          <td>
            <p style="font-weight:600;font-size:.85rem;margin:0;">{{ $b->user->name }}</p>
            <p style="font-size:.75rem;color:var(--text-muted);margin:0;">{{ $b->user->phone }}</p>
          </td>
          <td>
            <p style="font-weight:600;font-size:.85rem;margin:0;">{{ $b->hostel->name }}</p>
            <p style="font-size:.75rem;color:var(--text-muted);margin:0;">{{ $b->room->name }}</p>
          </td>
          <td style="font-size:.82rem;">{{ $b->check_in->format('d M Y') }}</td>
          <td style="font-size:.82rem;">{{ $b->check_out->format('d M Y') }}</td>
          <td style="font-size:.88rem;font-weight:700;color:var(--brand-primary);">₹{{ number_format($b->total_amount) }}</td>
          <td><span class="badge-status badge-{{ $b->status }}">{{ ucfirst($b->status) }}</span></td>
          <td><span class="badge-status badge-{{ $b->payment_status==='paid'?'active':'pending' }}">{{ ucfirst($b->payment_status) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No bookings yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $bookings->links() }}</div>
</div>
@endsection
