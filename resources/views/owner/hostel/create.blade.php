@extends('layouts.app')
@section('title', 'Add New Hostel')

@push('styles')
<style>
.owner-sidebar { width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0; }
.form-card { background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem; }
.form-card h6 { font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color); }
.map-preview { height:220px;border-radius:12px;overflow:hidden;margin-top:1rem;border:1px solid var(--border-color);display:none; }
/* Location suggestions */
.loc-suggestions { position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:9999;background:var(--bg-surface);border:1px solid var(--border-color);border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.12);max-height:240px;overflow-y:auto;display:none; }
.loc-sug-item { padding:0.65rem 1rem;cursor:pointer;display:flex;align-items:flex-start;gap:8px;border-bottom:1px solid var(--border-color);font-size:0.84rem;transition:background 0.1s; }
.loc-sug-item:last-child { border-bottom:none; }
.loc-sug-item:hover { background:var(--bg-subtle); }
.loc-wrap { position:relative; }
</style>
@endpush

@section('content')
<div style="display:flex;min-height:calc(100vh - 65px);">
  <aside class="owner-sidebar">
    <div class="pt-3">
      <div class="sidebar-section-label">My Hostel</div>
      <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item"><i class="bi bi-speedometer2"></i> Overview</a>
      <a href="{{ route('owner.hostel.create') }}" class="sidebar-item active"><i class="bi bi-plus-circle"></i> Add Hostel</a>
      <a href="{{ route('owner.hostel.bookings') }}" class="sidebar-item"><i class="bi bi-calendar-check"></i> Bookings</a>
      <div class="sidebar-section-label">Account</div>
      <a href="{{ route('owner.subscription') }}" class="sidebar-item"><i class="bi bi-credit-card"></i> Subscription</a>
    </div>
  </aside>

  <div style="flex:1;padding:2rem;max-width:800px;">
    <div class="page-header d-flex align-items-center justify-content-between">
      <div>
        <h1>Add New Hostel</h1>
        <p>Fill in the details. Your listing will be reviewed before going live.</p>
      </div>
      <a href="{{ route('owner.hostel.dashboard') }}" class="btn-outline-findr">← Back</a>
    </div>

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1rem;font-size:0.85rem;color:var(--danger);">
      <strong>Please fix these errors:</strong>
      <ul style="margin:4px 0 0;padding-left:1.2rem;">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
      </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('owner.hostel.store') }}" enctype="multipart/form-data">
      @csrf

      <!-- Basic Info -->
      <div class="form-card">
        <h6><i class="bi bi-info-circle me-2" style="color:var(--brand-primary);"></i>Basic Information</h6>
        <div class="mb-3">
          <label class="form-label">Hostel Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="e.g. Green Valley Boys Hostel" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Describe your hostel — facilities, environment, nearby colleges...">{{ old('description') }}</textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Hostel For <span style="color:var(--danger);">*</span></label>
            <select name="gender_type" class="form-select" required>
              <option value="boys"  {{ old('gender_type')==='boys'  ? 'selected':'' }}>🚹 Boys Only</option>
              <option value="girls" {{ old('gender_type')==='girls' ? 'selected':'' }}>🚺 Girls Only</option>
              <option value="coed"  {{ old('gender_type')==='coed'  ? 'selected':'' }}>👥 Co-ed</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Contact Phone</label>
            <input type="tel" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="10-digit number">
          </div>
        </div>
      </div>

      <!-- Location -->
      <div class="form-card">
        <h6><i class="bi bi-geo-alt me-2" style="color:var(--brand-secondary);"></i>Location</h6>

        <!-- Location search with autocomplete -->
        <div class="mb-3">
          <label class="form-label">Search & Pin Location</label>
          <div class="loc-wrap">
            <div style="display:flex;gap:8px;">
              <div style="flex:1;position:relative;">
                <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                <input type="text" id="locationSearch" class="form-control" style="padding-left:2.2rem;"
                       placeholder="Type area, street or city to search…"
                       oninput="ownerLocationTyped(this.value)" onkeydown="ownerLocationKeydown(event)"
                       autocomplete="off">
              </div>
              <button type="button" class="btn-outline-findr" onclick="ownerRequestGPS()" title="Use current location" style="white-space:nowrap;">
                <i class="bi bi-crosshair me-1"></i>My Location
              </button>
            </div>
            <div id="ownerSuggestions" class="loc-suggestions"></div>
          </div>
          <p style="font-size:0.75rem;color:var(--text-muted);margin:6px 0 0;">
            <i class="bi bi-info-circle me-1"></i>Search to auto-fill address and coordinates, or click "My Location"
          </p>
        </div>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Full Address <span style="color:var(--danger);">*</span></label>
            <input type="text" name="address" id="addressField" class="form-control" value="{{ old('address') }}" placeholder="Street, Area" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">City <span style="color:var(--danger);">*</span></label>
            <input type="text" name="city" id="cityField" class="form-control" value="{{ old('city') }}" placeholder="Kozhikode" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">State <span style="color:var(--danger);">*</span></label>
            <input type="text" name="state" id="stateField" class="form-control" value="{{ old('state') }}" placeholder="Kerala" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">PIN Code</label>
            <input type="text" name="pincode" id="pincodeField" class="form-control" value="{{ old('pincode') }}" placeholder="673001" maxlength="6">
          </div>
        </div>

        <!-- Coordinates (auto-filled, but editable) -->
        <div class="row g-3 mt-0">
          <div class="col-md-6">
            <label class="form-label">Latitude <span style="font-size:0.72rem;color:var(--text-muted);">(auto-filled)</span></label>
            <input type="number" name="latitude" id="latField" class="form-control" value="{{ old('latitude') }}" step="any" placeholder="Auto-detected">
          </div>
          <div class="col-md-6">
            <label class="form-label">Longitude <span style="font-size:0.72rem;color:var(--text-muted);">(auto-filled)</span></label>
            <input type="number" name="longitude" id="lngField" class="form-control" value="{{ old('longitude') }}" step="any" placeholder="Auto-detected">
          </div>
        </div>

        <!-- Mini map preview -->
        <div id="mapPreview" class="map-preview">
          <iframe id="mapFrame" src="" style="width:100%;height:100%;border:none;" loading="lazy"></iframe>
        </div>
      </div>

      <!-- Rules -->
      <div class="form-card">
        <h6><i class="bi bi-clipboard-check me-2" style="color:var(--brand-accent);"></i>Rules & Policies</h6>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Curfew Time</label>
            <input type="time" name="curfew_time" class="form-control" value="{{ old('curfew_time') }}">
          </div>
          <div class="col-md-6">
            <label class="form-label">Guest Policy</label>
            <select name="allow_guests" class="form-select">
              <option value="0">Guests not allowed</option>
              <option value="1">Guests allowed</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">House Rules</label>
            <textarea name="house_rules" class="form-control" rows="2" placeholder="No smoking, no loud music after 10 PM...">{{ old('house_rules') }}</textarea>
          </div>
        </div>
      </div>

      <!-- Amenities -->
      <div class="form-card">
        <h6><i class="bi bi-stars me-2" style="color:var(--warning);"></i>Amenities</h6>
        <div class="row g-0">
          @foreach([
            ['has_wifi','Wi-Fi','bi-wifi'],['has_ac','AC Rooms','bi-thermometer-snow'],
            ['has_cctv','CCTV','bi-camera'],['has_parking','Parking','bi-p-circle'],
            ['has_laundry','Laundry','bi-bag'],['has_power_backup','Power Backup','bi-lightning'],
            ['has_gym','Gym','bi-bicycle'],['has_mess','In-house Mess','bi-egg-fried'],
            ['has_security','24/7 Security','bi-shield-check'],
          ] as [$n,$l,$i])
          <div class="col-6 col-md-4">
            <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;cursor:pointer;">
              <input type="checkbox" name="{{ $n }}" value="1" {{ old($n) ? 'checked':'' }} style="accent-color:var(--brand-primary);width:15px;height:15px;">
              <i class="bi {{ $i }}" style="color:var(--brand-primary);"></i>
              <span style="font-size:0.85rem;">{{ $l }}</span>
            </label>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Photos -->
      <div class="form-card">
        <h6><i class="bi bi-images me-2" style="color:var(--brand-primary);"></i>Photos</h6>
        <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:1rem;">First image will be the cover. JPG/PNG, max 5MB each.</p>
        <div style="border:2px dashed var(--border-color);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;"
             onclick="document.getElementById('hostelImages').click()"
             onmouseenter="this.style.borderColor='var(--brand-primary)'"
             onmouseleave="this.style.borderColor='var(--border-color)'">
          <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--text-muted);"></i>
          <p style="color:var(--text-muted);font-size:0.88rem;margin:0.5rem 0 0;">Click or drag photos here</p>
        </div>
        <input type="file" id="hostelImages" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
        <div id="preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:1rem;"></div>
      </div>

      <div class="d-flex gap-3 pb-4">
        <a href="{{ route('owner.hostel.dashboard') }}" class="btn-outline-findr">Cancel</a>
        <button type="submit" class="btn-primary-findr">
          <i class="bi bi-building-add me-2"></i>Submit Hostel
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
var ownerSuggestTimer = null;
var ownerSuggestItems = [];
var ownerSuggestIndex = -1;

// ── GPS for owner ─────────────────────────────────────────
function ownerRequestGPS() {
    showToast('Detecting your location…', 'info');
    if (!navigator.geolocation) { showToast('Geolocation not supported', 'warning'); return; }
    navigator.geolocation.getCurrentPosition(
        function(pos) {
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            document.getElementById('latField').value = lat.toFixed(6);
            document.getElementById('lngField').value = lng.toFixed(6);
            reverseGeocodeOwner(lat, lng);
            updateMapPreview(lat, lng);
            showToast('Location detected!', 'success');
        },
        function() { showToast('Could not detect location. Please search manually.', 'warning'); }
    );
}

// ── Reverse geocode for owner ──────────────────────────────
function reverseGeocodeOwner(lat, lng) {
    fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
        headers: { 'Accept-Language': 'en' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var addr    = data.address;
        var road    = addr.road || addr.neighbourhood || '';
        var suburb  = addr.suburb || addr.village || addr.town || '';
        var city    = addr.city || addr.town || addr.village || addr.county || '';
        var state   = addr.state || '';
        var pincode = addr.postcode || '';

        var fullAddr = [road, suburb].filter(Boolean).join(', ');
        if (fullAddr) document.getElementById('addressField').value = fullAddr;
        if (city)    document.getElementById('cityField').value    = city;
        if (state)   document.getElementById('stateField').value   = state;
        if (pincode) document.getElementById('pincodeField').value = pincode;
        document.getElementById('locationSearch').value = city + (state ? ', ' + state : '');
    })
    .catch(function() {});
}

// ── Location search autocomplete ──────────────────────────
function ownerLocationTyped(val) {
    clearTimeout(ownerSuggestTimer);
    if (val.length < 2) { hideOwnerSuggestions(); return; }
    ownerSuggestTimer = setTimeout(function() { fetchOwnerSuggestions(val); }, 350);
}

function fetchOwnerSuggestions(query) {
    var url = 'https://nominatim.openstreetmap.org/search?q='
        + encodeURIComponent(query)
        + '&format=json&addressdetails=1&limit=5&countrycodes=in';

    fetch(url, { headers: { 'Accept-Language': 'en' } })
    .then(function(r) { return r.json(); })
    .then(function(results) { showOwnerSuggestions(results); })
    .catch(function() { hideOwnerSuggestions(); });
}

function showOwnerSuggestions(results) {
    var box = document.getElementById('ownerSuggestions');
    if (!results.length) { hideOwnerSuggestions(); return; }
    ownerSuggestItems = results;
    ownerSuggestIndex = -1;

    var html = '';
    results.forEach(function(r, i) {
        var parts = r.display_name.split(',');
        var main  = parts[0].trim();
        var sub   = parts.slice(1, 3).join(',').trim();
        html += '<div class="loc-sug-item" onclick="selectOwnerSuggestion(' + i + ')">'
             + '<i class="bi bi-geo-alt-fill" style="color:var(--brand-primary);flex-shrink:0;margin-top:2px;"></i>'
             + '<div><div style="font-weight:600;">' + main + '</div>'
             + (sub ? '<div style="font-size:0.73rem;color:var(--text-muted);">' + sub + '</div>' : '')
             + '</div></div>';
    });
    box.innerHTML = html;
    box.style.display = 'block';
}

function selectOwnerSuggestion(idx) {
    var r    = ownerSuggestItems[idx];
    var addr = r.address;
    var lat  = parseFloat(r.lat);
    var lng  = parseFloat(r.lon);

    // Fill all fields
    document.getElementById('latField').value     = lat.toFixed(6);
    document.getElementById('lngField').value     = lng.toFixed(6);

    var road    = addr.road || addr.neighbourhood || addr.suburb || '';
    var suburb  = addr.suburb || addr.village || '';
    var city    = addr.city || addr.town || addr.village || addr.county || '';
    var state   = addr.state || '';
    var pincode = addr.postcode || '';

    var fullAddr = [road, suburb].filter(Boolean).join(', ') || r.display_name.split(',').slice(0,2).join(',').trim();

    document.getElementById('addressField').value  = fullAddr;
    document.getElementById('cityField').value     = city;
    document.getElementById('stateField').value    = state;
    document.getElementById('pincodeField').value  = pincode;
    document.getElementById('locationSearch').value = city + (state ? ', ' + state : '');

    updateMapPreview(lat, lng);
    hideOwnerSuggestions();
    showToast('Location set: ' + city, 'success');
}

function hideOwnerSuggestions() {
    var box = document.getElementById('ownerSuggestions');
    if (box) box.style.display = 'none';
}

function ownerLocationKeydown(e) {
    var items = document.querySelectorAll('.loc-sug-item');
    if (!items.length) return;
    if (e.key === 'ArrowDown') { e.preventDefault(); ownerSuggestIndex = Math.min(ownerSuggestIndex+1, items.length-1); items.forEach(function(el,i){ el.style.background = i===ownerSuggestIndex?'var(--bg-subtle)':''; }); }
    else if (e.key === 'ArrowUp') { e.preventDefault(); ownerSuggestIndex = Math.max(ownerSuggestIndex-1, 0); items.forEach(function(el,i){ el.style.background = i===ownerSuggestIndex?'var(--bg-subtle)':''; }); }
    else if (e.key === 'Enter') { e.preventDefault(); if (ownerSuggestIndex >= 0) selectOwnerSuggestion(ownerSuggestIndex); }
    else if (e.key === 'Escape') hideOwnerSuggestions();
}

document.addEventListener('click', function(e) { if (!e.target.closest('.loc-wrap')) hideOwnerSuggestions(); });

// ── Map Preview ───────────────────────────────────────────
function updateMapPreview(lat, lng) {
    var preview = document.getElementById('mapPreview');
    var frame   = document.getElementById('mapFrame');
    var url = 'https://www.openstreetmap.org/export/embed.html?bbox='
        + (lng-0.01) + ',' + (lat-0.01) + ',' + (lng+0.01) + ',' + (lat+0.01)
        + '&layer=mapnik&marker=' + lat + ',' + lng;
    frame.src = url;
    preview.style.display = 'block';
}

// ── Image Preview ─────────────────────────────────────────
function previewImages(input) {
    var preview = document.getElementById('preview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(function(file, idx) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.style.cssText = 'position:relative;width:85px;height:85px;';
            div.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:10px;border:' + (idx===0 ? '2px solid var(--brand-primary)' : '1px solid var(--border-color)') + ';">'
                + (idx===0 ? '<span style="position:absolute;bottom:0;left:0;right:0;background:var(--brand-primary);color:#fff;font-size:0.58rem;font-weight:700;text-align:center;padding:2px;border-radius:0 0 8px 8px;">COVER</span>' : '')
                + '<button type="button" onclick="this.parentNode.remove()" style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:0.7rem;">✕</button>';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
