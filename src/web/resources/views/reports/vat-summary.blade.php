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
        badge="MWST / VAT REPORT"
    />

    <x-print.kpi-grid :kpis="$kpis" />

    <div class="report-table-wrapper">
        <div class="table-heading">{{ __('Swiss VAT Classification & Liabilities Breakdown') }}</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>{{ __('VAT Rate Category') }}</th>
                    <th class="text-center">{{ __('Item / Line Count') }}</th>
                    <th class="text-right">{{ __('Net Taxable Base') }}</th>
                    <th class="text-right">{{ __('VAT Amount') }}</th>
                    <th class="text-right">{{ __('Gross Total Turnover') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables['vat_breakdown']['rows'] as $row)
                    <tr>
                        <td class="font-bold">
                            <span class="badge badge-info">{{ $row['rate'] }}</span>
                        </td>
                        <td class="text-center">{{ $row['items_count'] }}</td>
                        <td class="text-right">{{ $row['net_base'] }}</td>
                        <td class="text-right font-bold" style="color: var(--primary-color);">{{ $row['tax_amount'] }}</td>
                        <td class="text-right font-bold">{{ $row['gross_total'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted" style="padding: 24px;">
                            {{ __('No taxable transactions recorded in this period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-print.summary-box :summary="$summary" />

    @if($notes)
        <div class="notes-box">
            <strong>{{ __('Statutory Note:') }}</strong> {{ $notes }}
        </div>
    @endif

    <x-print.footer
        :company="$company"
        :branding="$branding"
        :generatedAt="$generatedAt"
    />
</x-layouts::print>
