<?php $__env->startSection('title', 'Create Account'); ?>

<?php $__env->startPush('styles'); ?>
<style>
.auth-page { background: var(--bg-base); min-height: calc(100vh - 65px); display:flex; align-items:flex-start; justify-content:center; padding: 2rem 1rem; }
.auth-card { background: var(--bg-surface); border-radius: 24px; padding: 2.5rem; width: 100%; max-width: 560px; box-shadow: var(--card-shadow); border: 1px solid var(--border-color); }
.step-indicator { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:2rem; }
.step-dot { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;transition:all 0.25s; }
.step-dot.active  { background:var(--brand-primary); color:#fff; }
.step-dot.done    { background:var(--brand-accent);  color:#fff; }
.step-dot.pending { background:var(--border-color);  color:var(--text-muted); }
.step-line { flex:1;height:2px;background:var(--border-color);max-width:60px; }
.step-line.done { background:var(--brand-accent); }
.role-card { border:2px solid var(--border-color); border-radius:14px; padding:1.1rem; cursor:pointer; text-align:center; transition:all 0.2s; }
.role-card:hover { border-color:var(--brand-primary); background:rgba(92,95,239,0.04); }
.role-card.selected { border-color:var(--brand-primary); background:rgba(92,95,239,0.07); }
.role-card .role-icon { font-size:2rem; margin-bottom:0.5rem; }
.role-card h6 { font-weight:700; font-size:0.9rem; margin:0; color:var(--text-primary); }
.role-card p  { font-size:0.78rem; color:var(--text-muted); margin:4px 0 0; }
.upload-zone { border:2px dashed var(--border-color); border-radius:12px; padding:1.5rem; text-align:center; cursor:pointer; transition:all 0.2s; }
.upload-zone:hover { border-color:var(--brand-primary); background:rgba(92,95,239,0.04); }
.upload-zone.has-file { border-color:var(--brand-accent); background:rgba(16,185,129,0.05); }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="auth-page">
<div class="auth-card">
    <p style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.5rem;font-weight:800;text-align:center;color:var(--brand-primary);margin-bottom:4px;">Create Account</p>
    <p style="text-align:center;color:var(--text-muted);font-size:0.88rem;margin-bottom:1.5rem;">Join SolMate today</p>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step-dot active" id="step1dot">1</div>
        <div class="step-line" id="line12"></div>
        <div class="step-dot pending" id="step2dot">2</div>
        <div class="step-line" id="line23"></div>
        <div class="step-dot pending" id="step3dot">3</div>
        <div class="step-line" id="line34"></div>
        <div class="step-dot pending" id="step4dot"><i class="bi bi-check2"></i></div>
    </div>

    <form method="POST" action="<?php echo e(route('register')); ?>" enctype="multipart/form-data" id="regForm">
    <?php echo csrf_field(); ?>

    <!-- ── STEP 1: Role Selection ──────────────────── -->
    <div id="step1">
        <h6 style="font-weight:700;margin-bottom:0.3rem;">Who are you?</h6>
        <p style="font-size:0.84rem;color:var(--text-muted);margin-bottom:1.25rem;">Select how you want to use SolMate</p>
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="role-card" onclick="selectRole('user',this)">
                    <div class="role-icon">👤</div>
                    <h6>Student / User</h6>
                    <p>Find hostels & mess near you</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="role-card" onclick="selectRole('hostel_owner',this)">
                    <div class="role-icon">🏠</div>
                    <h6>Hostel Owner</h6>
                    <p>List and manage your hostel</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="role-card" onclick="selectRole('mess_owner',this)">
                    <div class="role-icon">🍽️</div>
                    <h6>Mess Owner</h6>
                    <p>List your mess & food menu</p>
                </div>
            </div>
        </div>
        <input type="hidden" name="role" id="roleInput" value="<?php echo e(old('role','user')); ?>">
        <div id="roleError" style="color:var(--danger);font-size:0.82rem;margin-top:0.5rem;display:none;">Please select a role</div>
        <button type="button" class="btn-primary-findr w-100 mt-4" onclick="goStep(2)">Continue →</button>
    </div>

    <!-- ── STEP 2: Personal Info ───────────────────── -->
    <div id="step2" style="display:none;">
        <h6 style="font-weight:700;margin-bottom:1rem;">Personal Information</h6>
        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                value="<?php echo e(old('name')); ?>" placeholder="Arjun Kumar" required>
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                    value="<?php echo e(old('email')); ?>" placeholder="you@email.com" required>
                <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone Number</label>
                <div style="display:flex; gap:6px;">
                    <span style="background:var(--bg-subtle);border:1.5px solid var(--input-border);border-radius:10px;padding:0.6rem 0.8rem;font-size:0.88rem;color:var(--text-secondary);white-space:nowrap;">🇮🇳 +91</span>
                    <input type="tel" name="phone" class="form-control <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        value="<?php echo e(old('phone')); ?>" placeholder="9876543210" required maxlength="10">
                </div>
                <?php $__errorArgs = ['phone'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>
        <div class="row g-3 mt-0">
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <div style="position:relative;">
                    <input type="password" name="password" class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                        placeholder="Min 8 chars" required style="padding-right:2.5rem;">
                    <button type="button" onclick="togglePwd2()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                        <i class="bi bi-eye" id="eye2"></i>
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
            <div class="col-md-6">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat password" required>
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn-outline-findr" onclick="goStep(1)">← Back</button>
            <button type="button" class="btn-primary-findr flex-grow-1" onclick="goStep(3)">Continue →</button>
        </div>
    </div>

    <!-- ── STEP 3: Identity (Owners only) / Terms (Users) ── -->
    <div id="step3" style="display:none;">
        <!-- Owner identity section -->
        <div id="ownerIdentity">
            <h6 style="font-weight:700;margin-bottom:0.3rem;">Identity Verification</h6>
            <p style="font-size:0.84rem;color:var(--text-muted);margin-bottom:1.25rem;">Required to list your property. Documents are kept secure.</p>

            <div class="mb-3">
                <label class="form-label">Identity Type</label>
                <div class="d-flex gap-2">
                    <label style="flex:1;border:2px solid var(--border-color);border-radius:10px;padding:0.75rem;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.15s;" id="lblAadhaar">
                        <input type="radio" name="identity_type" value="aadhaar" onchange="selectIdentity('aadhaar')" style="accent-color:var(--brand-primary);">
                        <span>
                            <strong style="font-size:0.88rem;display:block;">Aadhaar Card</strong>
                            <small style="color:var(--text-muted);">12-digit number</small>
                        </span>
                    </label>
                    <label style="flex:1;border:2px solid var(--border-color);border-radius:10px;padding:0.75rem;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.15s;" id="lblPassport">
                        <input type="radio" name="identity_type" value="passport" onchange="selectIdentity('passport')" style="accent-color:var(--brand-primary);">
                        <span>
                            <strong style="font-size:0.88rem;display:block;">Passport</strong>
                            <small style="color:var(--text-muted);">8-character alphanumeric</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label" id="idNumberLabel">ID Number</label>
                <input type="text" name="identity_number" id="idNumberInput" class="form-control" placeholder="e.g. 1234 5678 9012" maxlength="14">
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Front Side</label>
                    <div class="upload-zone" onclick="document.getElementById('frontDoc').click()" id="frontZone">
                        <i class="bi bi-cloud-upload" style="font-size:1.5rem;color:var(--text-muted);"></i>
                        <p style="font-size:0.82rem;color:var(--text-muted);margin:6px 0 0;" id="frontLabel">Click to upload front</p>
                        <p style="font-size:0.72rem;color:var(--text-muted);margin:2px 0 0;">JPG, PNG or PDF · Max 5MB</p>
                    </div>
                    <input type="file" id="frontDoc" name="identity_front" accept="image/*,.pdf" style="display:none;" onchange="fileChosen('frontZone','frontLabel',this)">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Back Side <span style="color:var(--text-muted); font-weight:400;">(Optional)</span></label>
                    <div class="upload-zone" onclick="document.getElementById('backDoc').click()" id="backZone">
                        <i class="bi bi-cloud-upload" style="font-size:1.5rem;color:var(--text-muted);"></i>
                        <p style="font-size:0.82rem;color:var(--text-muted);margin:6px 0 0;" id="backLabel">Click to upload back</p>
                        <p style="font-size:0.72rem;color:var(--text-muted);margin:2px 0 0;">JPG, PNG or PDF · Max 5MB</p>
                    </div>
                    <input type="file" id="backDoc" name="identity_back" accept="image/*,.pdf" style="display:none;" onchange="fileChosen('backZone','backLabel',this)">
                </div>
            </div>
        </div>

        <!-- User: just terms -->
        <div id="userTerms" style="display:none;">
            <h6 style="font-weight:700;margin-bottom:0.5rem;">Almost there!</h6>
            <p style="font-size:0.88rem;color:var(--text-muted);">You're ready to create your account. By continuing you agree to our Terms of Service and Privacy Policy.</p>
            <div style="background:var(--bg-subtle);border-radius:12px;padding:1rem;margin-top:1rem;">
                <label style="display:flex;gap:10px;cursor:pointer;font-size:0.88rem;color:var(--text-secondary);">
                    <input type="checkbox" id="termsCheck" required style="accent-color:var(--brand-primary);width:16px;height:16px;flex-shrink:0;margin-top:2px;">
                    I agree to the <a href="#" style="color:var(--brand-primary);">Terms of Service</a> and <a href="#" style="color:var(--brand-primary);">Privacy Policy</a>
                </label>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn-outline-findr" onclick="goStep(2)">← Back</button>
            <button type="submit" class="btn-primary-findr flex-grow-1" id="submitBtn">
                <i class="bi bi-person-check me-2"></i>Create Account
            </button>
        </div>
    </div>

    </form>

    <div style="margin-top:1.5rem;text-align:center;font-size:0.85rem;color:var(--text-secondary);">
        Already have an account? <a href="<?php echo e(route('login')); ?>" style="font-weight:600;color:var(--brand-primary);">Sign in</a>
    </div>
</div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
let currentRole = '<?php echo e(old("role","user")); ?>';
let currentStep = 1;

function selectRole(role, el) {
    currentRole = role;
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('roleError').style.display = 'none';
}

function goStep(n) {
    if (n === 2 && !currentRole) { document.getElementById('roleError').style.display='block'; return; }
    document.querySelectorAll('[id^="step"]').forEach(s => { if(s.id.match(/^step\d$/)) s.style.display='none'; });
    document.getElementById('step'+n).style.display = 'block';
    currentStep = n;

    // Toggle identity vs terms on step 3
    if (n === 3) {
        const isOwner = ['hostel_owner','mess_owner'].includes(currentRole);
        document.getElementById('ownerIdentity').style.display = isOwner ? 'block' : 'none';
        document.getElementById('userTerms').style.display     = isOwner ? 'none'  : 'block';
    }

    // Update dots
    for (let i=1;i<=4;i++) {
        const dot = document.getElementById('step'+i+'dot');
        if (!dot) continue;
        dot.className = 'step-dot ' + (i < n ? 'done' : i === n ? 'active' : 'pending');
    }
    document.querySelectorAll('.step-line').forEach((l,i) => {
        l.className = 'step-line ' + (i+1 < n ? 'done' : '');
    });
}

function selectIdentity(type) {
    const isAadhaar = type === 'aadhaar';
    document.getElementById('lblAadhaar').style.borderColor = isAadhaar ? 'var(--brand-primary)' : 'var(--border-color)';
    document.getElementById('lblPassport').style.borderColor= !isAadhaar? 'var(--brand-primary)' : 'var(--border-color)';
    document.getElementById('idNumberLabel').textContent = isAadhaar ? 'Aadhaar Number (12 digits)' : 'Passport Number (e.g. A1234567)';
    document.getElementById('idNumberInput').placeholder  = isAadhaar ? '1234 5678 9012' : 'A1234567';
}

function fileChosen(zoneId, labelId, input) {
    if (input.files[0]) {
        document.getElementById(zoneId).classList.add('has-file');
        document.getElementById(labelId).textContent = input.files[0].name;
        document.getElementById(labelId).style.color = 'var(--brand-accent)';
    }
}

function togglePwd2() {
    const f = document.querySelector('input[name="password"]');
    const i = document.getElementById('eye2');
    f.type = f.type==='password' ? 'text' : 'password';
    i.className = f.type==='password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Auto-select role if old() present
document.addEventListener('DOMContentLoaded', () => {
    const oldRole = '<?php echo e(old("role","")); ?>';
    if (oldRole) {
        const cards = document.querySelectorAll('.role-card');
        const roleMap = {'user':0,'hostel_owner':1,'mess_owner':2};
        if (cards[roleMap[oldRole]]) cards[roleMap[oldRole]].classList.add('selected');
        <?php if(old('role')): ?>
        goStep(<?php echo e(old('_step',1)); ?>);
        <?php endif; ?>
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\SolmateProject\hostel-mess-finder\laravel\resources\views/auth/register.blade.php ENDPATH**/ ?>