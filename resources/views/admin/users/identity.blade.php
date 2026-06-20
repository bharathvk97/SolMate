@extends('layouts.admin')
@section('title', 'Identity Verification')

@section('content')
<div class="page-header"><h1>Identity Verification</h1><p>Review and verify owner identity documents.</p></div>

@if($users->isEmpty())
<div style="text-align:center;padding:4rem;color:var(--text-muted);">
  <div style="font-size:3rem;margin-bottom:1rem;">✅</div>
  <h5>All identity verifications are up to date!</h5>
</div>
@else
<div class="row g-3">
  @foreach($users as $u)
  <div class="col-lg-6">
    <div class="card-findr">
      <div style="padding:1.25rem;">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:1rem;">
          <img src="{{ $u->avatar_url }}" style="width:48px;height:48px;border-radius:50%;object-fit:cover;" alt="">
          <div>
            <p style="font-weight:700;font-size:.95rem;margin:0;">{{ $u->name }}</p>
            <p style="font-size:.78rem;color:var(--text-muted);margin:0;">{{ $u->email }} · {{ ucfirst(str_replace('_',' ',$u->role)) }}</p>
          </div>
          <span class="badge-status badge-pending ms-auto">Pending</span>
        </div>

        <div style="background:var(--bg-subtle);border-radius:10px;padding:.8rem 1rem;margin-bottom:1rem;">
          <div class="d-flex justify-content-between mb-1" style="font-size:.82rem;">
            <span style="color:var(--text-muted);">ID Type</span>
            <strong>{{ ucfirst($u->identity_type ?? '—') }}</strong>
          </div>
          <div class="d-flex justify-content-between" style="font-size:.82rem;">
            <span style="color:var(--text-muted);">ID Number</span>
            <strong>{{ $u->identity_number ?? '—' }}</strong>
          </div>
        </div>

        <!-- Document Images -->
        <div class="row g-2 mb-3">
          @if($u->identity_document_front)
          <div class="col-6">
            <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;">Front Side</p>
            <a href="{{ Storage::url($u->identity_document_front) }}" target="_blank">
              <img src="{{ Storage::url($u->identity_document_front) }}" alt="Front ID"
                   style="width:100%;height:110px;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">
            </a>
          </div>
          @endif
          @if($u->identity_document_back)
          <div class="col-6">
            <p style="font-size:.75rem;color:var(--text-muted);margin-bottom:4px;">Back Side</p>
            <a href="{{ Storage::url($u->identity_document_back) }}" target="_blank">
              <img src="{{ Storage::url($u->identity_document_back) }}" alt="Back ID"
                   style="width:100%;height:110px;object-fit:cover;border-radius:10px;border:1px solid var(--border-color);">
            </a>
          </div>
          @endif
        </div>

        <div class="d-flex gap-2">
          <form method="POST" action="{{ route('admin.identity.verify', $u->id) }}" style="flex:1;margin:0;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="verified">
            <button type="submit" class="btn-primary-findr w-100" style="padding:.5rem;">
              <i class="bi bi-check2-circle me-1"></i>Verify
            </button>
          </form>
          <form method="POST" action="{{ route('admin.identity.verify', $u->id) }}" style="flex:1;margin:0;">
            @csrf @method('PUT')
            <input type="hidden" name="status" value="rejected">
            <button type="submit" style="width:100%;background:rgba(239,68,68,.08);color:var(--danger);border:1.5px solid rgba(239,68,68,.3);border-radius:10px;padding:.5rem;cursor:pointer;font-weight:600;">
              <i class="bi bi-x-circle me-1"></i>Reject
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div style="margin-top:1.5rem;">{{ $users->links() }}</div>
@endif
@endsection
