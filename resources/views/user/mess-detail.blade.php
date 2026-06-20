@extends('layouts.app')
@section('title', $mess->name)

@push('styles')
<style>
.slot-tab { border-radius:10px; padding:0.7rem 1.2rem; border:2px solid var(--border-color); cursor:pointer; background:transparent; color:var(--text-secondary); font-weight:600; font-size:0.85rem; transition:all 0.15s; display:flex; align-items:center; gap:8px; }
.slot-tab:hover { border-color:var(--brand-primary); color:var(--brand-primary); }
.slot-tab.active { background:var(--brand-primary); border-color:var(--brand-primary); color:#fff; }
.slot-tab .slot-open-dot { width:8px;height:8px;border-radius:50%;background:#10B981;display:inline-block;animation:pulse 1.5s infinite; }
.slot-tab .slot-closed-dot { width:8px;height:8px;border-radius:50%;background:#9CA3AF;display:inline-block; }
@keyframes pulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:0.7;transform:scale(1.3)} }
.menu-item-row { display:flex;align-items:center;justify-content:space-between;padding:0.75rem 0;border-bottom:1px solid var(--border-color); }
.menu-item-row:last-child { border-bottom:none; }
.plan-card { background:var(--bg-surface);border:2px solid var(--border-color);border-radius:16px;padding:1.5rem;transition:all 0.2s;cursor:pointer; }
.plan-card:hover,.plan-card.selected { border-color:var(--brand-primary);box-shadow:0 4px 20px rgba(92,95,239,0.12); }
</style>
@endpush

@section('content')
<div class="container py-4">

    <!-- Breadcrumb -->
    <nav style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1.25rem;">
        <a href="/" style="color:var(--text-muted);">Home</a> /
        <a href="/messes" style="color:var(--text-muted);">Messes</a> /
        <span style="color:var(--text-primary);">{{ $mess->name }}</span>
    </nav>

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge-{{ $mess->food_type==='veg' ? 'veg' : 'nonveg' }}">
                    {{ $mess->food_type==='veg' ? '🥦 Pure Veg' : ($mess->food_type==='non_veg' ? '🍗 Non-Veg' : '🍽️ Veg & Non-Veg') }}
                </span>
                @if($mess->has_delivery)<span style="background:rgba(16,185,129,0.1);color:var(--brand-accent);border-radius:20px;padding:3px 10px;font-size:0.75rem;font-weight:600;">🛵 Delivery Available</span>@endif
            </div>
            <h1 style="font-size:1.75rem;font-weight:800;margin:0;">{{ $mess->name }}</h1>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="stars">{{ str_repeat('★', round($mess->average_rating)) }}{{ str_repeat('☆', 5-round($mess->average_rating)) }}</span>
                <span style="font-weight:600;font-size:0.88rem;">{{ number_format($mess->average_rating,1) }}</span>
                <span style="color:var(--text-muted);font-size:0.85rem;">({{ $mess->total_reviews }} reviews)</span>
                <span style="color:var(--text-muted);">·</span>
                <span style="color:var(--text-muted);font-size:0.85rem;">{{ $mess->city }}, {{ $mess->state }}</span>
            </div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button onclick="toggleFav()" class="btn-outline-findr d-flex align-items-center gap-2" id="favBtn"><i class="bi bi-heart" id="favIcon"></i> Save</button>
            <div class="dropdown">
                <button class="btn-outline-findr d-flex align-items-center gap-2" data-bs-toggle="dropdown"><i class="bi bi-share"></i> Share</button>
                <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-surface);border-color:var(--border-color);border-radius:12px;">
                    <li><a class="dropdown-item" href="{{ $mess->whatsapp_share ?? '#' }}" target="_blank" style="color:#25D366;font-weight:600;"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a></li>
                    <li><a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" style="color:#1877F2;font-weight:600;"><i class="bi bi-facebook me-2"></i>Facebook</a></li>
                    <li><button class="dropdown-item" onclick="copyLink()" style="color:var(--text-secondary);"><i class="bi bi-link-45deg me-2"></i>Copy link</button></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Gallery -->
    @if($mess->images->count())
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;grid-template-rows:200px 200px;gap:8px;border-radius:16px;overflow:hidden;margin-bottom:1.5rem;">
        @foreach($mess->images->take(5) as $i => $img)
        <div style="{{ $i===0?'grid-row:1/3;':'' }}">
            <img src="{{ $img->url }}" alt="{{ $mess->name }}" style="width:100%;height:100%;object-fit:cover;cursor:pointer;">
        </div>
        @endforeach
    </div>
    @endif

    <div class="row g-4">
        <!-- Left -->
        <div class="col-lg-8">
            <!-- Today's Slot Status -->
            <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.25rem;margin-bottom:1.5rem;">
                <h6 style="font-weight:700;margin-bottom:1rem;">Today's Availability</h6>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach(['morning'=>'☀️','afternoon'=>'🌤️','evening'=>'🌅','night'=>'🌙'] as $slot => $icon)
                    @php $isOpen = $mess->isSlotOpen($slot); @endphp
                    <div style="flex:1;min-width:110px;background:{{ $isOpen?'rgba(16,185,129,0.06)':'var(--bg-subtle)' }};border:1.5px solid {{ $isOpen?'rgba(16,185,129,0.3)':'var(--border-color)' }};border-radius:12px;padding:0.9rem;text-align:center;">
                        <div style="font-size:1.3rem;margin-bottom:4px;">{{ $icon }}</div>
                        <div style="font-size:0.8rem;font-weight:700;color:{{ $isOpen?'var(--brand-accent)':'var(--text-muted)' }};">{{ ucfirst($slot) }}</div>
                        <div style="font-size:0.7rem;color:{{ $isOpen?'#10B981':'#9CA3AF' }};margin-top:2px;">
                            {{ $isOpen ? '● Open' : '○ Closed' }}
                        </div>
                        @php
                        $openKey  = $slot.'_open';
                        $closeKey = $slot.'_close';
                        @endphp
                        <div style="font-size:0.68rem;color:var(--text-muted);margin-top:2px;">{{ substr($mess->$openKey,0,5) }} – {{ substr($mess->$closeKey,0,5) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Menu Slots -->
            <div class="d-flex gap-2 mb-3 flex-wrap">
                @foreach(['morning'=>'☀️','afternoon'=>'🌤️','evening'=>'🌅','night'=>'🌙'] as $slot => $icon)
                @php $hasMenu = $mess->menus->where('slot',$slot)->where('is_available',true)->count(); @endphp
                <button class="slot-tab {{ $slot==='morning'?'active':'' }}" onclick="showSlot('{{ $slot }}',this)">
                    @php $isOpen = $mess->isSlotOpen($slot); @endphp
                    <span class="{{ $isOpen?'slot-open-dot':'slot-closed-dot' }}"></span>
                    {{ $icon }} {{ ucfirst($slot) }}
                </button>
                @endforeach
            </div>

            @foreach(['morning','afternoon','evening','night'] as $slot)
            <div id="menu-{{ $slot }}" style="display:{{ $slot==='morning'?'block':'none' }};">
                @php $menus = $mess->menus->where('slot',$slot)->where('is_available',true); @endphp
                @forelse($menus as $menu)
                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.25rem;margin-bottom:1rem;">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div>
                            <h6 style="font-weight:700;margin:0;">{{ $menu->title ?: ucfirst($slot).' Menu' }}</h6>
                            @if($menu->notes)<p style="font-size:0.8rem;color:var(--text-muted);margin:2px 0 0;">{{ $menu->notes }}</p>@endif
                        </div>
                        <div style="text-align:right;">
                            <strong style="font-size:1.1rem;color:var(--brand-primary);">₹{{ $menu->price }}</strong>
                            <div>
                                <span class="slot-pill {{ $menu->status==='open'?'slot-open':'slot-closed' }}">
                                    <span class="slot-dot"></span>{{ ucfirst($menu->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @foreach($menu->items as $item)
                    <div class="menu-item-row">
                        <div class="d-flex align-items-center gap-2">
                            <span style="width:6px;height:6px;border-radius:50%;background:var(--brand-primary);flex-shrink:0;"></span>
                            <span style="font-size:0.88rem;color:var(--text-primary);">{{ $item['name'] }}</span>
                        </div>
                        <span style="font-size:0.82rem;color:var(--text-muted);font-weight:500;">{{ $item['qty'] ?? '' }}</span>
                    </div>
                    @endforeach
                    @if($menu->images->count())
                    <div class="d-flex gap-2 mt-3 overflow-auto" style="padding-bottom:4px;">
                        @foreach($menu->images as $img)
                        <img src="{{ $img->url }}" style="height:70px;width:70px;object-fit:cover;border-radius:8px;flex-shrink:0;">
                        @endforeach
                    </div>
                    @endif
                </div>
                @empty
                <div style="text-align:center;padding:2.5rem;color:var(--text-muted);background:var(--bg-subtle);border-radius:14px;">
                    <i class="bi bi-calendar-x" style="font-size:2rem;"></i>
                    <p style="margin-top:0.5rem;font-size:0.88rem;">No {{ $slot }} menu available</p>
                </div>
                @endforelse
            </div>
            @endforeach

            <!-- About -->
            <div style="margin-top:2rem;">
                <h5 style="font-weight:700;margin-bottom:0.75rem;">About {{ $mess->name }}</h5>
                <p style="color:var(--text-secondary);line-height:1.8;font-size:0.92rem;">{{ $mess->description ?: 'No description available.' }}</p>
            </div>

            <!-- Reviews -->
            <div style="margin-top:2rem;">
                <h5 style="font-weight:700;margin-bottom:1rem;">Reviews ({{ $mess->total_reviews }})</h5>
                @auth
                <div style="background:var(--bg-subtle);border-radius:14px;padding:1.25rem;margin-bottom:1.5rem;">
                    <h6 style="font-weight:700;margin-bottom:0.75rem;">Write a Review</h6>
                    <div id="starRating" class="d-flex gap-2 mb-3">
                        @for($i=1;$i<=5;$i++)
                        <button type="button" onclick="setStarRating({{ $i }})" data-star="{{ $i }}" style="background:none;border:none;font-size:1.6rem;cursor:pointer;color:var(--text-muted);padding:0;">★</button>
                        @endfor
                    </div>
                    <textarea id="reviewBody" class="form-control" rows="3" placeholder="How was the food quality and service?"></textarea>
                    <button class="btn-primary-findr mt-2" onclick="submitReview({{ $mess->id }},'mess')">Submit Review</button>
                </div>
                @endauth
                @forelse($mess->reviews as $review)
                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:12px;padding:1.1rem;margin-bottom:0.75rem;">
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ $review->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($review->user->name).'&background=6366f1&color=fff' }}" style="width:40px;height:40px;border-radius:50%;flex-shrink:0;" alt="avatar">
                        <div style="flex:1;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong style="font-size:0.88rem;">{{ $review->user->name }}</strong>
                                @if($review->is_verified)<span style="background:#D1FAE5;color:#065F46;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:20px;">✓ Verified</span>@endif
                                <span style="margin-left:auto;font-size:0.78rem;color:var(--text-muted);">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="stars" style="margin:3px 0;font-size:0.85rem;">{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }}</div>
                            <p style="font-size:0.88rem;color:var(--text-secondary);margin:4px 0 0;">{{ $review->body }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <p style="text-align:center;color:var(--text-muted);padding:1.5rem;">No reviews yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Right: Subscription -->
        <div class="col-lg-4">
            <div style="position:sticky;top:80px;">
                @if($mess->subscriptionPlans->count())
                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:16px;padding:1.5rem;box-shadow:var(--card-shadow);">
                    <h6 style="font-weight:700;margin-bottom:1rem;">Monthly Subscription Plans</h6>
                    @foreach($mess->subscriptionPlans as $plan)
                    <div class="plan-card mb-2" onclick="selectPlan({{ $plan->id }}, '{{ $plan->name }}', {{ $plan->price }}, this)" id="plan-{{ $plan->id }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 style="font-weight:700;margin:0;font-size:0.92rem;">{{ $plan->name }}</h6>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @foreach($plan->slots as $s)
                                    <span style="background:var(--bg-subtle);border-radius:6px;padding:2px 7px;font-size:0.7rem;font-weight:600;color:var(--text-secondary);">{{ ucfirst($s) }}</span>
                                    @endforeach
                                </div>
                                <p style="font-size:0.78rem;color:var(--text-muted);margin:4px 0 0;">{{ $plan->duration_days }} days</p>
                            </div>
                            <strong style="font-size:1.1rem;color:var(--brand-primary);">₹{{ number_format($plan->price) }}</strong>
                        </div>
                    </div>
                    @endforeach
                    <div class="mb-3 mt-3">
                        <label class="form-label">Start Date</label>
                        <input type="date" id="subStartDate" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                    </div>
                    @auth
                    <button class="btn-primary-findr w-100" style="padding:0.75rem;" onclick="subscribeToMess()">
                        <i class="bi bi-credit-card me-2"></i>Subscribe Now
                    </button>
                    @else
                    <a href="/login" class="btn-primary-findr w-100 d-block text-center" style="padding:0.75rem;">Sign in to Subscribe</a>
                    @endauth
                </div>
                @endif

                <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.25rem;margin-top:1rem;">
                    <h6 style="font-weight:700;margin-bottom:0.75rem;">Contact</h6>
                    @if($mess->phone)
                    <a href="tel:{{ $mess->phone }}" class="d-flex align-items-center gap-2 mb-2" style="color:var(--text-secondary);font-size:0.88rem;text-decoration:none;">
                        <i class="bi bi-telephone" style="color:var(--brand-primary);"></i>{{ $mess->phone }}
                    </a>
                    @endif
                    <a href="{{ $mess->whatsapp_share ?? '#' }}" target="_blank"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;background:#25D366;color:#fff;border-radius:10px;padding:0.6rem;font-weight:600;font-size:0.88rem;text-decoration:none;margin-top:0.5rem;">
                        <i class="bi bi-whatsapp"></i>WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
function showSlot(slot, btn) {
    document.querySelectorAll('[id^="menu-"]').forEach(d => d.style.display='none');
    document.querySelectorAll('.slot-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('menu-'+slot).style.display = 'block';
    btn.classList.add('active');
}

let selectedPlanId = null, selectedPlanPrice = null;
function selectPlan(id, name, price, el) {
    selectedPlanId = id; selectedPlanPrice = price;
    document.querySelectorAll('.plan-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
}

let selectedRating = 0;
function setStarRating(r) {
    selectedRating = r;
    document.querySelectorAll('[data-star]').forEach((b,i) => b.style.color = i < r ? '#F59E0B' : 'var(--text-muted)');
}

function submitReview(id, type) {
    const body = document.getElementById('reviewBody').value;
    if (!selectedRating) { showToast('Please rate first','warning'); return; }
    if (body.length < 20) { showToast('Min 20 characters','warning'); return; }
    axios.post('/api/v1/reviews', { reviewable_type:type, reviewable_id:id, rating:selectedRating, body })
        .then(() => { showToast('Review submitted!','success'); setTimeout(()=>location.reload(),1500); })
        .catch(e => showToast(e.response?.data?.message||'Error','danger'));
}

function toggleFav() {
    axios.post('/api/v1/favourites', { type:'mess', id:{{ $mess->id }} })
        .then(r => {
            document.getElementById('favIcon').className = r.data.saved ? 'bi bi-heart-fill' : 'bi bi-heart';
            showToast(r.data.message);
        });
}

function copyLink() { navigator.clipboard.writeText(window.location.href).then(() => showToast('Link copied!')); }

function subscribeToMess() {
    if (!selectedPlanId) { showToast('Please select a plan','warning'); return; }
    const startDate = document.getElementById('subStartDate').value;
    axios.post('/api/v1/bookings/mess/create-order', { plan_id:selectedPlanId, start_date:startDate })
        .then(r => {
            const d = r.data.data;
            new Razorpay({
                key:d.key, amount:d.amount*100, currency:'INR',
                name:'SolMate', description:`Mess Subscription: ${d.booking_ref}`,
                order_id:d.order_id,
                theme:{ color:'#5C5FEF' },
                handler(res) {
                    axios.post('/api/v1/bookings/mess/verify', {
                        booking_id:d.booking_id,
                        razorpay_order_id:res.razorpay_order_id,
                        razorpay_payment_id:res.razorpay_payment_id,
                        razorpay_signature:res.razorpay_signature,
                    }).then(() => { showToast('Subscribed!','success'); setTimeout(()=>location.href='/bookings',2000); });
                }
            }).open();
        })
        .catch(e => showToast(e.response?.data?.message||'Error','danger'));
}
</script>
@endpush
