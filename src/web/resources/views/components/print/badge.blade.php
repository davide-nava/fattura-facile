@props([
    'status' => 'default',
    'label' => null,
])

@php
    $statusKey = strtolower(trim((string)$status));
    $badgeClass = match($statusKey) {
        'paid', 'approved', 'active', 'closed_won' => 'badge-success',
        'issued', 'pending', 'proposal', 'received' => 'badge-warning',
        'overdue', 'cancelled', 'rejected', 'closed_lost' => 'badge-danger',
        'partially_paid', 'draft' => 'badge-info',
        default => 'badge-neutral',
    };
@endphp

<span class="badge {{ $badgeClass }}">{{ $label ?? strtoupper((string)$status) }}</span>
