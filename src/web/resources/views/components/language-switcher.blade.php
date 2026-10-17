@props([
    'variant' => 'pills', // 'pills' or 'compact'
])

@php
    $currentLocale = app()->getLocale();
    $locales = [
        'it' => [
            'name' => 'Italiano',
            'label' => 'IT',
            'aria' => 'Italiano (IT)',
            'flag' => 'it',
        ],
        'en' => [
            'name' => 'English',
            'label' => 'EN',
            'aria' => 'English (EN)',
            'flag' => 'en',
        ],
        'de' => [
            'name' => 'Deutsch',
            'label' => 'DE',
            'aria' => 'Deutsch (DE)',
            'flag' => 'de',
        ],
        'fr' => [
            'name' => 'Français',
            'label' => 'FR',
            'aria' => 'Français (FR)',
            'flag' => 'fr',
        ],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'inline-flex items-center p-1 bg-slate-100 dark:bg-slate-800 rounded-xl border border-slate-200/80 dark:border-slate-700 shadow-xs']) }} role="group" aria-label="{{ __('Select Language') }}">
    @foreach ($locales as $code => $locale)
        @php
            $isActive = $currentLocale === $code;
        @endphp
        <a
            href="{{ route('locale.switch', $code) }}"
            onclick="window.setAppLocale && window.setAppLocale('{{ $code }}')"
            title="{{ $locale['name'] }}"
            aria-label="{{ $locale['aria'] }}"
            aria-current="{{ $isActive ? 'true' : 'false' }}"
            data-test="language-switcher-{{ $code }}"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200 {{ $isActive ? 'bg-white dark:bg-slate-900 text-teal-700 dark:text-teal-400 shadow-xs ring-1 ring-slate-200/80 dark:ring-slate-700 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-white/60 dark:hover:bg-slate-700/60' }}"
        >
            <span class="inline-flex shrink-0 items-center justify-center overflow-hidden rounded-[3px] shadow-xs">
                @if ($code === 'it')
                    <!-- Italy Flag -->
                    <svg class="w-4 h-3" viewBox="0 0 3 2" aria-hidden="true">
                        <rect width="1" height="2" fill="#009246"/>
                        <rect width="1" height="2" x="1" fill="#fff"/>
                        <rect width="1" height="2" x="2" fill="#ce2b37"/>
                    </svg>
                @elseif ($code === 'en')
                    <!-- UK Flag (English) -->
                    <svg class="w-4 h-3" viewBox="0 0 60 30" aria-hidden="true">
                        <clipPath id="uk-clip-{{ $code }}">
                            <path d="M0,0 v30 h60 v-30 z"/>
                        </clipPath>
                        <clipPath id="uk-diag-{{ $code }}">
                            <path d="M30,15 h30 v15 z v15 h-30 z h-30 v-15 z v-15 h30 z"/>
                        </clipPath>
                        <g clip-path="url(#uk-clip-{{ $code }})">
                            <path d="M0,0 v30 h60 v-30 z" fill="#012169"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" stroke="#fff" stroke-width="6"/>
                            <path d="M0,0 L60,30 M60,0 L0,30" clip-path="url(#uk-diag-{{ $code }})" stroke="#C8102E" stroke-width="4"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#fff" stroke-width="10"/>
                            <path d="M30,0 v30 M0,15 h60" stroke="#C8102E" stroke-width="6"/>
                        </g>
                    </svg>
                @elseif ($code === 'de')
                    <!-- Germany Flag -->
                    <svg class="w-4 h-3" viewBox="0 0 5 3" aria-hidden="true">
                        <rect width="5" height="1" fill="#000"/>
                        <rect width="5" height="1" y="1" fill="#dd0000"/>
                        <rect width="5" height="1" y="2" fill="#ffce00"/>
                    </svg>
                @elseif ($code === 'fr')
                    <!-- France Flag -->
                    <svg class="w-4 h-3" viewBox="0 0 3 2" aria-hidden="true">
                        <rect width="1" height="2" fill="#002395"/>
                        <rect width="1" height="2" x="1" fill="#fff"/>
                        <rect width="1" height="2" x="2" fill="#ed2939"/>
                    </svg>
                @endif
            </span>
            <span class="tracking-wide">{{ $locale['label'] }}</span>
        </a>
    @endforeach
</div>
