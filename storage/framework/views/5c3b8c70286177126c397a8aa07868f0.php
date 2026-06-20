<?php $__env->startSection('content'); ?>
<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="pt-3 pb-4">

            <div class="sidebar-section-label">Overview</div>
            <a href="<?php echo e(route('admin.dashboard')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="sidebar-section-label">Users</div>
            <a href="<?php echo e(route('admin.users')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.users*') ? 'active' : ''); ?>">
                <i class="bi bi-people"></i> All Users
            </a>
            <a href="<?php echo e(route('admin.identity')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.identity*') ? 'active' : ''); ?>">
                <i class="bi bi-shield-check"></i> Identity Verification
                <?php if($pendingIdentity ?? 0): ?>
                <span class="sidebar-badge"><?php echo e($pendingIdentity); ?></span>
                <?php endif; ?>
            </a>

            <div class="sidebar-section-label">Listings</div>
            <a href="<?php echo e(route('admin.hostels')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.hostels*') ? 'active' : ''); ?>">
                <i class="bi bi-building"></i> Hostels
                <?php if($pendingHostels ?? 0): ?>
                <span class="sidebar-badge"><?php echo e($pendingHostels); ?></span>
                <?php endif; ?>
            </a>
            <a href="<?php echo e(route('admin.messes')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.messes*') ? 'active' : ''); ?>">
                <i class="bi bi-egg-fried"></i> Messes
                <?php if($pendingMesses ?? 0): ?>
                <span class="sidebar-badge"><?php echo e($pendingMesses); ?></span>
                <?php endif; ?>
            </a>

            <div class="sidebar-section-label">Business</div>
            <a href="<?php echo e(route('admin.subscriptions')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.subscriptions*') ? 'active' : ''); ?>">
                <i class="bi bi-credit-card"></i> Subscriptions
            </a>
            <a href="<?php echo e(route('admin.bookings')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.bookings*') ? 'active' : ''); ?>">
                <i class="bi bi-calendar-check"></i> Bookings
            </a>

            <div class="sidebar-section-label">Content</div>
            <a href="<?php echo e(route('admin.reviews')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.reviews*') ? 'active' : ''); ?>">
                <i class="bi bi-star"></i> Reviews
            </a>

            <div class="sidebar-section-label">System</div>
            <a href="<?php echo e(route('admin.settings')); ?>" class="sidebar-item <?php echo e(request()->routeIs('admin.settings*') ? 'active' : ''); ?>">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        <?php echo $__env->yieldContent('admin-content'); ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/layouts/admin.blade.php ENDPATH**/ ?>