<x-layouts::print
    :title="$title"
    :company="$company"
    :branding="$branding"
    :orientation="$orientation"
    :paperSize="$paperSize"
>
    <x-print.header
        :company="$company"
        :branding="$branding"
        :title="$title"
        :subtitle="$subtitle"
        badge="EXECUTIVE HEALTH"
    />

    <x-print.kpi-grid :kpis="$kpis" />

    @if(isset($tables['financial_summary']))
        <x-print.table
            :headers="$tables['financial_summary']['headers']"
            :rows="$tables['financial_summary']['rows']"
            :alignment="$tables['financial_summary']['alignment'] ?? []"
            title="Executive Breakdown"
        />
    @endif

    <x-print.summary-box :summary="$summary" />

    @if($notes)
        <div class="notes-box">
            <strong>{{ __('Notes & Audit trail:') }}</strong> {{ $notes }}
        </div>
    @endif

    <x-print.footer
        :company="$company"
        :branding="$branding"
        :generatedAt="$generatedAt"
    />
</x-layouts::print>
