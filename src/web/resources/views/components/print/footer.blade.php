@props([
    'company',
    'branding',
    'generatedAt' => null,
])

<div class="report-footer">
    <div>
        {{ $branding->footerText }}
    </div>
    <div>
        @if($generatedAt)
            {{ __('Generated:') }} {{ $generatedAt->format('d.m.Y H:i') }}
        @endif
    </div>
</div>
