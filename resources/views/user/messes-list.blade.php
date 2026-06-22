@extends('layouts.app')
@section('title', 'All Messes')

@push('styles')
<style>
.filter-bar { background:var(--bg-surface);border-bottom:1px solid var(--border-color);padding:1rem 0;position:sticky;top:65px;z-index:100; }
.listing-card { background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-color);overflow:hidden;transition:all 0.25s;cursor:pointer;height:100%; }
.listing-card:hover { transform:translateY(-4px);box-shadow:var(--card-shadow-hover);border-color:transparent; }
.listing-card img { width:100%;height:190px;object-fit:cover; }
.slot-pill { display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:6px;font-size:0.68rem;font-weight:700; }
.slot-open   { background:#D1FAE5;color:#065F46; }
.slot-closed { background:var(--bg-subtle);color:var(--text-muted); }
</style>
@endpush

@section('content')

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="container">
    <form method="GET" action="{{ route('messes.index') }}" class="d-flex flex-wrap gap-2 align-items-center">
      <div style="position:relative;flex:1;min-width:200px;">
        <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
        <input type="text" name="q" class="form-control" style="padding-left:2.2rem;" placeholder="Search messes…" value="{{ request('q') }}">
      </div>
      <input type="text" name="city" class="form-control" style="max-width:160px;" placeholder="City" value="{{ request('city') }}">
      <select name="food_type" class="form-select" style="max-width:160px;">
        <option value="">All Food Types</option>
        <option value="veg"     {{ request('food_type')==='veg'     ? 'selected':'' }}>🥦 Pure Veg</option>
        <option value="non_veg" {{ request('food_type')==='non_veg' ? 'selected':'' }}>🍗 Non-Veg</option>
        <option value="both"    {{ request('food_type')==='both'    ? 'selected':'' }}>🍽️ Both</option>
      </select>
      <select name="slot" class="form-select" style="max-width:150px;">
        <option value="">Any Slot</option>
        <option value="morning"   {{ request('slot')==='morning'   ? 'selected':'' }}>☀️ Morning</option>
        <option value="afternoon" {{ request('slot')==='afternoon' ? 'selected':'' }}>🌤️ Afternoon</option>
        <option value="evening"   {{ request('slot')==='evening'   ? 'selected':'' }}>🌅 Evening</option>
        <option value="night"     {{ request('slot')==='night'     ? 'selected':'' }}>🌙 Night</option>
      </select>
      <label style="display:flex;align-items:center;gap:6px;font-size:0.85rem;white-space:nowrap;cursor:pointer;">
        <input type="checkbox" name="has_delivery" value="1" {{ request('has_delivery') ? 'checked':'' }} style="accent-color:var(--brand-primary);">
        🛵 Delivery
      </label>
      <button type="submit" class="btn-primary-findr" style="padding:0.6rem 1.25rem;">
        <i class="bi bi-funnel me-1"></i>Filter
      </button>
      @if(request()->hasAny(['q','city','food_type','slot','has_delivery']))
      <a href="{{ route('messes.index') }}" class="btn-outline-findr" style="padding:0.6rem 1rem;">Clear</a>
      @endif
    </form>
  </div>
</div>

<div class="container py-4">

  <!-- Header -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <h1 style="font-size:1.6rem;font-weight:800;margin:0;">All Messes</h1>
      <p style="color:var(--text-muted);font-size:0.88rem;margin:4px 0 0;">
        {{ $messes->total() }} mess{{ $messes->total() !== 1 ? 'es' : '' }} found
        @if(request('city')) in <strong>{{ request('city') }}</strong>@endif
      </p>
    </div>
    <a href="/" class="btn-outline-findr">
      <i class="bi bi-geo-alt me-1"></i>Search by Location
    </a>
  </div>

  @if($messes->isEmpty())
  <div style="text-align:center;padding:5rem 2rem;background:var(--bg-surface);border-radius:16px;border:1px solid var(--border-color);">
    <div style="font-size:3rem;margin-bottom:1rem;">🍽️</div>
    <h4 style="font-weight:700;margin-bottom:0.5rem;">No messes found</h4>
    <p style="color:var(--text-muted);">Try changing your filters or search term.</p>
    <a href="{{ route('messes.index') }}" class="btn-primary-findr" style="display:inline-block;margin-top:1rem;">View All Messes</a>
  </div>
  @else
  <div class="row g-4">
    @foreach($messes as $mess)
    @php
      $slotsOpen = [
        'morning'   => $mess->isSlotOpen('morning'),
        'afternoon' => $mess->isSlotOpen('afternoon'),
        'evening'   => $mess->isSlotOpen('evening'),
        'night'     => $mess->isSlotOpen('night'),
      ];
      $anyOpen = collect($slotsOpen)->contains(true);
    @endphp
    <div class="col-md-6 col-lg-4">
      <div class="listing-card" onclick="window.location='{{ route('messes.show', $mess->slug) }}'">
        <div style="position:relative;">
          <img src="{{ $mess->cover_image_url }}" alt="{{ $mess->name }}" loading="lazy">
          <span style="position:absolute;top:12px;left:12px;background:rgba(0,0,0,0.65);color:#fff;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:20px;text-transform:uppercase;">🍽️ Mess</span>
          <span style="position:absolute;top:12px;right:12px;background:{{ $mess->food_type==='veg'?'rgba(16,185,129,0.85)':'rgba(239,68,68,0.85)' }};color:#fff;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:20px;">
            {{ $mess->food_type==='veg' ? '🥦 Veg' : ($mess->food_type==='non_veg' ? '🍗 Non-Veg' : '🍽️ Both') }}
          </span>
          @if($anyOpen)
          <span style="position:absolute;bottom:10px;left:12px;background:rgba(16,185,129,0.9);color:#fff;font-size:0.7rem;font-weight:700;padding:3px 10px;border-radius:20px;">
            ● Open Now
          </span>
          @endif
        </div>

        <div style="padding:1rem 1.1rem 1.2rem;">
          <h6 style="font-weight:700;font-size:0.95rem;margin:0 0 4px;">{{ $mess->name }}</h6>
          <p style="font-size:0.78rem;color:var(--text-muted);margin:0 0 8px;">
            <i class="bi bi-geo-alt me-1"></i>{{ $mess->address }}, {{ $mess->city }}
          </p>

          <!-- Rating -->
          <div class="d-flex align-items-center gap-2 mb-2">
            <span style="color:#F59E0B;font-size:0.85rem;">
              {{ str_repeat('★', round($mess->average_rating ?? 0)) }}{{ str_repeat('☆', 5 - round($mess->average_rating ?? 0)) }}
            </span>
            <span style="font-size:0.78rem;color:var(--text-muted);">({{ $mess->total_reviews ?? 0 }} reviews)</span>
            @if($mess->has_delivery)
            <span style="margin-left:auto;background:rgba(16,185,129,0.1);color:var(--brand-accent);border-radius:20px;padding:2px 8px;font-size:0.7rem;font-weight:600;">🛵 Delivery</span>
            @endif
          </div>

          <!-- Slot Status -->
          <div class="d-flex gap-1 flex-wrap mb-3">
            @foreach(['morning'=>'☀️','afternoon'=>'🌤️','evening'=>'🌅','night'=>'🌙'] as $slot => $icon)
            <span class="slot-pill {{ $slotsOpen[$slot] ? 'slot-open' : 'slot-closed' }}">
              {{ $icon }} {{ ucfirst($slot) }}
            </span>
            @endforeach
          </div>

          <!-- Cheapest meal + CTA -->
          @php $cheapest = $mess->menus->sortBy('price')->first(); @endphp
          <div class="d-flex align-items-center justify-content-between mb-3">
            @if($cheapest)
            <div>
              <span style="font-size:0.72rem;color:var(--text-muted);">Meals from</span>
              <strong style="display:block;font-size:1.05rem;color:var(--brand-primary);">₹{{ number_format($cheapest->price) }}</strong>
            </div>
            @else
            <div></div>
            @endif
            <div style="font-size:0.78rem;color:var(--text-muted);">
              {{ $mess->subscriptionPlans->count() }} plan{{ $mess->subscriptionPlans->count() !== 1 ? 's' : '' }}
            </div>
          </div>

          <div class="d-flex gap-2">
            <a href="{{ route('messes.show', $mess->slug) }}"
               class="btn-primary-findr flex-grow-1 text-center" style="padding:0.5rem;font-size:0.84rem;"
               onclick="event.stopPropagation()">View Menu</a>
            <a href="https://wa.me/?text={{ urlencode('Check out ' . $mess->name . '! ' . route('messes.show', $mess->slug)) }}"
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
    {{ $messes->withQueryString()->links() }}
  </div>
  @endif
</div>
@endsection
