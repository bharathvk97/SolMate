@extends('layouts.app')
@section('title', 'Assets — Room Items')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-content { flex:1; padding:2rem; min-width:0; }

.flt-label { font-size:.72rem; font-weight:600; color:var(--text-muted); display:block; margin-bottom:3px; }
.btn-sm-findr { padding:.42rem .9rem !important; font-size:.8rem !important; }

.res-table { width:100%; border-collapse:separate; border-spacing:0; }
.res-table th { text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.4px; color:var(--text-muted); padding:.6rem .75rem; border-bottom:1px solid var(--border-color); white-space:nowrap; }
.res-table td { padding:.7rem .75rem; border-bottom:1px solid var(--border-color); font-size:.86rem; vertical-align:middle; }
.res-table tr:last-child td { border-bottom:none; }

.qty-badge { display:inline-flex; align-items:center; justify-content:center; min-width:38px; height:28px; padding:0 10px; border-radius:8px; background:var(--bg-subtle); color:var(--text-primary); font-weight:800; font-size:.9rem; }

.item-chip { display:inline-flex; align-items:center; gap:6px; font-weight:700; }
.item-ico { width:30px; height:30px; border-radius:8px; display:inline-flex; align-items:center; justify-content:center; background:rgba(92,95,239,0.1); color:#5C5FEF; flex-shrink:0; }

.icon-btn { width:30px; height:30px; border-radius:8px; border:1px solid var(--border-color); background:var(--bg-surface); color:var(--text-secondary); display:inline-flex; align-items:center; justify-content:center; cursor:pointer; }
.icon-btn:hover { border-color:var(--brand-primary); color:var(--brand-primary); }
.icon-btn.danger:hover { border-color:var(--danger); color:var(--danger); }
</style>
@endpush

@section('content')
<div class="owner-wrapper">
    @include('owner.partials.hostel-sidebar')

    <div class="owner-content">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
            <div>
                <h1 style="font-size:1.6rem;font-weight:800;margin:0;">Assets</h1>
                <p style="color:var(--text-muted);margin:4px 0 0;font-size:.9rem;">Track items in each room — beds, chairs, pillows and more.</p>
            </div>
            @if($hostels->isNotEmpty())
            <button class="btn-primary-findr" onclick="openAdd()"><i class="bi bi-plus-lg me-2"></i>Add Item</button>
            @endif
        </div>

        @if($hostels->isEmpty())
            <div class="card-findr" style="padding:2.5rem;text-align:center;">
                <i class="bi bi-box-seam" style="font-size:2.4rem;color:var(--text-muted);"></i>
                <h6 style="font-weight:700;margin-top:.75rem;">No hostels yet</h6>
                <p style="color:var(--text-muted);font-size:.9rem;">Create a hostel first, then you can start logging its room assets.</p>
                <a href="{{ route('owner.hostel.create') }}" class="btn-primary-findr d-inline-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i>Add Hostel</a>
            </div>
        @else

        <!-- Stat cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(92,95,239,0.1);color:#5C5FEF;"><i class="bi bi-card-checklist"></i></div>
                <div><div class="stat-value">{{ $stats['items'] }}</div><div class="stat-label">Item Entries</div></div>
            </div></div></div>
            <div class="col-6 col-md-3"><div class="stat-card"><div class="d-flex align-items-start gap-3">
                <div class="stat-icon" style="background:rgba(16,185,129,0.12);color:#10B981;"><i class="bi bi-stack"></i></div>
                <div><div class="stat-value">{{ $stats['units'] }}</div><div class="stat-label">Total Units</div></div>
            </div></div></div>
        </div>

        <!-- Search + filters -->
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end mb-3">
            <div style="flex:1;min-width:220px;">
                <label class="flt-label">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Item name or room number">
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
            @if(request('search') || request('hostel_id') || request('room_id'))
                <a href="{{ route('owner.hostel.assets') }}" class="btn-outline-findr btn-sm-findr">Clear</a>
            @endif
        </form>

        <!-- Assets table -->
        <div class="card-findr" style="padding:0;overflow:hidden;">
            @if($assets->isEmpty())
                <div class="p-5 text-center" style="color:var(--text-muted);">
                    <i class="bi bi-box2" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                    <h6 style="font-weight:700;">No items found</h6>
                    <p style="font-size:.86rem;margin:0;">Add your first room item or adjust the filters.</p>
                </div>
            @else
                <div style="overflow-x:auto;">
                    <table class="res-table">
                        <thead><tr>
                            <th>Room</th><th>Item</th><th>Quantity</th><th>Notes</th><th style="text-align:right;">Actions</th>
                        </tr></thead>
                        <tbody>
                            @foreach($assets as $a)
                            <tr>
                                <td>
                                    <div style="font-weight:700;">{{ $a->room_label ?? '—' }}</div>
                                    <div style="font-size:.76rem;color:var(--text-muted);">{{ $a->hostel?->name }}</div>
                                </td>
                                <td>
                                    <span class="item-chip"><span class="item-ico"><i class="bi bi-box-seam"></i></span>{{ $a->item_name }}</span>
                                </td>
                                <td><span class="qty-badge">{{ $a->quantity }}</span></td>
                                <td style="color:var(--text-muted);max-width:280px;">{{ $a->notes ?: '—' }}</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-end">
                                        <button class="icon-btn" title="Edit" onclick="openEdit({{ $a->id }})"><i class="bi bi-pencil"></i></button>
                                        <form method="POST" action="{{ route('owner.hostel.assets.delete', $a->id) }}" onsubmit="return confirm('Delete {{ addslashes($a->item_name) }}?');" style="margin:0;">
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

        <div style="margin-top:1rem;">{{ $assets->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</div>

<!-- ============ ADD / EDIT MODAL ============ -->
<div class="modal fade" id="assetModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content" style="background:var(--bg-surface);border:1px solid var(--border-color);border-radius:16px;">
      <form method="POST" id="assetForm">
        @csrf
        <div class="modal-header" style="border-color:var(--border-color);">
          <h5 class="modal-title" id="assetModalTitle" style="font-weight:800;">Add Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Hostel <span style="color:var(--danger);">*</span></label>
              <select name="hostel_id" id="a_hostel" class="form-select" required onchange="filterRooms(this.value)">
                <option value="">Select hostel…</option>
                @foreach($hostels as $h)<option value="{{ $h->id }}">{{ $h->name }}</option>@endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Room</label>
              <select name="room_id" id="a_room" class="form-select">
                <option value="">— None —</option>
                @foreach($hostels as $h)@foreach($h->rooms as $r)
                  <option value="{{ $r->id }}" data-hostel="{{ $h->id }}">{{ $r->name }}</option>
                @endforeach @endforeach
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label">Room No.</label>
              <input type="text" name="room_number" id="a_room_number" class="form-control" placeholder="A-101">
            </div>

            <div class="col-md-8">
              <label class="form-label">Item Name <span style="color:var(--danger);">*</span></label>
              <input type="text" name="item_name" id="a_item" class="form-control" required placeholder="Bed, Chair, Pillow…" list="commonItems">
              <datalist id="commonItems">
                <option value="Bed"><option value="Chair"><option value="Pillow"><option value="Table">
                <option value="Mattress"><option value="Fan"><option value="Cupboard"><option value="Bedsheet">
                <option value="Bucket"><option value="Light"><option value="Curtain"><option value="Dustbin">
              </datalist>
            </div>
            <div class="col-md-4">
              <label class="form-label">Quantity <span style="color:var(--danger);">*</span></label>
              <input type="number" name="quantity" id="a_qty" class="form-control" required min="0" max="100000" value="1">
            </div>

            <div class="col-12">
              <label class="form-label">Notes</label>
              <textarea name="notes" id="a_notes" class="form-control" rows="2" placeholder="Condition, brand, or anything worth noting…"></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-color:var(--border-color);">
          <button type="button" class="btn-outline-findr" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn-primary-findr"><i class="bi bi-save me-2"></i>Save Item</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const ASSETS     = @json($assets->getCollection()->keyBy('id'));
const STORE_URL  = "{{ route('owner.hostel.assets.store') }}";
const UPDATE_TPL = "{{ route('owner.hostel.assets.update', ['id'=>'__ID__']) }}";

let assetModal;
document.addEventListener('DOMContentLoaded', () => {
  assetModal = new bootstrap.Modal(document.getElementById('assetModal'));
});

function filterRooms(hostelId) {
  const sel = document.getElementById('a_room');
  [...sel.options].forEach(o => {
    if (!o.value) { o.hidden = false; return; }
    const match = o.dataset.hostel === String(hostelId);
    o.hidden = !match;
    if (!match && o.selected) sel.value = '';
  });
}

function resetForm() {
  const f = document.getElementById('assetForm');
  f.reset();
  document.getElementById('a_qty').value = 1;
  filterRooms('');
}

function openAdd() {
  resetForm();
  document.getElementById('assetModalTitle').textContent = 'Add Item';
  document.getElementById('assetForm').action = STORE_URL;
  assetModal.show();
}

function openEdit(id) {
  const a = ASSETS[id];
  if (!a) return;
  resetForm();
  document.getElementById('a_hostel').value = a.hostel_id ?? '';
  filterRooms(a.hostel_id ?? '');
  document.getElementById('a_room').value        = a.room_id ?? '';
  document.getElementById('a_room_number').value = a.room_number ?? '';
  document.getElementById('a_item').value        = a.item_name ?? '';
  document.getElementById('a_qty').value         = a.quantity ?? 0;
  document.getElementById('a_notes').value       = a.notes ?? '';
  document.getElementById('assetModalTitle').textContent = 'Edit Item';
  document.getElementById('assetForm').action = UPDATE_TPL.replace('__ID__', id);
  assetModal.show();
}
</script>
@endpush
