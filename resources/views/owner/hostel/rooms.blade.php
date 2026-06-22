@extends('layouts.app')
@section('title', 'Manage Rooms — ' . $hostel->name)

@push('styles')
<style>
.owner-sidebar { width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0; }
.room-card { background:var(--bg-surface);border:1.5px solid var(--border-color);border-radius:14px;overflow:hidden;margin-bottom:1rem;transition:all 0.2s; }
.room-card:hover { border-color:var(--brand-primary);box-shadow:0 4px 20px rgba(92,95,239,0.1); }
.facility-badge { display:inline-flex;align-items:center;gap:4px;background:var(--bg-subtle);border-radius:6px;padding:3px 8px;font-size:0.72rem;font-weight:600;color:var(--text-secondary); }
.room-form-card { background:var(--bg-surface);border:1px solid var(--border-color);border-radius:14px;padding:1.5rem;margin-bottom:1.25rem; }
.room-form-card h6 { font-weight:700;margin-bottom:1.25rem;padding-bottom:0.6rem;border-bottom:1px solid var(--border-color); }
.img-thumb { position:relative;width:75px;height:75px; }
.img-thumb img { width:100%;height:100%;object-fit:cover;border-radius:8px;border:1px solid var(--border-color); }
.img-thumb .del-btn { position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:var(--danger);color:#fff;border:none;cursor:pointer;font-size:0.7rem;display:flex;align-items:center;justify-content:center; }
</style>
@endpush

@section('content')
<div style="display:flex;min-height:calc(100vh - 65px);">

  <!-- Sidebar -->
  <aside class="owner-sidebar">
    <div class="pt-3">
      <div class="sidebar-section-label">My Hostel</div>
      <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item"><i class="bi bi-speedometer2"></i> Overview</a>
      <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item"><i class="bi bi-building"></i> My Hostels</a>
      <a href="{{ route('owner.hostel.rooms', $hostel->id) }}" class="sidebar-item active"><i class="bi bi-door-open"></i> Rooms</a>
      <a href="{{ route('owner.hostel.bookings') }}" class="sidebar-item"><i class="bi bi-calendar-check"></i> Bookings</a>
      <a href="{{ route('owner.hostel.reviews') }}" class="sidebar-item"><i class="bi bi-star"></i> Reviews</a>
      <div class="sidebar-section-label">Account</div>
      <a href="{{ route('owner.subscription') }}" class="sidebar-item"><i class="bi bi-credit-card"></i> Subscription</a>
    </div>
  </aside>

  <!-- Main -->
  <div style="flex:1;padding:2rem;min-width:0;">

    <!-- Header -->
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
      <div>
        <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:4px;">
          <a href="{{ route('owner.hostel.dashboard') }}" style="color:var(--text-muted);">My Hostels</a>
          › {{ $hostel->name }}
        </div>
        <h1 style="font-size:1.6rem;font-weight:800;margin:0;">Manage Rooms</h1>
        <p style="color:var(--text-muted);margin:4px 0 0;font-size:0.88rem;">
          {{ $hostel->rooms->count() }} room type(s) ·
          {{ $hostel->rooms->sum('available_count') }} available of {{ $hostel->rooms->sum('total_count') }} total
        </p>
      </div>
      <button class="btn-primary-findr" onclick="showAddForm()" id="addRoomBtn">
        <i class="bi bi-plus-lg me-2"></i>Add Room Type
      </button>
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

    <!-- Add / Edit Room Form (hidden by default) -->
    <div id="roomFormWrap" style="display:none;">
      <div class="room-form-card">
        <h6 id="formTitle"><i class="bi bi-door-open me-2" style="color:var(--brand-primary);"></i>Add New Room Type</h6>

        <form method="POST" id="roomForm" enctype="multipart/form-data">
          @csrf
          <input type="hidden" name="_method" id="formMethod" value="POST">
          <input type="hidden" name="room_id"  id="formRoomId"  value="">

          <div class="row g-3">
            <!-- Name -->
            <div class="col-md-6">
              <label class="form-label">Room Name <span style="color:var(--danger);">*</span></label>
              <input type="text" name="name" id="rName" class="form-control" placeholder="e.g. AC Single Room" required>
            </div>
            <!-- Type -->
            <div class="col-md-6">
              <label class="form-label">Room Type <span style="color:var(--danger);">*</span></label>
              <select name="type" id="rType" class="form-select" required>
                <option value="single">Single</option>
                <option value="double">Double</option>
                <option value="triple">Triple</option>
                <option value="shared">Shared</option>
                <option value="dormitory">Dormitory</option>
              </select>
            </div>
            <!-- Price -->
            <div class="col-md-4">
              <label class="form-label">Monthly Rent (₹) <span style="color:var(--danger);">*</span></label>
              <input type="number" name="price_per_month" id="rPrice" class="form-control" placeholder="5000" min="0" required>
            </div>
            <!-- Security Deposit -->
            <div class="col-md-4">
              <label class="form-label">Security Deposit (₹)</label>
              <input type="number" name="security_deposit" id="rDeposit" class="form-control" placeholder="0" min="0" value="0">
            </div>
            <!-- Daily Price -->
            <div class="col-md-4">
              <label class="form-label">Daily Rate (₹) <span style="font-size:0.72rem;color:var(--text-muted);">Optional</span></label>
              <input type="number" name="price_per_day" id="rPriceDay" class="form-control" placeholder="200" min="0">
            </div>
            <!-- Capacity -->
            <div class="col-md-4">
              <label class="form-label">Capacity (persons/room)</label>
              <input type="number" name="capacity" id="rCapacity" class="form-control" placeholder="1" min="1" value="1">
            </div>
            <!-- Total Count -->
            <div class="col-md-4">
              <label class="form-label">Total Rooms of This Type</label>
              <input type="number" name="total_count" id="rTotal" class="form-control" placeholder="5" min="1" value="1">
            </div>
            <!-- Available Count -->
            <div class="col-md-4">
              <label class="form-label">Currently Available</label>
              <input type="number" name="available_count" id="rAvail" class="form-control" placeholder="5" min="0" value="1">
            </div>
            <!-- Floor -->
            <div class="col-md-4">
              <label class="form-label">Floor Number</label>
              <input type="text" name="floor_number" id="rFloor" class="form-control" placeholder="Ground / 1 / 2…">
            </div>
            <!-- AC -->
            <div class="col-md-4 d-flex align-items-end pb-2">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;">
                <input type="checkbox" name="is_ac" id="rAc" value="1" style="accent-color:var(--brand-primary);width:16px;height:16px;">
                ❄️ Air Conditioned
              </label>
            </div>
            <!-- Availability Toggle -->
            <div class="col-md-4 d-flex align-items-end pb-2">
              <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;">
                <input type="checkbox" name="is_available" id="rIsAvail" value="1" checked style="accent-color:var(--brand-primary);width:16px;height:16px;">
                ✓ Mark as Available
              </label>
            </div>
          </div>

          <!-- Facilities -->
          <div style="margin-top:1.25rem;padding-top:1.25rem;border-top:1px solid var(--border-color);">
            <label class="form-label">Room Facilities</label>
            <div class="row g-0">
              @foreach([
                ['has_attached_bathroom','🚿 Attached Bathroom'],
                ['has_balcony','🌿 Balcony'],
                ['has_study_table','📚 Study Table'],
                ['has_wardrobe','👔 Wardrobe'],
                ['has_tv','📺 Television'],
                ['has_fridge','❄️ Refrigerator'],
              ] as [$field, $label])
              <div class="col-6 col-md-4">
                <label style="display:flex;align-items:center;gap:8px;padding:7px 10px;border-radius:8px;cursor:pointer;font-size:0.85rem;">
                  <input type="checkbox" name="{{ $field }}" id="r_{{ $field }}" value="1" style="accent-color:var(--brand-primary);width:15px;height:15px;">
                  {{ $label }}
                </label>
              </div>
              @endforeach
            </div>
          </div>

          <!-- Description -->
          <div style="margin-top:1rem;">
            <label class="form-label">Description <span style="font-size:0.72rem;color:var(--text-muted);">Optional</span></label>
            <textarea name="description" id="rDesc" class="form-control" rows="2" placeholder="Any additional details about this room type…"></textarea>
          </div>

          <!-- Photos -->
          <div style="margin-top:1rem;">
            <label class="form-label">Room Photos <span style="font-size:0.72rem;color:var(--text-muted);">Optional</span></label>
            <div style="border:2px dashed var(--border-color);border-radius:12px;padding:1.5rem;text-align:center;cursor:pointer;"
                 onclick="document.getElementById('roomImages').click()"
                 onmouseenter="this.style.borderColor='var(--brand-primary)'"
                 onmouseleave="this.style.borderColor='var(--border-color)'">
              <i class="bi bi-camera" style="font-size:1.5rem;color:var(--text-muted);"></i>
              <p style="font-size:0.82rem;color:var(--text-muted);margin:0.4rem 0 0;">Click to upload room photos</p>
            </div>
            <input type="file" id="roomImages" name="images[]" multiple accept="image/*" style="display:none;" onchange="previewRoomImages(this)">
            <div id="roomImgPreview" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:0.75rem;"></div>
          </div>

          <div class="d-flex gap-3 mt-4">
            <button type="button" class="btn-outline-findr" onclick="hideAddForm()">Cancel</button>
            <button type="submit" class="btn-primary-findr" id="roomSubmitBtn">
              <i class="bi bi-save me-2"></i>Save Room
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Room List -->
    @if($hostel->rooms->isEmpty())
    <div style="text-align:center;padding:4rem 2rem;background:var(--bg-surface);border:1.5px dashed var(--border-color);border-radius:16px;">
      <div style="font-size:3rem;margin-bottom:1rem;">🛏️</div>
      <h5 style="font-weight:700;margin-bottom:0.5rem;">No rooms added yet</h5>
      <p style="color:var(--text-muted);font-size:0.9rem;margin-bottom:1.5rem;">Add your first room type so guests can book.</p>
      <button class="btn-primary-findr" onclick="showAddForm()">
        <i class="bi bi-plus-lg me-2"></i>Add First Room
      </button>
    </div>
    @else
    <div id="roomList">
      @foreach($hostel->rooms as $room)
      <div class="room-card" id="roomCard{{ $room->id }}">
        <div class="row g-0">

          {{-- Image --}}
          @if($room->images->count())
          <div class="col-md-3">
            <img src="{{ $room->images->first()->url ?? asset('images/hostel-placeholder.jpg') }}"
                 alt="{{ $room->name }}"
                 style="width:100%;height:180px;object-fit:cover;">
          </div>
          <div class="col-md-9">
          @else
          <div class="col-12">
          @endif

            <div style="padding:1.25rem;">
              <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                <div>
                  <div class="d-flex align-items-center gap-2 flex-wrap">
                    <h6 style="font-weight:700;font-size:1rem;margin:0;">{{ $room->name }}</h6>
                    <span style="background:var(--bg-subtle);color:var(--text-secondary);border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:600;">{{ ucfirst($room->type) }}</span>
                    @if($room->is_ac)<span style="background:rgba(59,130,246,0.1);color:#2563EB;border-radius:6px;padding:2px 8px;font-size:0.75rem;font-weight:600;">❄️ AC</span>@endif
                    <span class="badge-status badge-{{ $room->is_available && $room->available_count > 0 ? 'active' : 'inactive' }}">
                      {{ $room->is_available && $room->available_count > 0 ? 'Available' : 'Unavailable' }}
                    </span>
                  </div>
                  @if($room->description)
                  <p style="font-size:0.82rem;color:var(--text-muted);margin:4px 0 0;">{{ Str::limit($room->description, 80) }}</p>
                  @endif
                </div>
                <div style="text-align:right;flex-shrink:0;">
                  <strong style="font-size:1.2rem;color:var(--brand-primary);">₹{{ number_format($room->price_per_month) }}</strong>
                  <span style="font-size:0.75rem;color:var(--text-muted);">/month</span>
                  @if($room->security_deposit > 0)
                  <p style="font-size:0.75rem;color:var(--text-muted);margin:2px 0 0;">₹{{ number_format($room->security_deposit) }} deposit</p>
                  @endif
                </div>
              </div>

              {{-- Stats Row --}}
              <div class="d-flex flex-wrap gap-3 mt-3">
                <div style="font-size:0.82rem;">
                  <span style="color:var(--text-muted);">Capacity:</span>
                  <strong>{{ $room->capacity }} person(s)</strong>
                </div>
                <div style="font-size:0.82rem;">
                  <span style="color:var(--text-muted);">Available:</span>
                  <strong style="color:{{ $room->available_count > 0 ? 'var(--brand-accent)' : 'var(--danger)' }};">
                    {{ $room->available_count }} / {{ $room->total_count }}
                  </strong>
                </div>
                @if($room->floor_number)
                <div style="font-size:0.82rem;">
                  <span style="color:var(--text-muted);">Floor:</span>
                  <strong>{{ $room->floor_number }}</strong>
                </div>
                @endif
                @if($room->price_per_day)
                <div style="font-size:0.82rem;">
                  <span style="color:var(--text-muted);">Daily:</span>
                  <strong>₹{{ number_format($room->price_per_day) }}</strong>
                </div>
                @endif
              </div>

              {{-- Facilities --}}
              <div class="d-flex flex-wrap gap-1 mt-2">
                @if($room->has_attached_bathroom)<span class="facility-badge">🚿 Attached Bath</span>@endif
                @if($room->has_balcony)          <span class="facility-badge">🌿 Balcony</span>@endif
                @if($room->has_study_table)      <span class="facility-badge">📚 Study Table</span>@endif
                @if($room->has_wardrobe)         <span class="facility-badge">👔 Wardrobe</span>@endif
                @if($room->has_tv)               <span class="facility-badge">📺 TV</span>@endif
                @if($room->has_fridge)           <span class="facility-badge">❄️ Fridge</span>@endif
              </div>

              {{-- Actions --}}
              <div class="d-flex gap-2 mt-3 flex-wrap">
                <button onclick="editRoom({{ $room->id }})"
                  style="background:rgba(92,95,239,0.08);color:var(--brand-primary);border:1px solid rgba(92,95,239,0.3);border-radius:8px;padding:5px 14px;font-size:0.82rem;cursor:pointer;font-weight:600;">
                  <i class="bi bi-pencil me-1"></i>Edit
                </button>

                {{-- Quick availability toggle --}}
                <form method="POST" action="{{ route('owner.hostel.rooms.toggle', [$hostel->id, $room->id]) }}" style="margin:0;">
                  @csrf
                  <button type="submit"
                    style="background:{{ $room->is_available ? 'rgba(239,68,68,0.08)' : 'rgba(16,185,129,0.08)' }};color:{{ $room->is_available ? 'var(--danger)' : 'var(--brand-accent)' }};border:1px solid {{ $room->is_available ? 'rgba(239,68,68,0.3)' : 'rgba(16,185,129,0.3)' }};border-radius:8px;padding:5px 14px;font-size:0.82rem;cursor:pointer;font-weight:600;">
                    {{ $room->is_available ? '✕ Mark Unavailable' : '✓ Mark Available' }}
                  </button>
                </form>

                <form method="POST" action="{{ route('owner.hostel.rooms.delete', [$hostel->id, $room->id]) }}" style="margin:0;"
                      onsubmit="return confirm('Delete this room type? This cannot be undone.')">
                  @csrf @method('DELETE')
                  <button type="submit"
                    style="background:rgba(239,68,68,0.06);color:var(--danger);border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:5px 14px;font-size:0.82rem;cursor:pointer;">
                    <i class="bi bi-trash"></i>
                  </button>
                </form>
              </div>

            </div>
          </div>{{-- end col --}}
        </div>{{-- end row --}}
      </div>{{-- end room-card --}}
      @endforeach
    </div>
    @endif

  </div>
</div>

{{-- Hidden room data for JS edit --}}
<script id="roomsData" type="application/json">
<?php
$roomsJson = $hostel->rooms->map(function($r) {
    return [
        'id'                   => $r->id,
        'name'                 => $r->name,
        'type'                 => $r->type,
        'price_per_month'      => $r->price_per_month,
        'price_per_day'        => $r->price_per_day,
        'security_deposit'     => $r->security_deposit,
        'capacity'             => $r->capacity,
        'total_count'          => $r->total_count,
        'available_count'      => $r->available_count,
        'floor_number'         => $r->floor_number,
        'description'          => $r->description,
        'is_ac'                => (bool) $r->is_ac,
        'is_available'         => (bool) $r->is_available,
        'has_attached_bathroom'=> (bool) $r->has_attached_bathroom,
        'has_balcony'          => (bool) $r->has_balcony,
        'has_study_table'      => (bool) $r->has_study_table,
        'has_wardrobe'         => (bool) $r->has_wardrobe,
        'has_tv'               => (bool) $r->has_tv,
        'has_fridge'           => (bool) $r->has_fridge,
    ];
});
echo json_encode($roomsJson);
?>
</script>
@endsection

@push('scripts')
<script>
var roomsData = JSON.parse(document.getElementById('roomsData').textContent);
var editingId = null;

function showAddForm() {
    editingId = null;
    resetForm();
    document.getElementById('formTitle').innerHTML = '<i class="bi bi-door-open me-2" style="color:var(--brand-primary);"></i>Add New Room Type';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('roomForm').action   = '{{ route("owner.hostel.rooms.store", $hostel->id) }}';
    document.getElementById('roomFormWrap').style.display = 'block';
    document.getElementById('roomFormWrap').scrollIntoView({ behavior:'smooth', block:'start' });
    document.getElementById('addRoomBtn').style.display = 'none';
}

function hideAddForm() {
    document.getElementById('roomFormWrap').style.display = 'none';
    document.getElementById('addRoomBtn').style.display = 'flex';
    resetForm();
}

function editRoom(id) {
    var r = roomsData.find(function(x) { return x.id === id; });
    if (!r) return;
    editingId = id;

    document.getElementById('formTitle').innerHTML = '<i class="bi bi-pencil me-2" style="color:var(--brand-primary);"></i>Edit Room';
    document.getElementById('formMethod').value  = 'PUT';
    document.getElementById('formRoomId').value  = id;
    document.getElementById('roomForm').action   = '/owner/hostel/{{ $hostel->id }}/rooms/' + id + '/update';

    document.getElementById('rName').value        = r.name;
    document.getElementById('rType').value        = r.type;
    document.getElementById('rPrice').value       = r.price_per_month;
    document.getElementById('rPriceDay').value    = r.price_per_day || '';
    document.getElementById('rDeposit').value     = r.security_deposit;
    document.getElementById('rCapacity').value    = r.capacity;
    document.getElementById('rTotal').value       = r.total_count;
    document.getElementById('rAvail').value       = r.available_count;
    document.getElementById('rFloor').value       = r.floor_number || '';
    document.getElementById('rDesc').value        = r.description || '';
    document.getElementById('rAc').checked        = !!r.is_ac;
    document.getElementById('rIsAvail').checked   = !!r.is_available;
    document.getElementById('r_has_attached_bathroom').checked = !!r.has_attached_bathroom;
    document.getElementById('r_has_balcony').checked           = !!r.has_balcony;
    document.getElementById('r_has_study_table').checked       = !!r.has_study_table;
    document.getElementById('r_has_wardrobe').checked          = !!r.has_wardrobe;
    document.getElementById('r_has_tv').checked                = !!r.has_tv;
    document.getElementById('r_has_fridge').checked            = !!r.has_fridge;

    document.getElementById('roomFormWrap').style.display = 'block';
    document.getElementById('roomFormWrap').scrollIntoView({ behavior:'smooth', block:'start' });
    document.getElementById('addRoomBtn').style.display = 'none';
}

function resetForm() {
    var form = document.getElementById('roomForm');
    form.reset();
    document.getElementById('roomImgPreview').innerHTML = '';
    document.getElementById('formRoomId').value = '';
    document.getElementById('r_has_study_table').checked = true;
    document.getElementById('rIsAvail').checked = true;
}

function previewRoomImages(input) {
    var preview = document.getElementById('roomImgPreview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(function(file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var div = document.createElement('div');
            div.className = 'img-thumb';
            div.innerHTML = '<img src="' + e.target.result + '">'
                + '<button type="button" class="del-btn" onclick="this.parentNode.remove()">✕</button>';
            preview.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}
</script>
@endpush
