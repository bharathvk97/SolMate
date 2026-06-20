{{-- resources/views/admin/messes/index.blade.php --}}
@extends('layouts.admin')
@section('title', 'Manage Messes')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Messes</h1><p>Approve and manage mess listings.</p></div>
</div>

<!-- Status Tabs -->
<div style="display:flex;gap:0;border-bottom:2px solid var(--border-color);margin-bottom:1.5rem;">
  @foreach([''=>'All','pending'=>'Pending','active'=>'Active','inactive'=>'Inactive'] as $v=>$l)
  <a href="{{ request()->fullUrlWithQuery(['status'=>$v]) }}"
     style="padding:.6rem 1.2rem;font-size:.85rem;font-weight:600;border-bottom:2px solid {{ request('status')===$v?'var(--brand-primary)':'transparent' }};margin-bottom:-2px;color:{{ request('status')===$v?'var(--brand-primary)':'var(--text-muted)' }};text-decoration:none;">{{ $l }}</a>
  @endforeach
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>Mess</th><th>Owner</th><th>Food Type</th><th>City</th><th>Rating</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        @forelse($messes as $m)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <img src="{{ $m->cover_image_url ?? '/images/mess-placeholder.jpg' }}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="">
              <div>
                <p style="font-weight:700;font-size:.88rem;margin:0;">{{ $m->name }}</p>
                <p style="font-size:.72rem;color:var(--text-muted);margin:0;">{{ $m->has_delivery?'🛵 Delivery':'' }}</p>
              </div>
            </div>
          </td>
          <td style="font-size:.85rem;">{{ $m->owner->name ?? '—' }}</td>
          <td>
            <span style="background:{{ $m->food_type==='veg'?'rgba(16,185,129,.1)':'rgba(239,68,68,.1)' }};color:{{ $m->food_type==='veg'?'var(--brand-accent)':'var(--danger)' }};border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;">
              {{ $m->food_type==='veg'?'🥦 Veg':($m->food_type==='non_veg'?'🍗 Non-Veg':'Both') }}
            </span>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $m->city }}</td>
          <td>
            <span style="color:#F59E0B;">★</span>
            <span style="font-size:.85rem;font-weight:600;">{{ number_format($m->average_rating,1) }}</span>
          </td>
          <td><span class="badge-status badge-{{ $m->status }}">{{ ucfirst($m->status) }}</span></td>
          <td>
            <div style="display:flex;gap:4px;">
              <a href="/messes/{{ $m->slug }}" target="_blank" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:7px;padding:3px 9px;font-size:.75rem;text-decoration:none;"><i class="bi bi-eye"></i></a>
              @if($m->status !== 'active')
              <form method="POST" action="{{ route('admin.messes.status',$m->id) }}" style="margin:0;">
                @csrf @method('PUT') <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(16,185,129,.08);color:var(--brand-accent);border:1px solid rgba(16,185,129,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Approve</button>
              </form>
              @endif
              @if($m->status === 'active')
              <form method="POST" action="{{ route('admin.messes.status',$m->id) }}" style="margin:0;">
                @csrf @method('PUT') <input type="hidden" name="status" value="inactive">
                <button type="submit" style="background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Deactivate</button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No messes found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $messes->withQueryString()->links() }}</div>
</div>
@endsection
