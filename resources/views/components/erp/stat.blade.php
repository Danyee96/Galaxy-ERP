@props(['label', 'value', 'href' => null, 'tone' => 'neutral', 'caption' => null])
<div {{ $attributes->class(['erp-stat']) }}>
  <dt><span class="erp-status-dot erp-status-dot--{{ $tone }}" aria-hidden="true"></span>{{ $label }}</dt>
  <dd>@if($href)<a href="{{ $href }}" aria-label="{{ $label }}: {{ number_format($value) }}">{{ number_format($value) }}<i class="bx bx-right-arrow-alt" aria-hidden="true"></i></a>@else{{ number_format($value) }}@endif
    @if($caption)<small class="erp-stat-caption">{{ $caption }}</small>@endif
  </dd>
</div>
