<?php $__env->startSection('title', 'My Profile'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.profile-avatar-wrap { position:relative; display:inline-block; }
.avatar-change { position:absolute; bottom:0; right:0; width:28px; height:28px; border-radius:50%; background:var(--brand-primary); color:#fff; border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:.8rem; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="container py-5" style="max-width:720px;">
  <div class="page-header"><h1>My Profile</h1><p>Manage your personal information and settings.</p></div>

  <!-- Alert -->
  <?php if(session('success')): ?>
  <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.3);border-radius:10px;padding:.8rem 1rem;margin-bottom:1.5rem;color:var(--brand-accent);font-size:.88rem;">
    <i class="bi bi-check-circle-fill me-2"></i><?php echo e(session('success')); ?>

  </div>
  <?php endif; ?>

  <div class="row g-4">
    <!-- Avatar + Info -->
    <div class="col-md-4 text-center">
      <div class="card-findr p-3">
        <div class="profile-avatar-wrap mb-2">
          <img src="<?php echo e($user->avatar_url); ?>" alt="<?php echo e($user->name); ?>" style="width:100px;height:100px;border-radius:50%;object-fit:cover;border:3px solid var(--brand-primary);">
          <button class="avatar-change" onclick="document.getElementById('avatarInput').click()"><i class="bi bi-camera-fill"></i></button>
          <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
        </div>
        <h5 style="font-weight:700;margin:0;"><?php echo e($user->name); ?></h5>
        <p style="font-size:.8rem;color:var(--text-muted);margin:3px 0;"><?php echo e(ucfirst(str_replace('_',' ',$user->role))); ?></p>
        <span class="badge-status badge-<?php echo e($user->status); ?>" style="margin-top:.5rem;display:inline-block;"><?php echo e(ucfirst($user->status)); ?></span>

        <?php if(in_array($user->role,['hostel_owner','mess_owner'])): ?>
        <hr style="border-color:var(--border-color);margin:1rem 0;">
        <p style="font-size:.78rem;color:var(--text-muted);margin-bottom:.25rem;">Subscription</p>
        <?php if($user->hasActiveSubscription()): ?>
        <span style="background:rgba(16,185,129,.1);color:var(--brand-accent);border-radius:20px;padding:4px 12px;font-size:.78rem;font-weight:700;">
          ✓ Active until <?php echo e($user->subscription_expires_at->format('d M Y')); ?>

        </span>
        <?php else: ?>
        <span style="background:rgba(239,68,68,.1);color:var(--danger);border-radius:20px;padding:4px 12px;font-size:.78rem;font-weight:700;">✗ Expired</span>
        <a href="<?php echo e(route('owner.subscription')); ?>" class="btn-primary-findr d-block mt-2" style="font-size:.78rem;padding:.4rem;">Renew</a>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Edit Form -->
    <div class="col-md-8">
      <div class="card-findr p-4">
        <h6 style="font-weight:700;margin-bottom:1.25rem;">Personal Information</h6>
        <form method="POST" action="/api/v1/profile" id="profileForm" onsubmit="saveProfile(event)">
          <?php echo csrf_field(); ?>
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo e($user->name); ?>" required>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" class="form-control" value="<?php echo e($user->email); ?>" disabled style="opacity:.6;">
              <p style="font-size:.72rem;color:var(--text-muted);margin:3px 0 0;">Email cannot be changed.</p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="tel" name="phone" class="form-control" value="<?php echo e(ltrim($user->phone,'91')); ?>" maxlength="10">
            </div>
          </div>
          <div class="row g-3 mt-0">
            <div class="col-md-6">
              <label class="form-label">City</label>
              <input type="text" name="city" class="form-control" value="<?php echo e($user->city); ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">State</label>
              <input type="text" name="state" class="form-control" value="<?php echo e($user->state); ?>">
            </div>
          </div>
          <button type="submit" class="btn-primary-findr mt-4" id="saveBtn">
            <i class="bi bi-save me-2"></i>Save Changes
          </button>
        </form>
      </div>

      <!-- Change Password -->
      <div class="card-findr p-4 mt-3">
        <h6 style="font-weight:700;margin-bottom:1.25rem;">Change Password</h6>
        <form onsubmit="changePassword(event)">
          <div class="mb-3">
            <label class="form-label">Current Password</label>
            <input type="password" id="curPwd" class="form-control" placeholder="••••••••">
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">New Password</label>
              <input type="password" id="newPwd" class="form-control" placeholder="Min 8 chars">
            </div>
            <div class="col-md-6">
              <label class="form-label">Confirm New</label>
              <input type="password" id="confPwd" class="form-control" placeholder="Repeat">
            </div>
          </div>
          <button type="submit" class="btn-outline-findr mt-3">Update Password</button>
        </form>
      </div>

      <!-- Theme Toggle -->
      <div class="card-findr p-4 mt-3">
        <div class="d-flex align-items-center justify-content-between">
          <div>
            <h6 style="font-weight:700;margin:0;">Appearance</h6>
            <p style="font-size:.82rem;color:var(--text-muted);margin:2px 0 0;">Choose your preferred colour theme.</p>
          </div>
          <button onclick="ThemeManager.toggle()" style="background:var(--bg-subtle);border:1.5px solid var(--border-color);border-radius:10px;padding:.5rem 1rem;font-size:.85rem;cursor:pointer;color:var(--text-secondary);">
            <span id="themeLabel">🌙 Dark Mode</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function saveProfile(e) {
  e.preventDefault();
  const btn = document.getElementById('saveBtn');
  btn.disabled = true; btn.textContent = 'Saving…';
  const data = Object.fromEntries(new FormData(e.target));
  axios.post('/api/v1/profile', data)
    .then(r => { showToast('Profile updated!','success'); btn.disabled=false; btn.innerHTML='<i class="bi bi-save me-2"></i>Save Changes'; })
    .catch(e => { showToast(e.response?.data?.message||'Error','danger'); btn.disabled=false; btn.innerHTML='<i class="bi bi-save me-2"></i>Save Changes'; });
}

function changePassword(e) {
  e.preventDefault();
  const cur = document.getElementById('curPwd').value;
  const np  = document.getElementById('newPwd').value;
  const cp  = document.getElementById('confPwd').value;
  if (np !== cp) { showToast('Passwords do not match','warning'); return; }
  axios.post('/api/v1/change-password', { current_password:cur, password:np, password_confirmation:cp })
    .then(() => showToast('Password changed!','success'))
    .catch(e => showToast(e.response?.data?.message||'Error','danger'));
}

function uploadAvatar(input) {
  const fd = new FormData(); fd.append('avatar', input.files[0]);
  axios.post('/api/v1/profile', fd, { headers:{'Content-Type':'multipart/form-data'} })
    .then(r => {
      document.querySelector('.profile-avatar-wrap img').src = r.data.data.avatar_url+'?t='+Date.now();
      showToast('Avatar updated!','success');
    });
}

// Update theme button label
document.addEventListener('DOMContentLoaded', () => {
  const isDark = document.documentElement.dataset.theme === 'dark';
  document.getElementById('themeLabel').textContent = isDark ? '☀️ Light Mode' : '🌙 Dark Mode';
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/user/profile.blade.php ENDPATH**/ ?>