<?php $__env->startSection('title', 'Subscriptions'); ?>
<?php $__env->startSection('admin-content'); ?>
<?php $__env->startSection('content'); ?>
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Subscriptions</h1><p>All owner subscription payments.</p></div>
  <div>
    <form method="POST" action="<?php echo e(route('admin.expire-accounts')); ?>" style="margin:0;">
      <?php echo csrf_field(); ?>
      <button type="submit" class="btn-outline-findr" style="font-size:.82rem;" onclick="return confirm('Deactivate all expired accounts?')">
        <i class="bi bi-hourglass-split me-1"></i>Run Expiry Check
      </button>
    </form>
  </div>
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>Owner</th><th>Role</th><th>Plan</th><th>Amount</th><th>Paid On</th><th>Expires</th><th>Status</th></tr></thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $subs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td>
            <p style="font-weight:600;font-size:.88rem;margin:0;"><?php echo e($s->user->name); ?></p>
            <p style="font-size:.75rem;color:var(--text-muted);margin:0;"><?php echo e($s->user->email); ?></p>
          </td>
          <td>
            <span style="background:var(--bg-subtle);border-radius:6px;padding:2px 8px;font-size:.75rem;font-weight:600;color:var(--text-secondary);">
              <?php echo e($s->user->role==='hostel_owner'?'Hostel':'Mess'); ?> Owner
            </span>
          </td>
          <td style="font-size:.85rem;font-weight:600;"><?php echo e($s->plan->name); ?></td>
          <td style="font-size:.88rem;font-weight:700;color:var(--brand-primary);">₹<?php echo e(number_format($s->amount_paid)); ?></td>
          <td style="font-size:.82rem;color:var(--text-muted);"><?php echo e($s->created_at->format('d M Y')); ?></td>
          <td style="font-size:.82rem;">
            <span style="color:<?php echo e($s->expires_at->isPast()?'var(--danger)':'var(--brand-accent)'); ?>;">
              <?php echo e($s->expires_at->format('d M Y')); ?>

              <?php echo e($s->expires_at->isPast() ? '(Expired)' : ''); ?>

            </span>
          </td>
          <td><span class="badge-status badge-<?php echo e($s->payment_status==='paid'?'active':'pending'); ?>"><?php echo e(ucfirst($s->payment_status)); ?></span></td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No subscriptions yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;"><?php echo e($subs->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/admin/subscriptions.blade.php ENDPATH**/ ?>