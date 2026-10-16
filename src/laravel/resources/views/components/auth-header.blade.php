@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center mb-2">
    <flux:heading size="xl" level="1" class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">{{ $title }}</flux:heading>
    <flux:subheading class="text-sm text-slate-600 dark:text-slate-400 mt-1">{{ $description }}</flux:subheading>
</div>
