<?php $__env->startSection('title', 'Find Hostels & Messes Near You'); ?>

<?php $__env->startPush('styles'); ?>
<style>
/* Hero */
.search-hero {
    background: linear-gradient(135deg, #0F0F23 0%, #1a1a3e 50%, #0F0F23 100%);
    padding: 4rem 0 6rem;
    position: relative;
    overflow: hidden;
}
.search-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse at 30% 50%, rgba(92,95,239,0.3) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 30%, rgba(249,115,22,0.15) 0%, transparent 50%);
}
.search-hero h1 { font-size: clamp(2rem, 5vw, 3.2rem); font-weight:800; color:#fff; line-height:1.15; }
.search-hero h1 em { color: var(--brand-secondary); font-style:normal; }
.search-bar {
    background: var(--bg-surface);
    border-radius: 16px;
    padding: 0.5rem;
    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    border: 1px solid var(--border-color);
}
.search-input-wrap { flex:1; min-width:200px; position:relative; }
.search-input-wrap input { border:none; background:transparent; font-size:0.95rem; color:var(--text-primary); padding:0.65rem 0.75rem 0.65rem 2.5rem; width:100%; }
.search-input-wrap input:focus { outline:none; }
.search-input-wrap i { position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-muted); }
.search-divider { width:1px; height:36px; background:var(--border-color); }
.filter-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:20px;
    border:1.5px solid var(--border-color);
    font-size:0.8rem; font-weight:600;
    color:var(--text-secondary); background:var(--bg-subtle);
    cursor:pointer; transition:all 0.15s; white-space:nowrap;
}
.filter-chip:hover, .filter-chip.active { border-color:var(--brand-primary); color:var(--brand-primary); background:rgba(92,95,239,0.08); }
.listing-card {
    background:var(--bg-surface); border-radius:16px; border:1px solid var(--border-color);
    overflow:hidden; transition:all 0.25s; cursor:pointer;
}
.listing-card:hover { transform:translateY(-4px); box-shadow:var(--card-shadow-hover); border-color:transparent; }
.listing-card img { width:100%; height:200px; object-fit:cover; }
.listing-card .badge-type {
    position:absolute; top:12px; left:12px;
    background:rgba(0,0,0,0.65); backdrop-filter:blur(6px);
    color:#fff; font-size:0.72rem; font-weight:700;
    padding:3px 10px; border-radius:20px;
    text-transform:uppercase; letter-spacing:0.06em;
}
.listing-card .badge-featured {
    position:absolute; top:12px; right:12px;
    background:var(--brand-secondary); color:#fff;
    font-size:0.7rem; font-weight:700; padding:3px 10px; border-radius:20px;
}
.fav-btn {
    position:absolute; bottom:12px; right:12px;
    width:34px; height:34px; border-radius:50%;
    background:rgba(255,255,255,0.9); border:none;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; transition:all 0.2s;
    color:#9CA3AF; font-size:1rem;
}
.fav-btn:hover, .fav-btn.saved { color:#EF4444; background:#fff; transform:scale(1.1); }
.distance-pill {
    display:inline-flex; align-items:center; gap:4px;
    background:var(--bg-subtle); color:var(--text-muted);
    font-size:0.75rem; font-weight:600; padding:3px 8px; border-radius:20px;
}
.section-title { font-size:1.5rem; font-weight:800; margin-bottom:0.25rem; }
.section-sub   { color:var(--text-muted); font-size:0.88rem; margin-bottom:1.5rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

<!-- ── SEARCH HERO ──────────────────────────────── -->
<section class="search-hero">
    <div class="container position-relative" style="z-index:2;">
        <div class="text-center mb-4">
            <h1>Find <em>Hostels</em> & <em>Mess</em><br>near you</h1>
            <p style="color:rgba(255,255,255,0.65); font-size:1rem; margin-top:0.75rem;">
                Real-time availability · Location-based · Verified listings
            </p>
        </div>

        <!-- Search Bar -->
        <div class="search-bar" style="max-width:700px; margin:0 auto;">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="searchQuery" placeholder="Search hostel, mess or area..." autocomplete="off">
            </div>
            <div class="search-divider d-none d-md-block"></div>
            <div class="search-input-wrap d-none d-md-flex align-items-center" style="min-width:160px;">
                <i class="bi bi-geo-alt"></i>
                <input type="text" id="locationInput" placeholder="Location" style="font-size:0.88rem;">
            </div>
            <button class="btn-primary-findr" onclick="doSearch()" style="border-radius:12px; padding:0.7rem 1.5rem; white-space:nowrap;">
                <i class="bi bi-search me-1 d-none d-md-inline"></i>Search
            </button>
        </div>

        <!-- Quick Filters -->
        <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
            <button class="filter-chip active" onclick="setType('both',this)"><i class="bi bi-grid"></i>All</button>
            <button class="filter-chip" onclick="setType('hostel',this)"><i class="bi bi-building"></i>Hostels</button>
            <button class="filter-chip" onclick="setType('mess',this)"><i class="bi bi-egg-fried"></i>Messes</button>
            <button class="filter-chip" onclick="toggleFilter('wifi')"><i class="bi bi-wifi"></i>Wi-Fi</button>
            <button class="filter-chip" onclick="toggleFilter('ac')"><i class="bi bi-thermometer-snow"></i>AC</button>
            <button class="filter-chip" onclick="toggleFilter('delivery')"><i class="bi bi-bicycle"></i>Delivery</button>
            <button class="filter-chip" onclick="toggleFilter('veg')"><i class="bi bi-leaf"></i>Veg Only</button>
        </div>
    </div>
</section>

<!-- ── STAT BAR ───────────────────────────────── -->
<div style="background:var(--bg-surface); border-bottom:1px solid var(--border-color);">
    <div class="container">
        <div class="row py-3 text-center">
            <div class="col-4">
                <strong style="font-size:1.2rem; color:var(--brand-primary);"><?php echo e($stats['hostels'] ?? '50+'); ?></strong>
                <p style="font-size:0.78rem; color:var(--text-muted); margin:0;">Verified Hostels</p>
            </div>
            <div class="col-4" style="border-left:1px solid var(--border-color); border-right:1px solid var(--border-color);">
                <strong style="font-size:1.2rem; color:var(--brand-secondary);"><?php echo e($stats['messes'] ?? '30+'); ?></strong>
                <p style="font-size:0.78rem; color:var(--text-muted); margin:0;">Registered Messes</p>
            </div>
            <div class="col-4">
                <strong style="font-size:1.2rem; color:var(--brand-accent);"><?php echo e($stats['cities'] ?? '5+'); ?></strong>
                <p style="font-size:0.78rem; color:var(--text-muted); margin:0;">Cities</p>
            </div>
        </div>
    </div>
</div>

<!-- ── MAIN LISTINGS ──────────────────────────── -->
<div class="container py-5">
    <div class="row g-4">
        <!-- Listings -->
        <div class="col-lg-8">
            <!-- Location Banner -->
            <div style="background:rgba(92,95,239,0.07);border:1px solid rgba(92,95,239,0.2);border-radius:12px;padding:0.9rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;">
                <i class="bi bi-geo-alt-fill" style="color:var(--brand-primary);font-size:1.1rem;"></i>
                <span style="font-size:0.88rem;color:var(--text-secondary);">
                    Showing results near <strong id="locationDisplay" style="color:var(--text-primary);">your location</strong>
                </span>
                <button onclick="getLocation()" style="margin-left:auto;background:none;border:none;font-size:0.82rem;color:var(--brand-primary);cursor:pointer;font-weight:600;">
                    <i class="bi bi-crosshair me-1"></i>Update Location
                </button>
            </div>

            <!-- Results Header -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="section-title" id="resultsTitle">Nearby Listings</h2>
                <select class="form-select" id="sortSelect" style="width:auto;font-size:0.85rem;" onchange="doSearch()">
                    <option value="distance">Nearest First</option>
                    <option value="rating">Top Rated</option>
                    <option value="price_asc">Price: Low to High</option>
                </select>
            </div>

            <!-- Loading Skeleton -->
            <div id="skeleton" class="row g-3">
                <?php for($i=0; $i<6; $i++): ?>
                <div class="col-md-6">
                    <div style="background:var(--bg-surface);border-radius:16px;overflow:hidden;border:1px solid var(--border-color);">
                        <div style="height:200px;background:linear-gradient(90deg,var(--bg-subtle) 25%,var(--border-color) 50%,var(--bg-subtle) 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;"></div>
                        <div style="padding:1rem;">
                            <div style="height:16px;background:var(--bg-subtle);border-radius:4px;margin-bottom:8px;width:70%;"></div>
                            <div style="height:12px;background:var(--bg-subtle);border-radius:4px;width:50%;"></div>
                        </div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
          
<style>
    @keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>

            <!-- Results Grid -->
            <div id="resultsGrid" class="row g-3" style="display:none;"></div>

            <!-- Empty State -->
            <div id="emptyState" style="display:none; text-align:center; padding:4rem 2rem;">
                <div style="font-size:3.5rem; margin-bottom:1rem;">🔍</div>
                <h4 style="font-weight:700; margin-bottom:0.5rem;">No results found</h4>
                <p style="color:var(--text-muted); font-size:0.9rem;">Try adjusting your filters or expanding the search radius.</p>
            </div>

            <!-- Load More -->
            <div id="loadMoreWrap" style="text-align:center; margin-top:2rem; display:none;">
                <button class="btn-outline-findr" onclick="loadMore()">Load more results</button>
            </div>
        </div>

        <!-- Sidebar Filters -->
        <div class="col-lg-4 d-none d-lg-block">
            <div style="position:sticky; top:80px;">
                <div class="card-findr p-4">
                    <h6 style="font-weight:700; margin-bottom:1.25rem;">Refine Results</h6>

                    <div class="mb-4">
                        <label class="form-label">Search Radius</label>
                        <input type="range" id="radius" min="1" max="20" value="5" style="width:100%; accent-color:var(--brand-primary);" oninput="document.getElementById('radiusVal').textContent=this.value">
                        <div class="d-flex justify-content-between" style="font-size:0.78rem; color:var(--text-muted); margin-top:4px;">
                            <span>1 km</span><span id="radiusVal">5</span> km<span>20 km</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Price Range (₹/month)</label>
                        <div class="d-flex gap-2 align-items-center">
                            <input type="number" id="minPrice" class="form-control" placeholder="Min" style="font-size:0.85rem;">
                            <span style="color:var(--text-muted);">–</span>
                            <input type="number" id="maxPrice" class="form-control" placeholder="Max" style="font-size:0.85rem;">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Hostel For</label>
                        <div class="d-flex flex-wrap gap-2">
                            <button class="filter-chip" onclick="setGender('boys',this)">🚹 Boys</button>
                            <button class="filter-chip" onclick="setGender('girls',this)">🚺 Girls</button>
                            <button class="filter-chip" onclick="setGender('coed',this)">👥 Co-ed</button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Minimum Rating</label>
                        <div class="d-flex gap-2">
                            <?php $__currentLoopData = [1,2,3,4,5]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <button onclick="setRating(<?php echo e($r); ?>,this)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;padding:2px;color:var(--text-muted);" title="<?php echo e($r); ?> star+">★</button>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <button class="btn-primary-findr w-100" onclick="doSearch()">
                        <i class="bi bi-funnel me-2"></i>Apply Filters
                    </button>
                    <button class="btn-outline-findr w-100 mt-2" onclick="resetFilters()">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="background:var(--bg-surface);border-color:var(--border-color);border-radius:20px;">
            <div class="modal-header" style="border-color:var(--border-color);">
                <h5 class="modal-title" style="font-weight:700;">Map View</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div id="map" style="height:500px; border-radius:0 0 20px 20px;"></div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let userLat = null, userLng = null;
let activeFilters = { type:'both', wifi:false, ac:false, delivery:false, veg:false, gender:null, minRating:null };
let currentPage = 1;
let allResults  = { hostels:[], messes:[] };

// Auto-locate on load
window.addEventListener('DOMContentLoaded', () => { getLocation(); });

function getLocation() {
    if (!navigator.geolocation) { loadResults(11.2588, 75.7804); return; }
    navigator.geolocation.getCurrentPosition(
        p => { userLat=p.coords.latitude; userLng=p.coords.longitude; loadResults(userLat,userLng); },
        ()  => { loadResults(11.2588, 75.7804); /* default: Kozhikode */ }
    );
}

function loadResults(lat, lng) {
    userLat = lat; userLng = lng;
    document.getElementById('skeleton').style.display = 'flex';
    document.getElementById('resultsGrid').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';

    const params = new URLSearchParams({
        lat, lng,
        radius  : document.getElementById('radius')?.value ?? 5,
        type    : activeFilters.type,
        sort    : document.getElementById('sortSelect')?.value ?? 'distance',
        q       : document.getElementById('searchQuery')?.value ?? '',
        ...(activeFilters.wifi     && { has_wifi:1 }),
        ...(activeFilters.ac       && { has_ac:1 }),
        ...(activeFilters.delivery && { has_delivery:1 }),
        ...(activeFilters.veg      && { food_type:'veg' }),
        ...(activeFilters.gender   && { gender_type:activeFilters.gender }),
        ...(activeFilters.minRating && { min_rating:activeFilters.minRating }),
        ...(document.getElementById('minPrice')?.value && { min_price:document.getElementById('minPrice').value }),
        ...(document.getElementById('maxPrice')?.value && { max_price:document.getElementById('maxPrice').value }),
    });

    axios.get(`/api/v1/search/nearby?${params}`)
        .then(r => {
            const data = r.data.data;
            allResults.hostels = data.hostels?.data ?? [];
            allResults.messes  = data.messes?.data  ?? [];
            renderResults();
        })
        .catch(() => {
            document.getElementById('skeleton').style.display = 'none';
            document.getElementById('emptyState').style.display = 'block';
        });
}

function renderResults() {
    const grid  = document.getElementById('resultsGrid');
    const items = [...allResults.hostels, ...allResults.messes];
    document.getElementById('skeleton').style.display = 'none';

    if (!items.length) {
        document.getElementById('emptyState').style.display = 'block';
        return;
    }

    grid.style.display = 'flex';
    grid.innerHTML = items.map(item => item.type === 'hostel' ? hostelCard(item) : messCard(item)).join('');
    document.getElementById('resultsTitle').textContent = `${items.length} listings found`;
    document.getElementById('loadMoreWrap').style.display = items.length >= 10 ? 'block' : 'none';
}

function hostelCard(h) {
    const stars = '★'.repeat(Math.round(h.rating||0)) + '☆'.repeat(5-Math.round(h.rating||0));
    return `<div class="col-md-6">
    <div class="listing-card" onclick="window.location='/hostels/${h.slug}'">
        <div style="position:relative;">
            <img src="${h.cover_image || '/images/hostel-placeholder.jpg'}" alt="${h.name}" style="width:100%;height:200px;object-fit:cover;">
            <span class="badge-type">Hostel</span>
            ${h.is_featured ? '<span class="badge-featured">⭐ Featured</span>' : ''}
            <button class="fav-btn" onclick="toggleFav(event,'hostel',${h.id},this)"><i class="bi bi-heart"></i></button>
        </div>
        <div style="padding:1rem 1.1rem 1.2rem;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <h6 style="font-weight:700;font-size:0.95rem;margin:0;line-height:1.3;">${h.name}</h6>
                <span class="distance-pill"><i class="bi bi-geo-alt"></i>${h.distance_km ?? '?'} km</span>
            </div>
            <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 8px;">${h.address}</p>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="stars">${stars}</span>
                <span class="rating-text">${h.rating?.toFixed(1)||'New'} (${h.total_reviews})</span>
                <span class="ms-auto" style="font-size:0.78rem;color:var(--text-muted);">${h.gender_type==='boys'?'🚹 Boys':h.gender_type==='girls'?'🚺 Girls':'👥 Co-ed'}</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span style="font-size:0.78rem;color:var(--text-muted);">Starting from</span>
                    <strong style="display:block;font-size:1rem;color:var(--brand-primary);">₹${(h.starting_price||0).toLocaleString()}<span style="font-weight:400;font-size:0.78rem;color:var(--text-muted);">/mo</span></strong>
                </div>
                <div class="d-flex gap-1">
                    ${h.has_wifi ? '<span style="background:var(--bg-subtle);border-radius:6px;padding:3px 7px;font-size:0.7rem;" title="Wi-Fi">📶</span>' : ''}
                    ${h.has_ac   ? '<span style="background:var(--bg-subtle);border-radius:6px;padding:3px 7px;font-size:0.7rem;" title="AC">❄️</span>' : ''}
                </div>
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="/hostels/${h.slug}" class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;">View Details</a>
                <a href="${h.whatsapp_share}" target="_blank" style="background:#25D366;color:#fff;border-radius:10px;padding:0.5rem 0.75rem;display:flex;align-items:center;gap:4px;font-size:0.82rem;font-weight:600;text-decoration:none;" onclick="event.stopPropagation()"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>
    </div>`;
}

function messCard(m) {
    const stars = '★'.repeat(Math.round(m.rating||0)) + '☆'.repeat(5-Math.round(m.rating||0));
    const openSlot = Object.entries(m.slots_open||{}).find(([,v])=>v)?.[0];
    return `<div class="col-md-6">
    <div class="listing-card" onclick="window.location='/messes/${m.slug}'">
        <div style="position:relative;">
            <img src="${m.cover_image || '/images/mess-placeholder.jpg'}" alt="${m.name}" style="width:100%;height:200px;object-fit:cover;">
            <span class="badge-type">Mess</span>
            <span style="position:absolute;top:12px;right:12px;" class="badge-${m.food_type==='veg'?'veg':'nonveg'}">${m.food_type==='veg'?'🥦 Veg':'🍗 Non-Veg'}</span>
            <button class="fav-btn" onclick="toggleFav(event,'mess',${m.id},this)"><i class="bi bi-heart"></i></button>
        </div>
        <div style="padding:1rem 1.1rem 1.2rem;">
            <div class="d-flex align-items-start justify-content-between gap-2">
                <h6 style="font-weight:700;font-size:0.95rem;margin:0;">${m.name}</h6>
                <span class="distance-pill"><i class="bi bi-geo-alt"></i>${m.distance_km ?? '?'} km</span>
            </div>
            <p style="font-size:0.8rem;color:var(--text-muted);margin:4px 0 8px;">${m.address}</p>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="stars">${stars}</span>
                <span class="rating-text">${m.rating?.toFixed(1)||'New'} (${m.total_reviews})</span>
            </div>
            <!-- Slot Status -->
            <div class="d-flex gap-1 flex-wrap mb-3">
                ${['morning','afternoon','evening','night'].map(s => `<span class="slot-pill ${m.slots_open?.[s]?'slot-open':'slot-closed'}"><span class="slot-dot"></span>${s.charAt(0).toUpperCase()+s.slice(1)}</span>`).join('')}
            </div>
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span style="font-size:0.78rem;color:var(--text-muted);">Meals from</span>
                    <strong style="display:block;font-size:1rem;color:var(--brand-primary);">₹${m.cheapest_meal||'N/A'}</strong>
                </div>
                ${m.has_delivery ? '<span style="background:rgba(16,185,129,0.1);color:var(--brand-accent);border-radius:8px;padding:4px 10px;font-size:0.75rem;font-weight:600;">🛵 Delivery</span>' : ''}
            </div>
            <div class="d-flex gap-2 mt-3">
                <a href="/messes/${m.slug}" class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;">View Menu</a>
                <a href="${m.whatsapp_share}" target="_blank" style="background:#25D366;color:#fff;border-radius:10px;padding:0.5rem 0.75rem;display:flex;align-items:center;gap:4px;font-size:0.82rem;font-weight:600;text-decoration:none;" onclick="event.stopPropagation()"><i class="bi bi-whatsapp"></i></a>
            </div>
        </div>
    </div>
    </div>`;
}

function doSearch()   { if(userLat) loadResults(userLat,userLng); }
function loadMore()   { /* TODO: implement pagination */ }
function resetFilters(){ activeFilters={type:'both'};document.querySelectorAll('.filter-chip').forEach(c=>c.classList.remove('active'));document.querySelector('.filter-chip').classList.add('active');doSearch(); }

function setType(t,el) {
    activeFilters.type=t;
    document.querySelectorAll('.filter-chip').forEach(c=>{if(['both','hostel','mess'].some(x=>c.getAttribute('onclick')?.includes(x)))c.classList.remove('active');});
    el.classList.add('active');
    doSearch();
}

function toggleFilter(f) {
    activeFilters[f]=!activeFilters[f];
    event.currentTarget.classList.toggle('active', activeFilters[f]);
    doSearch();
}

function setGender(g,el) {
    activeFilters.gender = activeFilters.gender===g ? null : g;
    document.querySelectorAll('[onclick*="setGender"]').forEach(b=>b.classList.remove('active'));
    if(activeFilters.gender) el.classList.add('active');
    doSearch();
}

function setRating(r,el) {
    activeFilters.minRating = activeFilters.minRating===r ? null : r;
    const btns = document.querySelectorAll('[onclick*="setRating"]');
    btns.forEach((b,i) => b.style.color = i < r && activeFilters.minRating ? 'var(--warning)' : 'var(--text-muted)');
    doSearch();
}

function toggleFav(e,type,id,btn) {
    e.stopPropagation();
    <?php if(auth()->guard()->check()): ?>
    axios.post('/api/v1/favourites', {type,id})
        .then(r => {
            btn.classList.toggle('saved', r.data.saved);
            btn.querySelector('i').className = r.data.saved ? 'bi bi-heart-fill' : 'bi bi-heart';
            showToast(r.data.message, 'success');
        });
    <?php else: ?>
    window.location='/login';
    <?php endif; ?>
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/user/home.blade.php ENDPATH**/ ?>