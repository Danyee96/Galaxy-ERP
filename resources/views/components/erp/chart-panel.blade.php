@props(['id', 'title', 'description' => null, 'type' => 'donut', 'labels' => [], 'series' => [], 'empty' => 'No records to display.'])
<section {{ $attributes->class(['erp-panel']) }} aria-labelledby="{{ $id }}-title">
  <header class="erp-panel-header">
    <div><h2 id="{{ $id }}-title">{{ $title }}</h2>@if($description)<p>{{ $description }}</p>@endif</div>
    <details class="erp-chart-data">
      <summary aria-label="View data for {{ $title }}">View data</summary>
      <div class="erp-chart-data-table" tabindex="0" role="region" aria-label="{{ $title }} data">
        <table class="table"><thead><tr><th scope="col">Category</th>@foreach($series as $item)<th scope="col">{{ $item['name'] }}</th>@endforeach</tr></thead>
          <tbody>@forelse($labels as $index => $label)<tr><th scope="row">{{ $label }}</th>@foreach($series as $item)<td>{{ number_format($item['data'][$index] ?? 0) }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($series) + 1 }}">{{ $empty }}</td></tr>@endforelse</tbody>
        </table>
      </div>
    </details>
  </header>
  <div class="erp-chart-wrap">
    <div id="{{ $id }}" class="erp-chart" role="img" aria-label="{{ $title }} chart. Exact values are available under View data." data-chart="{{ json_encode(['type' => $type, 'labels' => array_values($labels), 'series' => $series, 'empty' => $empty]) }}"></div>
    <p class="erp-chart-state" role="status" data-chart-state>Loading chart…</p>
    <noscript><p class="erp-chart-state">Enable JavaScript to view the chart, or use View data.</p></noscript>
  </div>
</section>
