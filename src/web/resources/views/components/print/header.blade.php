@props([
    'company',
    'branding',
    'title',
    'subtitle' => null,
    'badge' => null,
])

<div class="report-header">
    <div class="company-branding">
        @if($branding->hasLogo())
            <img src="{{ $branding->logoUrl }}" alt="{{ $company->name }}" class="logo">
        @else
            <div class="brand-text">{{ $company->name }}</div>
        @endif
        <div class="company-details">
            <div>{{ $company->street }}</div>
            <div>{{ $company->postal_code }} {{ $company->city }} &bull; {{ $company->country }}</div>
            @if($company->tax_number)
                <div>UID: <strong>{{ $company->tax_number }}</strong></div>
            @endif
            @if($company->vat_number)
                <div>MWST / VAT: <strong>{{ $company->vat_number }}</strong></div>
            @endif
            @if($company->email)
                <div>{{ $company->email }}</div>
            @endif
        </div>
    </div>

    <div class="report-title-section">
        <div class="report-title">{{ $title }}</div>
        @if($subtitle)
            <div class="report-subtitle">{{ $subtitle }}</div>
        @endif
        @if($badge)
            <div style="margin-top: 6px;">
                <span class="badge badge-info">{{ $badge }}</span>
            </div>
        @endif
    </div>
</div>
