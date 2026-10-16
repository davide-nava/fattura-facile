<x-layouts::app :title="__('Dashboard')">
    <div class="flex flex-col gap-8">
        <!-- Welcome Banner -->
        <div class="p-6 sm:p-8 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 border border-teal-200">
                        <span class="w-2 h-2 rounded-full bg-teal-500 animate-pulse"></span>
                        {{ __('Management Active') }}
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                        IT 🇮🇹 (SDI) & CH 🇨🇭 (QR-Bill)
                    </span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight" data-welcome="Benvenuto in">
                    {{ __('Welcome to') }} <span class="text-teal-600">FatturaFacile</span>
                </h1>
                <p class="text-slate-500 text-sm max-w-2xl">
                    {{ __('Lightweight, transparent invoicing and business management under your control. Manage master records, accounting documents, and fiscal compliance.') }}
                </p>
            </div>

            <!-- Quick Action Buttons -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ url('/admin/invoices') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-teal-700 rounded-xl shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('Invoices & Documents') }}</span>
                </a>
                <a href="{{ url('/admin/customers') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span>{{ __('CRM & Customers') }}</span>
                </a>
                <a href="{{ url('/admin') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-teal-700 bg-teal-50 hover:bg-teal-100 border border-teal-200 rounded-xl transition-all duration-200">
                    <span>{{ __('ERP Panel') }}</span>
                </a>
            </div>
        </div>

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Net Turnover') }}</span>
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">CHF / EUR 48,250.00</div>
                    <div class="flex items-center gap-1.5 mt-2 text-xs font-medium text-emerald-600">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                        <span>{{ __('+12.4% compared to previous period') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Issued Documents') }}</span>
                    <div class="p-2 bg-teal-50 text-teal-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">{{ __('34 Documents') }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-xs font-medium text-slate-500">
                        <span>{{ __('Invoices, Proforma and Credit Notes') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('CRM Contacts') }}</span>
                    <div class="p-2 bg-cyan-50 text-cyan-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">{{ __('128 Contacts') }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-xs font-medium text-slate-500">
                        <span>{{ __('Customers, Suppliers, B2B Leads') }}</span>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="p-6 bg-white rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">{{ __('Compliance & Privacy') }}</span>
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                </div>
                <div>
                    <div class="text-2xl font-black text-slate-900 tracking-tight">{{ __('100% Compliant') }}</div>
                    <div class="flex items-center gap-1.5 mt-2 text-xs font-medium text-emerald-600">
                        <span>{{ __('EU GDPR & Swiss FADP') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Workspace Sections -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Active Modules Directory -->
            <div class="lg:col-span-2 p-6 sm:p-8 bg-white rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">{{ __('Active Operational Modules') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('Clean Code Architecture & Bounded Contexts') }}</p>
                    </div>
                    <a href="{{ url('/admin') }}" class="text-xs font-semibold text-teal-600 hover:text-teal-700">{{ __('View all') }} &rarr;</a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-teal-500/50 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-teal-600 text-white flex items-center justify-center font-bold text-xs">CRM</div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('CRM & Contacts') }}</h3>
                                <p class="text-[11px] text-slate-500">{{ __('Customers, Suppliers, Leads, B2B Portal') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/admin/customers') }}" class="text-xs font-medium text-teal-600 hover:underline">{{ __('Access module') }} &rarr;</a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-teal-500/50 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold text-xs">CAT</div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Product Catalog') }}</h3>
                                <p class="text-[11px] text-slate-500">{{ __('Items, Services, Price Lists, VAT Rates') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/admin/products') }}" class="text-xs font-medium text-teal-600 hover:underline">{{ __('Access module') }} &rarr;</a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-teal-500/50 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs">FAT</div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Active Sales Cycle') }}</h3>
                                <p class="text-[11px] text-slate-500">{{ __('Quotes, Orders, Invoices, QR-Bill') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/admin/invoices') }}" class="text-xs font-medium text-teal-600 hover:underline">{{ __('Access module') }} &rarr;</a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/70 hover:border-teal-500/50 transition-all">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-8 h-8 rounded-lg bg-cyan-600 text-white flex items-center justify-center font-bold text-xs">LOG</div>
                            <div>
                                <h3 class="text-sm font-bold text-slate-900">{{ __('Inventory & Logistics') }}</h3>
                                <p class="text-[11px] text-slate-500">{{ __('Stock, Movements, WAC Cost') }}</p>
                            </div>
                        </div>
                        <a href="{{ url('/admin') }}" class="text-xs font-medium text-teal-600 hover:underline">{{ __('Access module') }} &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Regional & Legal Quick Guide -->
            <div class="p-6 sm:p-8 bg-white rounded-3xl border border-slate-200/80 shadow-sm space-y-6">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">{{ __('Regional Specifications') }}</h2>
                    <p class="text-xs text-slate-500">{{ __('Dual Italy & Switzerland compliance') }}</p>
                </div>

                <div class="space-y-4 text-xs text-slate-600">
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 space-y-1">
                        <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                            <span>🇮🇹 {{ __('Italy (FatturaPA & SDI)') }}</span>
                        </div>
                        <p class="text-emerald-800">
                            {{ __('XML FatturaPA support standard Agenzia delle Entrate, 7-character Recipient Code and PEC.') }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-teal-50 border border-teal-200 space-y-1">
                        <div class="font-bold text-teal-900 flex items-center gap-1.5">
                            <span>🇨🇭 {{ __('Switzerland (QR-Bill & SIX)') }}</span>
                        </div>
                        <p class="text-teal-800">
                            {{ __('QR-Bill support standard SIX Interbank, Swiss 5-cent rounding (CHF) and UID CHE ID.') }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                        <div class="font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🔒 {{ __('Portability & Security') }}</span>
                        </div>
                        <p class="text-slate-600">
                            {{ __('Zero intermediaries: data resides exclusively in your SQLite / PostgreSQL database or cloud tenant.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts::app>
