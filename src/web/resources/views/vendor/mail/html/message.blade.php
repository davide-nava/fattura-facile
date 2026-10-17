<x-mail::layout>
{{-- Header --}}
<x-slot:header>
@if (isset($header))
{!! $header !!}
@else
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
Fatture semplici, offline e cloud
</span>
</td>
</tr>
</table>
</x-mail::header>
@endif
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} Fattura Facile. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
