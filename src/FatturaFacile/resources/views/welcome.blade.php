<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    @include('partials.head')
</head>

<body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-teal-500 selection:text-white">

    <!-- Header & Navigazione matching https://fatturafacile.eu/ -->
    <header class="sticky top-0 z-40 w-full backdrop-blur-md bg-white/90 border-b border-slate-200/80 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">

            <!-- Logo Branding Coerente -->
            <a href="#hero" class="flex items-center gap-3 group" aria-label="Fattura Facile Home">
                <x-app-logo-icon class="h-10 w-10 shrink-0 transition-transform duration-300 group-hover:scale-105" />
                <div class="flex flex-col">
                    <span class="text-2xl font-extrabold tracking-tight text-slate-900 leading-none">
                        Fattura<span class="text-teal-600">Facile</span>
                    </span>
                    <span class="text-[11px] font-semibold text-slate-500 mt-1">Fatture semplici, offline e cloud</span>
                </div>
            </a>

            <!-- Desktop Nav Links -->
            <nav class="hidden md:flex items-center space-x-8 text-sm font-medium text-slate-600">
                <a href="#perche" class="hover:text-teal-600 transition-colors">Perché Noi</a>
                <a href="#versioni" class="hover:text-teal-600 transition-colors">Offline vs Cloud</a>
                <a href="#community-richieste" class="hover:text-teal-600 transition-colors text-teal-700 font-semibold">Moduli & Sviluppo</a>
                <a href="#faq" class="hover:text-teal-600 transition-colors">FAQ</a>
                <a href="https://fatturafacile.eu" target="_blank" rel="noopener noreferrer" class="hover:text-teal-600 transition-colors flex items-center gap-1.5">
                    <span>Sito Ufficiale</span>
                    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500"></span>
                </a>
            </nav>

            <!-- Auth & Action Buttons -->
            <div class="flex items-center gap-3">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-teal-700 rounded-xl shadow-sm hover:shadow transition-all duration-200">
                            Vai alla Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold text-slate-700 hover:text-teal-600 transition-colors">
                            Accedi
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2.5 text-sm font-semibold text-white bg-slate-900 hover:bg-teal-700 rounded-xl shadow-sm hover:shadow transition-all duration-200">
                                Registrati
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="hero" class="relative overflow-hidden pt-12 pb-20 lg:pt-20 lg:pb-32 bg-gradient-to-b from-slate-100/70 via-white to-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Top Market Badges -->
            <div class="flex flex-wrap items-center justify-center gap-3 mb-8">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shadow-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pronto per Italia 🇮🇹 (FatturaPA / SDI) & Svizzera 🇨🇭 (QR-Bill)</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-teal-50 text-teal-800 border border-teal-200">
                    <svg class="w-3.5 h-3.5 text-teal-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 002 0V7z" clip-rule="evenodd"></path>
                    </svg>
                    <span>Privacy Compliant: GDPR UE & nLPD Svizzera</span>
                </span>
            </div>

            <!-- Main Headline -->
            <div class="text-center max-w-4xl mx-auto">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight">
                    <span>Fatturazione semplice, veloce e</span><br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-teal-600 to-emerald-600">sotto il tuo totale controllo.</span>
                </h1>
                <p class="mt-6 text-lg sm:text-xl text-slate-600 max-w-2xl mx-auto font-normal leading-relaxed">
                    Basta software contabili complessi ed esosi. Emetti fatture in 30 secondi dal tuo PC o chiavetta USB con SQLite, 100% gratuito e senza abbonamenti, oppure sfrutta la versione Cloud ospitata sul tuo server.
                </p>

                <!-- CTA Buttons -->
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                            <span>Entra nella Dashboard Gestionale</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-8 py-4 text-base font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-5 h-5 text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                            </svg>
                            <span>Accedi al Gestionale</span>
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-base font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-2xl shadow-sm hover:shadow transition-all duration-200">
                                <span>Crea Account Gratuito</span>
                            </a>
                        @endif
                    @endauth
                </div>

                <!-- Pillar highlights -->
                <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4 text-left">
                    <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-sm flex items-start gap-3">
                        <div class="p-2 bg-emerald-50 rounded-xl text-emerald-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase">Zero Abbonamento</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Desktop free per sempre</p>
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-sm flex items-start gap-3">
                        <div class="p-2 bg-teal-50 rounded-xl text-teal-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase">Portabile su USB</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Nessuna installazione</p>
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-sm flex items-start gap-3">
                        <div class="p-2 bg-cyan-50 rounded-xl text-cyan-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase">100% Offline</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Funziona senza internet</p>
                        </div>
                    </div>

                    <div class="p-4 bg-white rounded-2xl border border-slate-200/70 shadow-sm flex items-start gap-3">
                        <div class="p-2 bg-slate-100 rounded-xl text-slate-700 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 uppercase">I Tuoi Dati Sono Tuoi</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Zero server intermediari</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Filosofia & Valore -->
    <section id="perche" class="py-20 bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center max-w-3xl mx-auto">
                <h2 class="text-xs font-bold uppercase tracking-wider text-teal-600">La filosofia di Fattura Facile</h2>
                <p class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Non un ERP complicato da 500 bottoni, ma il tuo strumento quotidiano
                </p>
                <p class="mt-4 text-slate-600">
                    Idraulici, elettricisti, installatori, giardinieri e liberi professionisti emettono in media 10-20 documenti al mese. Non hanno bisogno di gestire una supply chain complessa: hanno solo bisogno di farsi pagare puntuali.
                </p>
            </div>

            <!-- Feature Grid -->
            <div class="mt-16 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-teal-500/50 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-teal-600 text-white flex items-center justify-center mb-6 shadow-md shadow-teal-600/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Velocità di emissione</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Crea una fattura o preventivo completo in meno di un minuto: seleziona il cliente dalla rubrica, inserisci gli articoli e genera il PDF pronto per l'invio.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-teal-500/50 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center mb-6 shadow-md shadow-slate-900/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Bivalenza Fiscale IT & CH</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Supporto completo sia per la fatturazione elettronica SDI italiana (FatturaPA XML) che per le QR-Fatture svizzere conformi allo standard SIX Interbank.
                    </p>
                </div>

                <div class="p-8 rounded-3xl bg-slate-50 border border-slate-200/80 hover:border-teal-500/50 hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center mb-6 shadow-md shadow-emerald-600/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-3">Sovranità dei tuoi dati</h3>
                    <p class="text-slate-600 text-sm leading-relaxed">
                        Nessun cloud di terze parti che rivende i tuoi dati o ti blocca l'accesso se scade l'abbonamento. Il database è tuo, protetto e portatile.
                    </p>
                </div>
            </div>

        </div>
    </section>

    <!-- Versioni: Offline USB vs Cloud -->
    <section id="versioni" class="py-20 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h2 class="text-xs font-bold uppercase tracking-wider text-teal-600">Due modalità flessibili</h2>
                <p class="mt-2 text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">
                    Desktop Portabile su USB oppure Cloud Self-Hosted
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-5xl mx-auto">
                <!-- Option 1: Desktop USB -->
                <div class="p-8 bg-white rounded-3xl border border-slate-200/80 shadow-md flex flex-col justify-between">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-teal-50 text-teal-800 mb-4">
                            100% Gratuito & Libero
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">Versione Desktop USB</h3>
                        <p class="mt-3 text-slate-600 text-sm">
                            Ideale per professionisti singoli, artigiani e chi lavora spesso in cantiere o senza connessione internet stabile.
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Portabile su chiavetta USB con database SQLite locale</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Zero installazione e zero dipendenze sul sistema</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>100% Offline: funziona ovunque senza rete</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Backup crittografato in un solo clic</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100">
                        <a href="https://github.com/davide-nava/FatturaFacile" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-slate-900 hover:bg-teal-700 rounded-xl transition-all duration-200">
                            Scarica Versione Desktop (GitHub)
                        </a>
                    </div>
                </div>

                <!-- Option 2: Cloud Self-Hosted -->
                <div class="p-8 bg-white rounded-3xl border-2 border-teal-600 shadow-xl flex flex-col justify-between relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-teal-600 text-white text-[10px] font-bold uppercase tracking-wider px-4 py-1 rounded-bl-xl">
                        Consigliato per Team
                    </div>

                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-800 mb-4">
                            Multi-Tenant & API
                        </div>
                        <h3 class="text-2xl font-black text-slate-900">Versione Cloud & ERP</h3>
                        <p class="mt-3 text-slate-600 text-sm">
                            Ideale per aziende con più collaboratori, accesso remoto e portale di consultazione per i clienti.
                        </p>
                        <ul class="mt-6 space-y-3 text-sm text-slate-700">
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Accesso multi-utente via browser con ruoli e permessi</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Database PostgreSQL con isolamento multi-tenant garantito</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>Portale Self-Service B2B/B2C per i tuoi clienti</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="text-teal-600 font-bold">&check;</span>
                                <span>API REST v1 e integrazione Filament Admin</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8 pt-6 border-t border-slate-100">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="w-full inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-all duration-200">
                                Vai al tuo Gestionale
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="w-full inline-flex items-center justify-center px-6 py-3 text-sm font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl transition-all duration-200">
                                Accedi al Cloud
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-20 bg-white border-t border-slate-200">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-xs font-bold uppercase tracking-wider text-teal-600">Risposte immediate</h2>
                <p class="mt-2 text-3xl font-extrabold text-slate-900 tracking-tight">
                    Domande Frequenti
                </p>
            </div>

            <div class="space-y-4">
                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <h3 class="text-base font-bold text-slate-900">Come funziona la versione portabile USB?</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Basta copiare la cartella su una comune chiavetta USB e avviarla con un doppio clic. Non scrive nulla nel registro di Windows e conserva tutti i dati in un file SQLite sul supporto stesso.
                    </p>
                </div>

                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <h3 class="text-base font-bold text-slate-900">Come gestite FatturaPA (SDI) e le QR-Fatture svizzere?</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Per l'Italia viene prodotto l'XML conforme alle specifiche dell'Agenzia delle Entrate per il Sistema di Interscambio. Per la Svizzera vengono generate QR-Bill complete conformi alle specifiche SIX Interbank Clearing.
                    </p>
                </div>

                <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200/80">
                    <h3 class="text-base font-bold text-slate-900">I miei dati sono davvero al sicuro?</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Assolutamente sì. Nessun dato viene venduto o inviato a server terzi per telemetria. Sei tu il titolare esclusivo del trattamento dei tuoi dati nel pieno rispetto di GDPR e nLPD.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Official Footer Component -->
    @include('partials.footer')

</body>
</html>
