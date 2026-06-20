<?php $__env->startSection('title', 'Manage Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Users</h1><p>Manage all registered users, owners, and admins.</p></div>
</div>

<!-- Filters -->
<div class="card-findr p-3 mb-4">
  <form method="GET" class="row g-2 align-items-end">
    <div class="col-md-4">
      <input type="text" name="q" class="form-control" placeholder="Search name or email…" value="<?php echo e(request('q')); ?>">
    </div>
    <div class="col-md-3">
      <select name="role" class="form-select">
        <option value="">All Roles</option>
        <option value="user" <?php echo e(request('role')=='user'?'selected':''); ?>>Users</option>
        <option value="hostel_owner" <?php echo e(request('role')=='hostel_owner'?'selected':''); ?>>Hostel Owners</option>
        <option value="mess_owner" <?php echo e(request('role')=='mess_owner'?'selected':''); ?>>Mess Owners</option>
        <option value="admin" <?php echo e(request('role')=='admin'?'selected':''); ?>>Admins</option>
      </select>
    </div>
    <div class="col-md-3">
      <select name="status" class="form-select">
        <option value="">All Statuses</option>
        <option value="active" <?php echo e(request('status')=='active'?'selected':''); ?>>Active</option>
        <option value="inactive" <?php echo e(request('status')=='inactive'?'selected':''); ?>>Inactive</option>
        <option value="suspended" <?php echo e(request('status')=='suspended'?'selected':''); ?>>Suspended</option>
        <option value="pending_verification" <?php echo e(request('status')=='pending_verification'?'selected':''); ?>>Pending</option>
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
        <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <img src="<?php echo e($u->avatar_url); ?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;" alt="">
              <div>
                <p style="font-weight:600;font-size:.88rem;margin:0;"><?php echo e($u->name); ?></p>
                <p style="font-size:.75rem;color:var(--text-muted);margin:0;"><?php echo e($u->email); ?></p>
              </div>
            </div>
          </td>
          <td>
            <span style="background:var(--bg-subtle);color:var(--text-secondary);border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;">
              <?php echo e(ucfirst(str_replace('_',' ',$u->role))); ?>

            </span>
          </td>
          <td><span class="badge-status badge-<?php echo e($u->status); ?>"><?php echo e(ucfirst($u->status)); ?></span></td>
          <td>
            <?php if($u->identity_status): ?>
            <span class="badge-status badge-<?php echo e($u->identity_status==='verified'?'active':($u->identity_status==='rejected'?'inactive':'pending')); ?>">
              <?php echo e(ucfirst($u->identity_status)); ?>

            </span>
            <?php else: ?>
            <span style="color:var(--text-muted);font-size:.8rem;">—</span>
            <?php endif; ?>
          </td>
          <td style="font-size:.82rem;color:var(--text-muted);"><?php echo e($u->created_at->format('d M Y')); ?></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <?php if($u->status !== 'suspended'): ?>
              <form method="POST" action="<?php echo e(route('admin.users.status', $u->id)); ?>" style="margin:0;">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="suspended">
                <button type="submit" style="background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Suspend</button>
              </form>
              <?php else: ?>
              <form method="POST" action="<?php echo e(route('admin.users.status', $u->id)); ?>" style="margin:0;">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(16,185,129,.08);color:var(--brand-accent);border:1px solid rgba(16,185,129,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Activate</button>
              </form>
              <?php endif; ?>
              <?php if(in_array($u->role,['hostel_owner','mess_owner']) && $u->identity_status==='pending'): ?>
              <a href="<?php echo e(route('admin.identity')); ?>?user=<?php echo e($u->id); ?>" style="background:rgba(92,95,239,.08);color:var(--brand-primary);border:1px solid rgba(92,95,239,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;text-decoration:none;">Verify ID</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="6" style="text-align:center;padding:2rem;color:var(--text-muted);">No users found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;"><?php echo e($users->withQueryString()->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/admin/users/index.blade.php ENDPATH**/ ?>