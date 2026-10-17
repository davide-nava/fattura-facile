@props([
    'kpis' => [],
])

@if(!empty($kpis))
<div class="kpi-grid">
    @foreach($kpis as $kpi)
        <div class="kpi-card">
            <div class="kpi-label">{{ $kpi['label'] }}</div>
            <div class="kpi-value">{{ $kpi['value'] }}</div>
            @if(!empty($kpi['subvalue']))
                <div class="kpi-sub">{{ $kpi['subvalue'] }}</div>
            @endif
        </div>
    @endforeach
</div>
@endif
