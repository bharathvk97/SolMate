@extends('layouts.app')
@section('title', 'Find Hostels & Messes Near You')

@push('styles')
<style>
.search-hero {
    background: linear-gradient(135deg, #0F0F23 0%, #1a1a3e 50%, #0F0F23 100%);
    padding: 4rem 0 5rem; position: relative; overflow: hidden;
}
.search-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(ellipse at 30% 50%, rgba(92,95,239,0.3) 0%, transparent 60%),
                radial-gradient(ellipse at 70% 30%, rgba(249,115,22,0.15) 0%, transparent 50%);
}
.search-hero h1 { font-size:clamp(1.8rem,5vw,3rem); font-weight:800; color:#fff; line-height:1.15; }
.search-hero h1 em { color:var(--brand-secondary); font-style:normal; }
.search-bar {
    background:var(--bg-surface); border-radius:16px; padding:0.5rem;
    box-shadow:0 20px 60px rgba(0,0,0,0.3); display:flex; align-items:center;
    gap:0.5rem; flex-wrap:wrap; border:1px solid var(--border-color);
}
.search-input-wrap { flex:1; min-width:180px; position:relative; }
.search-input-wrap input {
    border:none; background:transparent; font-size:0.93rem;
    color:var(--text-primary); padding:0.65rem 0.75rem 0.65rem 2.4rem; width:100%;
}
.search-input-wrap input:focus { outline:none; }
.search-input-wrap .si { position:absolute; left:0.75rem; top:50%; transform:translateY(-50%); color:var(--text-muted); font-size:0.9rem; pointer-events:none; }
.search-divider { width:1px; height:36px; background:var(--border-color); flex-shrink:0; }

/* Location autocomplete dropdown */
#locationSuggestions {
    position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:9999;
    background:var(--bg-surface); border:1px solid var(--border-color);
    border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,0.15);
    max-height:280px; overflow-y:auto; display:none;
}
#locationSuggestions .sug-item {
    padding:0.7rem 1rem; cursor:pointer; display:flex; align-items:flex-start;
    gap:10px; border-bottom:1px solid var(--border-color); transition:background 0.1s;
    font-size:0.85rem; color:var(--text-primary);
}
#locationSuggestions .sug-item:last-child { border-bottom:none; }
#locationSuggestions .sug-item:hover { background:var(--bg-subtle); }
#locationSuggestions .sug-item .sug-icon { color:var(--brand-primary); flex-shrink:0; margin-top:2px; }
#locationSuggestions .sug-item .sug-main { font-weight:600; }
#locationSuggestions .sug-item .sug-sub  { font-size:0.75rem; color:var(--text-muted); margin-top:1px; }
.loc-wrap { position:relative; }

.filter-chip {
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 14px; border-radius:20px; border:1.5px solid var(--border-color);
    font-size:0.8rem; font-weight:600; color:var(--text-secondary);
    background:var(--bg-subtle); cursor:pointer; transition:all 0.15s; white-space:nowrap;
}
.filter-chip:hover, .filter-chip.active { border-color:var(--brand-primary); color:var(--brand-primary); background:rgba(92,95,239,0.08); }
.listing-card {
    background:var(--bg-surface); border-radius:16px; border:1px solid var(--border-color);
    overflow:hidden; transition:all 0.25s; cursor:pointer;
}
.listing-card:hover { transform:translateY(-4px); box-shadow:var(--card-shadow-hover); border-color:transparent; }
.listing-card img { width:100%; height:195px; object-fit:cover; }
.badge-type {
    position:absolute; top:12px; left:12px;
    background:rgba(0,0,0,0.65); backdrop-filter:blur(6px);
    color:#fff; font-size:0.7rem; font-weight:700;
    padding:3px 10px; border-radius:20px; text-transform:uppercase;
}
@keyframes shimmer { 0%{background-position:200% 0} 100%{background-position:-200% 0} }
</style>
@endpush

@section('content')

<!-- SEARCH HERO -->
<section class="search-hero">
  <div class="container position-relative" style="z-index:2;">
    <div class="text-center mb-4">
      <h1>Find <em>Hostels</em> & <em>Mess</em><br>near you</h1>
      <p style="color:rgba(255,255,255,0.65);font-size:1rem;margin-top:0.75rem;">
        Real-time availability · Location-based · Verified listings
      </p>
    </div>

    <!-- Search Bar -->
    <div class="search-bar" style="max-width:720px;margin:0 auto;">
      <!-- Keyword search -->
      <div class="search-input-wrap">
        <i class="bi bi-search si"></i>
        <input type="text" id="searchQuery" placeholder="Search hostel, mess or name…" autocomplete="off">
      </div>

      <div class="search-divider d-none d-md-block"></div>

      <!-- Location input with autocomplete -->
      <div class="search-input-wrap loc-wrap d-none d-md-flex align-items-center" style="min-width:200px;">
        <i class="bi bi-geo-alt si"></i>
        <input type="text" id="locationInput" placeholder="Enter area, city…"
               autocomplete="off" oninput="onLocationTyped(this.value)" onkeydown="onLocationKeydown(event)"
               style="font-size:0.88rem;">
        <button id="gpsBtn" onclick="requestGPS()" title="Use my current location"
                style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0 6px;flex-shrink:0;font-size:1rem;">
          <i class="bi bi-crosshair"></i>
        </button>
        <div id="locationSuggestions"></div>
      </div>

      <button class="btn-primary-findr" onclick="doSearch()" style="border-radius:12px;padding:0.7rem 1.5rem;white-space:nowrap;">
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

<!-- STAT BAR -->
<div style="background:var(--bg-surface);border-bottom:1px solid var(--border-color);">
  <div class="container">
    <div class="row py-3 text-center">
      <div class="col-4">
        <strong style="font-size:1.2rem;color:var(--brand-primary);">{{ $stats['hostels'] ?? '0' }}</strong>
        <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">Verified Hostels</p>
      </div>
      <div class="col-4" style="border-left:1px solid var(--border-color);border-right:1px solid var(--border-color);">
        <strong style="font-size:1.2rem;color:var(--brand-secondary);">{{ $stats['messes'] ?? '0' }}</strong>
        <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">Registered Messes</p>
      </div>
      <div class="col-4">
        <strong style="font-size:1.2rem;color:var(--brand-accent);">{{ $stats['cities'] ?? '0' }}</strong>
        <p style="font-size:0.78rem;color:var(--text-muted);margin:0;">Cities</p>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="container py-5">
  <div class="row g-4">

    <!-- Results -->
    <div class="col-lg-8">

      <!-- Location Banner -->
      <div id="locationBanner" style="background:rgba(92,95,239,0.07);border:1px solid rgba(92,95,239,0.2);border-radius:12px;padding:0.9rem 1.1rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        <i class="bi bi-geo-alt-fill" style="color:var(--brand-primary);font-size:1.1rem;"></i>
        <span style="font-size:0.88rem;color:var(--text-secondary);flex:1;">
          Showing results near <strong id="locationDisplay" style="color:var(--text-primary);">detecting location…</strong>
        </span>
        <button onclick="requestGPS()" style="background:none;border:none;font-size:0.82rem;color:var(--brand-primary);cursor:pointer;font-weight:600;white-space:nowrap;">
          <i class="bi bi-crosshair me-1"></i>Use My Location
        </button>
      </div>

      <!-- Results Header -->
      <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <h2 style="font-size:1.4rem;font-weight:800;margin:0;" id="resultsTitle">Nearby Listings</h2>
        <div class="d-flex gap-2 flex-wrap">
          <select class="form-select" id="typeSelect" style="width:auto;font-size:0.85rem;" onchange="applyTypeFilter(this.value)" aria-label="Filter by listing type">
            <option value="both">All Listings</option>
            <option value="hostel">Hostels Only</option>
            <option value="mess">Messes Only</option>
          </select>
          <select class="form-select" id="sortSelect" style="width:auto;font-size:0.85rem;" onchange="doSearch()" aria-label="Sort results">
            <option value="distance">Nearest First</option>
            <option value="rating">Top Rated</option>
            <option value="price_asc">Price: Low to High</option>
          </select>
        </div>
      </div>

      <!-- Skeleton -->
      <div id="skeleton" class="row g-3">
        @for($i=0;$i<6;$i++)
        <div class="col-md-6">
          <div style="background:var(--bg-surface);border-radius:16px;overflow:hidden;border:1px solid var(--border-color);">
            <div style="height:195px;background:linear-gradient(90deg,var(--bg-subtle) 25%,var(--border-color) 50%,var(--bg-subtle) 75%);background-size:200% 100%;animation:shimmer 1.5s infinite;"></div>
            <div style="padding:1rem;">
              <div style="height:16px;background:var(--bg-subtle);border-radius:4px;margin-bottom:8px;width:70%;"></div>
              <div style="height:12px;background:var(--bg-subtle);border-radius:4px;width:50%;"></div>
            </div>
          </div>
        </div>
        @endfor
      </div>

      <!-- Results Grid -->
      <div id="resultsGrid" class="row g-3" style="display:none;"></div>

      <!-- Empty State -->
      <div id="emptyState" style="display:none;text-align:center;padding:4rem 2rem;">
        <div style="font-size:3.5rem;margin-bottom:1rem;">🔍</div>
        <h4 style="font-weight:700;margin-bottom:0.5rem;">No results found</h4>
        <p style="color:var(--text-muted);font-size:0.9rem;">Try a different location or expand the search radius.</p>
      </div>

      <div id="loadMoreWrap" style="text-align:center;margin-top:2rem;display:none;">
        <button class="btn-outline-findr" onclick="loadMore()">Load more results</button>
      </div>
    </div>

    <!-- Sidebar Filters -->
    <div class="col-lg-4 d-none d-lg-block">
      <div style="position:sticky;top:80px;">
        <div class="card-findr p-4">
          <h6 style="font-weight:700;margin-bottom:1.25rem;">Refine Results</h6>

          <div class="mb-4">
            <label class="form-label">Search Radius</label>
            <input type="range" id="radius" min="1" max="50" value="10"
                   style="width:100%;accent-color:var(--brand-primary);"
                   oninput="document.getElementById('radiusVal').textContent=this.value">
            <div class="d-flex justify-content-between" style="font-size:0.78rem;color:var(--text-muted);margin-top:4px;">
              <span>1 km</span><span><span id="radiusVal">10</span> km</span><span>50 km</span>
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
            <div class="d-flex gap-1" id="starBtns">
              @foreach([1,2,3,4,5] as $r)
              <button onclick="setRating({{ $r }},this)" data-star="{{ $r }}"
                style="background:none;border:none;font-size:1.4rem;cursor:pointer;padding:2px;color:var(--text-muted);">★</button>
              @endforeach
            </div>
          </div>

          <button class="btn-primary-findr w-100" onclick="doSearch()">
            <i class="bi bi-funnel me-2"></i>Apply Filters
          </button>
          <button class="btn-outline-findr w-100 mt-2" onclick="resetFilters()">Clear All</button>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
// ── State ──────────────────────────────────────────────────
var userLat = null, userLng = null;
var allResults = { hostels:[], messes:[] };
var activeFilters = { type:'both' };
var suggestTimer = null;
var suggestIndex = -1;
var suggestItems = [];

// ── Init ──────────────────────────────────────────────────
window.addEventListener('DOMContentLoaded', function() {
    requestGPS();
});

// ── GPS / Browser Location ────────────────────────────────
function requestGPS() {
    var btn = document.getElementById('gpsBtn');
    if (btn) btn.innerHTML = '<i class="bi bi-arrow-repeat" style="animation:spin 1s linear infinite;"></i>';

    if (!navigator.geolocation) {
        fallbackLocation();
        return;
    }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            userLat = pos.coords.latitude;
            userLng = pos.coords.longitude;
            reverseGeocode(userLat, userLng);
            loadResults();
            if (btn) btn.innerHTML = '<i class="bi bi-crosshair"></i>';
        },
        function() {
            fallbackLocation();
            if (btn) btn.innerHTML = '<i class="bi bi-crosshair"></i>';
        },
        { timeout: 8000, maximumAge: 60000 }
    );
}

function fallbackLocation() {
    // Default to Kozhikode, Kerala
    userLat = 11.2588;
    userLng = 75.7804;
    setLocationDisplay('Kozhikode, Kerala');
    document.getElementById('locationInput').value = 'Kozhikode, Kerala';
    loadResults();
}

// ── Reverse Geocode: coords → place name (Nominatim) ─────
function reverseGeocode(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
        headers: { 'Accept-Language': 'en' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var name = data.address.city
                || data.address.town
                || data.address.village
                || data.address.county
                || data.display_name.split(',')[0];
        var state = data.address.state || '';
        var display = state ? name + ', ' + state : name;
        setLocationDisplay(display);
        document.getElementById('locationInput').value = display;
    })
    .catch(function() {
        setLocationDisplay('Near you');
    });
}

// ── Forward Geocode: text → suggestions (Nominatim) ──────
function onLocationTyped(val) {
    clearTimeout(suggestTimer);
    if (val.length < 2) { hideSuggestions(); return; }
    suggestTimer = setTimeout(function() { fetchSuggestions(val); }, 350);
}

function fetchSuggestions(query) {
    var url = 'https://nominatim.openstreetmap.org/search?q='
        + encodeURIComponent(query)
        + '&format=json&addressdetails=1&limit=6&countrycodes=in';

    fetch(url, { headers: { 'Accept-Language': 'en' } })
    .then(function(r) { return r.json(); })
    .then(function(results) { showSuggestions(results); })
    .catch(function() { hideSuggestions(); });
}

function showSuggestions(results) {
    var box = document.getElementById('locationSuggestions');
    if (!results.length) { hideSuggestions(); return; }

    suggestItems = results;
    suggestIndex = -1;

    var html = '';
    results.forEach(function(r, i) {
        var parts = r.display_name.split(',');
        var main  = parts[0].trim();
        var sub   = parts.slice(1, 3).join(',').trim();
        html += '<div class="sug-item" onclick="selectSuggestion(' + i + ')" data-idx="' + i + '">'
             + '<i class="bi bi-geo-alt-fill sug-icon"></i>'
             + '<div><div class="sug-main">' + main + '</div>'
             + (sub ? '<div class="sug-sub">' + sub + '</div>' : '')
             + '</div></div>';
    });

    box.innerHTML = html;
    box.style.display = 'block';
}

function selectSuggestion(idx) {
    var r = suggestItems[idx];
    userLat = parseFloat(r.lat);
    userLng = parseFloat(r.lon);

    var parts = r.display_name.split(',');
    var display = parts[0].trim() + (parts[1] ? ', ' + parts[1].trim() : '');
    document.getElementById('locationInput').value = display;
    setLocationDisplay(display);
    hideSuggestions();
    loadResults();
}

function hideSuggestions() {
    var box = document.getElementById('locationSuggestions');
    if (box) { box.style.display = 'none'; }
}

// Keyboard navigation for suggestions
function onLocationKeydown(e) {
    var box  = document.getElementById('locationSuggestions');
    var items = box.querySelectorAll('.sug-item');
    if (!items.length) return;

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        suggestIndex = Math.min(suggestIndex + 1, items.length - 1);
        highlightSuggestion(items);
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        suggestIndex = Math.max(suggestIndex - 1, 0);
        highlightSuggestion(items);
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (suggestIndex >= 0) selectSuggestion(suggestIndex);
        else doSearch();
    } else if (e.key === 'Escape') {
        hideSuggestions();
    }
}

function highlightSuggestion(items) {
    items.forEach(function(el, i) {
        el.style.background = i === suggestIndex ? 'var(--bg-subtle)' : '';
    });
}

// Close suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.loc-wrap')) hideSuggestions();
});

// ── Results ───────────────────────────────────────────────
function setLocationDisplay(text) {
    var el = document.getElementById('locationDisplay');
    if (el) el.textContent = text;
}

function loadResults() {
    if (!userLat || !userLng) return;

    document.getElementById('skeleton').style.display    = 'block';
    document.getElementById('resultsGrid').style.display = 'none';
    document.getElementById('emptyState').style.display  = 'none';
    document.getElementById('loadMoreWrap').style.display= 'none';

    var params = new URLSearchParams({
        lat:    userLat,
        lng:    userLng,
        radius: document.getElementById('radius')?.value ?? 10,
        type:   activeFilters.type ?? 'both',
        sort:   document.getElementById('sortSelect')?.value ?? 'distance',
        q:      document.getElementById('searchQuery')?.value ?? '',
    });

    if (activeFilters.wifi)      params.append('has_wifi',    1);
    if (activeFilters.ac)        params.append('has_ac',      1);
    if (activeFilters.delivery)  params.append('has_delivery',1);
    if (activeFilters.veg)       params.append('food_type',   'veg');
    if (activeFilters.gender)    params.append('gender_type', activeFilters.gender);
    if (activeFilters.minRating) params.append('min_rating',  activeFilters.minRating);

    var minP = document.getElementById('minPrice')?.value;
    var maxP = document.getElementById('maxPrice')?.value;
    if (minP) params.append('min_price', minP);
    if (maxP) params.append('max_price', maxP);

    axios.get('/api/v1/search/nearby?' + params.toString())
        .then(function(r) {
            allResults.hostels = r.data.data.hostels ? r.data.data.hostels.data : [];
            allResults.messes  = r.data.data.messes  ? r.data.data.messes.data  : [];
            renderResults();
        })
        .catch(function(err) {
            document.getElementById('skeleton').style.display   = 'none';
            document.getElementById('emptyState').style.display = 'block';
        });
}

function renderResults() {
    document.getElementById('skeleton').style.display = 'none';

    var items = [];
    if (activeFilters.type !== 'mess')   items = items.concat(allResults.hostels);
    if (activeFilters.type !== 'hostel') items = items.concat(allResults.messes);

    var grid = document.getElementById('resultsGrid');

    if (!items.length) {
        document.getElementById('emptyState').style.display = 'block';
        return;
    }

    var html = '';
    for (var i = 0; i < items.length; i++) {
        html += items[i].type === 'hostel' ? buildHostelCard(items[i]) : buildMessCard(items[i]);
    }
    grid.innerHTML = html;
    grid.style.display = 'flex';

    var title = document.getElementById('resultsTitle');
    if (title) title.textContent = items.length + ' listings found';
    document.getElementById('loadMoreWrap').style.display = items.length >= 10 ? 'block' : 'none';
}

function buildHostelCard(h) {
    var price  = h.starting_price ? '₹' + parseInt(h.starting_price).toLocaleString() : 'N/A';
    var rating = Math.round(h.rating || 0);
    var stars  = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    var gender = h.gender_type === 'boys' ? '🚹 Boys' : h.gender_type === 'girls' ? '🚺 Girls' : '👥 Co-ed';
    var img    = h.cover_image || '/images/hostel-placeholder.jpg';
    var wifi   = h.has_wifi ? '<span style="background:var(--bg-subtle);border-radius:6px;padding:2px 6px;font-size:0.68rem;">📶</span>' : '';
    var ac     = h.has_ac   ? '<span style="background:var(--bg-subtle);border-radius:6px;padding:2px 6px;font-size:0.68rem;">❄️</span>' : '';

    return '<div class="col-md-6"><div class="listing-card" onclick="window.location=\'/hostels/' + h.slug + '\'">'
        + '<div style="position:relative;">'
        + '<img src="' + img + '" alt="' + h.name + '" loading="lazy">'
        + '<span class="badge-type">Hostel</span>'
        + '</div>'
        + '<div style="padding:1rem 1.1rem 1.2rem;">'
        + '<div class="d-flex align-items-start justify-content-between gap-2">'
        + '<h6 style="font-weight:700;font-size:0.93rem;margin:0;line-height:1.3;">' + h.name + '</h6>'
        + '<span style="background:var(--bg-subtle);border-radius:20px;padding:2px 8px;font-size:0.73rem;font-weight:600;color:var(--text-muted);white-space:nowrap;flex-shrink:0;">📍 ' + (h.distance_km || '?') + ' km</span>'
        + '</div>'
        + '<p style="font-size:0.78rem;color:var(--text-muted);margin:4px 0 8px;">' + h.address + '</p>'
        + '<div class="d-flex align-items-center gap-2 mb-2">'
        + '<span style="color:#F59E0B;font-size:0.85rem;">' + stars + '</span>'
        + '<span style="font-size:0.75rem;color:var(--text-muted);">(' + (h.total_reviews || 0) + ')</span>'
        + '<span style="margin-left:auto;font-size:0.75rem;color:var(--text-muted);">' + gender + '</span>'
        + '</div>'
        + '<div class="d-flex align-items-center justify-content-between">'
        + '<div>'
        + '<span style="font-size:0.72rem;color:var(--text-muted);">Starting from</span>'
        + '<strong style="display:block;font-size:1rem;color:var(--brand-primary);">' + price + '<span style="font-size:0.72rem;font-weight:400;color:var(--text-muted);">/mo</span></strong>'
        + '</div>'
        + '<div class="d-flex gap-1">' + wifi + ac + '</div>'
        + '</div>'
        + '<div class="d-flex gap-2 mt-3">'
        + '<a href="/hostels/' + h.slug + '" class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;font-size:0.84rem;" onclick="event.stopPropagation()">View Details</a>'
        + '<a href="' + h.whatsapp_share + '" target="_blank" style="background:#25D366;color:#fff;border-radius:10px;padding:0.5rem 0.75rem;display:flex;align-items:center;font-size:0.82rem;font-weight:600;text-decoration:none;" onclick="event.stopPropagation()">💬</a>'
        + '</div>'
        + '</div></div></div>';
}

function buildMessCard(m) {
    var rating = Math.round(m.rating || 0);
    var stars  = '★'.repeat(rating) + '☆'.repeat(5 - rating);
    var img    = m.cover_image || '/images/mess-placeholder.jpg';
    var food   = m.food_type === 'veg' ? '🥦 Veg' : m.food_type === 'non_veg' ? '🍗 Non-Veg' : '🍽️ Both';
    var delivery = m.has_delivery ? '<span style="background:rgba(16,185,129,0.1);color:var(--brand-accent);border-radius:8px;padding:3px 8px;font-size:0.72rem;font-weight:600;">🛵 Delivery</span>' : '';
    var slots = m.slots_open || {};
    var slotHtml = ['morning','afternoon','evening','night'].map(function(sl) {
        var open = slots[sl];
        return '<span style="padding:2px 6px;border-radius:5px;font-size:0.66rem;font-weight:700;background:' + (open ? '#D1FAE5' : 'var(--bg-subtle)') + ';color:' + (open ? '#065F46' : 'var(--text-muted)') + ';">' + sl.slice(0,3).toUpperCase() + '</span>';
    }).join('');

    return '<div class="col-md-6"><div class="listing-card" onclick="window.location=\'/messes/' + m.slug + '\'">'
        + '<div style="position:relative;">'
        + '<img src="' + img + '" alt="' + m.name + '" loading="lazy">'
        + '<span class="badge-type">Mess</span>'
        + '<span style="position:absolute;top:12px;right:12px;background:rgba(0,0,0,0.6);color:#fff;font-size:0.7rem;font-weight:700;padding:3px 9px;border-radius:20px;">' + food + '</span>'
        + '</div>'
        + '<div style="padding:1rem 1.1rem 1.2rem;">'
        + '<div class="d-flex align-items-start justify-content-between gap-2">'
        + '<h6 style="font-weight:700;font-size:0.93rem;margin:0;">' + m.name + '</h6>'
        + '<span style="background:var(--bg-subtle);border-radius:20px;padding:2px 8px;font-size:0.73rem;font-weight:600;color:var(--text-muted);white-space:nowrap;flex-shrink:0;">📍 ' + (m.distance_km || '?') + ' km</span>'
        + '</div>'
        + '<p style="font-size:0.78rem;color:var(--text-muted);margin:4px 0 8px;">' + m.address + '</p>'
        + '<div class="d-flex align-items-center gap-2 mb-2">'
        + '<span style="color:#F59E0B;font-size:0.85rem;">' + stars + '</span>'
        + '<span style="font-size:0.75rem;color:var(--text-muted);">(' + (m.total_reviews || 0) + ')</span>'
        + '</div>'
        + '<div class="d-flex gap-1 flex-wrap mb-2">' + slotHtml + '</div>'
        + '<div class="d-flex align-items-center justify-content-between">'
        + '<strong style="font-size:0.95rem;color:var(--brand-primary);">₹' + (m.cheapest_meal || 'N/A') + ' /meal</strong>'
        + delivery
        + '</div>'
        + '<div class="d-flex gap-2 mt-3">'
        + '<a href="/messes/' + m.slug + '" class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;font-size:0.84rem;" onclick="event.stopPropagation()">View Menu</a>'
        + '<a href="' + m.whatsapp_share + '" target="_blank" style="background:#25D366;color:#fff;border-radius:10px;padding:0.5rem 0.75rem;display:flex;align-items:center;font-size:0.82rem;font-weight:600;text-decoration:none;" onclick="event.stopPropagation()">💬</a>'
        + '</div>'
        + '</div></div></div>';
}

// ── Filter Controls ───────────────────────────────────────
function doSearch() { hideSuggestions(); if (userLat) loadResults(); }
function loadMore() { /* TODO: pagination */ }

function setType(t, el) {
    // chip handler — delegates to the shared filter so chips + dropdown stay in sync
    applyTypeFilter(t);
}

function applyTypeFilter(t) {
    activeFilters.type = t;

    // keep the quick-filter chips in sync
    document.querySelectorAll('.filter-chip').forEach(function(c) {
        var oc = c.getAttribute('onclick');
        if (oc && oc.indexOf('setType') !== -1) {
            c.classList.toggle('active', oc.indexOf("'" + t + "'") !== -1);
        }
    });

    // keep the dropdown in sync
    var sel = document.getElementById('typeSelect');
    if (sel) sel.value = t;

    loadResults();
}

function toggleFilter(f) {
    activeFilters[f] = !activeFilters[f];
    event.currentTarget.classList.toggle('active', !!activeFilters[f]);
    loadResults();
}

function setGender(g, el) {
    activeFilters.gender = activeFilters.gender === g ? null : g;
    document.querySelectorAll('[onclick*="setGender"]').forEach(function(b) { b.classList.remove('active'); });
    if (activeFilters.gender) el.classList.add('active');
    loadResults();
}

function setRating(r, el) {
    activeFilters.minRating = activeFilters.minRating === r ? null : r;
    document.querySelectorAll('[data-star]').forEach(function(b) {
        b.style.color = (activeFilters.minRating && parseInt(b.dataset.star) <= activeFilters.minRating)
            ? '#F59E0B' : 'var(--text-muted)';
    });
    loadResults();
}

function resetFilters() {
    activeFilters = { type:'both' };
    document.querySelectorAll('.filter-chip').forEach(function(c) { c.classList.remove('active'); });
    document.querySelector('.filter-chip').classList.add('active');
    var ts = document.getElementById('typeSelect');
    if (ts) ts.value = 'both';
    document.querySelectorAll('[data-star]').forEach(function(b) { b.style.color='var(--text-muted)'; });
    document.getElementById('minPrice').value = '';
    document.getElementById('maxPrice').value = '';
    document.getElementById('radius').value = '10';
    document.getElementById('radiusVal').textContent = '10';
    loadResults();
}

// CSS spin animation
var style = document.createElement('style');
style.textContent = '@keyframes spin{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}';
document.head.appendChild(style);
</script>
@endpush
