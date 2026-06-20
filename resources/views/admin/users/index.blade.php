@extends('layouts.admin')
@section('title', 'Manage Users')

@section('content')
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Users</h1><p>Manage all registered users, owners, and admins.</p></div>
</div>

<!-- Filters -->
<div class="card-findr p-3 mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-4">
      <input type="text" name="q" class="form-control" placeholder="Search name or email…" value="{{ request('q') }}">
    </div>
    <div class="col-md-3">
      <select name="role" class="form-select">
        <option value="">All Roles</option>
        <option value="user" {{ request('role')=='user'?'selected':'' }}>Users</option>
        <option value="hostel_owner" {{ request('role')=='hostel_owner'?'selected':'' }}>Hostel Owners</option>
        <option value="mess_owner" {{ request('role')=='mess_owner'?'selected':'' }}>Mess Owners</option>
        <option value="admin" {{ request('role')=='admin'?'selected':'' }}>Admins</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <option value="active" {{ request('status')=='active'?'selected':'' }}>Active</option>
        <option value="inactive" {{ request('status')=='inactive'?'selected':'' }}>Inactive</option>
        <option value="suspended" {{ request('status')=='suspended'?'selected':'' }}>Suspended</option>
        <option value="pending_verification" {{ request('status')=='pending_verification'?'selected':'' }}>Pending</option>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn-primary-findr w-100">Filter</button>
    </div>
  </form>
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr>
        <th>User</th><th>Role</th><th>Status</th><th>Identity</th><th>Joined</th><th>Actions</th>
      </tr></thead>
      <tbody>
        @forelse($users as $u)
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <img src="{{ $u->avatar_url }}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
              <div>
                <p style="font-weight:600;font-size:.88rem;margin:0;">{{ $u->name }}</p>
                <p style="font-size:.75rem;color:var(--text-muted);margin:0;">{{ $u->email }}</p>
              </div>
            </div>
          </td>
          <td>
            <span style="background:var(--bg-subtle);color:var(--text-secondary);border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;">
              {{ ucfirst(str_replace('_',' ',$u->role)) }}
            </span>
          </td>
          <td><span class="badge-status badge-{{ $u->status }}">{{ ucfirst($u->status) }}</span></td>
          <td>
            @if($u->identity_status)
            <span class="badge-status badge-{{ $u->identity_status==='verified'?'active':($u->identity_status==='rejected'?'inactive':'pending') }}">
              {{ ucfirst($u->identity_status) }}
            </span>
            @else
            <span style="color:var(--text-muted);font-size:.8rem;">—</span>
            @endif
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);">{{ $u->created_at->format('d M Y') }}</td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              @if($u->status !== 'suspended')
              <form method="POST" action="{{ route('admin.users.status', $u->id) }}" style="margin:0;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="suspended">
                <button type="submit" style="background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Suspend</button>
              </form>
              @else
              <form method="POST" action="{{ route('admin.users.status', $u->id) }}" style="margin:0;">
                @csrf @method('PUT')
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(16,185,129,.08);color:var(--brand-accent);border:1px solid rgba(16,185,129,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Activate</button>
              </form>
              @endif
              @if(in_array($u->role,['hostel_owner','mess_owner']) && $u->identity_status==='pending')
              <a href="{{ route('admin.identity') }}?user={{ $u->id }}" style="background:rgba(92,95,239,.08);color:var(--brand-primary);border:1px solid rgba(92,95,239,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;text-decoration:none;">Verify ID</a>
              @endif
            </div>
          </td>
        </tr>
        @empty
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">No users found.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection
