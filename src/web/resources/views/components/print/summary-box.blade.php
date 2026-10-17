@props([
    'summary' => [],
])

@if(!empty($summary))
<div class="summary-container">
    <table class="summary-box">
        @php
            $keys = array_keys($summary);
            $lastKey = end($keys);
        @endphp
        @foreach($summary as $label => $value)
            <tr class="{{ $label === $lastKey ? 'grand-total' : '' }}">
                <td class="font-medium">{{ $label }}:</td>
                <td class="text-right font-bold">{{ $value }}</td>
            </tr>
        @endforeach
    </table>
</div>
@endif
