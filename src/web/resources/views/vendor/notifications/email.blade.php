<x-mail::message>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
<table cellpadding="0" cellspacing="0" border="0" role="presentation" style="margin: 0 auto; text-align: center;">
<tr>
<td style="vertical-align: middle; padding-right: 12px; text-align: center;">
<img src="https://fatturafacile.eu/img/logo-email.png" class="logo" alt="{{ config('app.name', 'FatturaFacile') }}" style="height: 44px; max-height: 44px; width: auto; vertical-align: middle; border: 0; display: inline-block;" />
</td>
<td style="vertical-align: middle; text-align: left;">
<span class="brand-title" style="font-size: 22px; font-weight: 800; letter-spacing: -0.5px; color: #0f172a; line-height: 1.1; display: block; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
Fattura<span style="color: #0d9488;">Facile</span>
</span>
<span class="brand-subtitle" style="font-size: 11px; font-weight: 600; color: #64748b; display: block; margin-top: 3px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
@lang('Fatture semplici, offline e cloud')
</span>
</td>
</tr>
</table>
</x-mail::header>
</x-slot:header>

{{-- Greeting --}}
@if (! empty($greeting))
# {{ is_string($greeting) ? __($greeting) : $greeting }}
@else
@if ($level === 'error')
# @lang('Whoops!')
@else
# @lang('Hello!')
@endif
@endif

{{-- Intro Lines --}}
@foreach ($introLines as $line)
{{ is_string($line) ? __($line) : $line }}

@endforeach

{{-- Action Button --}}
@isset($actionText)
<?php
    $color = match ($level) {
        'success', 'error' => $level,
        default => 'primary',
    };
?>
<x-mail::button :url="$actionUrl" :color="$color">
{{ is_string($actionText) ? __($actionText) : $actionText }}
</x-mail::button>
@endisset

{{-- Outro Lines --}}
@foreach ($outroLines as $line)
{{ is_string($line) ? __($line) : $line }}

@endforeach

{{-- Salutation --}}
@if (! empty($salutation))
{{ is_string($salutation) ? __($salutation) : $salutation }}
@else
@lang('Regards,')<br>
{{ config('app.name') }}
@endif

{{-- Subcopy --}}
@isset($actionText)
<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => is_string($actionText) ? __($actionText) : $actionText,
    ]
) <span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
@endisset
</x-mail::message>
