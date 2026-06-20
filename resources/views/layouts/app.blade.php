<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Hostel & Mess Finder') — SolMate</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
    /* ═══════════════════════════════════════════
       DESIGN TOKENS — Light Mode (default)
    ═══════════════════════════════════════════ */
    :root,
    [data-theme="light"] {
        --brand-primary:   #5C5FEF;
        --brand-secondary: #F97316;
        --brand-accent:    #10B981;
        --bg-base:         #F8F9FF;
        --bg-surface:      #FFFFFF;
        --bg-elevated:     #FFFFFF;
        --bg-subtle:       #F0F1FF;
        --border-color:    #E4E6F0;
        --border-focus:    #5C5FEF;
        --text-primary:    #0F0F23;
        --text-secondary:  #4B5563;
        --text-muted:      #9CA3AF;
        --text-inverse:    #FFFFFF;
        --nav-bg:          rgba(255,255,255,0.95);
        --nav-shadow:      0 1px 0 rgba(0,0,0,0.06);
        --card-shadow:     0 1px 3px rgba(0,0,0,0.04), 0 4px 16px rgba(0,0,0,0.06);
        --card-shadow-hover:0 4px 12px rgba(92,95,239,0.12), 0 16px 40px rgba(0,0,0,0.1);
        --input-bg:        #FFFFFF;
        --input-border:    #D1D5DB;
        --badge-veg:       #D1FAE5;
        --badge-veg-text:  #065F46;
        --badge-nonveg:    #FEE2E2;
        --badge-nonveg-text:#991B1B;
        --success:         #10B981;
        --warning:         #F59E0B;
        --danger:          #EF4444;
        --info:            #3B82F6;
        --sidebar-width:   260px;
    }

    [data-theme="dark"] {
        --brand-primary:   #818CF8;
        --brand-secondary: #FB923C;
        --brand-accent:    #34D399;
        --bg-base:         #0D0D1A;
        --bg-surface:      #161628;
        --bg-elevated:     #1E1E38;
        --bg-subtle:       #1A1A2E;
        --border-color:    #2A2A4A;
        --border-focus:    #818CF8;
        --text-primary:    #F1F5F9;
        --text-secondary:  #94A3B8;
        --text-muted:      #64748B;
        --text-inverse:    #0F0F23;
        --nav-bg:          rgba(22,22,40,0.97);
        --nav-shadow:      0 1px 0 rgba(255,255,255,0.04);
        --card-shadow:     0 1px 3px rgba(0,0,0,0.3), 0 4px 16px rgba(0,0,0,0.4);
        --card-shadow-hover:0 4px 12px rgba(129,140,248,0.2), 0 16px 40px rgba(0,0,0,0.5);
        --input-bg:        #1E1E38;
        --input-border:    #2A2A4A;
        --badge-veg:       #064E3B;
        --badge-veg-text:  #6EE7B7;
        --badge-nonveg:    #7F1D1D;
        --badge-nonveg-text:#FCA5A5;
        --success:         #34D399;
        --warning:         #FBBF24;
        --danger:          #F87171;
        --info:            #60A5FA;
    }

    /* ═══════════════════════════════════════════
       BASE
    ═══════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    html { scroll-behavior: smooth; }

    body {
        font-family: 'Inter', sans-serif;
        font-size: 15px;
        line-height: 1.6;
        background: var(--bg-base);
        color: var(--text-primary);
        transition: background 0.25s ease, color 0.25s ease;
        min-height: 100vh;
    }

    h1,h2,h3,h4,h5,h6,
    .display-1,.display-2,.display-3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        color: var(--text-primary);
        font-weight: 700;
    }

    a { color: var(--brand-primary); text-decoration: none; }
    a:hover { color: var(--brand-primary); text-decoration: underline; }

    /* ═══════════════════════════════════════════
       NAVBAR
    ═══════════════════════════════════════════ */
    .main-navbar {
        background: var(--nav-bg);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border-color);
        box-shadow: var(--nav-shadow);
        position: sticky;
        top: 0;
        z-index: 1030;
        transition: background 0.25s ease, border-color 0.25s ease;
    }

    .navbar-brand {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.4rem;
        color: var(--brand-primary) !important;
        letter-spacing: -0.5px;
    }
    .navbar-brand span { color: var(--brand-secondary); }

    .nav-link {
        color: var(--text-secondary) !important;
        font-weight: 500;
        font-size: 0.9rem;
        padding: 0.5rem 0.85rem !important;
        border-radius: 8px;
        transition: all 0.15s ease;
    }
    .nav-link:hover, .nav-link.active {
        color: var(--brand-primary) !important;
        background: var(--bg-subtle);
    }

    .theme-toggle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        border: 1.5px solid var(--border-color);
        background: var(--bg-surface);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 1.1rem;
    }
    .theme-toggle:hover {
        border-color: var(--brand-primary);
        color: var(--brand-primary);
        transform: scale(1.05);
    }

    /* ═══════════════════════════════════════════
       SIDEBAR (Dashboard layouts)
    ═══════════════════════════════════════════ */
    .app-wrapper {
        display: flex;
        min-height: calc(100vh - 65px);
    }

    .sidebar {
        width: var(--sidebar-width);
        background: var(--bg-surface);
        border-right: 1px solid var(--border-color);
        position: sticky;
        top: 65px;
        height: calc(100vh - 65px);
        overflow-y: auto;
        flex-shrink: 0;
        transition: background 0.25s ease, border-color 0.25s ease;
    }

    .sidebar::-webkit-scrollbar { width: 4px; }
    .sidebar::-webkit-scrollbar-track { background: transparent; }
    .sidebar::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }

    .sidebar-section-label {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: var(--text-muted);
        padding: 1.25rem 1.25rem 0.4rem;
    }

    .sidebar-item {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.6rem 1.25rem;
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 0.88rem;
        border-radius: 8px;
        margin: 0.1rem 0.6rem;
        transition: all 0.15s ease;
        cursor: pointer;
    }
    .sidebar-item:hover {
        background: var(--bg-subtle);
        color: var(--brand-primary);
        text-decoration: none;
    }
    .sidebar-item.active {
        background: rgba(92,95,239,0.1);
        color: var(--brand-primary);
        font-weight: 600;
    }
    [data-theme="dark"] .sidebar-item.active { background: rgba(129,140,248,0.12); }
    .sidebar-item .bi { font-size: 1rem; flex-shrink: 0; }
    .sidebar-badge {
        margin-left: auto;
        background: var(--brand-secondary);
        color: #fff;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 1px 6px;
        border-radius: 20px;
    }

    .main-content {
        flex: 1;
        min-width: 0;
        padding: 2rem;
    }

    /* ═══════════════════════════════════════════
       CARDS
    ═══════════════════════════════════════════ */
    .card-findr {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        transition: all 0.25s ease;
    }
    .card-findr:hover {
        box-shadow: var(--card-shadow-hover);
        transform: translateY(-2px);
        border-color: transparent;
    }

    .card-findr .card-img {
        width: 100%;
        aspect-ratio: 16/10;
        object-fit: cover;
        display: block;
    }

    .card-findr .card-body-findr {
        padding: 1.1rem 1.25rem 1.25rem;
    }

    /* Stats card */
    .stat-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 1.25rem 1.5rem;
        box-shadow: var(--card-shadow);
        transition: all 0.2s ease;
    }
    .stat-card:hover { box-shadow: var(--card-shadow-hover); }
    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .stat-card .stat-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-primary);
    }
    .stat-card .stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 2px;
    }
    .stat-card .stat-change {
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 4px;
    }

    /* ═══════════════════════════════════════════
       FORMS
    ═══════════════════════════════════════════ */
    .form-control, .form-select {
        background: var(--input-bg);
        border: 1.5px solid var(--input-border);
        color: var(--text-primary);
        border-radius: 10px;
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        transition: all 0.2s ease;
    }
    .form-control:focus, .form-select:focus {
        background: var(--input-bg);
        border-color: var(--border-focus);
        box-shadow: 0 0 0 3px rgba(92,95,239,0.12);
        color: var(--text-primary);
        outline: none;
    }
    .form-control::placeholder { color: var(--text-muted); }
    .form-label {
        font-size: 0.83rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.4rem;
    }

    /* ═══════════════════════════════════════════
       BUTTONS
    ═══════════════════════════════════════════ */
    .btn-primary-findr {
        background: var(--brand-primary);
        color: #fff;
        border: none;
        border-radius: 10px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-primary-findr:hover {
        background: #4749d1;
        transform: translateY(-1px);
        box-shadow: 0 4px 16px rgba(92,95,239,0.35);
    }
    .btn-outline-findr {
        background: transparent;
        color: var(--brand-primary);
        border: 1.5px solid var(--brand-primary);
        border-radius: 10px;
        padding: 0.6rem 1.4rem;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .btn-outline-findr:hover {
        background: var(--brand-primary);
        color: #fff;
    }

    /* ═══════════════════════════════════════════
       BADGES
    ═══════════════════════════════════════════ */
    .badge-veg {
        background: var(--badge-veg);
        color: var(--badge-veg-text);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
    }
    .badge-nonveg {
        background: var(--badge-nonveg);
        color: var(--badge-nonveg-text);
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 20px;
    }
    .badge-status {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
    }
    .badge-active  { background: #D1FAE5; color: #065F46; }
    .badge-pending { background: #FEF3C7; color: #92400E; }
    .badge-rejected{ background: #FEE2E2; color: #991B1B; }
    .badge-inactive{ background: #F3F4F6; color: #6B7280; }
    [data-theme="dark"] .badge-active  { background: #064E3B; color: #6EE7B7; }
    [data-theme="dark"] .badge-pending { background: #78350F; color: #FDE68A; }
    [data-theme="dark"] .badge-rejected{ background: #7F1D1D; color: #FCA5A5; }
    [data-theme="dark"] .badge-inactive{ background: #1F2937; color: #9CA3AF; }

    /* ═══════════════════════════════════════════
       RATING STARS
    ═══════════════════════════════════════════ */
    .stars { color: #F59E0B; font-size: 0.8rem; letter-spacing: -1px; }
    .rating-text { font-size: 0.78rem; color: var(--text-muted); font-weight: 500; }

    /* ═══════════════════════════════════════════
       TABLES
    ═══════════════════════════════════════════ */
    .table-findr {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }
    .table-findr thead th {
        background: var(--bg-subtle);
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-color);
    }
    .table-findr thead th:first-child { border-radius: 10px 0 0 0; }
    .table-findr thead th:last-child  { border-radius: 0 10px 0 0; }
    .table-findr tbody td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border-color);
        color: var(--text-primary);
        font-size: 0.88rem;
        vertical-align: middle;
    }
    .table-findr tbody tr:last-child td { border-bottom: none; }
    .table-findr tbody tr:hover td { background: var(--bg-subtle); }

    /* ═══════════════════════════════════════════
       ALERTS / TOASTS
    ═══════════════════════════════════════════ */
    .toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 9999; }
    .toast-findr {
        background: var(--bg-elevated);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        padding: 1rem 1.25rem;
        min-width: 280px;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        animation: slideUp 0.3s ease;
        color: var(--text-primary);
    }
    @keyframes slideUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

    /* ═══════════════════════════════════════════
       SLOT STATUS PILL
    ═══════════════════════════════════════════ */
    .slot-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .slot-open   { background: #D1FAE5; color: #065F46; }
    .slot-closed { background: #F3F4F6; color: #6B7280; }
    [data-theme="dark"] .slot-open   { background: #064E3B; color: #6EE7B7; }
    [data-theme="dark"] .slot-closed { background: #1F2937; color: #9CA3AF; }
    .slot-dot {
        width: 6px; height: 6px;
        border-radius: 50%;
        display: inline-block;
    }
    .slot-open .slot-dot   { background: #10B981; }
    .slot-closed .slot-dot { background: #9CA3AF; }

    /* ═══════════════════════════════════════════
       PAGE HEADER
    ═══════════════════════════════════════════ */
    .page-header {
        margin-bottom: 1.75rem;
        padding-bottom: 1.25rem;
        border-bottom: 1px solid var(--border-color);
    }
    .page-header h1 {
        font-size: 1.6rem;
        font-weight: 800;
        margin: 0;
    }
    .page-header p {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 4px 0 0;
    }

    /* ═══════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════ */
    @media (max-width: 768px) {
        .sidebar { display: none; }
        .main-content { padding: 1rem; }
        :root { --sidebar-width: 0px; }
    }

    /* ═══════════════════════════════════════════
       MISC UTILITIES
    ═══════════════════════════════════════════ */
    .section-divider {
        border: none;
        border-top: 1px solid var(--border-color);
        margin: 1.5rem 0;
    }
    .text-brand  { color: var(--brand-primary); }
    .text-muted-findr { color: var(--text-muted); }
    .bg-surface  { background: var(--bg-surface); }
    .bg-subtle   { background: var(--bg-subtle); }
    .border-findr{ border: 1px solid var(--border-color); }

    .avatar-sm {
        width: 36px; height: 36px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--border-color);
    }
    .avatar-md {
        width: 48px; height: 48px;
        border-radius: 50%;
        object-fit: cover;
    }
    </style>

    @stack('styles')
</head>
<body>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav class="main-navbar">
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between" style="height:65px;">

            <!-- Brand -->
            <a class="navbar-brand" href="{{ route('home') }}">
                SolMate<span></span>
            </a>

            <!-- Center Nav (desktop) -->
            <div class="d-none d-md-flex align-items-center gap-1">
                <a href="{{ route('home') }}"    class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
                    <i class="bi bi-house-door me-1"></i>Home
                </a>
                <a href="{{ route('hostels.index') }}" class="nav-link {{ request()->routeIs('hostels.*') ? 'active' : '' }}">
                    <i class="bi bi-building me-1"></i>Hostels
                </a>
                <a href="{{ route('messes.index') }}" class="nav-link {{ request()->routeIs('messes.*') ? 'active' : '' }}">
                    <i class="bi bi-egg-fried me-1"></i>Messes
                </a>
            </div>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-2">

                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" title="Toggle theme">
                    <i class="bi bi-sun-fill" id="themeIcon"></i>
                </button>

                @auth
                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="theme-toggle" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-2" style="min-width:300px; background:var(--bg-surface); border-color:var(--border-color);">
                            <p class="text-center py-3" style="color:var(--text-muted); font-size:0.85rem;">No new notifications</p>
                        </div>
                    </div>

                    <!-- User Menu -->
                    <div class="dropdown">
                        <button class="d-flex align-items-center gap-2 border-0 bg-transparent" data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url }}" alt="avatar" class="avatar-sm">
                            <span class="d-none d-md-block" style="font-size:0.85rem; font-weight:600; color:var(--text-primary);">
                                {{ auth()->user()->name }}
                            </span>
                            <i class="bi bi-chevron-down" style="font-size:0.7rem; color:var(--text-muted);"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" style="background:var(--bg-surface); border-color:var(--border-color); border-radius:12px; box-shadow:var(--card-shadow); min-width:200px;">
                            <li class="px-3 py-2">
                                <small style="color:var(--text-muted); font-size:0.72rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">
                                    {{ ucfirst(str_replace('_',' ',auth()->user()->role)) }}
                                </small>
                                <p style="font-weight:600; color:var(--text-primary); font-size:0.88rem; margin:0;">{{ auth()->user()->email }}</p>
                            </li>
                            <li><hr style="border-color:var(--border-color); margin:4px 0;"></li>
                            @if(auth()->user()->isAdmin())
                            <li><a class="dropdown-item" href="{{ route('admin.dashboard') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-speedometer2 me-2"></i>Admin Panel</a></li>
                            @elseif(auth()->user()->isHostelOwner())
                            <li><a class="dropdown-item" href="{{ route('owner.hostel.dashboard') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                            @elseif(auth()->user()->isMessOwner())
                            <li><a class="dropdown-item" href="{{ route('owner.mess.dashboard') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-grid me-2"></i>Dashboard</a></li>
                            @else
                            <li><a class="dropdown-item" href="{{ route('user.bookings') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-calendar-check me-2"></i>My Bookings</a></li>
                            <li><a class="dropdown-item" href="{{ route('user.favourites') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-heart me-2"></i>Favourites</a></li>
                            @endif
                            <li><a class="dropdown-item" href="{{ route('profile') }}" style="color:var(--text-primary); border-radius:8px;"><i class="bi bi-person me-2"></i>Profile</a></li>
                            <li><hr style="border-color:var(--border-color); margin:4px 0;"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item" style="color:var(--danger); border-radius:8px;"><i class="bi bi-box-arrow-right me-2"></i>Sign Out</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn-outline-findr d-none d-md-inline-flex align-items-center gap-1" style="padding: 0.45rem 1.1rem;">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="btn-primary-findr d-none d-md-inline-flex align-items-center gap-1" style="padding: 0.45rem 1.1rem;">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </div>
</nav>

<!-- ═══════════════ CONTENT ═══════════════ -->
<main>
    @yield('content')
</main>

<!-- ═══════════════ TOAST ═══════════════ -->
<div class="toast-container" id="toastContainer"></div>

<!-- ═══════════════ FOOTER ═══════════════ -->
@unless(request()->routeIs('admin.*') || request()->routeIs('owner.*'))
<footer style="background:var(--bg-surface); border-top:1px solid var(--border-color); margin-top:4rem; padding:3rem 0 2rem;">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <p style="font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.3rem; color:var(--brand-primary);">SolMate<span style="color:var(--brand-secondary);">.</span></p>
                <p style="color:var(--text-muted); font-size:0.88rem; margin-top:0.5rem;">Discover and book the best hostels and mess services near you.</p>
            </div>
            <div class="col-md-2">
                <p style="font-weight:700; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted);">Explore</p>
                <ul style="list-style:none; padding:0;">
                    <li><a href="{{ route('hostels.index') }}" style="color:var(--text-secondary); font-size:0.88rem; display:block; padding:4px 0;">Hostels</a></li>
                    <li><a href="{{ route('messes.index') }}" style="color:var(--text-secondary); font-size:0.88rem; display:block; padding:4px 0;">Messes</a></li>
                </ul>
            </div>
            <div class="col-md-2">
                <p style="font-weight:700; font-size:0.8rem; text-transform:uppercase; letter-spacing:0.08em; color:var(--text-muted);">For Owners</p>
                <ul style="list-style:none; padding:0;">
                    <li><a href="{{ route('register') }}" style="color:var(--text-secondary); font-size:0.88rem; display:block; padding:4px 0;">List Your Hostel</a></li>
                    <li><a href="{{ route('register') }}" style="color:var(--text-secondary); font-size:0.88rem; display:block; padding:4px 0;">List Your Mess</a></li>
                </ul>
            </div>
        </div>
        <hr style="border-color:var(--border-color); margin:2rem 0 1rem;">
        <p style="color:var(--text-muted); font-size:0.82rem; text-align:center;">&copy; {{ date('Y') }} SolMate All rights reserved.</p>
    </div>
</footer>
@endunless

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
// ═══════════════════════════════════════
// THEME MANAGER
// ═══════════════════════════════════════
const ThemeManager = {
    key: 'hostelfindr_theme',

    init() {
        const saved = localStorage.getItem(this.key) ||
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        this.apply(saved);

        document.getElementById('themeToggle').addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            this.apply(current === 'dark' ? 'light' : 'dark');
        });
    },

    apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(this.key, theme);
        const icon = document.getElementById('themeIcon');
        if (icon) icon.className = theme === 'dark' ? 'bi bi-moon-fill' : 'bi bi-sun-fill';
    }
};
ThemeManager.init();

// ═══════════════════════════════════════
// TOAST UTILITY
// ═══════════════════════════════════════
function showToast(message, type = 'success') {
    const icons = { success: 'check-circle-fill', danger: 'x-circle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
    const colors = { success: '#10B981', danger: '#EF4444', warning: '#F59E0B', info: '#3B82F6' };
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = 'toast-findr';
    toast.innerHTML = `<i class="bi bi-${icons[type]||icons.success}" style="color:${colors[type]||colors.success}; font-size:1.2rem; flex-shrink:0;"></i><span style="font-size:0.88rem; font-weight:500;">${message}</span>`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity 0.3s'; setTimeout(() => toast.remove(), 300); }, 3500);
}

// ═══════════════════════════════════════
// CSRF for Axios
// ═══════════════════════════════════════
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;
axios.defaults.headers.common['Accept'] = 'application/json';
</script>

@stack('scripts')
</body>
</html>
