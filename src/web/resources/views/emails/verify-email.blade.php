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

# @lang('Verify your email address')

@lang('Please click the button below to verify your email address.')

<x-mail::button :url="$url" color="primary">
@lang('Verify Email Address')
</x-mail::button>

@lang('If you did not create an account, no further action is required.')

@lang('Regards,')<br>
{{ config('app.name') }}

<x-slot:subcopy>
@lang(
    "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
    'into your web browser:',
    [
        'actionText' => __('Verify Email Address'),
    ]
) <span class="break-all">[{{ $url }}]({{ $url }})</span>
</x-slot:subcopy>
</x-mail::message>
