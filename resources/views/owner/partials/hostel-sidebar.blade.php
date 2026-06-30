{{-- Canonical hostel-owner sidebar. Inline styles keep it consistent on every page. --}}
<aside class="owner-sidebar" style="width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0;">
    <div class="pt-3">
        <div class="sidebar-section-label">My Hostel</div>
        <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Overview
        </a>
        <a href="{{ route('owner.hostel.listings') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.listings*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> My Hostels
        </a>
        @isset($hostel)
        <a href="{{ route('owner.hostel.rooms', $hostel->id) }}" class="sidebar-item {{ request()->routeIs('owner.hostel.rooms*') ? 'active' : '' }}">
            <i class="bi bi-door-open"></i> Rooms
        </a>
        @endisset
        <a href="{{ route('owner.hostel.bookings') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.bookings*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Residents
            @if(($pendingBookings ?? 0))<span class="sidebar-badge">{{ $pendingBookings }}</span>@endif
        </a>
        <a href="{{ route('owner.hostel.members') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.members*') ? 'active' : '' }}">
            <i class="bi bi-person-vcard"></i> Subscription
        </a>
        <a href="{{ route('owner.hostel.assets') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.assets*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> Assets
        </a>
        <a href="{{ route('owner.hostel.reviews') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.reviews*') ? 'active' : '' }}">
            <i class="bi bi-star"></i> Reviews
        </a>
        <div class="sidebar-section-label">Account</div>
        <a href="{{ route('owner.subscription') }}" class="sidebar-item {{ request()->routeIs('owner.subscription*') ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Billing
            @if(!auth()->user()->hasActiveSubscription())<span class="sidebar-badge" style="background:var(--danger);">!</span>@endif
        </a>
        <a href="{{ route('profile') }}" class="sidebar-item {{ request()->routeIs('profile') ? 'active' : '' }}">
            <i class="bi bi-person"></i> Profile
        </a>
    </div>
</aside>
