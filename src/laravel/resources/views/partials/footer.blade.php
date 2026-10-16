<footer class="bg-slate-950 text-slate-400 py-12 border-t border-slate-800 text-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-10">

            <!-- Col 1: Brand -->
            <div class="space-y-4">
                <div class="flex items-center gap-2">
                    <span class="text-xl font-bold text-white tracking-tight">Fattura<span class="text-teal-400">Facile</span></span>
                </div>
                <p class="text-xs font-semibold text-teal-400" data-slogan="Fatture semplici, offline e cloud">{{ __('Simple invoices, offline and cloud') }}</p>
                <p class="text-xs text-slate-400 leading-relaxed">
                    {{ __('The invoicing software designed specifically for Italian and Swiss artisans and micro-businesses. Developed with a local SQLite architecture and self-hosted option.') }}
                </p>
                <div class="text-xs text-slate-300">
                    <strong>{{ __('Email Contact:') }}</strong> <a href="mailto:info@fatturafacile.eu" class="text-teal-400 hover:underline">info@fatturafacile.eu</a>
                </div>
                <div class="text-xs text-slate-500">
                    {{ __('Registered domain:') }} <a href="https://fatturafacile.eu" target="_blank" rel="noopener noreferrer" class="text-teal-400 hover:underline">fatturafacile.eu</a>
                </div>
            </div>

            <!-- Col 2: Prodotto -->
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">{{ __('Product') }}</h4>
                <ul class="space-y-2 text-xs">
                    <li><a href="{{ url('/#perche') }}" class="hover:text-white transition-colors">{{ __('Why Fattura Facile') }}</a></li>
                    <li><a href="{{ url('/#versioni') }}" class="hover:text-white transition-colors">{{ __('Desktop vs Cloud') }}</a></li>
                    <li><a href="{{ url('/#community-richieste') }}" class="hover:text-teal-400 transition-colors">{{ __('Request Free Modules') }}</a></li>
                    <li><a href="https://github.com/davide-nava/FatturaFacile" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">{{ __('GitHub Repository') }}</a></li>
                </ul>
            </div>

            <!-- Col 3: Legale & Compliance -->
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">{{ __('Privacy & Compliance') }}</h4>
                <ul class="space-y-2 text-xs">
                    <li><span class="text-slate-400">{{ __('GDPR Notice (EU Reg. 2016/679)') }}</span></li>
                    <li><span class="text-slate-400">{{ __('Swiss FADP Compliance') }}</span></li>
                    <li><span class="text-slate-400">{{ __('Offline Privacy Architecture') }}</span></li>
                    <li><a href="https://github.com/davide-nava/FatturaFacile/blob/main/LICENSE" target="_blank" rel="noopener noreferrer" class="hover:text-white transition-colors">{{ __('Software License') }}</a></li>
                </ul>
            </div>

            <!-- Col 4: Copertura Geografica -->
            <div>
                <h4 class="text-white text-xs font-bold uppercase tracking-wider mb-4">{{ __('Markets Served') }}</h4>
                <p class="text-xs text-slate-400 mb-2">
                    <strong>{{ __('Italy 🇮🇹:') }}</strong> {{ __('Compatible with FatturaPA XML, payment tracking, and flat-rate/standard regimes.') }}
                </p>
                <p class="text-xs text-slate-400">
                    <strong>{{ __('Switzerland 🇨🇭:') }}</strong> {{ __('Compatible with QR-Invoice (SIX standard), Swiss VAT (standard, reduced, special).') }}
                </p>
            </div>

        </div>

        <div class="pt-8 border-t border-slate-900 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
            <div>
                &copy; {{ date('Y') }} Fattura Facile.
            </div>
            <div class="flex gap-4">
                <a href="https://fatturafacile.eu" target="_blank" rel="noopener noreferrer" class="hover:text-slate-300">{{ __('Privacy Policy') }}</a>
                <span>•</span>
                <a href="https://fatturafacile.eu" target="_blank" rel="noopener noreferrer" class="hover:text-slate-300">fatturafacile.eu</a>
            </div>
        </div>
    </div>
</footer>
