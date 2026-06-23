@extends('layouts.app')
@section('title', 'Reviews')

@push('styles')
<style>
.owner-wrapper { display:flex; min-height:calc(100vh - 65px); }
.owner-sidebar { width:240px; background:var(--bg-surface); border-right:1px solid var(--border-color); position:sticky; top:65px; height:calc(100vh - 65px); overflow-y:auto; flex-shrink:0; }
.owner-content { flex:1; padding:2rem; min-width:0; }
.rev-card { background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:1.1rem 1.25rem; margin-bottom:1rem; }
.rev-avatar { width:44px; height:44px; border-radius:50%; flex-shrink:0; object-fit:cover; }
.rev-verified { background:#D1FAE5; color:#065F46; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:20px; }
.rev-listing { font-size:0.76rem; color:var(--text-muted); background:var(--bg-subtle); border-radius:6px; padding:2px 8px; white-space:nowrap; }
.rev-reply { background:rgba(92,95,239,0.06); border-left:3px solid var(--brand-primary); border-radius:0 8px 8px 0; padding:0.7rem 0.9rem; margin-top:0.75rem; }
.rev-reply-label { font-size:0.76rem; font-weight:700; color:var(--brand-primary); margin:0 0 3px; }
.rev-reply-form { margin-top:0.6rem; }
.rev-reply-form summary { display:inline-flex; align-items:center; gap:6px; cursor:pointer; font-size:0.8rem; font-weight:600; color:var(--brand-primary); list-style:none; }
.rev-reply-form summary::-webkit-details-marker { display:none; }
</style>
@endpush

@section('content')
<div class="owner-wrapper">
    <!-- Sidebar -->
    <aside class="owner-sidebar">
        <div class="pt-3">
            <div class="sidebar-section-label">My Hostel</div>
            <a href="{{ route('owner.hostel.dashboard') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Overview</a>
            <a href="{{ route('owner.hostel.listings') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.listings*') ? 'active' : '' }}"><i class="bi bi-building"></i> My Hostels</a>
            <a href="{{ route('owner.hostel.bookings') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.bookings*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> Bookings
                @if($pendingBookings ?? 0)<span class="sidebar-badge">{{ $pendingBookings }}</span>@endif
            </a>
            <a href="{{ route('owner.hostel.reviews') }}" class="sidebar-item {{ request()->routeIs('owner.hostel.reviews*') ? 'active' : '' }}"><i class="bi bi-star"></i> Reviews</a>
            <div class="sidebar-section-label">Account</div>
            <a href="{{ route('owner.subscription') }}" class="sidebar-item {{ request()->routeIs('owner.subscription*') ? 'active' : '' }}">
                <i class="bi bi-credit-card"></i> Subscription
                @if(!auth()->user()->hasActiveSubscription())<span class="sidebar-badge" style="background:var(--danger);">!</span>@endif
            </a>
            <a href="{{ route('profile') }}" class="sidebar-item"><i class="bi bi-person"></i> Profile</a>
        </div>
    </aside>

    <div class="owner-content">
        <div class="page-header"><h1>Reviews</h1><p>See what guests are saying about your hostels and reply to their feedback.</p></div>

        <!-- Summary -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:rgba(245,158,11,0.1);color:#F59E0B;"><i class="bi bi-star-fill"></i></div>
                        <div><div class="stat-value">{{ number_format($stats['average'], 1) }}</div><div class="stat-label">Average Rating</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:rgba(92,95,239,0.1);color:#5C5FEF;"><i class="bi bi-chat-square-text"></i></div>
                        <div><div class="stat-value">{{ $stats['total'] }}</div><div class="stat-label">Total Reviews</div></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="stat-card">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon" style="background:rgba(16,185,129,0.1);color:#10B981;"><i class="bi bi-reply-fill"></i></div>
                        <div><div class="stat-value">{{ $stats['replied'] }}</div><div class="stat-label">Replied</div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        @forelse($reviews as $review)
        <div class="rev-card">
            <div class="d-flex gap-3">
                <img src="{{ $review->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=Deleted&background=9ca3af&color=fff' }}" class="rev-avatar" alt="avatar">
                <div style="flex:1;min-width:0;">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <strong style="font-size:0.9rem;">{{ $review->user?->name ?? 'Deleted user' }}</strong>
                        @if($review->is_verified)<span class="rev-verified">✓ Verified Stay</span>@endif
                        <span style="margin-left:auto;font-size:0.78rem;color:var(--text-muted);">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                        <span style="font-size:0.9rem;color:#F59E0B;">{{ str_repeat('★', $review->rating) }}<span style="color:var(--border-color);">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                        <span class="rev-listing"><i class="bi bi-building"></i> {{ $review->reviewable?->name ?? 'Deleted listing' }}</span>
                    </div>
                    <p style="font-size:0.88rem;color:var(--text-secondary);margin:8px 0 0;">{{ $review->body }}</p>

                    @if($review->owner_reply)
                    <div class="rev-reply">
                        <p class="rev-reply-label">Your reply{{ $review->owner_replied_at ? ' · '.$review->owner_replied_at->diffForHumans() : '' }}</p>
                        <p style="margin:0;font-size:0.85rem;color:var(--text-secondary);">{{ $review->owner_reply }}</p>
                    </div>
                    @endif

                    <details class="rev-reply-form">
                        <summary><i class="bi bi-reply"></i> {{ $review->owner_reply ? 'Edit reply' : 'Reply to this review' }}</summary>
                        <form method="POST" action="{{ route('owner.hostel.reviews.reply', $review->id) }}" style="margin-top:0.6rem;">
                            @csrf
                            <textarea name="owner_reply" rows="2" class="form-control" maxlength="1000" placeholder="Write a public reply…" required>{{ $review->owner_reply }}</textarea>
                            <button type="submit" class="btn-primary-findr mt-2" style="padding:0.4rem 1rem;font-size:0.82rem;">Post Reply</button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
        @empty
        <div class="card-findr p-5 text-center" style="color:var(--text-muted);">
            <i class="bi bi-star" style="font-size:3rem;margin-bottom:1rem;display:block;"></i>
            <h5 style="font-weight:700;">No reviews yet</h5>
            <p style="font-size:.88rem;">When guests review your hostels, their feedback will appear here.</p>
        </div>
        @endforelse

        <div style="margin-top:1rem;">{{ $reviews->links() }}</div>
    </div>
</div>
@endsection
