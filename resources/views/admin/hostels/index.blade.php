@extends('layouts.admin')
@section('title', 'Manage Hostels')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Hostels</h1><p>Review, approve or reject hostel listings.</p></div>
  <div style="background:rgba(249,115,22,.1);color:var(--brand-secondary);border-radius:10px;padding:.5rem 1rem;font-size:.85rem;font-weight:700;">
    {{ $hostels->where('status','pending')->count() }} pending review
  </div>
</div>

<!-- Status Filter Tabs -->
<div style="display:flex;gap:0;border-bottom:2px solid var(--border-color);margin-bottom:1.5rem;">
  @foreach([''=>'All','pending'=>'Pending','active'=>'Active','inactive'=>'Inactive','rejected'=>'Rejected'] as $v=>$l)
  <a href="{{ request()->fullUrlWithQuery(['status'=>$v]) }}"
     style="padding:.6rem 1.2rem;font-size:.85rem;font-weight:600;border-bottom:2px solid {{ request('status')===$v?'var(--brand-primary)':'transparent' }};margin-bottom:-2px;color:{{ request('status')===$v?'var(--brand-primary)':'var(--text-muted)' }};text-decoration:none;">
    {{ $l }}
  </a>
  @endforeach
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr>
        <th>Hostel</th><th>Owner</th><th>City</th><th>Rooms</th><th>Rating</th><th>Status</th><th>Actions</th>
      </tr></thead>
      <tbody>
        @forelse($hostels as $h)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <img src="{{ $h->cover_image_url ?? '/images/hostel-placeholder.jpg' }}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="">
              <div>
                <p style="font-weight:700;font-size:.88rem;margin:0;">{{ $h->name }}</p>
                <p style="font-size:.72rem;color:var(--text-muted);margin:0;">{{ ucfirst($h->gender_type) }}</p>
              </div>
            </div>
          </td>
          <td style="font-size:.85rem;">{{ $h->owner->name ?? '—' }}</td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $h->city }}</td>
          <td style="font-size:.85rem;">{{ $h->rooms_count ?? 0 }}</td>
          <td>
            <span style="color:#F59E0B;font-size:.85rem;">★</span>
            <span style="font-size:.85rem;font-weight:600;">{{ number_format($h->average_rating,1) }}</span>
          </td>
          <td><span class="badge-status badge-{{ $h->status }}">{{ ucfirst($h->status) }}</span></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <a href="/hostels/{{ $h->slug }}" target="_blank" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:7px;padding:3px 9px;font-size:.75rem;text-decoration:none;"><i class="bi bi-eye"></i></a>
              @if($h->status === 'pending' || $h->status === 'inactive')
              <form method="POST" action="{{ route('admin.hostels.status', $h->id) }}" style="margin:0;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(16,185,129,.08);color:var(--brand-accent);border:1px solid rgba(16,185,129,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Approve</button>
              </form>
              @endif
              @if($h->status !== 'rejected')
              <button onclick="rejectHostel({{ $h->id }})" style="background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Reject</button>
              @endif
              @if($h->status === 'active')
              <form method="POST" action="{{ route('admin.hostels.status', $h->id) }}" style="margin:0;">
                @csrf @method('PUT')
                <input type="hidden" name="is_featured" value="1">
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(245,158,11,.08);color:var(--warning);border:1px solid rgba(245,158,11,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">
                  {{ $h->is_featured ? '★ Unfeature' : '☆ Feature' }}
                </button>
              </form>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No hostels found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $hostels->withQueryString()->links() }}</div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--bg-surface);border-color:var(--border-color);border-radius:16px;">
      <div class="modal-header" style="border-color:var(--border-color);">
        <h5 class="modal-title" style="font-weight:700;">Reject Hostel</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="rejectForm" method="POST" style="margin:0;">
          @csrf @method('PUT')
          <input type="hidden" name="status" value="rejected">
          <div class="mb-3">
            <label class="form-label">Reason for rejection</label>
            <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Explain why this listing is being rejected…" required></textarea>
          </div>
          <button type="submit" class="btn-primary-findr w-100">Confirm Rejection</button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function rejectHostel(id) {
  document.getElementById('rejectForm').action = `/admin/hostels/${id}/status`;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
@endpush
