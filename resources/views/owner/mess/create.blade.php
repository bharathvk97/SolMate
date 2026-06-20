@extends('layouts.app')
@section('title', 'Add New Mess')

@section('content')
<div style="display:flex;min-height:calc(100vh - 65px);">
  <aside style="width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0;">
    <div class="pt-3">
      <div class="sidebar-section-label">My Mess</div>
      <a href="{{ route('owner.mess.dashboard') }}" class="sidebar-item"><i class="bi bi-speedometer2"></i> Overview</a>
      <a href="{{ route('owner.mess.create') }}" class="sidebar-item active"><i class="bi bi-plus-circle"></i> Add Mess</a>
      <a href="{{ route('owner.mess.bookings') }}" class="sidebar-item"><i class="bi bi-calendar-check"></i> Subscribers</a>
      <div class="sidebar-section-label">Account</div>
      <a href="{{ route('owner.subscription') }}" class="sidebar-item"><i class="bi bi-credit-card"></i> Subscription</a>
    </div>
  </aside>

  <div style="flex:1;padding:2rem;max-width:800px;">
    <div class="page-header"><h1>Add New Mess</h1><p>Create your mess listing and set up menus.</p></div>

    <form id="messForm" onsubmit="submitMess(event)">
      <!-- Basic Info -->
      <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);"><i class="bi bi-info-circle me-2" style="color:var(--brand-primary);"></i>Basic Information</h6>
        <div class="mb-3">
          <label class="form-label">Mess Name <span style="color:var(--danger);">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Sri Ram Home Food" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3" placeholder="Describe your mess — cuisine type, specialty dishes, hygiene standards..."></textarea>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Food Type <span style="color:var(--danger);">*</span></label>
            <select name="food_type" class="form-select" required>
              <option value="">Select…</option>
              <option value="veg">Pure Vegetarian</option>
              <option value="non_veg">Non-Vegetarian</option>
              <option value="both">Both (Veg & Non-Veg)</option>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Contact Phone</label>
            <input type="tel" name="phone" class="form-control" placeholder="10-digit number">
          </div>
        </div>
        <div class="row g-3 mt-0">
          <div class="col-md-6">
            <label class="form-label d-flex align-items-center gap-2">
              <input type="checkbox" name="has_delivery" value="1" style="accent-color:var(--brand-primary);">
              Delivery Available
            </label>
          </div>
          <div class="col-md-6">
            <label class="form-label d-flex align-items-center gap-2">
              <input type="checkbox" name="is_pure_veg" value="1" style="accent-color:var(--brand-primary);">
              Pure Veg (no eggs)
            </label>
          </div>
        </div>
      </div>

      <!-- Slot Timings -->
      <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);"><i class="bi bi-clock me-2" style="color:var(--warning);"></i>Meal Timings</h6>
        @foreach([['morning','☀️','07:00','09:30'],['afternoon','🌤️','12:00','14:30'],['evening','🌅','16:00','18:00'],['night','🌙','19:00','21:30']] as [$slot,$icon,$defOpen,$defClose])
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:.6rem 0;border-bottom:1px solid var(--border-color);">
          <label style="display:flex;align-items:center;gap:6px;width:130px;cursor:pointer;">
            <input type="checkbox" name="{{ $slot }}_enabled" value="1" checked style="accent-color:var(--brand-primary);">
            <span>{{ $icon }} {{ ucfirst($slot) }}</span>
          </label>
          <div style="display:flex;align-items:center;gap:8px;flex:1;">
            <input type="time" name="{{ $slot }}_open"  class="form-control" value="{{ $defOpen }}"  style="max-width:120px;">
            <span style="color:var(--text-muted);">to</span>
            <input type="time" name="{{ $slot }}_close" class="form-control" value="{{ $defClose }}" style="max-width:120px;">
          </div>
        </div>
        @endforeach
      </div>

      <!-- Location -->
      <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);"><i class="bi bi-geo-alt me-2" style="color:var(--brand-secondary);"></i>Location</h6>
        <div class="row g-3">
          <div class="col-12">
            <input type="text" name="address" class="form-control" placeholder="Full street address" required>
          </div>
          <div class="col-md-4"><input type="text" name="city"  class="form-control" placeholder="City"  required></div>
          <div class="col-md-4"><input type="text" name="state" class="form-control" placeholder="State" required></div>
          <div class="col-md-4"><input type="text" name="pin_code" class="form-control" placeholder="PIN"></div>
          <div class="col-md-6"><input type="number" name="latitude"  id="lat" class="form-control" placeholder="Latitude"  step="any"></div>
          <div class="col-md-6"><input type="number" name="longitude" id="lng" class="form-control" placeholder="Longitude" step="any"></div>
        </div>
        <button type="button" class="btn-outline-findr mt-2" onclick="detectLocation()">
          <i class="bi bi-crosshair me-1"></i>Auto-detect
        </button>
      </div>

      <!-- Photos -->
      <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
        <h6 style="font-weight:700;margin-bottom:1rem;padding-bottom:.6rem;border-bottom:1px solid var(--border-color);"><i class="bi bi-images me-2" style="color:var(--brand-primary);"></i>Photos</h6>
        <div style="border:2px dashed var(--border-color);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;" onclick="document.getElementById('messImages').click()">
          <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--text-muted);"></i>
          <p style="color:var(--text-muted);font-size:.88rem;margin:.5rem 0 0;">Click to upload mess photos</p>
        </div>
        <input type="file" id="messImages" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
        <div id="preview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:1rem;"></div>
      </div>

      <div class="d-flex gap-3">
        <a href="{{ route('owner.mess.dashboard') }}" class="btn-outline-findr">Cancel</a>
        <button type="submit" class="btn-primary-findr" id="submitBtn">
          <i class="bi bi-egg-fried me-2"></i>Submit Mess
        </button>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
function detectLocation() {
  navigator.geolocation.getCurrentPosition(p => {
    document.getElementById('lat').value = p.coords.latitude.toFixed(6);
    document.getElementById('lng').value = p.coords.longitude.toFixed(6);
    showToast('Location detected!','success');
  }, () => showToast('Could not detect location','warning'));
}

function previewImages(input) {
  const preview = document.getElementById('preview');
  Array.from(input.files).forEach(file => {
    const reader = new FileReader();
    reader.onload = e => {
      const w = document.createElement('div');
      w.style.cssText = 'position:relative;width:80px;height:80px;';
      w.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">
        <button type="button" onclick="this.parentNode.remove()" style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:.7rem;"><i class="bi bi-x"></i></button>`;
      preview.appendChild(w);
    };
    reader.readAsDataURL(file);
  });
}

function submitMess(e) {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.textContent = 'Submitting…';
  const fd = new FormData(document.getElementById('messForm'));
  axios.post('/api/v1/owner/messes', fd, { headers:{'Content-Type':'multipart/form-data'} })
    .then(() => { showToast('Mess submitted for review!','success'); setTimeout(() => window.location='{{ route("owner.mess.dashboard") }}', 1500); })
    .catch(err => {
      const errs = err.response?.data?.errors;
      showToast(errs ? Object.values(errs).flat().join('. ') : (err.response?.data?.message||'Error'), 'danger');
      btn.disabled=false; btn.innerHTML='<i class="bi bi-egg-fried me-2"></i>Submit Mess';
    });
}
</script>
@endpush
