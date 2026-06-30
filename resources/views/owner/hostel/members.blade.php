@extends('layouts.app')
@section('title', 'Subscription — Members')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-content { flex:1; padding:2rem; min-width:0; }

.rent-pill { display:inline-flex; align-items:center; gap:6px; padding:.4rem .9rem; border-radius:30px; border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-secondary); font-size:.82rem; font-weight:600; text-decoration:none; }
.rent-pill:hover { border-color:var(--brand-primary); color:var(--brand-primary); }
.rent-pill.active { background:var(--brand-primary); border-color:var(--brand-primary); color:#fff; }
.pill-count { background:rgba(0,0,0,0.08); border-radius:20px; padding:0 .5rem; font-size:.72rem; }
.rent-pill.active .pill-count { background:rgba(255,255,255,0.25); }

.flt-label { font-size:.72rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px; }
.btn-sm-findr { padding:.42rem .9rem !important; font-size:.8rem !important; }

.res-table { width:100%; border-collapse:separate; border-spacing:0; }
.res-table th { text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted); padding:.6rem .75rem; border-bottom:1px solid var(--border-color); white-space:nowrap; }
.res-table td { padding:.7rem .75rem; border-bottom:1px solid var(--border-color); font-size:.86rem; vertical-align:middle; }
.res-table tr:last-child td { border-bottom:none; }

.avatar-sm { width:38px; height:38px; border-radius:50%; object-fit:cover; border:1px solid var(--border-color); flex-shrink:0; }
.avatar-ph { width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:var(--bg-subtle); color:var(--text-muted); font-weight:700; flex-shrink:0; }

.rent-badge { display:inline-block; font-size:.7rem; font-weight:700; padding:3px 9px; border-radius:20px; text-transform:capitalize; }
.rb-pending      { background:#FEF3C7; color:#92400E; }
.rb-advance_paid { background:#DBEAFE; color:#1E40AF; }
.rb-fully_paid   { background:#D1FAE5; color:#065F46; }
[data-theme="dark"] .rb-pending      { background:rgba(245,158,11,.18); color:#FDE68A; }
[data-theme="dark"] .rb-advance_paid { background:rgba(59,130,246,.18); color:#BFDBFE; }
[data-theme="dark"] .rb-fully_paid   { background:rgba(16,185,129,.18); color:#6EE7B7; }

.icon-btn { width:30px; height:30px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-secondary); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
.icon-btn:hover { border-color:var(--brand-primary); color:var(--brand-primary); }
.icon-btn.danger:hover { border-color:var(--danger); color:var(--danger); }

.dl-row { display:flex; gap:10px; padding:.45rem 0; border-bottom:1px solid var(--border-color); font-size:.88rem; }
.dl-row:last-child { border-bottom:none; }
.dl-row .k { width:130px; flex-shrink:0; color:var(--text-muted); }
.dl-row .v { color:var(--text-primary); font-weight:500; }

/* Add/Edit modal: keep the footer (Save button) pinned and scroll only the
   fields. A <form> wraps the whole modal, which breaks Bootstrap's default
   scrollable layout, so we re-establish the flex column on the form here. */
#memberModal .modal-content { max-height: calc(100vh - 3.5rem); }
#memberModal .modal-content > form { display:flex; flex-direction:column; min-height:0; max-height:100%; overflow:hidden; }
#memberModal .modal-body { flex:1 1 auto; min-height:0; overflow-y:auto; }
#memberModal .modal-header, #memberModal .modal-footer { flex:0 0 auto; }
</style>
@endpush

@section('content')
@php $rent = request('rent'); @endphp
<div class="owner-wrapper">
    @include('owner.partials.hostel-sidebar')

    <div class="owner-content">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;margin:0;">Subscription</h1>
                <p style="color:var(--text-muted);margin:4px 0 0;font-size:.9rem;">Manage the people staying in your hostels, their rooms and rent.</p>
            </div>
            @if($hostels->isNotEmpty())
            <button class="btn-primary-findr" onclick="openAdd()"><i class="bi bi-plus-lg me-2"></i>Add Member</button>
            @endif
        </div>

        @if($hostels->isEmpty())
            <div class="card-findr" style="padding:2.5rem;text-align:center;">
                <i class="bi bi-building" style="font-size:2.4rem;color:var(--text-muted);"></i>
                <h6 style="font-weight:700;margin-top:.75rem;">No hostels yet</h6>
                <p style="color:var(--text-muted);font-size:.9rem;">Create a hostel first, then you can start adding members.</p>
                <a href="{{ route('owner.hostel.create') }}" class="btn-primary-findr d-inline-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i>Add Hostel</a>
            </div>
        @else

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(92,95,239,0.1);color:#5C5FEF;"><i class="bi bi-people-fill"></i></div>
                <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Members</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(245,158,11,0.12);color:#F59E0B;"><i class="bi bi-hourglass-split"></i></div>
                <div><div class="stat-value">{{ $stats['pending'] }}</div><div class="stat-label">Rent Unpaid</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(59,130,246,0.12);color:#3B82F6;"><i class="bi bi-cash-coin"></i></div>
                <div><div class="stat-value">{{ $stats['advance_paid'] }}</div><div class="stat-label">Advance Paid</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10B981;"><i class="bi bi-check-circle-fill"></i></div>
                <div><div class="stat-value">{{ $stats['fully_paid'] }}</div><div class="stat-label">Rent Paid</div></div>
            </div></div></div>
        </div>

        <!-- Rent filter pills -->
        <div class="d-flex flex-wrap gap-2 mb-3">
            @php $keep = array_filter(['search'=>request('search'),'hostel_id'=>request('hostel_id')]); @endphp
            <a href="{{ route('owner.hostel.members', $keep) }}" class="rent-pill {{ !$rent ? 'active' : '' }}">All <span class="pill-count">{{ $stats['total'] }}</span></a>
            <a href="{{ route('owner.hostel.members', $keep + ['rent'=>'pending']) }}" class="rent-pill {{ $rent==='pending' ? 'active' : '' }}">Unpaid <span class="pill-count">{{ $stats['pending'] }}</span></a>
            <a href="{{ route('owner.hostel.members', $keep + ['rent'=>'advance_paid']) }}" class="rent-pill {{ $rent==='advance_paid' ? 'active' : '' }}">Advance <span class="pill-count">{{ $stats['advance_paid'] }}</span></a>
            <a href="{{ route('owner.hostel.members', $keep + ['rent'=>'fully_paid']) }}" class="rent-pill {{ $rent==='fully_paid' ? 'active' : '' }}">Paid <span class="pill-count">{{ $stats['fully_paid'] }}</span></a>
        </div>

        <!-- Search + filters -->
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <input type="hidden" name="rent" value="{{ $rent }}">
            <div style="flex:1;min-width:220px;">
                <label class="flt-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Name, phone, place, room no, ID number or #id">
            </div>
            @if($hostels->count() > 1)
            <div>
                <label class="flt-label">Hostel</label>
                <select name="hostel_id" class="form-select form-select-sm" style="width:auto;">
                    <option value="">All hostels</option>
                    @foreach($hostels as $h)
                        <option value="{{ $h->id }}" @selected((string)request('hostel_id')===(string)$h->id)>{{ $h->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <button type="submit" class="btn-primary-findr btn-sm-findr"><i class="bi bi-search me-1"></i>Search</button>
            @if(request('search') || request('hostel_id'))
                <a href="{{ route('owner.hostel.members', $rent ? ['rent'=>$rent] : []) }}" class="btn-outline-findr btn-sm-findr">Clear</a>
            @endif
        </form>

        <!-- Members table -->
        <div class="card-findr" style="padding:0;overflow:hidden;">
            @if($members->isEmpty())
                <div class="p-5 text-center" style="color:var(--text-muted);">
                    <i class="bi bi-person-vcard" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                    <h6 style="font-weight:700;">No members found</h6>
                    <p style="font-size:.86rem;margin:0;">Add your first member or adjust the filters.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="res-table">
                        <thead><tr>
                            <th>Member</th><th>Contact</th><th>Room</th><th>ID Proof</th><th>Stay</th><th>Rent</th><th style="text-align:right;">Actions</th>
                        </tr></thead>
                        <tbody>
                            @foreach($members as $m)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($m->photo_url)
                                            <img src="{{ $m->photo_url }}" class="avatar-sm" alt="">
                                        @else
                                            <div class="avatar-ph">{{ strtoupper(mb_substr($m->name,0,1)) }}</div>
                                        @endif
                                        <div>
                                            <div style="font-weight:700;">{{ $m->name }}</div>
                                            <div style="font-size:.76rem;color:var(--text-muted);">
                                                #{{ $m->id }}@if($m->age) · {{ $m->age }} yrs @endif @if($m->place) · {{ $m->place }} @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($m->phone)<div><i class="bi bi-telephone" style="color:var(--text-muted);"></i> {{ $m->phone }}</div>@endif
                                    @if($m->email)<div style="font-size:.78rem;color:var(--text-muted);">{{ $m->email }}</div>@endif
                                    @if(!$m->phone && !$m->email)<span style="color:var(--text-muted);">—</span>@endif
                                </td>
                                <td>
                                    <div style="font-weight:600;">{{ $m->room_label ?? '—' }}</div>
                                    <div style="font-size:.76rem;color:var(--text-muted);">{{ $m->hostel?->name }}</div>
                                </td>
                                <td>
                                    @if($m->id_proof_number)
                                        <div style="font-size:.72rem;color:var(--text-muted);text-transform:uppercase;">{{ $m->id_proof_type ?? 'ID' }}</div>
                                        <div style="font-family:monospace;">{{ $m->id_proof_number }}</div>
                                    @else <span style="color:var(--text-muted);">—</span> @endif
                                </td>
                                <td style="font-size:.8rem;color:var(--text-muted);">
                                    @if($m->date_of_join)<div>In: {{ $m->date_of_join->format('d M Y') }}</div>@endif
                                    @if($m->date_of_left)<div>Out: {{ $m->date_of_left->format('d M Y') }}</div>@endif
                                    @if(!$m->date_of_join && !$m->date_of_left)—@endif
                                </td>
                                <td><span class="rent-badge rb-{{ $m->rent_status }}">{{ str_replace('_',' ',$m->rent_status) }}</span></td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="icon-btn" title="View" onclick="openView({{ $m->id }})"><i class="bi bi-eye"></i></button>
                                        <button class="icon-btn" title="Edit" onclick="openEdit({{ $m->id }})"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('owner.hostel.members.delete', $m->id) }}" onsubmit="return confirm('Remove {{ addslashes($m->name) }}?');" style="margin:0;">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-btn danger" title="Delete"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <div style="margin-top:1rem;">{{ $members->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>

<!-- ============ ADD / EDIT MODAL ============ -->
<div class="modal fade" id="memberModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:16px;">
      <form method="POST" id="memberForm" enctype="multipart/form-data">
        @csrf
        <div class="modal-header" style="border-color:var(--border-color);">
          <h5 class="modal-title" id="memberModalTitle" style="font-weight:800;">Add Member</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Hostel <span style="color:var(--danger);">*</span></label>
              <select name="hostel_id" id="m_hostel" class="form-select" required onchange="filterRooms(this.value)">
                <option value="">Select hostel…</option>
                @foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Room</label>
              <select name="room_id" id="m_room" class="form-select">
                <option value="">— None —</option>
                @foreach($hostels as $h)@foreach($h->rooms as $r)
                  <option value="{{ $r->id }}" data-hostel="{{ $h->id }}">{{ $r->name }}</option>
                @endforeach @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Room No.</label>
              <input type="text" name="room_number" id="m_room_number" class="form-control" placeholder="A-101">
            </div>

            <div class="col-md-6">
              <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
              <input type="text" name="name" id="m_name" class="form-control" required>
            </div>
            <div class="col-md-3">
              <label class="form-label">Age</label>
              <input type="number" name="age" id="m_age" class="form-control" min="1" max="120">
            </div>
            <div class="col-md-3">
              <label class="form-label">Native Place</label>
              <input type="text" name="place" id="m_place" class="form-control" placeholder="City / town">
            </div>

            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" id="m_phone" class="form-control" placeholder="9XXXXXXXXX">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" id="m_email" class="form-control" placeholder="name@example.com">
            </div>

            <div class="col-md-4">
              <label class="form-label">ID Proof Type</label>
              <select name="id_proof_type" id="m_idtype" class="form-select">
                <option value="">— Select —</option>
                <option value="aadhaar">Aadhaar</option>
                <option value="pan">PAN</option>
                <option value="passport">Passport</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">ID Proof Number</label>
              <input type="text" name="id_proof_number" id="m_idnum" class="form-control" placeholder="Aadhaar / PAN / Passport number">
            </div>

            <div class="col-md-4">
              <label class="form-label">Date of Join</label>
              <input type="date" name="date_of_join" id="m_join" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Date of Left</label>
              <input type="date" name="date_of_left" id="m_left" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Monthly Rent (₹)</label>
              <input type="number" name="monthly_rent" id="m_rent" class="form-control" min="0" step="0.01">
            </div>

            <div class="col-md-4">
              <label class="form-label">Rent Status <span style="color:var(--danger);">*</span></label>
              <select name="rent_status" id="m_rentstatus" class="form-select" required>
                <option value="pending">Unpaid</option>
                <option value="advance_paid">Advance Paid</option>
                <option value="fully_paid">Fully Paid</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label">Photo</label>
              <div class="d-flex align-items-center gap-3">
                <img id="m_photo_preview" src="" alt="" style="display:none;width:48px;height:48px;border-radius:8px;object-fit:cover;border:1px solid var(--border-color);">
                <input type="file" name="photo" id="m_photo" class="form-control" accept="image/*" onchange="previewPhoto(this,'m_photo_preview')">
              </div>
            </div>

            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" id="m_notes" class="form-control" rows="2" placeholder="Anything worth remembering about this member…"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-color:var(--border-color);">
          <button type="button" class="btn-outline-findr" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-findr"><i class="bi bi-save me-2"></i>Save Member</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ============ VIEW MODAL ============ -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content" style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:16px;">
      <div class="modal-header" style="border-color:var(--border-color);">
        <h5 class="modal-title" style="font-weight:800;">Member Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="text-center mb-3">
          <img id="v_photo" src="" alt="" style="display:none;width:84px;height:84px;border-radius:50%;object-fit:cover;border:1px solid var(--border-color);">
          <div id="v_photo_ph" class="avatar-ph" style="width:84px;height:84px;font-size:1.6rem;margin:0 auto;"></div>
          <h5 id="v_name" style="font-weight:800;margin-top:.6rem;margin-bottom:0;"></h5>
          <span id="v_rent" class="rent-badge"></span>
        </div>
        <div id="v_details"></div>
      </div>
      <div class="modal-footer" style="border-color:var(--border-color);">
        <button type="button" class="btn-outline-findr" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn-primary-findr" id="v_editBtn"><i class="bi bi-pencil me-2"></i>Edit</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const MEMBERS    = @json($members->getCollection()->keyBy('id'));
const STORE_URL  = "{{ route('owner.hostel.members.store') }}";
const UPDATE_TPL = "{{ route('owner.hostel.members.update', ['id'=>'__ID__']) }}";
const RENT_LABEL = { pending:'Unpaid', advance_paid:'Advance Paid', fully_paid:'Fully Paid' };

let memberModal, viewModal;
document.addEventListener('DOMContentLoaded', () => {
  memberModal = new bootstrap.Modal(document.getElementById('memberModal'));
  viewModal   = new bootstrap.Modal(document.getElementById('viewModal'));
});

function filterRooms(hostelId) {
  const sel = document.getElementById('m_room');
  [...sel.options].forEach(o => {
    if (!o.value) { o.hidden = false; return; }
    const match = o.dataset.hostel === String(hostelId);
    o.hidden = !match;
    if (!match && o.selected) sel.value = '';
  });
}

function previewPhoto(input, imgId) {
  const img = document.getElementById(imgId);
  if (input.files && input.files[0]) {
    img.src = URL.createObjectURL(input.files[0]);
    img.style.display = 'block';
  }
}

function resetForm() {
  const f = document.getElementById('memberForm');
  f.reset();
  document.getElementById('m_photo_preview').style.display = 'none';
  filterRooms('');
}

function openAdd() {
  resetForm();
  document.getElementById('memberModalTitle').textContent = 'Add Member';
  document.getElementById('memberForm').action = STORE_URL;
  memberModal.show();
}

function fmtDate(d) { return d ? String(d).slice(0,10) : ''; }

function fillForm(m) {
  resetForm();
  document.getElementById('m_hostel').value = m.hostel_id ?? '';
  filterRooms(m.hostel_id ?? '');
  document.getElementById('m_room').value        = m.room_id ?? '';
  document.getElementById('m_room_number').value = m.room_number ?? '';
  document.getElementById('m_name').value        = m.name ?? '';
  document.getElementById('m_age').value         = m.age ?? '';
  document.getElementById('m_place').value       = m.place ?? '';
  document.getElementById('m_phone').value       = m.phone ?? '';
  document.getElementById('m_email').value       = m.email ?? '';
  document.getElementById('m_idtype').value      = m.id_proof_type ?? '';
  document.getElementById('m_idnum').value       = m.id_proof_number ?? '';
  document.getElementById('m_join').value        = fmtDate(m.date_of_join);
  document.getElementById('m_left').value        = fmtDate(m.date_of_left);
  document.getElementById('m_rent').value        = m.monthly_rent ?? '';
  document.getElementById('m_rentstatus').value  = m.rent_status ?? 'pending';
  document.getElementById('m_notes').value       = m.notes ?? '';
  if (m.photo_url) {
    const p = document.getElementById('m_photo_preview');
    p.src = m.photo_url; p.style.display = 'block';
  }
}

function openEdit(id) {
  const m = MEMBERS[id];
  if (!m) return;
  fillForm(m);
  document.getElementById('memberModalTitle').textContent = 'Edit Member';
  document.getElementById('memberForm').action = UPDATE_TPL.replace('__ID__', id);
  if (viewModal) viewModal.hide();
  memberModal.show();
}

function row(k, v) {
  if (v === null || v === undefined || v === '') return '';
  return `<div class="dl-row"><div class="k">${k}</div><div class="v">${v}</div></div>`;
}

function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function openView(id) {
  const m = MEMBERS[id];
  if (!m) return;
  const img = document.getElementById('v_photo');
  const ph  = document.getElementById('v_photo_ph');
  if (m.photo_url) { img.src = m.photo_url; img.style.display = 'block'; ph.style.display = 'none'; }
  else { img.style.display = 'none'; ph.style.display = 'flex'; ph.textContent = (m.name || '?').charAt(0).toUpperCase(); }

  document.getElementById('v_name').textContent = m.name ?? '';
  const rb = document.getElementById('v_rent');
  rb.className = 'rent-badge rb-' + (m.rent_status || 'pending');
  rb.textContent = RENT_LABEL[m.rent_status] ?? m.rent_status ?? '';

  const roomLabel = m.room_number || (m.room ? m.room.name : '');
  let html = '';
  html += row('Hostel', m.hostel ? esc(m.hostel.name) : '');
  html += row('Room', roomLabel ? esc(roomLabel) : '');
  html += row('Age', m.age);
  html += row('Native Place', m.place ? esc(m.place) : '');
  html += row('Phone', m.phone ? esc(m.phone) : '');
  html += row('Email', m.email ? esc(m.email) : '');
  html += row('ID Proof', m.id_proof_number ? (esc((m.id_proof_type || '').toUpperCase()) + ' · ' + esc(m.id_proof_number)) : '');
  html += row('Date of Join', fmtDate(m.date_of_join));
  html += row('Date of Left', fmtDate(m.date_of_left));
  html += row('Monthly Rent', m.monthly_rent ? ('₹' + m.monthly_rent) : '');
  html += row('Notes', m.notes ? esc(m.notes) : '');
  document.getElementById('v_details').innerHTML = html || '<p style="color:var(--text-muted);">No extra details.</p>';

  document.getElementById('v_editBtn').onclick = () => openEdit(id);
  viewModal.show();
}
</script>
@endpush
