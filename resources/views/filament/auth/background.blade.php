@props([
    'panelLabel',
    'eyebrow',
    'tone' => 'employee',
])

@php
    $orbPrimary = $tone === 'admin' ? '#f59e0b' : '#0ea5e9';
    $orbSecondary = $tone === 'admin' ? '#0f172a' : '#f59e0b';
@endphp

<div class="loe-auth-stage" aria-hidden="true">
    <div class="loe-auth-stage__orb loe-auth-stage__orb--one" style="background: radial-gradient(circle, {{ $orbPrimary }} 0%, transparent 68%);"></div>
    <div class="loe-auth-stage__orb loe-auth-stage__orb--two" style="background: radial-gradient(circle, {{ $orbSecondary }} 0%, transparent 70%);"></div>
    <div class="loe-auth-stage__grid"></div>

    <div class="loe-auth-stage__panel">
        <div class="loe-auth-stage__eyebrow">{{ $eyebrow }}</div>
        <div class="loe-auth-stage__title">{{ $panelLabel }}</div>
        <div class="loe-auth-stage__caption">Secure access with a clearer, more premium entry experience.</div>
    </div>
</div>
