@extends('layouts.app')
@section('title', 'Add New Hostel')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-sidebar { width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0; }
.form-section { background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem; }
.form-section h6 { font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color); }
.amenity-check { display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;cursor:pointer;transition:background .15s; }
.amenity-check:hover { background:var(--bg-subtle); }
.drop-zone { border:2px dashed var(--border-color);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all .2s; }
.drop-zone:hover { border-color:var(--brand-primary);background:rgba(92,95,239,.04); }
.img-preview { display:flex;flex-wrap:wrap;gap:8px;margin-top:1rem; }
.img-thumb { position:relative;width:80px;height:80px; }
.img-thumb img { width:100%;height:100%;object-fit:cover;border-radius:8px; }
.img-thumb button { position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:.7rem;display:flex;align-items:center;justify-content:center; }
</style>
@endpush

@section('content')
<div class="owner-wrapper">
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
    <div class="page-header">
      <h1>Add New Hostel</h1>
      <p>Fill in the details below. Your listing will be reviewed before going live.</p>
    </div>

    @if(session('error'))
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.3);border-radius:10px;padding:.8rem 1rem;margin-bottom:1rem;font-size:.85rem;color:var(--danger);">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('owner.hostel.store') }}" enctype="multipart/form-data" id="hostelForm">
      @csrf

      <!-- Basic Info -->
      <div class="form-section">
        <h6><i class="bi bi-info-circle me-2" style="color:var(--brand-primary);"></i>Basic Information</h6>
        <div class="mb-3">
          <label class="form-label">Hostel Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Green Valley Boys Hostel" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4" placeholder="Describe your hostel — facilities, environment, nearby colleges..."></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Hostel Type <span style="color:var(--danger);">*</span></label>
            <select name="gender_type" class="form-select" required>
              <option value="">Select…</option>
              <option value="boys">Boys Only</option>
              <option value="girls">Girls Only</option>
              <option value="coed">Co-ed</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Contact Phone</label>
            <input type="tel" name="phone" class="form-control" placeholder="10-digit number">
          </div>
        </div>
      </div>

      <!-- Location -->
      <div class="form-section">
        <h6><i class="bi bi-geo-alt me-2" style="color:var(--brand-secondary);"></i>Location</h6>
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label">Full Address <span style="color:var(--danger);">*</span></label>
            <input type="text" name="address" class="form-control" placeholder="Street, Area" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">City <span style="color:var(--danger);">*</span></label>
            <input type="text" name="city" class="form-control" placeholder="City" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">State <span style="color:var(--danger);">*</span></label>
            <input type="text" name="state" class="form-control" placeholder="Kerala" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Pin Code</label>
            <input type="text" name="pincode" class="form-control" placeholder="673001" maxlength="6">
          </div>
          <div class="col-md-6">
            <label class="form-label">Latitude</label>
            <input type="number" name="latitude" id="lat" class="form-control" placeholder="11.2588" step="any">
          </div>
          <div class="col-md-6">
            <label class="form-label">Longitude</label>
            <input type="number" name="longitude" id="lng" class="form-control" placeholder="75.7804" step="any">
          </div>
        </div>
        <button type="button" class="btn-outline-findr mt-2" onclick="detectLocation()">
          <i class="bi bi-crosshair me-1"></i>Auto-detect Location
        </button>
      </div>

      <!-- Rules & Policies -->
      <div class="form-section">
        <h6><i class="bi bi-clipboard-check me-2" style="color:var(--brand-accent);"></i>Rules & Policies</h6>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Curfew Time</label>
            <input type="time" name="curfew_time" class="form-control">
          </div>
          <div class="col-md-6">
            <label class="form-label">Guest Policy</label>
            <select name="allow_guests" class="form-select">
              <option value="0">Guests not allowed</option>
              <option value="1">Guests allowed</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">House Rules</label>
          <textarea name="house_rules" class="form-control" rows="2" placeholder="No loud music after 10 PM, no smoking..."></textarea>
        </div>
      </div>

      <!-- Amenities -->
      <div class="form-section">
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
            <label class="amenity-check">
              <input type="checkbox" name="{{ $n }}" value="1" style="accent-color:var(--brand-primary);">
              <i class="bi {{ $i }}" style="color:var(--brand-primary);font-size:.9rem;"></i>
              <span style="font-size:.85rem;">{{ $l }}</span>
            </label>
          </div>
          @endforeach
        </div>
      </div>

      <!-- Images -->
      <div class="form-section">
        <h6><i class="bi bi-images me-2" style="color:var(--brand-primary);"></i>Photos</h6>
        <p style="font-size:.82rem;color:var(--text-muted);margin-bottom:1rem;">Add up to 10 photos. First image will be the cover photo. JPG/PNG, max 5 MB each.</p>
        <div class="drop-zone" onclick="document.getElementById('hostelImages').click()">
          <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--text-muted);"></i>
          <p style="color:var(--text-muted);font-size:.88rem;margin:.5rem 0 0;">Click or drag photos here</p>
        </div>
        <input type="file" id="hostelImages" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
        <div class="img-preview" id="preview"></div>
      </div>

      <div class="d-flex gap-3">
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
function previewImages(input) {
    var preview = document.getElementById('preview');
    if (!preview) return;
    Array.from(input.files).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.style.cssText = 'position:relative;width:85px;height:85px;';
            div.innerHTML =
                '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">' +
                '<button type="button" onclick="this.parentNode.remove()" style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:0.75rem;">✕</button>';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

function detectLocation() {
    navigator.geolocation.getCurrentPosition(function(p) {
        document.getElementById('lat').value = p.coords.latitude.toFixed(6);
        document.getElementById('lng').value = p.coords.longitude.toFixed(6);
        showToast('Location detected!', 'success');
    }, function() {
        showToast('Could not detect location', 'warning');
    });
}
</script>
@endpush

