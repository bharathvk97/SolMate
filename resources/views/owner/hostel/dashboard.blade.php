@extends('layouts.app')
@section('title', 'Owner Dashboard')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-sidebar { width:240px; background:var(--bg-surface); border-right:1px solid var(--border-color); position:sticky; top:65px; height:calc(100vh - 65px); overflow-y:auto; flex-shrink:0; }
.owner-content { flex:1; padding:2rem; min-width:0; }
</style>
@endpush

@section('content')
<div class="owner-wrapper">
    <!-- Sidebar -->
    @include('owner.partials.hostel-sidebar')

    <div class="owner-content">
        <!-- Subscription Warning -->
        @if(!auth()->user()->hasActiveSubscription())
        <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;">
            <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);font-size:1.2rem;"></i>
            <div>
                <strong style="color:var(--danger);">Subscription Required</strong>
                <p style="margin:2px 0 0;font-size:0.85rem;color:var(--text-secondary);">Your listings are inactive. <a href="{{ route('owner.subscription') }}" style="color:var(--brand-primary);font-weight:600;">Renew subscription →</a></p>
            </div>
        </div>
        @endif

        <div class="page-header d-flex align-items-center justify-content-between">
            <div>
                <h1>Hostel Dashboard</h1>
                <p>Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <a href="{{ route('owner.hostel.create') }}" class="btn-primary-findr d-flex align-items-center gap-2">
                <i class="bi bi-plus-lg"></i> Add Hostel
            </a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            @foreach([
                ['label'=>'My Hostels',      'value'=>$stats['total_hostels']??0,   'icon'=>'bi-building',        'color'=>'#5C5FEF','bg'=>'rgba(92,95,239,0.1)'],
                ['label'=>'Active Listings',  'value'=>$stats['active_hostels']??0,  'icon'=>'bi-check-circle',    'color'=>'#10B981','bg'=>'rgba(16,185,129,0.1)'],
                ['label'=>'Total Bookings',   'value'=>$stats['total_bookings']??0,  'icon'=>'bi-calendar-check',  'color'=>'#F97316','bg'=>'rgba(249,115,22,0.1)'],
                ['label'=>'Pending Bookings', 'value'=>$stats['pending_bookings']??0,'icon'=>'bi-hourglass-split', 'color'=>'#F59E0B','bg'=>'rgba(245,158,11,0.1)'],
            ] as $s)
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['color'] }};"><i class="bi {{ $s['icon'] }}"></i></div>
                        <div><div class="stat-value">{{ $s['value'] }}</div><div class="stat-label">{{ $s['label'] }}</div></div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Hostels List -->
        <div class="card-findr">
            <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                <h6 style="font-weight:700;margin:0;">My Hostels</h6>
                <a href="{{ route('owner.hostel.create') }}" class="btn-primary-findr" style="padding:0.4rem 1rem;font-size:0.82rem;"><i class="bi bi-plus-lg me-1"></i>Add New</a>
            </div>
            <div style="padding:1rem;">
                <div class="table-responsive">
                    <table class="table-findr">
                        <thead><tr>
                            <th>Hostel</th><th>Status</th><th>Rooms</th><th>Available</th><th>Rating</th><th>Actions</th>
                        </tr></thead>
                        <tbody>
                            @forelse($hostels ?? [] as $hostel)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.88rem;">{{ $hostel->name }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $hostel->city }}</div>
                                </td>
                                <td><span class="badge-status badge-{{ $hostel->status }}">{{ ucfirst($hostel->status) }}</span></td>
                                <td style="font-size:0.88rem;">{{ $hostel->rooms->count() }}</td>
                                <td style="font-size:0.88rem;color:var(--brand-accent);">{{ $hostel->rooms->sum('available_count') }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <span style="color:#F59E0B;font-size:0.85rem;">★</span>
                                        <span style="font-size:0.85rem;font-weight:600;">{{ number_format($hostel->average_rating,1) }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('owner.hostel.edit', $hostel->id) }}" style="background:var(--bg-subtle);color:var(--text-secondary);border:none;border-radius:8px;padding:5px 10px;font-size:0.8rem;text-decoration:none;"><i class="bi bi-pencil"></i></a>
                                        <a href="{{ route('owner.hostel.rooms', $hostel->id) }}" style="background:rgba(92,95,239,0.1);color:var(--brand-primary);border:none;border-radius:8px;padding:5px 10px;font-size:0.8rem;text-decoration:none;"><i class="bi bi-door-open"></i> Rooms</a>
                                        <a href="/hostels/{{ $hostel->slug }}" target="_blank" style="background:var(--bg-subtle);color:var(--text-secondary);border:none;border-radius:8px;padding:5px 10px;font-size:0.8rem;text-decoration:none;"><i class="bi bi-eye"></i></a>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">
                                No hostels yet. <a href="{{ route('owner.hostel.create') }}" style="color:var(--brand-primary);">Add your first hostel →</a>
                            </td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Recent Bookings -->
        <div class="card-findr mt-3">
            <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                <h6 style="font-weight:700;margin:0;">Recent Booking Requests</h6>
                <a href="{{ route('owner.hostel.bookings') }}" class="text-brand" style="font-size:0.82rem;">View all →</a>
            </div>
            <div style="padding:1rem;">
                <div class="table-responsive">
                    <table class="table-findr">
                        <thead><tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead>
                        <tbody>
                            @forelse($recentBookings ?? [] as $booking)
                            <tr>
                                <td>
                                    <div style="font-weight:600;font-size:0.85rem;">{{ $booking->user->name }}</div>
                                    <div style="font-size:0.75rem;color:var(--text-muted);">{{ $booking->user->phone }}</div>
                                </td>
                                <td style="font-size:0.85rem;">{{ $booking->room->name }}</td>
                                <td style="font-size:0.85rem;">{{ $booking->check_in->format('d M Y') }}</td>
                                <td style="font-weight:600;font-size:0.85rem;">₹{{ number_format($booking->total_amount) }}</td>
                                <td><span class="badge-status badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span></td>
                                <td>
                                    @if($booking->status === 'pending')
                                    <div class="d-flex gap-1">
                                        <form method="POST" action="{{ route('owner.hostel.bookings.status', $booking->id) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" style="background:#D1FAE5;color:#065F46;border:none;border-radius:8px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;">Confirm</button>
                                        </form>
                                        <form method="POST" action="{{ route('owner.hostel.bookings.status', $booking->id) }}">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" style="background:rgba(239,68,68,0.1);color:var(--danger);border:none;border-radius:8px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;">Reject</button>
                                        </form>
                                    </div>
                                    @else
                                    <span class="badge-status badge-{{ $booking->status }}">{{ ucfirst($booking->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" style="text-align:center;padding:1.5rem;color:var(--text-muted);">No bookings yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
