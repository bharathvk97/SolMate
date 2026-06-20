<?php $__env->startSection('title', 'Reviews'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header"><h1>Reviews</h1><p>Moderate user reviews across all listings.</p></div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr><th>User</th><th>Listing</th><th>Rating</th><th>Review</th><th>Date</th><th>Visible</th><th>Action</th></tr></thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $reviews; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr style="<?php echo e($r->is_hidden ? 'opacity:.55;' : ''); ?>">
          <td>
            <div style="display:flex;align-items:center;gap:8px;">
              <img src="<?php echo e($r->user->avatar_url); ?>" style="width:32px;height:32px;border-radius:50%;" alt="">
              <p style="font-weight:600;font-size:.83rem;margin:0;"><?php echo e($r->user->name); ?></p>
            </div>
          </td>
          <td style="font-size:.82rem;">
            <p style="font-weight:600;margin:0;"><?php echo e($r->reviewable?->name ?? 'Deleted'); ?></p>
            <p style="font-size:.72rem;color:var(--text-muted);margin:0;"><?php echo e(class_basename($r->reviewable_type)); ?></p>
          </td>
          <td>
            <span style="color:#F59E0B;"><?php echo e(str_repeat('★',$r->rating)); ?></span>
            <span style="color:var(--border-color);"><?php echo e(str_repeat('★',5-$r->rating)); ?></span>
          </td>
          <td style="font-size:.82rem;max-width:250px;">
            <p style="margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($r->body); ?></p>
          </td>
          <td style="font-size:.78rem;color:var(--text-muted);"><?php echo e($r->created_at->format('d M Y')); ?></td>
          <td>
            <span class="badge-status badge-<?php echo e($r->is_hidden?'inactive':'active'); ?>"><?php echo e($r->is_hidden?'Hidden':'Visible'); ?></span>
          </td>
          <td>
            <form method="POST" action="<?php echo e(route('admin.reviews.toggle', $r->id)); ?>" style="margin:0;">
              <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
              <button type="submit" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">
                <?php echo e($r->is_hidden ? 'Show' : 'Hide'); ?>

              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No reviews yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;"><?php echo e($reviews->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/admin/reviews.blade.php ENDPATH**/ ?>