<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? __('Report') }} - {{ $company->name ?? config('app.name') }}</title>
    <style>
        :root {
            --primary-color: {{ $branding->primaryColor ?? '#0d9488' }};
            --secondary-color: {{ $branding->secondaryColor ?? '#1e293b' }};
            --accent-color: {{ $branding->accentColor ?? '#f59e0b' }};
            --font-family: {{ $branding->fontFamily ?? "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif" }};
            --text-main: #1f2937;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --bg-light: #f8fafc;
        }

        /* Reset & Base */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: var(--font-family);
            font-size: 11.5px;
            color: var(--text-main);
            background-color: #ffffff;
            line-height: 1.5;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Page Layout & Margins */
        @page {
            size: {{ $paperSize ?? 'A4' }} {{ $orientation ?? 'portrait' }};
            margin: 12mm 10mm 15mm 10mm;
        }

        /* Screen Preview Mode */
        @media screen {
            body {
                background-color: #f1f5f9;
                padding: 24px 16px;
            }
            .print-container {
                max-width: {{ ($orientation ?? 'portrait') === 'landscape' ? '1120px' : '840px' }};
                margin: 0 auto;
                background: #ffffff;
                padding: 36px 44px;
                border-radius: 8px;
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);
            }
            .screen-toolbar {
                max-width: {{ ($orientation ?? 'portrait') === 'landscape' ? '1120px' : '840px' }};
                margin: 0 auto 20px auto;
                display: flex;
                align-items: center;
                justify-content: space-between;
                background: #ffffff;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.04);
            }
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                padding: 8px 16px;
                font-size: 13px;
                font-weight: 500;
                border-radius: 6px;
                cursor: pointer;
                text-decoration: none;
                transition: all 0.15s ease-in-out;
            }
            .btn-primary {
                background-color: var(--primary-color);
                color: #ffffff;
                border: 1px solid transparent;
            }
            .btn-primary:hover { opacity: 0.9; }
            .btn-secondary {
                background-color: #f8fafc;
                color: #334155;
                border: 1px solid #cbd5e1;
            }
            .btn-secondary:hover { background-color: #e2e8f0; }
        }

        @media print {
            body { background: transparent; padding: 0; }
            .no-print, .screen-toolbar { display: none !important; }
            .print-container { padding: 0; max-width: 100% !important; box-shadow: none !important; }
            .page-break { page-break-after: always; break-after: page; }
            .avoid-break { page-break-inside: avoid; break-inside: avoid; }
        }

        /* Typography */
        h1, h2, h3, h4 { color: var(--secondary-color); font-weight: 700; line-height: 1.25; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-muted { color: var(--text-muted); }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .font-medium { font-weight: 500; }

        /* Flex & Grid */
        .flex { display: flex; }
        .flex-col { flex-direction: column; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .gap-2 { gap: 8px; }
        .gap-3 { gap: 12px; }
        .gap-4 { gap: 16px; }
        .gap-6 { gap: 24px; }
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .grid-cols-6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }

        /* Header Component */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--primary-color);
            margin-bottom: 22px;
        }
        .company-branding {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .company-branding .logo {
            max-height: 50px;
            max-width: 180px;
            object-fit: contain;
            margin-bottom: 6px;
        }
        .company-branding .brand-text {
            font-size: 20px;
            font-weight: 800;
            color: var(--primary-color);
            letter-spacing: -0.5px;
        }
        .company-details {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.45;
        }
        .report-title-section {
            text-align: right;
        }
        .report-title {
            font-size: 20px;
            font-weight: 800;
            color: var(--secondary-color);
            letter-spacing: -0.3px;
        }
        .report-subtitle {
            font-size: 11.5px;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 12px;
            margin-bottom: 22px;
        }
        .kpi-card {
            background-color: var(--bg-light);
            border: 1px solid var(--border-color);
            border-left: 3.5px solid var(--primary-color);
            padding: 10px 14px;
            border-radius: 6px;
            break-inside: avoid;
        }
        .kpi-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
            font-weight: 600;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-top: 4px;
        }
        .kpi-sub {
            font-size: 9.5px;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Report Tables */
        .report-table-wrapper {
            margin-bottom: 22px;
            break-inside: auto;
        }
        .table-heading {
            font-size: 13px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 8px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .report-table th {
            background-color: var(--bg-light);
            color: var(--secondary-color);
            font-weight: 600;
            text-align: left;
            padding: 7px 10px;
            border-top: 1px solid var(--border-color);
            border-bottom: 2px solid var(--border-color);
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .report-table td {
            padding: 7px 10px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }
        .report-table tr:nth-child(even) td {
            background-color: #fafbfb;
        }

        /* Summary Box */
        .summary-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 16px;
            margin-bottom: 24px;
            break-inside: avoid;
        }
        .summary-box {
            width: 48%;
            border-collapse: collapse;
            font-size: 11.5px;
        }
        .summary-box td {
            padding: 5px 8px;
        }
        .summary-box tr.grand-total td {
            font-size: 13px;
            font-weight: 700;
            border-top: 2px solid var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            background-color: var(--bg-light);
            color: var(--secondary-color);
        }

        /* Status Badge */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 9.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
        .badge-neutral { background: #f1f5f9; color: #475569; }

        /* Notes Box */
        .notes-box {
            margin-top: 18px;
            padding: 10px 14px;
            background-color: var(--bg-light);
            border-left: 3px solid var(--primary-color);
            font-size: 10.5px;
            color: var(--text-muted);
            border-radius: 4px;
            break-inside: avoid;
        }

        /* Report Footer */
        .report-footer {
            margin-top: 32px;
            padding-top: 12px;
            border-top: 1px solid var(--border-color);
            font-size: 9.5px;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
            align-items: center;
            break-inside: avoid;
        }
    </style>
</head>
<body>
    @if(!isset($isPdf) || !$isPdf)
    <div class="screen-toolbar no-print">
        <div>
            <a href="{{ route('reports.index') }}" class="btn btn-secondary">&larr; {{ __('Back to Reports') }}</a>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" class="btn btn-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/></svg>
                {{ __('Print') }}
            </button>
            <a href="{{ request()->fullUrlWithQuery(['download' => 1]) }}" class="btn btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                {{ __('Download PDF') }}
            </a>
        </div>
    </div>
    @endif

    <div class="print-container">
        {{ $slot }}
    </div>
</body>
</html>
