@extends('layouts.app')
@section('title', 'Create Account')

@push('styles')
<style>
.auth-page { background:var(--bg-base);min-height:calc(100vh - 65px);display:flex;align-items:flex-start;justify-content:center;padding:2rem 1rem; }
.auth-card { background:var(--bg-surface);border-radius:24px;padding:2.5rem;width:100%;max-width:560px;box-shadow:var(--card-shadow);border:1px solid var(--border-color); }
.step-indicator { display:flex;align-items:center;justify-content:center;gap:0;margin-bottom:2rem; }
.step-dot { width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.78rem;font-weight:700;transition:all 0.25s; }
.step-dot.active  { background:var(--brand-primary);color:#fff; }
.step-dot.done    { background:var(--brand-accent);color:#fff; }
.step-dot.pending { background:var(--border-color);color:var(--text-muted); }
.step-line { flex:1;height:2px;background:var(--border-color);max-width:60px; }
.step-line.done { background:var(--brand-accent); }
.role-card { border:2px solid var(--border-color);border-radius:14px;padding:1.1rem;cursor:pointer;text-align:center;transition:all 0.2s; }
.role-card:hover { border-color:var(--brand-primary);background:rgba(92,95,239,0.04); }
.role-card.selected { border-color:var(--brand-primary);background:rgba(92,95,239,0.07); }
.role-card .role-icon { font-size:2rem;margin-bottom:0.5rem; }
.role-card h6 { font-weight:700;font-size:0.9rem;margin:0;color:var(--text-primary); }
.role-card p  { font-size:0.78rem;color:var(--text-muted);margin:4px 0 0; }
.upload-zone { border:2px dashed var(--border-color);border-radius:12px;padding:1.5rem;text-align:center;cursor:pointer;transition:all 0.2s; }
.upload-zone:hover { border-color:var(--brand-primary);background:rgba(92,95,239,0.04); }
.upload-zone.has-file { border-color:var(--brand-accent);background:rgba(16,185,129,0.05); }
</style>
@endpush

@section('content')
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

    @if($errors->any())
    <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:10px;padding:0.8rem 1rem;margin-bottom:1rem;font-size:0.85rem;color:var(--danger);">
        <strong>Please fix these errors:</strong>
        <ul style="margin:4px 0 0;padding-left:1.2rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- Use novalidate to disable browser HTML5 validation — we handle it in JS --}}
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" id="regForm" novalidate>
    @csrf

    <!-- ── STEP 1: Role ── -->
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
        <input type="hidden" name="role" id="roleInput" value="{{ old('role','') }}">
        <div id="roleError" style="color:var(--danger);font-size:0.82rem;margin-top:0.5rem;display:none;">
            <i class="bi bi-exclamation-circle me-1"></i>Please select a role to continue.
        </div>
        <button type="button" class="btn-primary-findr w-100 mt-4" onclick="goStep(2)">Continue →</button>
    </div>

    <!-- ── STEP 2: Personal Info ── -->
    <div id="step2" style="display:none;">
        <h6 style="font-weight:700;margin-bottom:1rem;">Personal Information</h6>

        <div class="mb-3">
            <label class="form-label">Full Name <span style="color:var(--danger);">*</span></label>
            <input type="text" name="name" id="inp_name"
                class="form-control @error('name') is-invalid @enderror"
                value="{{ old('name') }}" placeholder="Arjun Kumar">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Email Address <span style="color:var(--danger);">*</span></label>
                <input type="email" name="email" id="inp_email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}" placeholder="you@email.com">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone Number <span style="color:var(--danger);">*</span></label>
                <div style="display:flex;gap:6px;">
                    <span style="background:var(--bg-subtle);border:1.5px solid var(--input-border);border-radius:10px;padding:0.6rem 0.8rem;font-size:0.88rem;color:var(--text-secondary);white-space:nowrap;">🇮🇳 +91</span>
                    <input type="tel" name="phone" id="inp_phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}" placeholder="9876543210" maxlength="10">
                </div>
                @error('phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="row g-3 mt-0">
            <div class="col-md-6">
                <label class="form-label">Password <span style="color:var(--danger);">*</span></label>
                <div style="position:relative;">
                    <input type="password" name="password" id="inp_password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Min 8 characters" style="padding-right:2.5rem;">
                    <button type="button" onclick="togglePwd2()"
                        style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                        <i class="bi bi-eye" id="eye2"></i>
                    </button>
                </div>
                @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirm Password <span style="color:var(--danger);">*</span></label>
                <input type="password" name="password_confirmation" id="inp_confirm"
                    class="form-control" placeholder="Repeat password">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn-outline-findr" onclick="goStep(1)">← Back</button>
            <button type="button" class="btn-primary-findr flex-grow-1" onclick="validateStep2()">Continue →</button>
        </div>
    </div>

    <!-- ── STEP 3: Identity (Owners) / Terms (Users) ── -->
    <div id="step3" style="display:none;">

        <!-- Owner: Identity Verification -->
        <div id="ownerIdentity" style="display:none;">
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
                    <label class="form-label">Back Side <span style="color:var(--text-muted);font-weight:400;">(Optional)</span></label>
                    <div class="upload-zone" onclick="document.getElementById('backDoc').click()" id="backZone">
                        <i class="bi bi-cloud-upload" style="font-size:1.5rem;color:var(--text-muted);"></i>
                        <p style="font-size:0.82rem;color:var(--text-muted);margin:6px 0 0;" id="backLabel">Click to upload back</p>
                        <p style="font-size:0.72rem;color:var(--text-muted);margin:2px 0 0;">JPG, PNG or PDF · Max 5MB</p>
                    </div>
                    <input type="file" id="backDoc" name="identity_back" accept="image/*,.pdf" style="display:none;" onchange="fileChosen('backZone','backLabel',this)">
                </div>
            </div>
        </div>

        <!-- User: Terms -->
        <div id="userTerms" style="display:none;">
            <h6 style="font-weight:700;margin-bottom:0.5rem;">Almost there!</h6>
            <p style="font-size:0.88rem;color:var(--text-muted);">You're ready to create your account.</p>

            <!-- Summary box -->
            <div style="background:var(--bg-subtle);border-radius:12px;padding:1rem;margin:1rem 0;">
                <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                    <span style="color:var(--text-muted);">Name</span>
                    <strong id="summaryName"></strong>
                </div>
                <div class="d-flex justify-content-between mb-1" style="font-size:0.85rem;">
                    <span style="color:var(--text-muted);">Email</span>
                    <strong id="summaryEmail"></strong>
                </div>
                <div class="d-flex justify-content-between" style="font-size:0.85rem;">
                    <span style="color:var(--text-muted);">Phone</span>
                    <strong id="summaryPhone"></strong>
                </div>
            </div>

            <!-- Terms checkbox — no required attribute, validated in JS -->
            <div style="background:var(--bg-subtle);border-radius:12px;padding:1rem;">
                <label style="display:flex;gap:10px;cursor:pointer;font-size:0.88rem;color:var(--text-secondary);">
                    <input type="checkbox" id="termsCheck" name="terms" value="1"
                        style="accent-color:var(--brand-primary);width:16px;height:16px;flex-shrink:0;margin-top:2px;">
                    I agree to the
                    <a href="#" style="color:var(--brand-primary);">Terms of Service</a>
                    and
                    <a href="#" style="color:var(--brand-primary);">Privacy Policy</a>
                </label>
                <div id="termsError" style="color:var(--danger);font-size:0.8rem;margin-top:6px;display:none;">
                    <i class="bi bi-exclamation-circle me-1"></i>Please accept the terms to continue.
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button type="button" class="btn-outline-findr" onclick="goStep(2)">← Back</button>
            {{-- type=button so browser doesn't trigger HTML5 validation — we validate in JS --}}
            <button type="button" class="btn-primary-findr flex-grow-1" id="submitBtn" onclick="submitRegister()">
                <i class="bi bi-person-check me-2"></i>Create Account
            </button>
        </div>
    </div>

    </form>

    <div style="margin-top:1.5rem;text-align:center;font-size:0.85rem;color:var(--text-secondary);">
        Already have an account? <a href="{{ route('login') }}" style="font-weight:600;color:var(--brand-primary);">Sign in</a>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
var currentRole = '{{ old("role","") }}';
var currentStep = 1;

// ── Role Selection ────────────────────────────────────────
function selectRole(role, el) {
    currentRole = role;
    document.getElementById('roleInput').value = role;
    document.querySelectorAll('.role-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');
    document.getElementById('roleError').style.display = 'none';
}

// ── Step Navigation ───────────────────────────────────────
function goStep(n) {
    // Step 1 → 2: must have a role
    if (n === 2 && !currentRole) {
        document.getElementById('roleError').style.display = 'block';
        return;
    }

    // Hide all steps
    [1,2,3].forEach(function(i) {
        var el = document.getElementById('step' + i);
        if (el) el.style.display = 'none';
    });

    // Show target step
    var target = document.getElementById('step' + n);
    if (target) target.style.display = 'block';
    currentStep = n;

    // Step 3: show correct section based on role
    if (n === 3) {
        var isOwner = (currentRole === 'hostel_owner' || currentRole === 'mess_owner');
        document.getElementById('ownerIdentity').style.display = isOwner ? 'block' : 'none';
        document.getElementById('userTerms').style.display     = isOwner ? 'none'  : 'block';

        // Fill summary for users
        if (!isOwner) {
            document.getElementById('summaryName').textContent  = document.getElementById('inp_name').value;
            document.getElementById('summaryEmail').textContent = document.getElementById('inp_email').value;
            document.getElementById('summaryPhone').textContent = '+91 ' + document.getElementById('inp_phone').value;
        }
    }

    // Update step dots
    for (var i = 1; i <= 4; i++) {
        var dot = document.getElementById('step' + i + 'dot');
        if (!dot) continue;
        if (i < n)      dot.className = 'step-dot done';
        else if (i === n) dot.className = 'step-dot active';
        else            dot.className = 'step-dot pending';
    }

    // Update step lines
    var lines = document.querySelectorAll('.step-line');
    lines.forEach(function(l, idx) {
        l.className = 'step-line' + (idx + 1 < n ? ' done' : '');
    });

    // Scroll to top of card
    document.querySelector('.auth-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ── Step 2 Validation ─────────────────────────────────────
function validateStep2() {
    var name     = document.getElementById('inp_name').value.trim();
    var email    = document.getElementById('inp_email').value.trim();
    var phone    = document.getElementById('inp_phone').value.trim();
    var password = document.getElementById('inp_password').value;
    var confirm  = document.getElementById('inp_confirm').value;
    var errors   = [];

    if (!name)                        errors.push('Full name is required.');
    if (!email || !email.includes('@'))errors.push('A valid email is required.');
    if (!/^\d{10}$/.test(phone))      errors.push('Phone must be exactly 10 digits.');
    if (password.length < 8)          errors.push('Password must be at least 8 characters.');
    if (password !== confirm)         errors.push('Passwords do not match.');

    if (errors.length) {
        showToast(errors[0], 'warning');
        return;
    }
    goStep(3);
}

// ── Final Submit with Terms Validation ───────────────────
function submitRegister() {
    var isOwner = (currentRole === 'hostel_owner' || currentRole === 'mess_owner');

    // Only check terms for regular users
    if (!isOwner) {
        var terms = document.getElementById('termsCheck');
        if (!terms || !terms.checked) {
            document.getElementById('termsError').style.display = 'block';
            showToast('Please accept the Terms of Service to continue.', 'warning');
            return;
        }
        document.getElementById('termsError').style.display = 'none';
    }

    // Disable button to prevent double submit
    var btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating Account…';

    document.getElementById('regForm').submit();
}

// ── Identity Type ─────────────────────────────────────────
function selectIdentity(type) {
    var isAadhaar = (type === 'aadhaar');
    document.getElementById('lblAadhaar').style.borderColor  = isAadhaar  ? 'var(--brand-primary)' : 'var(--border-color)';
    document.getElementById('lblPassport').style.borderColor = !isAadhaar ? 'var(--brand-primary)' : 'var(--border-color)';
    document.getElementById('idNumberLabel').textContent     = isAadhaar  ? 'Aadhaar Number (12 digits)' : 'Passport Number (e.g. A1234567)';
    document.getElementById('idNumberInput').placeholder     = isAadhaar  ? '1234 5678 9012' : 'A1234567';
}

function fileChosen(zoneId, labelId, input) {
    if (input.files[0]) {
        document.getElementById(zoneId).classList.add('has-file');
        document.getElementById(labelId).textContent = input.files[0].name;
        document.getElementById(labelId).style.color = 'var(--brand-accent)';
    }
}

function togglePwd2() {
    var f = document.querySelector('input[name="password"]');
    var i = document.getElementById('eye2');
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}

// Restore state if form came back with validation errors from server
document.addEventListener('DOMContentLoaded', function() {
    var oldRole = '{{ old("role","") }}';
    if (oldRole) {
        currentRole = oldRole;
        document.getElementById('roleInput').value = oldRole;
        var roleMap = { 'user': 0, 'hostel_owner': 1, 'mess_owner': 2 };
        var cards = document.querySelectorAll('.role-card');
        if (cards[roleMap[oldRole]]) cards[roleMap[oldRole]].classList.add('selected');
        // Jump to step 2 so errors are visible
        goStep(2);
    }
});
</script>
@endpush
