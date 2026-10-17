<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="light dark">
<meta name="supported-color-schemes" content="light dark">
<style>
@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}

@media (prefers-color-scheme: dark) {
body,
.wrapper {
background-color: #121212 !important;
color: #e0e0e0 !important;
}

.body {
background-color: #121212 !important;
border-color: #121212 !important;
}

.inner-body {
background-color: #1e1e1e !important;
border-color: #2d2d2d !important;
box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4) !important;
}

.content-cell {
color: #e0e0e0 !important;
}

h1,
h2,
h3 {
color: #ffffff !important;
}

p,
ul,
ol,
li,
blockquote {
color: #e0e0e0 !important;
}

a {
color: #2dd4bf !important;
}

.header a,
.brand-title {
color: #ffffff !important;
}

.brand-subtitle {
color: #94a3b8 !important;
}

.subcopy {
border-top-color: #2d2d2d !important;
}

.subcopy p,
.subcopy span {
color: #a1a1aa !important;
}

.footer p {
color: #71717a !important;
}

.footer a {
color: #2dd4bf !important;
}

.panel {
border-left-color: #14b8a6 !important;
}

.panel-content {
background-color: #262626 !important;
color: #e0e0e0 !important;
}

.panel-content p {
color: #e0e0e0 !important;
}

.table th {
border-bottom-color: #2d2d2d !important;
color: #ffffff !important;
}

.table td {
color: #e0e0e0 !important;
}

.button-primary,
.button-blue {
background-color: #0d9488 !important;
border-top-color: #0d9488 !important;
border-bottom-color: #0d9488 !important;
border-left-color: #0d9488 !important;
border-right-color: #0d9488 !important;
color: #ffffff !important;
}

.button-success,
.button-green {
background-color: #16a34a !important;
border-top-color: #16a34a !important;
border-bottom-color: #16a34a !important;
border-left-color: #16a34a !important;
border-right-color: #16a34a !important;
color: #ffffff !important;
}

.button-error,
.button-red {
background-color: #dc2626 !important;
border-top-color: #dc2626 !important;
border-bottom-color: #dc2626 !important;
border-left-color: #dc2626 !important;
border-right-color: #dc2626 !important;
color: #ffffff !important;
}
}

[data-ogsc] body,
[data-ogsc] .wrapper {
background-color: #121212 !important;
}

[data-ogsc] .inner-body {
background-color: #1e1e1e !important;
border-color: #2d2d2d !important;
}

[data-ogsc] h1,
[data-ogsc] h2,
[data-ogsc] h3,
[data-ogsc] .brand-title {
color: #ffffff !important;
}

[data-ogsc] p,
[data-ogsc] td,
[data-ogsc] span {
color: #e0e0e0 !important;
}

[data-ogsc] a {
color: #2dd4bf !important;
}
</style>
{!! $head ?? '' !!}
</head>
<body>

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
<!-- Body content -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
