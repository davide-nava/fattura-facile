@props([
    'items' => [],
])

@php
    $activeGroupKeys = [];
    foreach ($items as $it) {
        if ($it->hasChildren() && $it->isSelfOrChildActive()) {
            $activeGroupKeys[] = $it->key;
            $activeGroupKeys[] = 'mobile_'.$it->key;
        }
    }
@endphp

<div
    x-data="{
        collapsed: localStorage.getItem('ff_erp_sidebar_collapsed') === 'true',
        mobileOpen: false,
        searchQuery: '',
        openGroups: {{ Js::from($activeGroupKeys) }},
        init() {
            this.$watch('collapsed', value => {
                localStorage.setItem('ff_erp_sidebar_collapsed', value);
                window.dispatchEvent(new CustomEvent('sidebar-toggle', { detail: { collapsed: value } }));
            });
            window.dispatchEvent(new CustomEvent('sidebar-toggle', { detail: { collapsed: this.collapsed } }));
        },
        toggleCollapse() {
            this.collapsed = !this.collapsed;
        },
        toggleMobile() {
            this.mobileOpen = !this.mobileOpen;
        },
        closeMobile() {
            this.mobileOpen = false;
        },
        toggleGroup(key) {
            if (this.openGroups.includes(key)) {
                this.openGroups = this.openGroups.filter(g => g !== key);
            } else {
                this.openGroups.push(key);
            }
        },
        isGroupOpen(key) {
            return this.openGroups.includes(key);
        },
        matchesSearch(label, children) {
            if (!this.searchQuery.trim()) return true;
            const q = this.searchQuery.toLowerCase();
            if (label.toLowerCase().includes(q)) return true;
            if (children && children.some(c => c.toLowerCase().includes(q))) return true;
            return false;
        }
    }"
    x-init="init()"
    @toggle-mobile-sidebar.window="toggleMobile()"
    class="relative"
>
    <!-- ========================================== -->
    <!-- DESKTOP COLLAPSIBLE SIDEBAR                -->
    <!-- ========================================== -->
    <aside
        :class="collapsed ? 'w-20' : 'w-68'"
        class="hidden lg:flex fixed inset-y-0 left-0 z-40 flex-col bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 transition-all duration-300 ease-in-out shadow-xs select-none"
        aria-label="{{ __('menu.navigation') }}"
    >
        <!-- Desktop Brand Header -->
        <div
            class="h-18 px-4 flex items-center border-b border-slate-200/80 dark:border-slate-800 transition-colors"
            :class="collapsed ? 'justify-center' : 'justify-between'"
        >
            <!-- Expanded Logo -->
            <template x-if="!collapsed">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3 group overflow-hidden">
                    <x-app-logo-icon class="h-9 w-9 shrink-0 transition-transform duration-300 group-hover:scale-105" />
                    <div class="flex flex-col truncate">
                        <span class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-none">
                            Fattura<span class="text-teal-600 dark:text-teal-400">Facile</span>
                        </span>
                        <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">ERP Suite</span>
                    </div>
                </a>
            </template>

            <!-- Collapsed Icon -->
            <template x-if="collapsed">
                <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center justify-center p-1.5 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors group" title="Fattura Facile ERP">
                    <x-app-logo-icon class="h-8 w-8 shrink-0 transition-transform duration-300 group-hover:scale-110" />
                </a>
            </template>
        </div>

        <!-- Search Bar in Expanded Mode -->
        <div x-show="!collapsed" x-transition.opacity.duration.200ms class="px-3 pt-3 pb-1">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="{{ __('menu.search_menu') }}"
                    class="w-full pl-8 pr-7 py-1.5 text-xs rounded-xl bg-slate-100/80 dark:bg-slate-800/80 border border-transparent focus:border-teal-500 focus:bg-white dark:focus:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none transition-all"
                />
                <button
                    type="button"
                    x-show="searchQuery"
                    @click="searchQuery = ''"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Desktop Navigation Items -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden custom-scrollbar px-3 py-3 space-y-1.5">
            @foreach ($items as $item)
                @php
                    $hasChildren = $item->hasChildren();
                    $isActive = $item->isActive();
                    $isSelfOrChildActive = $item->isSelfOrChildActive();
                    $childLabels = array_map(fn($c) => $c->getLabel(), $item->children);
                @endphp

                <div
                    x-show="matchesSearch('{{ addslashes($item->getLabel()) }}', {{ Js::from($childLabels) }})"
                    class="relative group"
                >
                    @if (! $hasChildren)
                        <!-- Single Menu Item (e.g. Dashboard) -->
                        <a
                            href="{{ $item->getUrl() }}"
                            wire:navigate
                            class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 {{ $isActive ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 shadow-xs ring-1 ring-teal-500/20 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60' }}"
                            :class="collapsed ? 'justify-center px-0 w-12 h-11 mx-auto' : ''"
                            title="{{ $item->getLabel() }}"
                        >
                            <span class="flex items-center justify-center shrink-0 w-6 text-center">
                                <i class="{{ $item->icon }} text-base {{ $isActive ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400 group-hover:text-teal-600 dark:group-hover:text-teal-400' }} transition-colors"></i>
                            </span>
                            <span x-show="!collapsed" class="truncate">
                                {{ $item->getLabel() }}
                            </span>
                            @if ($item->badge)
                                <span x-show="!collapsed" class="ml-auto px-2 py-0.5 text-[10px] font-bold rounded-full bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-300">
                                    {{ $item->badge }}
                                </span>
                            @endif
                        </a>

                        <!-- Tooltip in Collapsed Mode -->
                        <div
                            x-show="collapsed"
                            class="absolute left-full top-1/2 -translate-y-1/2 ml-3 px-3 py-1.5 bg-slate-900 dark:bg-slate-800 text-white text-xs font-semibold rounded-lg shadow-xl whitespace-nowrap z-50 pointer-events-none invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200"
                        >
                            {{ $item->getLabel() }}
                        </div>
                    @else
                        <!-- Parent Menu Item with Sub-items -->
                        <button
                            type="button"
                            @click="collapsed ? null : toggleGroup('{{ $item->key }}')"
                            class="w-full flex items-center gap-3.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 cursor-pointer {{ $isSelfOrChildActive ? 'bg-slate-100/90 dark:bg-slate-800/90 text-teal-700 dark:text-teal-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/80 dark:hover:bg-slate-800/60' }}"
                            :class="collapsed ? 'justify-center px-0 w-12 h-11 mx-auto' : 'justify-between'"
                            aria-expanded="false"
                            :aria-expanded="isGroupOpen('{{ $item->key }}')"
                        >
                            <div class="flex items-center gap-3.5 min-w-0" :class="collapsed ? 'justify-center' : ''">
                                <span class="flex items-center justify-center shrink-0 w-6 text-center">
                                    <i class="{{ $item->icon }} text-base {{ $isSelfOrChildActive ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400 group-hover:text-teal-600 dark:group-hover:text-teal-400' }} transition-colors"></i>
                                </span>
                                <span x-show="!collapsed" class="truncate">
                                    {{ $item->getLabel() }}
                                </span>
                            </div>
                            <i
                                x-show="!collapsed"
                                class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                                :class="isGroupOpen('{{ $item->key }}') ? 'rotate-180 text-teal-600 dark:text-teal-400' : ''"
                            ></i>
                        </button>

                        <!-- Expanded Accordion Submenu -->
                        <div
                            x-show="!collapsed && isGroupOpen('{{ $item->key }}')"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 -translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0"
                            x-transition:leave-end="opacity-0 -translate-y-1"
                            class="ml-5 my-1 pl-4 border-l-2 border-slate-200 dark:border-slate-800 space-y-1"
                        >
                            @foreach ($item->children as $child)
                                @php
                                    $isChildCurrent = $child->isActive();
                                @endphp
                                <a
                                    href="{{ $child->getUrl() }}"
                                    wire:navigate
                                    class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-200 {{ $isChildCurrent ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 font-bold shadow-xs' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100/60 dark:hover:bg-slate-800/40' }}"
                                >
                                    <span class="w-4 text-center shrink-0">
                                        <i class="{{ $child->icon }} text-xs {{ $isChildCurrent ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}"></i>
                                    </span>
                                    <span class="truncate">{{ $child->getLabel() }}</span>
                                </a>
                            @endforeach
                        </div>

                        <!-- Collapsed Floating Flyout Dropdown -->
                        <div
                            x-show="collapsed"
                            class="absolute left-full top-0 ml-3 w-56 p-2 bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl shadow-2xl z-50 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all duration-200"
                        >
                            <div class="px-3 py-2 flex items-center gap-2 border-b border-slate-100 dark:border-slate-800 mb-1">
                                <i class="{{ $item->icon }} text-teal-600 dark:text-teal-400 text-sm"></i>
                                <span class="text-xs font-bold text-slate-900 dark:text-white">{{ $item->getLabel() }}</span>
                            </div>
                            <div class="space-y-0.5">
                                @foreach ($item->children as $child)
                                    @php
                                        $isChildCurrent = $child->isActive();
                                    @endphp
                                    <a
                                        href="{{ $child->getUrl() }}"
                                        wire:navigate
                                        class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg text-xs font-medium transition-all duration-150 {{ $isChildCurrent ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                                    >
                                        <i class="{{ $child->icon }} text-xs w-4 text-center {{ $isChildCurrent ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}"></i>
                                        <span class="truncate">{{ $child->getLabel() }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        <!-- Desktop Bottom Collapse Toggle Button -->
        <div class="p-3 border-t border-slate-200/80 dark:border-slate-800">
            <button
                type="button"
                @click="toggleCollapse()"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
                :class="collapsed ? 'justify-center px-0 w-12 h-10 mx-auto' : ''"
                :title="collapsed ? '{{ __('menu.expand_sidebar') }}' : '{{ __('menu.collapse_sidebar') }}'"
                aria-label="{{ __('menu.toggle_sidebar') }}"
            >
                <i
                    class="fa-solid text-sm transition-transform duration-300"
                    :class="collapsed ? 'fa-angles-right text-teal-600 dark:text-teal-400' : 'fa-angles-left'"
                ></i>
                <span x-show="!collapsed" class="truncate">
                    {{ __('menu.collapse_sidebar') }}
                </span>
            </button>
        </div>
    </aside>

    <!-- ========================================== -->
    <!-- MOBILE OFF-CANVAS DRAWER                   -->
    <!-- ========================================== -->
    <!-- Backdrop Overlay -->
    <div
        x-show="mobileOpen"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeMobile()"
        class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-xs lg:hidden"
        style="display: none;"
    ></div>

    <!-- Mobile Slide-In Sidebar -->
    <aside
        x-show="mobileOpen"
        x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 flex flex-col w-76 bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 shadow-2xl lg:hidden select-none"
        style="display: none;"
        aria-label="{{ __('menu.navigation') }}"
    >
        <!-- Mobile Header -->
        <div class="h-18 px-4 flex items-center justify-between border-b border-slate-200/80 dark:border-slate-800">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-3">
                <x-app-logo-icon class="h-9 w-9 shrink-0" />
                <div class="flex flex-col">
                    <span class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-none">
                        Fattura<span class="text-teal-600 dark:text-teal-400">Facile</span>
                    </span>
                    <span class="text-[10px] font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider mt-1">ERP Suite</span>
                </div>
            </a>
            <button
                type="button"
                @click="closeMobile()"
                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
                aria-label="{{ __('menu.close_menu') }}"
            >
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Mobile Navigation List -->
        <nav class="flex-1 overflow-y-auto custom-scrollbar px-3 py-4 space-y-1.5">
            @foreach ($items as $item)
                @php
                    $hasChildren = $item->hasChildren();
                    $isActive = $item->isActive();
                    $isSelfOrChildActive = $item->isSelfOrChildActive();
                @endphp

                <div class="space-y-1">
                    @if (! $hasChildren)
                        <a
                            href="{{ $item->getUrl() }}"
                            wire:navigate
                            @click="closeMobile()"
                            class="flex items-center gap-3.5 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isActive ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 font-semibold shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <span class="w-6 text-center shrink-0">
                                <i class="{{ $item->icon }} text-base {{ $isActive ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}"></i>
                            </span>
                            <span class="truncate">{{ $item->getLabel() }}</span>
                        </a>
                    @else
                        <button
                            type="button"
                            @click="toggleGroup('mobile_{{ $item->key }}')"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ $isSelfOrChildActive ? 'bg-slate-100 dark:bg-slate-800 text-teal-700 dark:text-teal-300 font-semibold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        >
                            <div class="flex items-center gap-3.5">
                                <span class="w-6 text-center shrink-0">
                                    <i class="{{ $item->icon }} text-base {{ $isSelfOrChildActive ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}"></i>
                                </span>
                                <span class="truncate">{{ $item->getLabel() }}</span>
                            </div>
                            <i
                                class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200"
                                :class="isGroupOpen('mobile_{{ $item->key }}') ? 'rotate-180 text-teal-600 dark:text-teal-400' : ''"
                            ></i>
                        </button>

                        <div
                            x-show="isGroupOpen('mobile_{{ $item->key }}')"
                            x-transition
                            class="ml-5 pl-4 border-l-2 border-slate-200 dark:border-slate-800 space-y-1 my-1"
                        >
                            @foreach ($item->children as $child)
                                @php
                                    $isChildCurrent = $child->isActive();
                                @endphp
                                <a
                                    href="{{ $child->getUrl() }}"
                                    wire:navigate
                                    @click="closeMobile()"
                                    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs font-medium transition-all {{ $isChildCurrent ? 'bg-teal-50 dark:bg-teal-950/60 text-teal-700 dark:text-teal-300 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                                >
                                    <span class="w-4 text-center shrink-0">
                                        <i class="{{ $child->icon }} text-xs {{ $isChildCurrent ? 'text-teal-600 dark:text-teal-400' : 'text-slate-400' }}"></i>
                                    </span>
                                    <span class="truncate">{{ $child->getLabel() }}</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </nav>

        <!-- Mobile Bottom Controls -->
        <div class="p-4 border-t border-slate-200/80 dark:border-slate-800 flex flex-col gap-3">
            <x-language-switcher variant="compact" class="w-full justify-center" />
        </div>
    </aside>
</div>
