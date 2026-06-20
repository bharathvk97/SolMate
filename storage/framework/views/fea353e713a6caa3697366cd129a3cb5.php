<?php $__env->startSection('title', 'Mess Owner Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div style="display:flex;min-height:calc(100vh - 65px);">
    <!-- Sidebar -->
    <aside style="width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0;">
        <div class="pt-3">
            <div class="sidebar-section-label">My Mess</div>
            <a href="<?php echo e(route('owner.mess.dashboard')); ?>" class="sidebar-item <?php echo e(request()->routeIs('owner.mess.dashboard') ? 'active' : ''); ?>"><i class="bi bi-speedometer2"></i> Overview</a>
            <a href="<?php echo e(route('owner.mess.listings')); ?>" class="sidebar-item <?php echo e(request()->routeIs('owner.mess.listings*') ? 'active' : ''); ?>"><i class="bi bi-egg-fried"></i> My Messes</a>
            <a href="<?php echo e(route('owner.mess.menus')); ?>" class="sidebar-item <?php echo e(request()->routeIs('owner.mess.menus*') ? 'active' : ''); ?>"><i class="bi bi-menu-button-wide"></i> Food Menus</a>
            <a href="<?php echo e(route('owner.mess.bookings')); ?>" class="sidebar-item <?php echo e(request()->routeIs('owner.mess.bookings*') ? 'active' : ''); ?>">
                <i class="bi bi-calendar-check"></i> Subscribers
                <?php if($pendingBookings ?? 0): ?><span class="sidebar-badge"><?php echo e($pendingBookings); ?></span><?php endif; ?>
            </a>
            <a href="<?php echo e(route('owner.mess.reviews')); ?>" class="sidebar-item"><i class="bi bi-star"></i> Reviews</a>
            <div class="sidebar-section-label">Account</div>
            <a href="<?php echo e(route('owner.subscription')); ?>" class="sidebar-item">
                <i class="bi bi-credit-card"></i> Subscription
                <?php if(!auth()->user()->hasActiveSubscription()): ?><span class="sidebar-badge" style="background:var(--danger);">!</span><?php endif; ?>
            </a>
        </div>
    </aside>

    <div style="flex:1;padding:2rem;min-width:0;">
        <?php if(!auth()->user()->hasActiveSubscription()): ?>
        <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:12px;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;align-items:center;gap:12px;">
            <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);font-size:1.2rem;"></i>
            <div>
                <strong style="color:var(--danger);">Subscription Expired</strong>
                <p style="margin:0;font-size:0.85rem;color:var(--text-secondary);">Renew to keep listings active. <a href="<?php echo e(route('owner.subscription')); ?>" style="color:var(--brand-primary);font-weight:600;">Renew now →</a></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="page-header d-flex align-items-center justify-content-between">
            <div><h1>Mess Dashboard</h1><p>Manage your mess, menus, and subscribers.</p></div>
            <a href="<?php echo e(route('owner.mess.create')); ?>" class="btn-primary-findr d-flex align-items-center gap-2"><i class="bi bi-plus-lg"></i>Add Mess</a>
        </div>

        <!-- Stats -->
        <div class="row g-3 mb-4">
            <?php $__currentLoopData = [
                ['label'=>'My Messes',       'value'=>$stats['total_messes']??0,    'icon'=>'bi-egg-fried',    'color'=>'#5C5FEF','bg'=>'rgba(92,95,239,0.1)'],
                ['label'=>'Active Messes',   'value'=>$stats['active_messes']??0,   'icon'=>'bi-check-circle', 'color'=>'#10B981','bg'=>'rgba(16,185,129,0.1)'],
                ['label'=>'Subscribers',     'value'=>$stats['total_subscribers']??0,'icon'=>'bi-people',      'color'=>'#F97316','bg'=>'rgba(249,115,22,0.1)'],
                ['label'=>'Total Menus',     'value'=>$stats['total_menus']??0,     'icon'=>'bi-list-check',   'color'=>'#8B5CF6','bg'=>'rgba(139,92,246,0.1)'],
            ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:<?php echo e($s['bg']); ?>;color:<?php echo e($s['color']); ?>;"><i class="bi <?php echo e($s['icon']); ?>"></i></div>
                        <div><div class="stat-value"><?php echo e($s['value']); ?></div><div class="stat-label"><?php echo e($s['label']); ?></div></div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Messes + Menu Quick Toggle -->
        <div class="row g-3">
            <div class="col-lg-7">
                <div class="card-findr">
                    <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                        <h6 style="font-weight:700;margin:0;">My Messes</h6>
                        <a href="<?php echo e(route('owner.mess.create')); ?>" class="btn-primary-findr" style="padding:0.4rem 1rem;font-size:0.82rem;"><i class="bi bi-plus-lg me-1"></i>Add</a>
                    </div>
                    <div class="p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $messes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mess): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div style="display:flex;align-items:center;gap:12px;padding:0.85rem 0;border-bottom:1px solid var(--border-color);">
                            <?php if($mess->cover_image): ?>
                            <img src="<?php echo e($mess->cover_image_url); ?>" style="width:52px;height:52px;border-radius:10px;object-fit:cover;flex-shrink:0;" alt="">
                            <?php else: ?>
                            <div style="width:52px;height:52px;border-radius:10px;background:var(--bg-subtle);display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1.4rem;">🍽️</div>
                            <?php endif; ?>
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:700;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo e($mess->name); ?></div>
                                <div style="font-size:0.75rem;color:var(--text-muted);"><?php echo e($mess->city); ?> · <?php echo e($mess->food_type==='veg'?'Pure Veg':($mess->food_type==='non_veg'?'Non-Veg':'Both')); ?></div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge-status badge-<?php echo e($mess->status); ?>"><?php echo e(ucfirst($mess->status)); ?></span>
                                <a href="<?php echo e(route('owner.mess.edit', $mess->id)); ?>" style="background:var(--bg-subtle);color:var(--text-secondary);border:none;border-radius:8px;padding:5px 10px;font-size:0.8rem;text-decoration:none;"><i class="bi bi-pencil"></i></a>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p style="text-align:center;color:var(--text-muted);padding:2rem;">No messes yet. <a href="<?php echo e(route('owner.mess.create')); ?>" style="color:var(--brand-primary);">Add one →</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Menu Slot Overview -->
            <div class="col-lg-5">
                <div class="card-findr">
                    <div class="p-4 pb-0"><h6 style="font-weight:700;margin:0;">Today's Menu Status</h6></div>
                    <div class="p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $messes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mess): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div style="margin-bottom:1.25rem;">
                            <p style="font-weight:700;font-size:0.88rem;margin-bottom:0.5rem;"><?php echo e($mess->name); ?></p>
                            <div class="d-flex flex-wrap gap-1">
                                <?php $__currentLoopData = ['morning'=>'☀️','afternoon'=>'🌤️','evening'=>'🌅','night'=>'🌙']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $slot => $icon): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                $menu = $mess->menus->where('slot',$slot)->where('is_available',true)->first();
                                $isOpen = $menu && $mess->isSlotOpen($slot) && $menu->status === 'open';
                                ?>
                                <div style="display:flex;flex-direction:column;align-items:center;gap:3px;">
                                    <span style="font-size:1rem;"><?php echo e($icon); ?></span>
                                    <?php if($menu): ?>
                                    <form method="POST" action="<?php echo e(route('owner.mess.menus.toggle', $menu->id)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('POST'); ?>
                                        <button type="submit" class="slot-pill <?php echo e($menu->status==='open'?'slot-open':'slot-closed'); ?>" style="border:none;cursor:pointer;font-size:0.68rem;">
                                            <span class="slot-dot"></span><?php echo e(ucfirst($menu->status)); ?>

                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <span class="slot-pill slot-closed" style="font-size:0.68rem;"><span class="slot-dot"></span>No Menu</span>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;padding:1rem;">Add a mess to manage menus</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Subscribers -->
                <div class="card-findr mt-3">
                    <div class="d-flex align-items-center justify-content-between p-4 pb-0">
                        <h6 style="font-weight:700;margin:0;">Recent Subscribers</h6>
                        <a href="<?php echo e(route('owner.mess.bookings')); ?>" style="font-size:0.82rem;" class="text-brand">All →</a>
                    </div>
                    <div class="p-3">
                        <?php $__empty_1 = true; $__currentLoopData = $recentBookings ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div style="display:flex;align-items:center;gap:10px;padding:0.6rem 0;border-bottom:1px solid var(--border-color);">
                            <img src="<?php echo e($b->user->avatar_url); ?>" style="width:34px;height:34px;border-radius:50%;" alt="">
                            <div style="flex:1;">
                                <p style="font-weight:600;font-size:0.85rem;margin:0;"><?php echo e($b->user->name); ?></p>
                                <p style="font-size:0.75rem;color:var(--text-muted);margin:0;"><?php echo e($b->plan->name); ?></p>
                            </div>
                            <span style="font-size:0.78rem;color:var(--text-muted);"><?php echo e($b->end_date->diffForHumans()); ?></span>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p style="text-align:center;color:var(--text-muted);font-size:0.85rem;padding:1rem;">No subscribers yet</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/owner/mess/dashboard.blade.php ENDPATH**/ ?>