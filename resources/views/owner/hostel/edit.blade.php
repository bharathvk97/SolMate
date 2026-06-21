@extends('layouts.app')
@section('title', 'Edit Hostel — ' . $hostel->name)

@section('content')
<div style="display:flex;min-height:calc(100vh - 65px);">

  <!-- Sidebar -->
  <aside style="width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0;">
    <div class="pt-3">
      <div class="sidebar-section-label">My Hostel</div>
      <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item"><i class="bi bi-speedometer2"></i> Overview</a>
      <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item active"><i class="bi bi-building"></i> My Hostels</a>
      <a href="{{ route('owner.hostel.bookings') }}" class="sidebar-item"><i class="bi bi-calendar-check"></i> Bookings</a>
      <div class="sidebar-section-label">Account</div>
      <a href="{{ route('owner.subscription') }}" class="sidebar-item"><i class="bi bi-credit-card"></i> Subscription</a>
    </div>
  </aside>

  <!-- Main Content -->
  <div style="flex:1;padding:2rem;min-width:0;">
    <div style="max-width:780px;">

      <!-- Header -->
      <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
          <h1 style="font-size:1.6rem;font-weight:800;margin:0;">Edit Hostel</h1>
          <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.88rem;">{{ $hostel->name }}</p>
        </div>
        <a href="{{ route('owner.hostel.dashboard') }}" class="btn-outline-findr">← Back</a>
      </div>

      @if(session('success'))
      <div style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.3);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;color:var(--brand-accent);">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
      </div>
      @endif

      @if(session('error'))
      <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1.25rem;font-size:0.88rem;color:var(--danger);">
        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
      </div>
      @endif

      @if($errors->any())
      <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1.25rem;font-size:0.85rem;color:var(--danger);">
        <strong>Please fix these errors:</strong>
        <ul style="margin:6px 0 0;padding-left:1.2rem;">
          @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      @endif

      <form method="POST" action="{{ route('owner.hostel.update', $hostel->id) }}" enctype="multipart/form-data">
        @csrf

        <!-- Basic Info -->
        <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
          <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color);">
            <i class="bi bi-info-circle me-2" style="color:var(--brand-primary);"></i>Basic Information
          </h6>

          <div class="mb-3">
            <label class="form-label">Hostel Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $hostel->name) }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="4">{{ old('description', $hostel->description) }}</textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Hostel For <span style="color:var(--danger);">*</span></label>
              <select name="gender_type" class="form-select" required>
                <option value="boys"  {{ old('gender_type',$hostel->gender_type) === 'boys'  ? 'selected' : '' }}>🚹 Boys Only</option>
                <option value="girls" {{ old('gender_type',$hostel->gender_type) === 'girls' ? 'selected' : '' }}>🚺 Girls Only</option>
                <option value="coed"  {{ old('gender_type',$hostel->gender_type) === 'coed'  ? 'selected' : '' }}>👥 Co-ed</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Contact Phone</label>
              <input type="tel" name="phone" class="form-control" value="{{ old('phone', $hostel->phone) }}">
            </div>
          </div>
        </div>

        <!-- Location -->
        <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
          <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color);">
            <i class="bi bi-geo-alt me-2" style="color:var(--brand-secondary);"></i>Location
          </h6>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label">Full Address <span style="color:var(--danger);">*</span></label>
              <input type="text" name="address" class="form-control" value="{{ old('address', $hostel->address) }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">City <span style="color:var(--danger);">*</span></label>
              <input type="text" name="city" class="form-control" value="{{ old('city', $hostel->city) }}" required>
            </div>
            <div class="col-md-4">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="{{ old('state', $hostel->state) }}">
            </div>
            <div class="col-md-4">
              <label class="form-label">PIN Code</label>
              <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $hostel->pincode) }}" maxlength="6">
            </div>
            <div class="col-md-6">
              <label class="form-label">Latitude</label>
              <input type="number" name="latitude" id="lat" class="form-control" value="{{ old('latitude', $hostel->lat) }}" step="any" placeholder="11.2588">
            </div>
            <div class="col-md-6">
              <label class="form-label">Longitude</label>
              <input type="number" name="longitude" id="lng" class="form-control" value="{{ old('longitude', $hostel->lng) }}" step="any" placeholder="75.7804">
            </div>
          </div>
          <button type="button" class="btn-outline-findr mt-2" onclick="detectLocation()">
            <i class="bi bi-crosshair me-1"></i>Auto-detect My Location
          </button>
        </div>

        <!-- Rules & Policies -->
        <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
          <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color);">
            <i class="bi bi-clipboard-check me-2" style="color:var(--brand-accent);"></i>Rules & Policies
          </h6>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Curfew Time</label>
              <input type="time" name="curfew_time" class="form-control" value="{{ old('curfew_time', $hostel->curfew_time) }}">
            </div>
            <div class="col-md-6">
              <label class="form-label">Guest Policy</label>
              <select name="allow_guests" class="form-select">
                <option value="0" {{ !old('allow_guests', $hostel->allow_guests) ? 'selected' : '' }}>Guests not allowed</option>
                <option value="1" {{ old('allow_guests', $hostel->allow_guests)  ? 'selected' : '' }}>Guests allowed</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label">House Rules</label>
              <textarea name="house_rules" class="form-control" rows="3" placeholder="No smoking, no loud music after 10 PM...">{{ old('house_rules', $hostel->house_rules) }}</textarea>
            </div>
          </div>
        </div>

        <!-- Amenities -->
        <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
          <h6 style="font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color);">
            <i class="bi bi-stars me-2" style="color:var(--warning);"></i>Amenities
          </h6>
          <div class="row g-0">
            @foreach([
              ['has_wifi','Wi-Fi','bi-wifi'],
              ['has_ac','AC Rooms','bi-thermometer-snow'],
              ['has_cctv','CCTV','bi-camera'],
              ['has_parking','Parking','bi-p-circle'],
              ['has_laundry','Laundry','bi-bag'],
              ['has_power_backup','Power Backup','bi-lightning'],
              ['has_gym','Gym','bi-bicycle'],
              ['has_mess','In-house Mess','bi-egg-fried'],
              ['has_security','24/7 Security','bi-shield-check'],
            ] as [$field, $label, $icon])
            <div class="col-6 col-md-4">
              <label style="display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;cursor:pointer;">
                <input type="checkbox" name="{{ $field }}" value="1"
                  {{ old($field, $hostel->$field) ? 'checked' : '' }}
                  style="accent-color:var(--brand-primary);width:16px;height:16px;">
                <i class="bi {{ $icon }}" style="color:var(--brand-primary);"></i>
                <span style="font-size:0.85rem;">{{ $label }}</span>
              </label>
            </div>
            @endforeach
          </div>
        </div>

        <!-- Current Photos -->
        <div style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem;">
          <h6 style="font-weight:700;margin-bottom:1rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color);">
            <i class="bi bi-images me-2" style="color:var(--brand-primary);"></i>Photos
          </h6>

          @if($hostel->images->count())
          <p style="font-size:0.82rem;color:var(--text-muted);margin-bottom:0.75rem;">Current photos ({{ $hostel->images->count() }})</p>
          <div style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:1.25rem;">
            @foreach($hostel->images as $img)
            <div style="position:relative;width:90px;height:90px;">
              <img src="{{ $img->url }}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;border:2px solid {{ $img->is_cover ? 'var(--brand-primary)' : 'var(--border-color)' }};" alt="">
              @if($img->is_cover)
              <span style="position:absolute;bottom:0;left:0;right:0;background:var(--brand-primary);color:#fff;font-size:0.58rem;font-weight:700;text-align:center;padding:2px;border-radius:0 0 8px 8px;letter-spacing:0.05em;">COVER</span>
              @endif
            </div>
            @endforeach
          </div>
          @endif

          <label class="form-label">{{ $hostel->images->count() ? 'Add More Photos' : 'Upload Photos' }}</label>
          <div style="border:2px dashed var(--border-color);border-radius:12px;padding:2rem;text-align:center;cursor:pointer;transition:all 0.2s;"
               onclick="document.getElementById('newImages').click()"
               onmouseenter="this.style.borderColor='var(--brand-primary)'"
               onmouseleave="this.style.borderColor='var(--border-color)'">
            <i class="bi bi-cloud-upload" style="font-size:2rem;color:var(--text-muted);"></i>
            <p style="font-size:0.85rem;color:var(--text-muted);margin:0.5rem 0 0;">Click to upload · JPG, PNG · Max 5MB each</p>
          </div>
          <input type="file" id="newImages" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewImages(this)">
          <div id="imagePreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:1rem;"></div>
        </div>

        <!-- Buttons -->
        <div class="d-flex gap-3 pb-4">
          <a href="{{ route('owner.hostel.dashboard') }}" class="btn-outline-findr">Cancel</a>
          <button type="submit" class="btn-primary-findr">
            <i class="bi bi-save me-2"></i>Save Changes
          </button>
        </div>

      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function detectLocation() {
    if (!navigator.geolocation) { alert('Geolocation not supported'); return; }
    navigator.geolocation.getCurrentPosition(function(p) {
        document.getElementById('lat').value = p.coords.latitude.toFixed(6);
        document.getElementById('lng').value = p.coords.longitude.toFixed(6);
        showToast('Location detected!', 'success');
    }, function() {
        showToast('Could not detect location. Enter manually.', 'warning');
    });
}

function previewImages(input) {
    var preview = document.getElementById('imagePreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.style.cssText = 'position:relative;width:85px;height:85px;';
            div.innerHTML =
                '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">' +
                '<button type="button" onclick="this.parentNode.remove()" style="position:absolute;top:-6px;right:-6px;width:22px;height:22px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:0.75rem;display:flex;align-items:center;justify-content:center;">✕</button>';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush