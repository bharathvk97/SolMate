@extends('layouts.admin')
@section('title', 'Reviews')

@section('content')
<div class="page-header"><h1>Reviews</h1><p>Moderate user reviews across all listings.</p></div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>User</th><th>Listing</th><th>Rating</th><th>Review</th><th>Date</th><th>Visible</th><th>Action</th></tr></thead>
      <tbody>
        @forelse($reviews as $r)
        <tr style="{{ $r->is_hidden ? 'opacity:.55;' : '' }}">
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <img src="{{ $r->user->avatar_url }}" style="width:32px;height:32px;border-radius:50%;" alt="">
              <p style="font-weight:600;font-size:.83rem;margin:0;">{{ $r->user->name }}</p>
            </div>
          </td>
          <td style="font-size:.82rem;">
            <p style="font-weight:600;margin:0;">{{ $r->reviewable?->name ?? 'Deleted' }}</p>
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;">{{ class_basename($r->reviewable_type) }}</p>
          </td>
          <td>
            <span style="color:#F59E0B;">{{ str_repeat('★',$r->rating) }}</span>
            <span style="color:var(--border-color);">{{ str_repeat('★',5-$r->rating) }}</span>
          </td>
          <td style="font-size:.82rem;max-width:250px;">
            <p style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $r->body }}</p>
          </td>
          <td style="font-size:.78rem;color:var(--text-muted);">{{ $r->created_at->format('d M Y') }}</td>
          <td>
            <span class="badge-status badge-{{ $r->is_hidden?'inactive':'active' }}">{{ $r->is_hidden?'Hidden':'Visible' }}</span>
          </td>
          <td>
            <form method="POST" action="{{ route('admin.reviews.toggle', $r->id) }}" style="margin:0;">
              @csrf @method('PUT')
              <button type="submit" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">
                {{ $r->is_hidden ? 'Show' : 'Hide' }}
              </button>
            </form>
          </td>
        </tr>
        @empty
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No reviews yet.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $reviews->links() }}</div>
</div>
@endsection
