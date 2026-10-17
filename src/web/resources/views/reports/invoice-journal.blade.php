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
        badge="SALES JOURNAL"
    />

    <x-print.kpi-grid :kpis="$kpis" />

    <div class="report-table-wrapper">
        <div class="table-heading">{{ __('Sales & Invoicing Journal') }}</div>
        <table class="report-table">
            <thead>
                <tr>
                    <th>{{ __('Invoice #') }}</th>
                    <th>{{ __('Date') }}</th>
                    <th>{{ __('Due Date') }}</th>
                    <th>{{ __('Customer') }}</th>
                    <th class="text-center">{{ __('Status') }}</th>
                    <th class="text-right">{{ __('Net Base') }}</th>
                    <th class="text-right">{{ __('VAT') }}</th>
                    <th class="text-right">{{ __('Total') }}</th>
                    <th class="text-right">{{ __('Outstanding') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tables['invoices']['rows'] as $row)
                    <tr>
                        <td class="font-bold">{{ $row['number'] }}</td>
                        <td>{{ $row['date'] }}</td>
                        <td>{{ $row['due_date'] }}</td>
                        <td>{{ $row['customer'] }}</td>
                        <td class="text-center">
                            <x-print.badge :status="$row['status']" />
                        </td>
                        <td class="text-right">{{ $row['subtotal'] }}</td>
                        <td class="text-right">{{ $row['tax'] }}</td>
                        <td class="text-right font-bold">{{ $row['total'] }}</td>
                        <td class="text-right" style="color: {{ $row['outstanding'] !== '0.00' ? '#b91c1c' : '#15803d' }}; font-weight: 600;">
                            {{ $row['outstanding'] }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted" style="padding: 24px;">
                            {{ __('No invoices recorded in this period.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <x-print.summary-box :summary="$summary" />

    @if($notes)
        <div class="notes-box">
            <strong>{{ __('Notes:') }}</strong> {{ $notes }}
        </div>
    @endif

    <x-print.footer
        :company="$company"
        :branding="$branding"
        :generatedAt="$generatedAt"
    />
</x-layouts::print>
