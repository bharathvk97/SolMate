@extends('layouts.app')

@section('content')
<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="pt-3 pb-4">

            <div class="sidebar-section-label">Overview</div>
            <a href="{{ route('admin.dashboard') }}" class="sidebar-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>

            <div class="sidebar-section-label">Users</div>
            <a href="{{ route('admin.users') }}" class="sidebar-item {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> All Users
            </a>
            <a href="{{ route('admin.identity') }}" class="sidebar-item {{ request()->routeIs('admin.identity*') ? 'active' : '' }}">
                <i class="bi bi-shield-check"></i> Identity Verification
                @if($pendingIdentity ?? 0)
                <span class="sidebar-badge">{{ $pendingIdentity }}</span>
                @endif
            </a>

            <div class="sidebar-section-label">Listings</div>
            <a href="{{ route('admin.hostels') }}" class="sidebar-item {{ request()->routeIs('admin.hostels*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Hostels
                @if($pendingHostels ?? 0)
                <span class="sidebar-badge">{{ $pendingHostels }}</span>
                @endif
            </a>
            <a href="{{ route('admin.messes') }}" class="sidebar-item {{ request()->routeIs('admin.messes*') ? 'active' : '' }}">
                <i class="bi bi-egg-fried"></i> Messes
                @if($pendingMesses ?? 0)
                <span class="sidebar-badge">{{ $pendingMesses }}</span>
                @endif
            </a>

            <div class="sidebar-section-label">Business</div>
            <a href="{{ route('admin.subscriptions') }}" class="sidebar-item {{ request()->routeIs('admin.subscriptions*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Subscriptions
            </a>
            <a href="{{ route('admin.bookings') }}" class="sidebar-item {{ request()->routeIs('admin.bookings*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Bookings
            </a>

            <div class="sidebar-section-label">Content</div>
            <a href="{{ route('admin.reviews') }}" class="sidebar-item {{ request()->routeIs('admin.reviews*') ? 'active' : '' }}">
                <i class="bi bi-star"></i> Reviews
            </a>

            <div class="sidebar-section-label">System</div>
            <a href="{{ route('admin.settings') }}" class="sidebar-item {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">
        @yield('admin-content')
    </div>
</div>
@endsection
