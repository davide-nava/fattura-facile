@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('app.name', 'Fattura Facile')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-9 items-center justify-center rounded-xl bg-slate-900 dark:bg-slate-800 p-1">
            <x-app-logo-icon class="size-7" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <a {{ $attributes->merge(['class' => 'flex items-center gap-3 group']) }}>
        <x-app-logo-icon class="h-10 w-10 shrink-0 transition-transform duration-300 group-hover:scale-105" />
        <div class="flex flex-col">
            <span class="text-2xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-none">
                Fattura<span class="text-teal-600 dark:text-teal-400">Facile</span>
            </span>
            <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 mt-1">Fatture semplici, offline e cloud</span>
        </div>
    </a>
@endif
