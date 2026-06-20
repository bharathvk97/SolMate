<?php $__env->startSection('title', 'Manage Hostels'); ?>

<?php $__env->startSection('content'); ?>
<div class="page-header d-flex align-items-center justify-content-between">
  <div><h1>Hostels</h1><p>Review, approve or reject hostel listings.</p></div>
  <div style="background:rgba(249,115,22,.1);color:var(--brand-secondary);border-radius:10px;padding:.5rem 1rem;font-size:.85rem;font-weight:700;">
    <?php echo e($hostels->where('status','pending')->count()); ?> pending review
  </div>
</div>

<!-- Status Filter Tabs -->
<div style="display:flex;gap:0;border-bottom:2px solid var(--border-color);margin-bottom:1.5rem;">
  <?php $__currentLoopData = [''=>'All','pending'=>'Pending','active'=>'Active','inactive'=>'Inactive','rejected'=>'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <a href="<?php echo e(request()->fullUrlWithQuery(['status'=>$v])); ?>"
     style="padding:.6rem 1.2rem;font-size:.85rem;font-weight:600;border-bottom:2px solid <?php echo e(request('status')===$v?'var(--brand-primary)':'transparent'); ?>;margin-bottom:-2px;color:<?php echo e(request('status')===$v?'var(--brand-primary)':'var(--text-muted)'); ?>;text-decoration:none;">
    <?php echo e($l); ?>

  </a>
  <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</div>

<div class="card-findr">
  <div class="table-responsive">
    <table class="table-findr">
      <thead><tr>
        <th>Hostel</th><th>Owner</th><th>City</th><th>Rooms</th><th>Rating</th><th>Status</th><th>Actions</th>
      </tr></thead>
      <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $hostels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <tr>
          <td>
            <div style="display:flex;align-items:center;gap:10px;">
              <img src="<?php echo e($h->cover_image_url ?? '/images/hostel-placeholder.jpg'); ?>" style="width:44px;height:44px;border-radius:8px;object-fit:cover;flex-shrink:0;" alt="">
              <div>
                <p style="font-weight:700;font-size:.88rem;margin:0;"><?php echo e($h->name); ?></p>
                <p style="font-size:.72rem;color:var(--text-muted);margin:0;"><?php echo e(ucfirst($h->gender_type)); ?></p>
              </div>
            </div>
          </td>
          <td style="font-size:.85rem;"><?php echo e($h->owner->name ?? '—'); ?></td>
          <td style="font-size:.82rem;color:var(--text-muted);"><?php echo e($h->city); ?></td>
          <td style="font-size:.85rem;"><?php echo e($h->rooms_count ?? 0); ?></td>
          <td>
            <span style="color:#F59E0B;font-size:.85rem;">★</span>
            <span style="font-size:.85rem;font-weight:600;"><?php echo e(number_format($h->average_rating,1)); ?></span>
          </td>
          <td><span class="badge-status badge-<?php echo e($h->status); ?>"><?php echo e(ucfirst($h->status)); ?></span></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;">
              <a href="/hostels/<?php echo e($h->slug); ?>" target="_blank" style="background:var(--bg-subtle);color:var(--text-secondary);border:1px solid var(--border-color);border-radius:7px;padding:3px 9px;font-size:.75rem;text-decoration:none;"><i class="bi bi-eye"></i></a>
              <?php if($h->status === 'pending' || $h->status === 'inactive'): ?>
              <form method="POST" action="<?php echo e(route('admin.hostels.status', $h->id)); ?>" style="margin:0;">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(16,185,129,.08);color:var(--brand-accent);border:1px solid rgba(16,185,129,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Approve</button>
              </form>
              <?php endif; ?>
              <?php if($h->status !== 'rejected'): ?>
              <button onclick="rejectHostel(<?php echo e($h->id); ?>)" style="background:rgba(239,68,68,.08);color:var(--danger);border:1px solid rgba(239,68,68,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">Reject</button>
              <?php endif; ?>
              <?php if($h->status === 'active'): ?>
              <form method="POST" action="<?php echo e(route('admin.hostels.status', $h->id)); ?>" style="margin:0;">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <input type="hidden" name="is_featured" value="1">
                <input type="hidden" name="status" value="active">
                <button type="submit" style="background:rgba(245,158,11,.08);color:var(--warning);border:1px solid rgba(245,158,11,.3);border-radius:7px;padding:3px 9px;font-size:.75rem;cursor:pointer;">
                  <?php echo e($h->is_featured ? '★ Unfeature' : '☆ Feature'); ?>

                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <tr><td colspan="7" style="text-align:center;padding:2rem;color:var(--text-muted);">No hostels found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  <div style="padding:1rem;"><?php echo e($hostels->withQueryString()->links()); ?></div>
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
          <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
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
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function rejectHostel(id) {
  document.getElementById('rejectForm').action = `/admin/hostels/${id}/status`;
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/admin/hostels/index.blade.php ENDPATH**/ ?>