@props(['title', 'description' => null, 'eyebrow' => null])
<header {{ $attributes->class(['erp-page-header']) }}>
  <div>
    @if($eyebrow)<p class="erp-eyebrow">{{ $eyebrow }}</p>@endif
    <h1>{{ $title }}</h1>
    @if($description)<p class="erp-page-description">{{ $description }}</p>@endif
  </div>
  @isset($actions)<div class="erp-page-actions">{{ $actions }}</div>@endisset
</header>
