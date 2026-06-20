@extends('layouts.app')
@section('title', $hostel->name)

@push('styles')
<style>
.gallery-grid { display:grid; grid-template-columns:2fr 1fr 1fr; grid-template-rows:200px 200px; gap:8px; border-radius:16px; overflow:hidden; }
.gallery-grid .main-img { grid-row:1/3; }
.gallery-grid img { width:100%;height:100%;object-fit:cover;cursor:pointer;transition:transform 0.3s; }
.gallery-grid img:hover { transform:scale(1.02); }
.detail-sticky { position:sticky; top:80px; }
.price-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:16px; padding:1.5rem; box-shadow:var(--card-shadow); }
.room-card { background:var(--bg-surface); border:1.5px solid var(--border-color); border-radius:14px; overflow:hidden; transition:all 0.2s; margin-bottom:1rem; }
.room-card:hover { border-color:var(--brand-primary); box-shadow:0 4px 20px rgba(92,95,239,0.1); }
.amenity-pill { display:inline-flex;align-items:center;gap:6px;background:var(--bg-subtle);border-radius:8px;padding:6px 12px;font-size:0.8rem;font-weight:500;color:var(--text-secondary);margin:3px; }
.review-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:12px; padding:1.1rem; margin-bottom:0.75rem; }
.tab-btn { background:none; border:none; padding:0.65rem 1.25rem; font-size:0.88rem; font-weight:600; color:var(--text-muted); border-bottom:2px solid transparent; cursor:pointer; transition:all 0.15s; }
.tab-btn.active { color:var(--brand-primary); border-bottom-color:var(--brand-primary); }
</style>
@endpush

@section('content')
<div class="container py-4">

    <!-- Breadcrumb -->
    <nav style="font-size:0.82rem; color:var(--text-muted); margin-bottom:1.25rem;">
        <a href="/" style="color:var(--text-muted);">Home</a> /
        <a href="/hostels" style="color:var(--text-muted);">Hostels</a> /
        <span style="color:var(--text-primary);">{{ $hostel->name }}</span>
    </nav>

    <!-- Header Row -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge-status badge-{{ $hostel->status }}">{{ ucfirst($hostel->status) }}</span>
                <span style="background:var(--bg-subtle);border-radius:6px;padding:3px 10px;font-size:0.78rem;font-weight:600;color:var(--text-secondary);">
                    {{ $hostel->gender_type==='boys'?'🚹 Boys Only':($hostel->gender_type==='girls'?'🚺 Girls Only':'👥 Co-ed') }}
                </span>
            </div>
            <h1 style="font-size:1.75rem; font-weight:800; margin:0;">{{ $hostel->name }}</h1>
            <div class="d-flex align-items-center gap-2 mt-1">
                <span class="stars">{{ str_repeat('★', round($hostel->average_rating)) }}{{ str_repeat('☆', 5-round($hostel->average_rating)) }}</span>
                <span style="font-weight:600; font-size:0.88rem;">{{ number_format($hostel->average_rating,1) }}</span>
                <span style="color:var(--text-muted); font-size:0.85rem;">({{ $hostel->total_reviews }} reviews)</span>
                <span style="color:var(--text-muted); font-size:0.85rem;">· {{ $hostel->city }}, {{ $hostel->state }}</span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button onclick="toggleFav()" class="btn-outline-findr d-flex align-items-center gap-2" id="favBtn">
                <i class="bi bi-heart" id="favIcon"></i> Save
            </button>
            <div class="dropdown">
                <button class="btn-outline-findr d-flex align-items-center gap-2" data-bs-toggle="dropdown">
                    <i class="bi bi-share"></i> Share
                </button>
                <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-surface);border-color:var(--border-color);border-radius:12px;">
                    <li><a class="dropdown-item" href="{{ $hostel->whatsapp_share ?? '#' }}" target="_blank" style="color:#25D366;font-weight:600;">
                        <i class="bi bi-whatsapp me-2"></i>WhatsApp
                    </a></li>
                    <li><a class="dropdown-item" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" style="color:#1877F2;font-weight:600;">
                        <i class="bi bi-facebook me-2"></i>Facebook
                    </a></li>
                    <li><a class="dropdown-item" href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($hostel->name) }}" target="_blank" style="color:var(--text-primary);font-weight:600;">
                        <i class="bi bi-twitter-x me-2"></i>Twitter / X
                    </a></li>
                    <li><hr style="border-color:var(--border-color);margin:4px 0;"></li>
                    <li><button class="dropdown-item" onclick="copyLink()" style="color:var(--text-secondary);">
                        <i class="bi bi-link-45deg me-2"></i>Copy link
                    </button></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Gallery -->
    @if($hostel->images->count())
    <div class="gallery-grid mb-4">
        @foreach($hostel->images->take(5) as $i => $img)
        <div class="{{ $i===0 ? 'main-img' : '' }}" style="{{ $i===0 ? '' : '' }}">
            <img src="{{ $img->url }}" alt="{{ $hostel->name }}" onclick="openGallery({{ $i }})">
        </div>
        @endforeach
    </div>
    @else
    <div style="height:320px;background:var(--bg-subtle);border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:1.5rem;">
        <div style="text-align:center;color:var(--text-muted);"><i class="bi bi-image" style="font-size:3rem;"></i><p class="mt-2">No images yet</p></div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Left: Details -->
        <div class="col-lg-8">
            <!-- Tabs -->
            <div style="border-bottom:1px solid var(--border-color); margin-bottom:1.5rem;">
                <button class="tab-btn active" onclick="switchTab('overview',this)">Overview</button>
                <button class="tab-btn" onclick="switchTab('rooms',this)">Rooms ({{ $hostel->rooms->count() }})</button>
                <button class="tab-btn" onclick="switchTab('amenities',this)">Amenities</button>
                <button class="tab-btn" onclick="switchTab('reviews',this)">Reviews ({{ $hostel->total_reviews }})</button>
            </div>

            <!-- Overview Tab -->
            <div id="tab-overview">
                <p style="color:var(--text-secondary); line-height:1.8; font-size:0.92rem;">{{ $hostel->description ?: 'No description provided.' }}</p>
                <div class="row g-3 mt-2">
                    @if($hostel->curfew_time)
                    <div class="col-6 col-md-3">
                        <div style="background:var(--bg-subtle);border-radius:12px;padding:1rem;text-align:center;">
                            <i class="bi bi-clock" style="font-size:1.4rem;color:var(--brand-primary);"></i>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:4px 0 0;">Curfew</p>
                            <strong style="font-size:0.9rem;">{{ $hostel->curfew_time }}</strong>
                        </div>
                    </div>
                    @endif
                    <div class="col-6 col-md-3">
                        <div style="background:var(--bg-subtle);border-radius:12px;padding:1rem;text-align:center;">
                            <i class="bi bi-person-check" style="font-size:1.4rem;color:var(--brand-secondary);"></i>
                            <p style="font-size:0.75rem;color:var(--text-muted);margin:4px 0 0;">Guests</p>
                            <strong style="font-size:0.9rem;">{{ $hostel->allow_guests ? 'Allowed' : 'Not Allowed' }}</strong>
                        </div>
                    </div>
                </div>
                @if($hostel->house_rules)
                <div style="margin-top:1.5rem;background:rgba(249,115,22,0.06);border:1px solid rgba(249,115,22,0.2);border-radius:12px;padding:1rem 1.25rem;">
                    <h6 style="font-weight:700;margin-bottom:0.5rem;color:var(--brand-secondary);"><i class="bi bi-clipboard-check me-2"></i>House Rules</h6>
                    <p style="font-size:0.88rem;color:var(--text-secondary);margin:0;">{{ $hostel->house_rules }}</p>
                </div>
                @endif
            </div>

            <!-- Rooms Tab -->
            <div id="tab-rooms" style="display:none;">
                @forelse($hostel->rooms as $room)
                <div class="room-card">
                    <div class="row g-0">
                        @if($room->images->count())
                        <div class="col-md-4">
                            <img src="{{ $room->images->first()->url }}" alt="{{ $room->name }}" style="width:100%;height:180px;object-fit:cover;">
                        </div>
                        @endif
                        <div class="col-md-{{ $room->images->count() ? 8 : 12 }}">
                            <div style="padding:1.1rem;">
                                <div class="d-flex align-items-start justify-content-between">
                                    <div>
                                        <h6 style="font-weight:700;margin:0;">{{ $room->name }}</h6>
                                        <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                            <span style="background:var(--bg-subtle);border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:600;color:var(--text-secondary);">
                                                {{ ucfirst($room->type) }}
                                            </span>
                                            @if($room->is_ac)<span style="background:rgba(59,130,246,0.1);color:#2563EB;border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:600;">❄️ AC</span>@endif
                                            @if($room->has_attached_bathroom)<span style="background:var(--bg-subtle);border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:600;color:var(--text-secondary);">🚿 Attached Bath</span>@endif
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <strong style="font-size:1.1rem;color:var(--brand-primary);">₹{{ number_format($room->price_per_month) }}<span style="font-size:0.75rem;color:var(--text-muted);font-weight:400;">/mo</span></strong>
                                        @if($room->security_deposit)<p style="font-size:0.75rem;color:var(--text-muted);margin:2px 0 0;">₹{{ number_format($room->security_deposit) }} deposit</p>@endif
                                    </div>
                                </div>
                                <div style="margin:0.75rem 0;font-size:0.82rem;color:var(--text-muted);">
                                    Capacity: {{ $room->capacity }} · {{ $room->available_count }} of {{ $room->total_count }} available
                                </div>
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <span style="font-size:0.8rem; color:{{ $room->is_available ? 'var(--success)' : 'var(--danger)' }}; font-weight:600;">
                                        {{ $room->is_available && $room->available_count > 0 ? '✓ Available' : '✗ No Vacancy' }}
                                    </span>
                                    @if($room->is_available)
                                    <button class="btn-primary-findr" style="padding:0.45rem 1.1rem;font-size:0.84rem;" onclick="bookRoom({{ $room->id }}, '{{ $room->name }}', {{ $room->price_per_month }})">
                                        Book Now
                                    </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <p style="text-align:center;color:var(--text-muted);padding:2rem;">No rooms listed yet.</p>
                @endforelse
            </div>

            <!-- Amenities Tab -->
            <div id="tab-amenities" style="display:none;">
                <div style="display:flex; flex-wrap:wrap; gap:4px;">
                    @if($hostel->has_wifi)<span class="amenity-pill"><i class="bi bi-wifi"></i>Wi-Fi</span>@endif
                    @if($hostel->has_ac)<span class="amenity-pill"><i class="bi bi-thermometer-snow"></i>AC</span>@endif
                    @if($hostel->has_cctv)<span class="amenity-pill"><i class="bi bi-camera"></i>CCTV</span>@endif
                    @if($hostel->has_parking)<span class="amenity-pill"><i class="bi bi-p-circle"></i>Parking</span>@endif
                    @if($hostel->has_laundry)<span class="amenity-pill"><i class="bi bi-bag"></i>Laundry</span>@endif
                    @if($hostel->has_power_backup)<span class="amenity-pill"><i class="bi bi-lightning"></i>Power Backup</span>@endif
                    @if($hostel->has_gym)<span class="amenity-pill"><i class="bi bi-bicycle"></i>Gym</span>@endif
                    @if($hostel->has_mess)<span class="amenity-pill"><i class="bi bi-egg-fried"></i>In-house Mess</span>@endif
                    @if($hostel->has_security)<span class="amenity-pill"><i class="bi bi-shield-check"></i>24/7 Security</span>@endif
                    @foreach($hostel->amenities as $amenity)
                    <span class="amenity-pill"><i class="bi bi-{{ $amenity->icon ?? 'check2' }}"></i>{{ $amenity->name }}</span>
                    @endforeach
                </div>
            </div>

            <!-- Reviews Tab -->
            <div id="tab-reviews" style="display:none;">
                @auth
                <div style="background:var(--bg-subtle);border-radius:14px;padding:1.25rem;margin-bottom:1.5rem;">
                    <h6 style="font-weight:700;margin-bottom:1rem;">Write a Review</h6>
                    <div id="starRating" class="d-flex gap-2 mb-3">
                        @for($i=1;$i<=5;$i++)
                        <button type="button" onclick="setStarRating({{ $i }})" style="background:none;border:none;font-size:1.6rem;cursor:pointer;color:var(--text-muted);padding:0;transition:all 0.1s;" data-star="{{ $i }}">★</button>
                        @endfor
                    </div>
                    <textarea id="reviewBody" class="form-control" rows="3" placeholder="Share your experience... (min 20 characters)"></textarea>
                    <button class="btn-primary-findr mt-2" onclick="submitReview({{ $hostel->id }},'hostel')">Submit Review</button>
                </div>
                @endauth

                @forelse($hostel->reviews as $review)
                <div class="review-card">
                    <div class="d-flex align-items-start gap-3">
                        <img src="{{ $review->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($review->user->name).'&background=6366f1&color=fff' }}" class="avatar-md" alt="avatar" style="border-radius:50%;flex-shrink:0;">
                        <div style="flex:1;">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <strong style="font-size:0.9rem;">{{ $review->user->name }}</strong>
                                @if($review->is_verified)<span style="background:#D1FAE5;color:#065F46;font-size:0.7rem;font-weight:700;padding:2px 8px;border-radius:20px;">✓ Verified Stay</span>@endif
                                <span style="margin-left:auto;font-size:0.78rem;color:var(--text-muted);">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="stars" style="margin:3px 0;">{{ str_repeat('★',  $review->rating) }}{{ str_repeat('☆', 5-$review->rating) }}</div>
                            <p style="font-size:0.88rem;color:var(--text-secondary);margin:6px 0 0;">{{ $review->body }}</p>
                            @if($review->owner_reply)
                            <div style="background:rgba(92,95,239,0.06);border-left:3px solid var(--brand-primary);border-radius:0 8px 8px 0;padding:0.75rem;margin-top:0.75rem;">
                                <p style="font-size:0.8rem;font-weight:700;color:var(--brand-primary);margin:0 0 4px;">Owner Reply</p>
                                <p style="font-size:0.84rem;color:var(--text-secondary);margin:0;">{{ $review->owner_reply }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                    <i class="bi bi-chat-square-text" style="font-size:2rem;"></i>
                    <p style="margin-top:0.5rem;">No reviews yet. Be the first!</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Booking Card -->
        <div class="col-lg-4">
            <div class="detail-sticky">
                <div class="price-card">
                    <p style="font-size:0.8rem;color:var(--text-muted);margin-bottom:4px;">Starting from</p>
                    <h3 style="font-weight:800;color:var(--brand-primary);margin-bottom:0.75rem;">
                        ₹{{ number_format($hostel->rooms->min('price_per_month') ?? 0) }}<span style="font-size:0.85rem;font-weight:400;color:var(--text-muted);">/month</span>
                    </h3>
                    <hr style="border-color:var(--border-color);">
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span style="color:var(--text-muted);">Location</span>
                            <span style="font-weight:600;">{{ $hostel->city }}, {{ $hostel->state }}</span>
                        </div>
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span style="color:var(--text-muted);">Available Rooms</span>
                            <span style="font-weight:600; color:var(--brand-accent);">{{ $hostel->rooms->sum('available_count') }} rooms</span>
                        </div>
                        @if($hostel->phone)
                        <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                            <span style="color:var(--text-muted);">Contact</span>
                            <a href="tel:{{ $hostel->phone }}" style="font-weight:600;">{{ $hostel->phone }}</a>
                        </div>
                        @endif
                    </div>
                    @auth
                    <button class="btn-primary-findr w-100" style="padding:0.75rem;" data-bs-toggle="modal" data-bs-target="#bookingModal">
                        <i class="bi bi-calendar-check me-2"></i>Book a Room
                    </button>
                    @else
                    <a href="/login" class="btn-primary-findr w-100 d-block text-center" style="padding:0.75rem;">
                        Sign in to Book
                    </a>
                    @endauth
                    <a href="{{ $hostel->whatsapp_share ?? '#' }}" target="_blank"
                        style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:0.75rem;background:#25D366;color:#fff;border-radius:10px;padding:0.6rem;font-weight:600;font-size:0.88rem;text-decoration:none;">
                        <i class="bi bi-whatsapp"></i>Contact on WhatsApp
                    </a>
                </div>

                <!-- Owner Info -->
                <div class="price-card mt-3">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $hostel->owner->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($hostel->owner->name).'&background=6366f1&color=fff' }}" class="avatar-md" style="border-radius:50%;">
                        <div>
                            <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">Managed by</p>
                            <strong style="font-size:0.92rem;">{{ $hostel->owner->name }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);border-color:var(--border-color);border-radius:20px;">
            <div class="modal-header" style="border-color:var(--border-color);">
                <h5 class="modal-title" style="font-weight:700;">Book a Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Select Room</label>
                    <select id="bookRoomId" class="form-select">
                        @foreach($hostel->rooms->where('is_available', true) as $room)
                        <option value="{{ $room->id }}" data-price="{{ $room->price_per_month }}" data-deposit="{{ $room->security_deposit }}">
                            {{ $room->name }} — ₹{{ number_format($room->price_per_month) }}/mo
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <label class="form-label">Check-in Date</label>
                        <input type="date" id="checkIn" class="form-control" min="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label">Check-out Date</label>
                        <input type="date" id="checkOut" class="form-control">
                    </div>
                </div>
                <div class="mb-3 mt-3">
                    <label class="form-label">Note for Owner (optional)</label>
                    <textarea id="userNote" class="form-control" rows="2" placeholder="Any special requirements..."></textarea>
                </div>
                <div id="bookingTotal" style="background:var(--bg-subtle);border-radius:10px;padding:0.9rem;display:none;">
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                        <span style="color:var(--text-muted);">Monthly Rent</span>
                        <span id="rentDisplay">—</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                        <span style="color:var(--text-muted);">Security Deposit</span>
                        <span id="depositDisplay">—</span>
                    </div>
                    <hr style="border-color:var(--border-color);margin:0.5rem 0;">
                    <div class="d-flex justify-content-between" style="font-weight:700;">
                        <span>Total</span>
                        <span id="totalDisplay" style="color:var(--brand-primary);">—</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-color:var(--border-color);">
                <button type="button" class="btn-outline-findr" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn-primary-findr" onclick="proceedBooking()">
                    <i class="bi bi-credit-card me-2"></i>Proceed to Pay
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
// Tabs
function switchTab(name, btn) {
    document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display='none');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-'+name).style.display='block';
    btn.classList.add('active');
}

// Reviews
let selectedRating = 0;
function setStarRating(r) {
    selectedRating = r;
    document.querySelectorAll('[data-star]').forEach((b,i) => { b.style.color = i < r ? '#F59E0B' : 'var(--text-muted)'; });
}

function submitReview(id, type) {
    const body = document.getElementById('reviewBody').value;
    if (!selectedRating) { showToast('Please select a rating','warning'); return; }
    if (body.length < 20) { showToast('Review must be at least 20 characters','warning'); return; }
    axios.post('/api/v1/reviews', { reviewable_type:type, reviewable_id:id, rating:selectedRating, body })
        .then(() => { showToast('Review submitted!','success'); setTimeout(()=>location.reload(),1500); })
        .catch(e  => showToast(e.response?.data?.message||'Error','danger'));
}

// Favourites
function toggleFav() {
    axios.post('/api/v1/favourites', { type:'hostel', id:{{ $hostel->id }} })
        .then(r => {
            document.getElementById('favIcon').className = r.data.saved ? 'bi bi-heart-fill' : 'bi bi-heart';
            document.getElementById('favBtn').innerHTML = (r.data.saved ? '<i class="bi bi-heart-fill"></i> Saved' : '<i class="bi bi-heart"></i> Save');
            showToast(r.data.message);
        });
}

// Copy Link
function copyLink() { navigator.clipboard.writeText(window.location.href).then(()=>showToast('Link copied!')); }

// Booking
document.querySelectorAll('#bookRoomId, #checkIn, #checkOut').forEach(el => el.addEventListener('change', calcTotal));
function calcTotal() {
    const sel = document.getElementById('bookRoomId');
    const opt = sel.options[sel.selectedIndex];
    const ci  = document.getElementById('checkIn').value;
    const co  = document.getElementById('checkOut').value;
    if (!ci || !co) return;
    const months  = Math.max(1, Math.round((new Date(co)-new Date(ci))/(1000*60*60*24*30)));
    const rent    = parseFloat(opt.dataset.price) || 0;
    const deposit = parseFloat(opt.dataset.deposit) || 0;
    const total   = rent * months + deposit;
    document.getElementById('rentDisplay').textContent    = `₹${rent.toLocaleString()} × ${months} mo`;
    document.getElementById('depositDisplay').textContent = `₹${deposit.toLocaleString()}`;
    document.getElementById('totalDisplay').textContent   = `₹${total.toLocaleString()}`;
    document.getElementById('bookingTotal').style.display = 'block';
}

function proceedBooking() {
    const roomId   = document.getElementById('bookRoomId').value;
    const checkIn  = document.getElementById('checkIn').value;
    const checkOut = document.getElementById('checkOut').value;
    const note     = document.getElementById('userNote').value;
    if (!checkIn || !checkOut) { showToast('Select check-in and check-out dates','warning'); return; }

    axios.post('/api/v1/bookings/hostel/create-order', { room_id:roomId, check_in:checkIn, check_out:checkOut, user_note:note })
        .then(r => {
            const d = r.data.data;
            const rzp = new Razorpay({
                key: d.key, amount: d.amount*100, currency:'INR',
                name:'SolMate', description:`Booking: ${d.booking_ref}`,
                order_id: d.order_id,
                prefill: d.prefill,
                theme: { color:'#5C5FEF' },
                handler(res) {
                    axios.post('/api/v1/bookings/hostel/verify', {
                        booking_id: d.booking_id,
                        razorpay_order_id: res.razorpay_order_id,
                        razorpay_payment_id: res.razorpay_payment_id,
                        razorpay_signature: res.razorpay_signature,
                    }).then(() => { showToast('Booking confirmed! 🎉','success'); setTimeout(()=>location.href='/bookings',2000); });
                }
            });
            rzp.open();
        })
        .catch(e => showToast(e.response?.data?.message||'Booking failed','danger'));
}
</script>
@endpush
