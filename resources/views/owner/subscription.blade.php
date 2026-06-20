@extends('layouts.app')
@section('title', 'Subscription Plans')

@section('content')
<div class="container py-5" style="max-width:960px;">
    <div class="text-center mb-5">
        <h1 style="font-weight:800;font-size:2rem;">Choose Your Plan</h1>
        <p style="color:var(--text-muted);">Keep your listing active and reach more students</p>

        @if(auth()->user()->hasActiveSubscription())
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.3);border-radius:20px;padding:8px 18px;margin-top:1rem;">
            <span style="width:8px;height:8px;border-radius:50%;background:#10B981;animation:pulse 1.5s infinite;"></span>
            <span style="font-size:0.85rem;font-weight:600;color:var(--brand-accent);">
                Active until {{ auth()->user()->subscription_expires_at->format('d M Y') }}
            </span>
        </div>
        @else
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:20px;padding:8px 18px;margin-top:1rem;">
            <i class="bi bi-exclamation-circle-fill" style="color:var(--danger);"></i>
            <span style="font-size:0.85rem;font-weight:600;color:var(--danger);">Subscription expired — listings are inactive</span>
        </div>
        @endif
    </div>

    <div class="row g-4 justify-content-center">
        @foreach($plans as $plan)
        <div class="col-md-4">
            <div class="card-findr h-100 {{ $plan->featured_listing ? 'border-brand' : '' }}" style="{{ $plan->featured_listing ? 'border:2px solid var(--brand-primary);' : '' }}">
                @if($plan->featured_listing)
                <div style="background:var(--brand-primary);color:#fff;text-align:center;padding:6px;font-size:0.78rem;font-weight:700;letter-spacing:0.06em;">⭐ MOST POPULAR</div>
                @endif
                <div style="padding:1.75rem;">
                    <h5 style="font-weight:800;margin-bottom:0.25rem;">{{ $plan->name }}</h5>
                    <div style="margin:1rem 0;">
                        <span style="font-size:2.2rem;font-weight:800;color:var(--brand-primary);">₹{{ number_format($plan->price) }}</span>
                        <span style="font-size:0.85rem;color:var(--text-muted);">/{{ $plan->duration_days }} days</span>
                    </div>
                    <ul style="list-style:none;padding:0;margin-bottom:1.5rem;">
                        @foreach($plan->features ?? [] as $feature)
                        <li style="display:flex;align-items:center;gap:8px;padding:5px 0;font-size:0.88rem;color:var(--text-secondary);">
                            <i class="bi bi-check2-circle" style="color:var(--brand-accent);flex-shrink:0;"></i>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>
                    <button class="btn-primary-findr w-100" style="padding:0.75rem;" onclick="choosePlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }})">
                        @if(auth()->user()->hasActiveSubscription() && auth()->user()->activeSubscription?->plan_id == $plan->id)
                            Current Plan
                        @else
                            Get Started
                        @endif
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Billing History -->
    @if($history->count())
    <div class="card-findr mt-5">
        <div class="p-4 pb-0"><h6 style="font-weight:700;margin:0;">Billing History</h6></div>
        <div class="p-3">
            <div class="table-responsive">
                <table class="table-findr">
                    <thead><tr><th>Plan</th><th>Amount</th><th>Date</th><th>Expires</th><th>Status</th></tr></thead>
                    <tbody>
                        @foreach($history as $sub)
                        <tr>
                            <td style="font-size:0.85rem;font-weight:600;">{{ $sub->plan->name }}</td>
                            <td style="font-size:0.85rem;">₹{{ number_format($sub->amount_paid) }}</td>
                            <td style="font-size:0.85rem;color:var(--text-muted);">{{ $sub->created_at->format('d M Y') }}</td>
                            <td style="font-size:0.85rem;color:var(--text-muted);">{{ $sub->expires_at->format('d M Y') }}</td>
                            <td><span class="badge-status badge-{{ $sub->payment_status==='paid'?'active':'pending' }}">{{ ucfirst($sub->payment_status) }}</span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function choosePlan(planId, planName, price) {
    axios.post('/api/v1/subscription/create-order', { plan_id: planId })
        .then(r => {
            const d = r.data.data;
            new Razorpay({
                key: d.key, amount: price * 100, currency: 'INR',
                name: 'SolMate', description: d.description,
                order_id: d.order_id,
                prefill: d.prefill,
                theme: { color: '#5C5FEF' },
                handler(res) {
                    axios.post('/api/v1/subscription/verify', {
                        subscription_id: d.subscription_id,
                        razorpay_order_id: res.razorpay_order_id,
                        razorpay_payment_id: res.razorpay_payment_id,
                        razorpay_signature: res.razorpay_signature,
                    }).then(() => {
                        showToast('Subscription activated! 🎉','success');
                        setTimeout(() => location.reload(), 1800);
                    });
                }
            }).open();
        })
        .catch(e => showToast(e.response?.data?.message || 'Payment error','danger'));
}
</script>
@endpush
