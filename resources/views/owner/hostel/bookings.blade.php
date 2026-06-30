@extends('layouts.app')
@section('title', 'Residents')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-sidebar { width:240px; background:var(--bg-surface); border-right:1px solid var(--border-color); position:sticky; top:65px; height:calc(100vh - 65px); overflow-y:auto; flex-shrink:0; }
.owner-content { flex:1; padding:2rem; min-width:0; }

.rent-pill { display:inline-flex; align-items:center; gap:6px; padding:.4rem .9rem; border-radius:30px; border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-secondary); font-size:.82rem; font-weight:600; text-decoration:none; }
.rent-pill:hover { border-color:var(--brand-primary); color:var(--brand-primary); text-decoration:none; }
.rent-pill.active { background:var(--brand-primary); border-color:var(--brand-primary); color:#fff; }
.pill-count { background:rgba(0,0,0,0.08); border-radius:20px; padding:0 .5rem; font-size:.72rem; }
.rent-pill.active .pill-count { background:rgba(255,255,255,0.25); }

.flt-label { font-size:.72rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px; }
.btn-sm-findr { padding:.42rem .9rem !important; font-size:.8rem !important; }

.res-table { width:100%; border-collapse:separate; border-spacing:0; }
.res-table th { text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted); padding:.6rem .75rem; border-bottom:1px solid var(--border-color); white-space:nowrap; }
.res-table td { padding:.8rem .75rem; border-bottom:1px solid var(--border-color); font-size:.86rem; vertical-align:middle; }
.res-table tr:last-child td { border-bottom:none; }

.status-chip { display:inline-block; font-size:.7rem; font-weight:700; padding:3px 9px; border-radius:20px; text-transform:capitalize; }
.st-confirmed   { background:#DBEAFE; color:#1E40AF; }
.st-checked_in  { background:#D1FAE5; color:#065F46; }
.st-checked_out { background:var(--bg-subtle); color:var(--text-muted); }

.rent-select { font-size:.78rem !important; padding:.3rem 1.6rem .3rem .6rem !important; font-weight:700 !important; border-radius:8px !important; cursor:pointer; }
.rent-select.rent-pending      { color:#92400E; border-color:#FCD34D !important; }
.rent-select.rent-advance_paid { color:#1E40AF; border-color:#93C5FD !important; }
.rent-select.rent-fully_paid   { color:#065F46; border-color:#6EE7B7 !important; }
[data-theme="dark"] .rent-select.rent-pending      { color:#FDE68A; }
[data-theme="dark"] .rent-select.rent-advance_paid { color:#BFDBFE; }
[data-theme="dark"] .rent-select.rent-fully_paid   { color:#6EE7B7; }
</style>
@endpush

@section('content')
@php
  $rent  = request('rent');
  $dates = array_filter(['start_date' => request('start_date'), 'end_date' => request('end_date')]);
@endphp
<div class="owner-wrapper">
    <!-- Sidebar -->
    @include('owner.partials.hostel-sidebar')

    <div class="owner-content">
        <div class="page-header"><h1>Residents</h1><p>Manage the people staying in your hostels and track their rent.</p></div>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-card"><div class="d-flex align-items-start gap-3">
                    <div class="stat-icon" style="background:rgba(92,95,239,0.1);color:#5C5FEF;"><i class="bi bi-people-fill"></i></div>
                    <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Residents</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card"><div class="d-flex align-items-start gap-3">
                    <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#F59E0B;"><i class="bi bi-hourglass-split"></i></div>
                    <div><div class="stat-value">{{ $stats['pending'] }}</div><div class="stat-label">Pending Rent</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card"><div class="d-flex align-items-start gap-3">
                    <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3B82F6;"><i class="bi bi-cash-coin"></i></div>
                    <div><div class="stat-value">{{ $stats['advance_paid'] }}</div><div class="stat-label">Advance Paid</div></div>
                </div></div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card"><div class="d-flex align-items-start gap-3">
                    <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10B981;"><i class="bi bi-check-circle-fill"></i></div>
                    <div><div class="stat-value">{{ $stats['fully_paid'] }}</div><div class="stat-label">Fully Paid</div></div>
                </div></div>
            </div>
        </div>

        <!-- Rent category filter pills -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('owner.hostel.bookings', $dates) }}" class="rent-pill {{ !$rent ? 'active' : '' }}">All <span class="pill-count">{{ $stats['total'] }}</span></a>
            <a href="{{ route('owner.hostel.bookings', $dates + ['rent' => 'pending']) }}" class="rent-pill {{ $rent === 'pending' ? 'active' : '' }}">Pending Rent <span class="pill-count">{{ $stats['pending'] }}</span></a>
            <a href="{{ route('owner.hostel.bookings', $dates + ['rent' => 'advance_paid']) }}" class="rent-pill {{ $rent === 'advance_paid' ? 'active' : '' }}">Advance Paid <span class="pill-count">{{ $stats['advance_paid'] }}</span></a>
            <a href="{{ route('owner.hostel.bookings', $dates + ['rent' => 'fully_paid']) }}" class="rent-pill {{ $rent === 'fully_paid' ? 'active' : '' }}">Fully Paid <span class="pill-count">{{ $stats['fully_paid'] }}</span></a>
        </div>

        <!-- Date range filter -->
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <input type="hidden" name="rent" value="{{ $rent }}">
            <div>
                <label class="flt-label">Staying from</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm" style="width:auto;">
            </div>
            <div>
                <label class="flt-label">Staying until</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm" style="width:auto;">
            </div>
            <button type="submit" class="btn-primary-findr btn-sm-findr">Apply</button>
            @if($dates)<a href="{{ route('owner.hostel.bookings', $rent ? ['rent' => $rent] : []) }}" class="btn-outline-findr btn-sm-findr">Clear dates</a>@endif
        </form>

        <!-- Residents table -->
        <div class="card-findr" style="padding:0;overflow:hidden;">
            @if($bookings->isEmpty())
                <div class="p-5 text-center" style="color:var(--text-muted);">
                    <i class="bi bi-people" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                    <h6 style="font-weight:700;">No residents found</h6>
                    <p style="font-size:.86rem;margin:0;">No confirmed residents match these filters yet.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="res-table">
                        <thead>
                            <tr>
                                <th>Resident</th>
                                <th>Room &amp; Stay</th>
                                <th>Rent</th>
                                <th>Booking</th>
                                <th>Rent Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $b)
                            <tr>
                                <td>
                                    <div style="font-weight:700;">{{ $b->user?->name ?? 'Deleted user' }}</div>
                                    <div style="font-size:.78rem;color:var(--text-muted);">
                                        @if($b->user?->phone)<i class="bi bi-telephone"></i> {{ preg_replace('/^91/', '', $b->user->phone) }}@endif
                                        <span style="font-family:monospace;"> · {{ $b->booking_ref }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $b->room?->name ?? 'Room removed' }} <span style="color:var(--text-muted);">· {{ $b->hostel?->name }}</span></div>
                                    <div style="font-size:.78rem;color:var(--text-muted);">{{ $b->check_in->format('d M Y') }} → {{ $b->check_out->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div style="font-weight:700;">₹{{ number_format($b->monthly_rate) }}<span style="font-weight:400;color:var(--text-muted);font-size:.76rem;">/mo</span></div>
                                    <div style="font-size:.78rem;color:var(--text-muted);">Total ₹{{ number_format($b->total_amount) }}</div>
                                </td>
                                <td><span class="status-chip st-{{ $b->status }}">{{ str_replace('_', ' ', $b->status) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('owner.hostel.bookings.rent', $b->id) }}" style="margin:0;">
                                        @csrf
                                        <select name="rent_status" onchange="this.form.submit()" class="form-select rent-select rent-{{ $b->rent_status }}">
                                            <option value="pending"      @selected($b->rent_status === 'pending')>Pending</option>
                                            <option value="advance_paid" @selected($b->rent_status === 'advance_paid')>Advance Paid</option>
                                            <option value="fully_paid"   @selected($b->rent_status === 'fully_paid')>Fully Paid</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div style="margin-top:1rem;">{{ $bookings->links() }}</div>
    </div>
</div>
@endsection
