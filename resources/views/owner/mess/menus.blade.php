@extends('layouts.app')
@section('title', 'Food Menus')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-sidebar { width:240px; background:var(--bg-surface); border-right:1px solid var(--border-color); position:sticky; top:65px; height:calc(100vh - 65px); overflow-y:auto; flex-shrink:0; }
.owner-content { flex:1; padding:2rem; min-width:0; }
.menu-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:1rem 1.1rem; height:100%; transition:opacity .2s; }
.menu-card.is-closed { opacity:.55; }
.slot-badge { display:inline-block; font-size:.7rem; font-weight:700; padding:3px 10px; border-radius:20px; text-transform:uppercase; letter-spacing:.3px; }
.slot-morning   { background:#FEF3C7; color:#92400E; }
.slot-afternoon { background:#DBEAFE; color:#1E40AF; }
.slot-evening   { background:#FCE7F3; color:#9D174D; }
.slot-night     { background:#E0E7FF; color:#3730A3; }
.unavail-badge { display:inline-block; margin-left:6px; font-size:.68rem; font-weight:700; padding:3px 8px; border-radius:20px; background:var(--bg-subtle); color:var(--text-muted); }
.menu-items { list-style:none; padding:0; margin:.5rem 0 0; }
.menu-items li { font-size:.85rem; padding:4px 0; border-bottom:1px dashed var(--border-color); color:var(--text-secondary); }
.menu-items li:last-child { border-bottom:none; }
.menu-items li span { color:var(--text-muted); font-size:.8rem; }
.menu-notes { font-size:.8rem; color:var(--text-muted); margin:.6rem 0 0; font-style:italic; }
.menu-count { font-size:.74rem; color:var(--text-muted); background:var(--bg-subtle); padding:2px 10px; border-radius:20px; }
.btn-sm-findr { padding:.32rem .7rem !important; font-size:.78rem !important; }
.btn-danger-soft { background:#FEE2E2; color:#B91C1C; border:none; border-radius:10px; padding:.34rem .6rem; font-size:.78rem; cursor:pointer; }
.btn-danger-soft:hover { background:#FECACA; }
.form-label-sm { font-size:.8rem; font-weight:600; margin-bottom:.25rem; display:block; }
.item-row input { font-size:.85rem; }
</style>
@endpush

@section('content')
@php
  $slotLabels = ['morning'=>'Morning','afternoon'=>'Afternoon','evening'=>'Evening','night'=>'Night'];
@endphp
<div class="owner-wrapper">
    <!-- Sidebar -->
    <aside class="owner-sidebar">
        <div class="pt-3">
            <div class="sidebar-section-label">My Mess</div>
            <a href="{{ route('owner.mess.dashboard') }}" class="sidebar-item {{ request()->routeIs('owner.mess.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Overview</a>
            <a href="{{ route('owner.mess.listings') }}" class="sidebar-item {{ request()->routeIs('owner.mess.listings*') ? 'active' : '' }}"><i class="bi bi-egg-fried"></i> My Messes</a>
            <a href="{{ route('owner.mess.menus') }}" class="sidebar-item {{ request()->routeIs('owner.mess.menus*') ? 'active' : '' }}"><i class="bi bi-menu-button-wide"></i> Food Menus</a>
            <a href="{{ route('owner.mess.bookings') }}" class="sidebar-item {{ request()->routeIs('owner.mess.bookings*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Subscribers
                @if($pendingBookings ?? 0)<span class="sidebar-badge">{{ $pendingBookings }}</span>@endif
            </a>
            <a href="{{ route('owner.mess.reviews') }}" class="sidebar-item {{ request()->routeIs('owner.mess.reviews*') ? 'active' : '' }}"><i class="bi bi-star"></i> Reviews</a>
            <div class="sidebar-section-label">Account</div>
            <a href="{{ route('owner.subscription') }}" class="sidebar-item {{ request()->routeIs('owner.subscription*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Subscription
                @if(!auth()->user()->hasActiveSubscription())<span class="sidebar-badge" style="background:var(--danger);">!</span>@endif
            </a>
            <a href="{{ route('profile') }}" class="sidebar-item"><i class="bi bi-person"></i> Profile</a>
        </div>
    </aside>

    <div class="owner-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="page-header" style="margin-bottom:0;"><h1>Food Menus</h1><p>Add and manage the meals you serve across each slot.</p></div>
            @if($messes->isNotEmpty())
                <button class="btn-primary-findr" onclick="openAddMenu()" style="padding:.55rem 1.1rem;"><i class="bi bi-plus-lg"></i> Add Menu</button>
            @endif
        </div>

        @if($messes->isEmpty())
            <div class="card-findr p-5 text-center" style="color:var(--text-muted);margin-top:1.5rem;">
                <i class="bi bi-shop" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
                <h5 style="font-weight:700;">No mess yet</h5>
                <p style="font-size:.88rem;">Create a mess listing first, then you can add its food menus here.</p>
                <a href="{{ route('owner.mess.create') }}" class="btn-primary-findr d-inline-block mt-2" style="padding:.5rem 1.2rem;">Create a Mess</a>
            </div>
        @else
            @foreach($messes as $mess)
                <div style="margin-top:1.75rem;">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <h5 style="font-weight:800;margin:0;"><i class="bi bi-egg-fried" style="color:var(--brand-primary);"></i> {{ $mess->name }}</h5>
                        <span class="menu-count">{{ $mess->menus->count() }} {{ Str::plural('menu', $mess->menus->count()) }}</span>
                    </div>

                    @if($mess->menus->isEmpty())
                        <div class="card-findr p-4 text-center" style="color:var(--text-muted);">
                            <p style="font-size:.86rem;margin:0;">No menus for this mess yet — click “Add Menu” to create one.</p>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach($mess->menus as $menu)
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="menu-card {{ $menu->status === 'closed' ? 'is-closed' : '' }}">
                                        <div class="d-flex justify-content-between align-items-start gap-2">
                                            <div>
                                                <span class="slot-badge slot-{{ $menu->slot }}">{{ $slotLabels[$menu->slot] ?? ucfirst($menu->slot) }}</span>
                                                @unless($menu->is_available)<span class="unavail-badge">Hidden</span>@endunless
                                            </div>
                                            <strong style="font-size:1rem;color:var(--brand-primary);">₹{{ number_format($menu->price) }}</strong>
                                        </div>
                                        @if($menu->title)<h6 style="font-weight:700;margin:.6rem 0 .1rem;">{{ $menu->title }}</h6>@endif
                                        <ul class="menu-items">
                                            @forelse($menu->items as $item)
                                                <li>{{ $item['name'] ?? '' }}@if(!empty($item['qty'])) <span>· {{ $item['qty'] }}</span>@endif</li>
                                            @empty
                                                <li style="color:var(--text-muted);">No items listed</li>
                                            @endforelse
                                        </ul>
                                        @if($menu->notes)<p class="menu-notes">{{ $menu->notes }}</p>@endif

                                        <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                                            <button type="button" class="btn-outline-findr btn-sm-findr"
                                                data-menu="{{ json_encode(['id'=>$menu->id,'slot'=>$menu->slot,'title'=>$menu->title,'price'=>$menu->price,'notes'=>$menu->notes,'is_available'=>(bool)$menu->is_available,'items'=>$menu->items], JSON_HEX_APOS|JSON_HEX_QUOT) }}"
                                                onclick="openEditMenu(this)"><i class="bi bi-pencil"></i> Edit</button>

                                            <form method="POST" action="{{ route('owner.mess.menus.toggle', $menu->id) }}" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn-outline-findr btn-sm-findr">
                                                    <i class="bi bi-{{ $menu->status === 'open' ? 'pause' : 'play' }}"></i>
                                                    {{ $menu->status === 'open' ? 'Mark Closed' : 'Mark Open' }}
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('owner.mess.menus.delete', $menu->id) }}" style="margin:0;" onsubmit="return confirm('Delete this menu? This cannot be undone.');">
                                                @csrf
                                                <button type="submit" class="btn-danger-soft" title="Delete"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Add / Edit Menu Modal -->
<div class="modal fade" id="menuModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" id="menuForm" data-store="{{ route('owner.mess.menus.store') }}" data-update-base="{{ url('owner/mess/menus') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="menuModalTitle">Add Menu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="messSelectWrap" class="mb-3">
                        <label class="form-label-sm">Mess</label>
                        <select name="mess_id" id="menuMessId" class="form-select">
                            @foreach($messes as $mess)<option value="{{ $mess->id }}">{{ $mess->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label-sm">Meal slot</label>
                            <select name="slot" id="menuSlot" class="form-select" required>
                                <option value="morning">Morning</option>
                                <option value="afternoon">Afternoon</option>
                                <option value="evening">Evening</option>
                                <option value="night">Night</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label-sm">Price (₹)</label>
                            <input type="number" name="price" id="menuPrice" class="form-control" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-sm">Title <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                        <input type="text" name="title" id="menuTitle" class="form-control" placeholder="e.g. South Indian Breakfast">
                    </div>
                    <div class="mb-2">
                        <label class="form-label-sm">Items</label>
                        <div id="itemsContainer"></div>
                        <button type="button" class="btn-outline-findr btn-sm-findr mt-1" onclick="addItemRow()"><i class="bi bi-plus"></i> Add item</button>
                    </div>
                    <div class="mb-3">
                        <label class="form-label-sm">Notes <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                        <textarea name="notes" id="menuNotes" class="form-control" rows="2" placeholder="e.g. Served with chutney &amp; sambar"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="hidden" name="is_available" value="0">
                        <input class="form-check-input" type="checkbox" name="is_available" value="1" id="menuAvailable" checked>
                        <label class="form-check-label" for="menuAvailable" style="font-size:.85rem;">Show this menu to customers</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-findr" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-findr">Save Menu</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let itemIdx = 0;
function escapeHtml(s){ return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function resetItems(){ document.getElementById('itemsContainer').innerHTML = ''; itemIdx = 0; }
function addItemRow(name, qty){
    name = name || ''; qty = qty || '';
    var row = document.createElement('div');
    row.className = 'item-row d-flex gap-2 mb-2';
    row.innerHTML =
        '<input name="items[' + itemIdx + '][name]" class="form-control" placeholder="Item (e.g. Idli)" value="' + escapeHtml(name) + '" required>' +
        '<input name="items[' + itemIdx + '][qty]" class="form-control" placeholder="Qty" value="' + escapeHtml(qty) + '" style="max-width:130px;">' +
        '<button type="button" class="btn-outline-findr" style="padding:0 .7rem;" onclick="this.parentElement.remove()">&times;</button>';
    document.getElementById('itemsContainer').appendChild(row);
    itemIdx++;
}
function openAddMenu(){
    var form = document.getElementById('menuForm');
    form.action = form.dataset.store;
    document.getElementById('menuModalTitle').textContent = 'Add Menu';
    document.getElementById('messSelectWrap').style.display = '';
    document.getElementById('menuMessId').disabled = false;
    document.getElementById('menuSlot').value = 'morning';
    document.getElementById('menuTitle').value = '';
    document.getElementById('menuPrice').value = '';
    document.getElementById('menuNotes').value = '';
    document.getElementById('menuAvailable').checked = true;
    resetItems(); addItemRow();
    bootstrap.Modal.getOrCreateInstance(document.getElementById('menuModal')).show();
}
function openEditMenu(btn){
    var m = JSON.parse(btn.dataset.menu);
    var form = document.getElementById('menuForm');
    form.action = form.dataset.updateBase + '/' + m.id + '/update';
    document.getElementById('menuModalTitle').textContent = 'Edit Menu';
    // mess can't be changed when editing an existing menu
    document.getElementById('messSelectWrap').style.display = 'none';
    document.getElementById('menuMessId').disabled = true;
    document.getElementById('menuSlot').value = m.slot;
    document.getElementById('menuTitle').value = m.title || '';
    document.getElementById('menuPrice').value = m.price;
    document.getElementById('menuNotes').value = m.notes || '';
    document.getElementById('menuAvailable').checked = !!m.is_available;
    resetItems();
    var items = (m.items && m.items.length) ? m.items : [{name:'',qty:''}];
    items.forEach(function(it){ addItemRow(it.name || '', it.qty || ''); });
    bootstrap.Modal.getOrCreateInstance(document.getElementById('menuModal')).show();
}
</script>
@endpush
