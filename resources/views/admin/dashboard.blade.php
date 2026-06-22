@extends('layouts.admin')

@section('admin-content')
<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1>Dashboard</h1>
        <p>Welcome back, {{ auth()->user()->name }}. Here's what's happening today.</p>
    </div>
    <div class="d-flex gap-2">
        <button class="btn-outline-findr" onclick="exportReport()">
            <i class="bi bi-download me-1"></i>Export
        </button>
        <form method="POST" action="{{ route('admin.expire-accounts') }}">
            @csrf
            <button type="submit" class="btn-primary-findr" onclick="return confirm('Deactivate all expired owner accounts?')">
                <i class="bi bi-clock me-1"></i>Run Expiry Check
            </button>
        </form>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    @php
    $stats = [
        ['label'=>'Total Users',       'value'=>$stats['total_users'],          'icon'=>'bi-people',          'color'=>'#5C5FEF', 'bg'=>'rgba(92,95,239,0.1)'],
        ['label'=>'Hostel Owners',     'value'=>$stats['total_hostel_owners'],   'icon'=>'bi-person-badge',    'color'=>'#F97316', 'bg'=>'rgba(249,115,22,0.1)'],
        ['label'=>'Mess Owners',       'value'=>$stats['total_mess_owners'],     'icon'=>'bi-person-badge',    'color'=>'#10B981', 'bg'=>'rgba(16,185,129,0.1)'],
        ['label'=>'Active Hostels',    'value'=>$stats['active_hostels'],        'icon'=>'bi-building',        'color'=>'#3B82F6', 'bg'=>'rgba(59,130,246,0.1)'],
        ['label'=>'Active Messes',     'value'=>$stats['active_messes'],         'icon'=>'bi-egg-fried',       'color'=>'#8B5CF6', 'bg'=>'rgba(139,92,246,0.1)'],
        ['label'=>'Revenue This Month','value'=>'₹'.number_format($stats['revenue_this_month']), 'icon'=>'bi-currency-rupee','color'=>'#EC4899','bg'=>'rgba(236,72,153,0.1)'],
        ['label'=>'Pending Hostels',   'value'=>$stats['pending_hostels'],       'icon'=>'bi-hourglass-split', 'color'=>'#F59E0B', 'bg'=>'rgba(245,158,11,0.1)'],
        ['label'=>'ID Verifications',  'value'=>$stats['pending_identity_verifications'],'icon'=>'bi-shield-exclamation','color'=>'#EF4444','bg'=>'rgba(239,68,68,0.1)'],
    ];
    @endphp
    @foreach($stats as $s)
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:{{ $s['bg'] }}; color:{{ $s['color'] }};">
                    <i class="bi {{ $s['icon'] }}"></i>
                </div>
                <div>
                    <div class="stat-value">{{ $s['value'] }}</div>
                    <div class="stat-label">{{ $s['label'] }}</div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Charts Row -->
<div class="row g-3 mb-4">
    <div class="col-md-8">
        <div class="card-findr p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h6 style="font-weight:700; margin:0; color:var(--text-primary);">Revenue Trend (Last 12 Months)</h6>
            </div>
            <canvas id="revenueChart" height="90"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-findr p-4">
            <h6 style="font-weight:700; margin-bottom:1rem; color:var(--text-primary);">User Growth</h6>
            <canvas id="userChart" height="160"></canvas>
        </div>
    </div>
</div>

<!-- Recent Pending Actions -->
<div class="row g-3">
    <!-- Pending Hostels -->
    <div class="col-md-6">
        <div class="card-findr">
            <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                <h6 style="font-weight:700; margin:0;">Pending Hostel Approvals</h6>
                <a href="{{ route('admin.hostels', ['status'=>'pending']) }}" style="font-size:0.82rem;" class="text-brand">View all →</a>
            </div>
            <div class="p-3">
                @forelse($pendingHostelsList ?? [] as $hostel)
                <div class="d-flex align-items-center gap-3 py-2">
                    <div style="width:40px;height:40px;border-radius:8px;background:var(--bg-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="bi bi-building" style="color:var(--brand-primary);"></i>
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <p style="font-weight:600; font-size:0.88rem; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $hostel->name }}</p>
                        <p style="font-size:0.78rem; color:var(--text-muted); margin:0;">{{ $hostel->city }} · {{ $hostel->owner->name }}</p>
                    </div>
                    <div class="d-flex gap-1">
                        <form method="POST" action="{{ route('admin.hostels.status', $hostel->id) }}">
                            @csrf @method('PUT')
                            <input type="hidden" name="status" value="active">
                            <button type="submit" style="background:#D1FAE5;color:#065F46;border:none;border-radius:6px;padding:4px 10px;font-size:0.75rem;font-weight:600;cursor:pointer;">Approve</button>
                        </form>
                        <a href="{{ route('admin.hostels') }}?status=pending" style="background:var(--bg-subtle);color:var(--text-secondary);border:none;border-radius:6px;padding:4px 10px;font-size:0.75rem;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;">View</a>
                    </div>
                </div>
                @empty
                <p style="text-align:center; color:var(--text-muted); padding:1rem 0; font-size:0.88rem;">No pending approvals</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Subscriptions -->
    <div class="col-md-6">
        <div class="card-findr">
            <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                <h6 style="font-weight:700; margin:0;">Recent Subscriptions</h6>
                <a href="{{ route('admin.subscriptions') }}" style="font-size:0.82rem;" class="text-brand">View all →</a>
            </div>
            <div class="p-3">
                <div class="table-responsive">
                    <table class="table-findr">
                        <thead>
                            <tr>
                                <th>Owner</th>
                                <th>Plan</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubscriptions ?? [] as $sub)
                            <tr>
                                <td>
                                    <div style="font-weight:600; font-size:0.85rem;">{{ $sub->user->name }}</div>
                                    <div style="font-size:0.75rem; color:var(--text-muted);">{{ $sub->user->role }}</div>
                                </td>
                                <td style="font-size:0.85rem;">{{ $sub->plan->name }}</td>
                                <td style="font-weight:600; font-size:0.85rem;">₹{{ number_format($sub->amount_paid) }}</td>
                                <td>
                                    <span class="badge-status {{ $sub->payment_status === 'paid' ? 'badge-active' : 'badge-pending' }}">
                                        {{ ucfirst($sub->payment_status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:1.5rem 0;">No subscriptions yet</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const isDark = () => document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor = () => isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.05)';
const textColor = () => isDark() ? '#94A3B8' : '#6B7280';

const revenueData = @json($monthlyRevenue ?? []);
const userData    = @json($userGrowth ?? []);

// Revenue Chart
const rCtx = document.getElementById('revenueChart').getContext('2d');
const revenueChart = new Chart(rCtx, {
    type: 'line',
    data: {
        labels: revenueData.map(d => `${d.month}/${d.year}`),
        datasets: [{
            label: 'Revenue (₹)',
            data: revenueData.map(d => d.total),
            borderColor: '#5C5FEF',
            backgroundColor: 'rgba(92,95,239,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#5C5FEF',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor() }, ticks: { color: textColor() } },
            y: { grid: { color: gridColor() }, ticks: { color: textColor(), callback: v => '₹'+v } }
        }
    }
});

// User Growth Doughnut
const uCtx = document.getElementById('userChart').getContext('2d');
new Chart(uCtx, {
    type: 'doughnut',
    data: {
        labels: ['Users', 'Hostel Owners', 'Mess Owners'],
        datasets: [{
            data: [{{ $stats['total_users'] ?? 0 }}, {{ $stats['total_hostel_owners'] ?? 0}}, {{ $stats['total_mess_owners'] ?? 0}}],
            backgroundColor: ['#5C5FEF','#F97316','#10B981'],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { color: textColor(), padding: 12, font: { size: 11 } } }
        }
    }
});

// Re-color on theme change
document.getElementById('themeToggle').addEventListener('click', () => {
    setTimeout(() => {
        revenueChart.options.scales.x.grid.color = gridColor();
        revenueChart.options.scales.y.grid.color = gridColor();
        revenueChart.options.scales.x.ticks.color = textColor();
        revenueChart.options.scales.y.ticks.color = textColor();
        revenueChart.update();
    }, 50);
});
</script>
@endpush
@endsection
