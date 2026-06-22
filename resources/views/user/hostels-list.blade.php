@extends('layouts.app')
@section('title', 'All Hostels')

@push('styles')
<style>
.filter-bar { background:var(--bg-surface);border-bottom:1px solid var(--border-color);padding:1rem 0;position:sticky;top:65px;z-index:100; }
.listing-card { background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-color);overflow:hidden;transition:all 0.25s;cursor:pointer;height:100%; }
.listing-card:hover { transform:translateY(-4px);box-shadow:var(--card-shadow-hover);border-color:transparent; }
.listing-card img { width:100%;height:190px;object-fit:cover; }
.badge-type { position:absolute;top:12px;left:12px;background:rgba(0,0,0,0.65);backdrop-filter:blur(6px);color:#fff;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase; }
.facility-chip { display:inline-flex;align-items:center;gap:4px;background:var(--bg-subtle);border-radius:6px;padding:3px 8px;font-size:0.7rem;font-weight:600;color:var(--text-secondary); }
</style>
@endpush

@section('content')

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="container">
    <form method="GET" action="{{ route('hostels.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
      <div style="position:relative;flex:1;min-width:200px;">
        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
        <input type="text" name="q" class="form-control" style="padding-left:2.2rem;" placeholder="Search hostels…" value="{{ request('q') }}">
      </div>
      <input type="text" name="city" class="form-control" style="max-width:160px;" placeholder="City" value="{{ request('city') }}">
      <select name="gender_type" class="form-select" style="max-width:140px;">
        <option value="">All Types</option>
        <option value="boys"  {{ request('gender_type')==='boys'  ? 'selected':'' }}>🚹 Boys</option>
        <option value="girls" {{ request('gender_type')==='girls' ? 'selected':'' }}>🚺 Girls</option>
        <option value="coed"  {{ request('gender_type')==='coed'  ? 'selected':'' }}>👥 Co-ed</option>
      </select>
      <select name="sort" class="form-select" style="max-width:160px;">
        <option value="featured" {{ request('sort')==='featured' ? 'selected':'' }}>Featured First</option>
        <option value="rating"   {{ request('sort')==='rating'   ? 'selected':'' }}>Top Rated</option>
        <option value="newest"   {{ request('sort')==='newest'   ? 'selected':'' }}>Newest</option>
      </select>
      <button type="submit" class="btn-primary-findr" style="padding:0.6rem 1.25rem;">
        <i class="bi bi-funnel me-1"></i>Filter
      </button>
      @if(request()->hasAny(['q','city','gender_type','sort']))
      <a href="{{ route('hostels.index') }}" class="btn-outline-findr" style="padding:0.6rem 1rem;">Clear</a>
      @endif
    </form>
  </div>
</div>

<div class="container py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;margin:0;">All Hostels</h1>
      <p style="color:var(--text-muted);font-size:0.88rem;margin:4px 0 0;">
        {{ $hostels->total() }} hostel{{ $hostels->total() !== 1 ? 's' : '' }} found
        @if(request('city')) in <strong>{{ request('city') }}</strong>@endif
      </p>
    </div>
    <a href="/" class="btn-outline-findr">
      <i class="bi bi-geo-alt me-1"></i>Search by Location
    </a>
  </div>

  @if($hostels->isEmpty())
  <div style="text-align:center;padding:5rem 2rem;background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-color);">
    <div style="font-size:3rem;margin-bottom:1rem;">🏠</div>
    <h4 style="font-weight:700;margin-bottom:0.5rem;">No hostels found</h4>
    <p style="color:var(--text-muted);">Try changing your search or filters.</p>
    <a href="{{ route('hostels.index') }}" class="btn-primary-findr" style="display:inline-block;margin-top:1rem;">View All Hostels</a>
  </div>
  @else
  <div class="row g-4">
    @foreach($hostels as $hostel)
    <div class="col-md-6 col-lg-4">
      <div class="listing-card" onclick="window.location='{{ route('hostels.show', $hostel->slug) }}'">
        <div style="position:relative;">
          <img src="{{ $hostel->cover_image_url }}" alt="{{ $hostel->name }}" loading="lazy">
          <span class="badge-type">🏠 Hostel</span>
          @if($hostel->is_featured)
          <span style="position:absolute;top:12px;right:12px;background:var(--brand-secondary);color:#fff;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:20px;">⭐ Featured</span>
          @endif
        </div>
        <div style="padding:1rem 1.1rem 1.2rem;">
          <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
            <h6 style="font-weight:700;font-size:0.95rem;margin:0;line-height:1.3;">{{ $hostel->name }}</h6>
            <span style="background:var(--bg-subtle);border-radius:6px;padding:2px 7px;font-size:0.72rem;font-weight:600;color:var(--text-secondary);white-space:nowrap;flex-shrink:0;">
              {{ $hostel->gender_type === 'boys' ? '🚹 Boys' : ($hostel->gender_type === 'girls' ? '🚺 Girls' : '👥 Co-ed') }}
            </span>
          </div>
          <p style="font-size:0.78rem;color:var(--text-muted);margin:0 0 8px;">
            <i class="bi bi-geo-alt me-1"></i>{{ $hostel->address }}, {{ $hostel->city }}
          </p>

          <!-- Rating -->
          <div class="d-flex align-items-center gap-2 mb-2">
            <span style="color:#F59E0B;font-size:0.85rem;">
              {{ str_repeat('★', round($hostel->average_rating ?? 0)) }}{{ str_repeat('☆', 5 - round($hostel->average_rating ?? 0)) }}
            </span>
            <span style="font-size:0.78rem;color:var(--text-muted);">({{ $hostel->total_reviews ?? 0 }} reviews)</span>
          </div>

          <!-- Facilities -->
          <div class="d-flex flex-wrap gap-1 mb-3">
            @if($hostel->has_wifi)    <span class="facility-chip">📶 Wi-Fi</span>@endif
            @if($hostel->has_ac)     <span class="facility-chip">❄️ AC</span>@endif
            @if($hostel->has_cctv)   <span class="facility-chip">📷 CCTV</span>@endif
            @if($hostel->has_parking)<span class="facility-chip">🅿️ Parking</span>@endif
            @if($hostel->has_mess)   <span class="facility-chip">🍽️ Mess</span>@endif
          </div>

          <!-- Price + CTA -->
          <div class="d-flex align-items-center justify-content-between">
            <div>
              <span style="font-size:0.72rem;color:var(--text-muted);">Starting from</span>
              <strong style="display:block;font-size:1.05rem;color:var(--brand-primary);">
                ₹{{ number_format($hostel->rooms->min('price_per_month') ?? 0) }}<span style="font-size:0.72rem;font-weight:400;color:var(--text-muted);">/mo</span>
              </strong>
            </div>
            <div style="font-size:0.78rem;color:{{ ($hostel->rooms->sum('available_count') ?? 0) > 0 ? 'var(--brand-accent)' : 'var(--danger)' }};font-weight:600;">
              {{ ($hostel->rooms->sum('available_count') ?? 0) > 0 ? ($hostel->rooms->sum('available_count') . ' rooms free') : 'No vacancy' }}
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <a href="{{ route('hostels.show', $hostel->slug) }}"
               class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;font-size:0.84rem;"
               onclick="event.stopPropagation()">View Details</a>
            <a href="https://wa.me/?text={{ urlencode('Check out ' . $hostel->name . '! ' . route('hostels.show', $hostel->slug)) }}"
               target="_blank"
               style="background:#25D366;color:#fff;border-radius:10px;padding:0.5rem 0.75rem;display:flex;align-items:center;font-size:0.82rem;font-weight:600;text-decoration:none;"
               onclick="event.stopPropagation()">💬</a>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>

  <!-- Pagination -->
  <div style="margin-top:2rem;">
    {{ $hostels->withQueryString()->links() }}
  </div>
  @endif
</div>
@endsection
