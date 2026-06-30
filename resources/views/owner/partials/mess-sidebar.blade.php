{{-- Canonical mess-owner sidebar. Inline styles keep it consistent on every page. --}}
<aside class="owner-sidebar" style="width:240px;background:var(--bg-surface);border-right:1px solid var(--border-color);position:sticky;top:65px;height:calc(100vh - 65px);overflow-y:auto;flex-shrink:0;">
    <div class="pt-3">
        <div class="sidebar-section-label">My Mess</div>
        <a href="{{ route('owner.mess.dashboard') }}" class="sidebar-item {{ request()->routeIs('owner.mess.dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> Overview
        </a>
        <a href="{{ route('owner.mess.listings') }}" class="sidebar-item {{ request()->routeIs('owner.mess.listings*') ? 'active' : '' }}">
            <i class="bi bi-egg-fried"></i> My Messes
        </a>
        <a href="{{ route('owner.mess.menus') }}" class="sidebar-item {{ request()->routeIs('owner.mess.menus*') ? 'active' : '' }}">
            <i class="bi bi-menu-button-wide"></i> Food Menus
        </a>
        <a href="{{ route('owner.mess.bookings') }}" class="sidebar-item {{ request()->routeIs('owner.mess.bookings*') ? 'active' : '' }}">
            <i class="bi bi-calendar-check"></i> Subscribers
            @if(($pendingBookings ?? 0))<span class="sidebar-badge">{{ $pendingBookings }}</span>@endif
        </a>
        <a href="{{ route('owner.mess.members') }}" class="sidebar-item {{ request()->routeIs('owner.mess.members*') ? 'active' : '' }}">
            <i class="bi bi-person-vcard"></i> Subscription
        </a>
        <a href="{{ route('owner.mess.reviews') }}" class="sidebar-item {{ request()->routeIs('owner.mess.reviews*') ? 'active' : '' }}">
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
