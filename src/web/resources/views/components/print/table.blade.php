@props([
    'headers' => [],
    'rows' => [],
    'alignment' => [],
    'title' => null,
])

<div class="report-table-wrapper">
    @if($title)
        <div class="table-heading">{{ $title }}</div>
    @endif
    <table class="report-table">
        <thead>
            <tr>
                @foreach($headers as $index => $header)
                    @php
                        $align = $alignment[$index] ?? 'left';
                    @endphp
                    <th class="text-{{ $align }}">{{ $header }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($row as $index => $cell)
                        @php
                            $align = $alignment[$index] ?? 'left';
                        @endphp
                        <td class="text-{{ $align }}">{!! is_scalar($cell) ? e((string)$cell) : $cell !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) }}" class="text-center text-muted" style="padding: 20px;">
                        {{ __('No records found for the selected criteria.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
