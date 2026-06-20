<?php $__env->startSection('title', 'Sign In'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.auth-hero {
    background: linear-gradient(135deg, var(--brand-primary) 0%, #7C3AED 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 2rem;
    position: relative;
    overflow: hidden;
}
.auth-hero::before {
    content: '';
    position: absolute;
    width: 600px; height: 600px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    top: -200px; right: -100px;
}
.auth-hero::after {
    content: '';
    position: absolute;
    width: 400px; height: 400px;
    border-radius: 50%;
    background: rgba(255,255,255,0.03);
    bottom: -100px; left: -80px;
}
.auth-card {
    background: var(--bg-surface);
    border-radius: 24px;
    padding: 2.5rem;
    width: 100%;
    max-width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    position: relative;
    z-index: 2;
    border: 1px solid var(--border-color);
}
.auth-brand {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--brand-primary);
    text-align: center;
    margin-bottom: 0.25rem;
}
.auth-brand span { color: var(--brand-secondary); }
.auth-subtitle {
    text-align: center;
    color: var(--text-muted);
    font-size: 0.88rem;
    margin-bottom: 2rem;
}
.otp-inputs { display: flex; gap: 8px; justify-content: center; }
.otp-input {
    width: 46px; height: 52px;
    text-align: center;
    font-size: 1.3rem;
    font-weight: 700;
    border: 2px solid var(--input-border);
    border-radius: 10px;
    background: var(--input-bg);
    color: var(--text-primary);
    transition: all 0.2s;
}
.otp-input:focus { border-color: var(--brand-primary); box-shadow: 0 0 0 3px rgba(92,95,239,0.12); outline: none; }
.divider { display: flex; align-items: center; gap: 1rem; margin: 1.25rem 0; }
.divider::before, .divider::after { content:''; flex:1; height:1px; background:var(--border-color); }
.divider span { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }
.role-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 20px; cursor: pointer;
    border: 1.5px solid var(--border-color); font-size: 0.82rem; font-weight: 600;
    color: var(--text-secondary); background: transparent; transition: all 0.15s;
}
.role-pill:hover, .role-pill.active {
    border-color: var(--brand-primary); color: var(--brand-primary);
    background: rgba(92,95,239,0.08);
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-hero">
    <div class="auth-card">
        <div class="auth-brand">SolMate<span></span></div>
        <p class="auth-subtitle">Sign in to your account</p>

        <?php if(session('error')): ?>
        <div style="background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); border-radius:10px; padding:0.75rem 1rem; margin-bottom:1rem; font-size:0.85rem; color:var(--danger);">
            <i class="bi bi-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

        </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <div style="position:relative;">
                    <i class="bi bi-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.9rem;"></i>
                    <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        style="padding-left:2.2rem;"
                        value="<?php echo e(old('email')); ?>" placeholder="you@example.com" required autofocus>
                </div>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="mb-3">
                <div class="d-flex justify-content-between">
                    <label class="form-label">Password</label>
                    <a href="<?php echo e(route('password.forgot')); ?>" style="font-size:0.8rem; color:var(--brand-primary);">Forgot password?</a>
                </div>
                <div style="position:relative;">
                    <i class="bi bi-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);font-size:0.9rem;"></i>
                    <input type="password" name="password" id="pwdField" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        style="padding-left:2.2rem; padding-right:2.5rem;"
                        placeholder="••••••••" required>
                    <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>
                <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-3">
                <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.85rem;color:var(--text-secondary);">
                    <input type="checkbox" name="remember" style="accent-color:var(--brand-primary);width:15px;height:15px;">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn-primary-findr w-100" style="padding:0.75rem; font-size:0.95rem;">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>

        <div class="divider"><span>or</span></div>

        <p style="text-align:center; font-size:0.88rem; color:var(--text-secondary); margin:0;">
            Don't have an account?
            <a href="<?php echo e(route('register')); ?>" style="font-weight:600; color:var(--brand-primary);">Create account</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function togglePwd() {
    const f = document.getElementById('pwdField');
    const i = document.getElementById('eyeIcon');
    if (f.type === 'password') { f.type = 'text'; i.className = 'bi bi-eye-slash'; }
    else { f.type = 'password'; i.className = 'bi bi-eye'; }
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/auth/login.blade.php ENDPATH**/ ?>