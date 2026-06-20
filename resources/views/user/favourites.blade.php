@extends('layouts.app')
@section('title', 'My Saved Places')

@section('content')
<div class="container py-5">
  <div class="page-header">
    <h1>Saved Places</h1>
    <p>Hostels and messes you've bookmarked.</p>
  </div>

  @if($favs->isEmpty())
  <div style="text-align:center;padding:5rem 2rem;color:var(--text-muted);">
    <div style="font-size:3.5rem;margin-bottom:1rem;">🤍</div>
    <h4 style="font-weight:700;margin-bottom:.5rem;">Nothing saved yet</h4>
    <p style="font-size:.9rem;">Tap the heart icon on any listing to save it here.</p>
    <a href="/" class="btn-primary-findr" style="display:inline-block;margin-top:1rem;">Start Exploring</a>
  </div>
  @else
  <div class="row g-3">
    @foreach($favs as $fav)
    @php $item = $fav->favourable; @endphp
    @if(!$item) @continue @endif
    <div class="col-md-6 col-lg-4">
      <div class="listing-card" onclick="window.location='/{{ $fav->favourable_type==='App\\Models\\Hostel'?'hostels':'messes' }}/{{ $item->slug }}'">
        <div style="position:relative;">
          <img src="{{ $item->cover_image_url ?? '/images/'.($fav->favourable_type==='App\\Models\\Hostel'?'hostel':'mess').'-placeholder.jpg' }}"
               alt="{{ $item->name }}" style="width:100%;height:180px;object-fit:cover;">
          <span style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,.6);color:#fff;font-size:.7rem;font-weight:700;padding:3px 9px;border-radius:20px;text-transform:uppercase;">
            {{ $fav->favourable_type==='App\Models\Hostel' ? 'Hostel' : 'Mess' }}
          </span>
          <form method="POST" action="/api/v1/favourites" style="position:absolute;top:8px;right:8px;margin:0;" onsubmit="removeFav(event,this,{{ $fav->id }})">
            @csrf
            <input type="hidden" name="type" value="{{ $fav->favourable_type==='App\\Models\\Hostel'?'hostel':'mess' }}">
            <input type="hidden" name="id" value="{{ $item->id }}">
            <button type="submit" style="width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.9);border:none;cursor:pointer;color:#EF4444;display:flex;align-items:center;justify-content:center;font-size:1rem;">
              <i class="bi bi-heart-fill"></i>
            </button>
          </form>
        </div>
        <div style="padding:1rem;">
          <h6 style="font-weight:700;margin:0;font-size:.92rem;">{{ $item->name }}</h6>
          <p style="font-size:.78rem;color:var(--text-muted);margin:3px 0 6px;">{{ $item->city }}, {{ $item->state }}</p>
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-1">
              <span style="color:#F59E0B;font-size:.85rem;">★</span>
              <span style="font-size:.85rem;font-weight:600;">{{ number_format($item->average_rating??0,1) }}</span>
              <span style="font-size:.78rem;color:var(--text-muted);">({{ $item->total_reviews??0 }})</span>
            </div>
            <span style="font-size:.72rem;color:var(--text-muted);">Saved {{ $fav->created_at->diffForHumans() }}</span>
          </div>
        </div>
      </div>
    </div>
    @endforeach
  </div>
  {{ $favs->links() }}
  @endif
</div>
@endsection

@push('scripts')
<script>
function removeFav(e, form, id) {
  e.preventDefault();
  const card = form.closest('.col-md-6');
  axios.post('/api/v1/favourites', new FormData(form))
    .then(() => { card.style.opacity='0'; card.style.transform='scale(.95)'; card.style.transition='all .3s'; setTimeout(()=>card.remove(),300); });
}
</script>
@endpush
