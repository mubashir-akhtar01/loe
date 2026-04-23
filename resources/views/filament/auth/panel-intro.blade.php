@props([
    'eyebrow',
    'headline',
    'highlights' => [],
    'panelLabel',
    'tone' => 'employee',
])

@php
    $badgeClasses = $tone === 'admin'
        ? 'border-[rgba(15,23,42,0.12)] bg-[linear-gradient(135deg,rgba(245,158,11,0.16),rgba(251,191,36,0.06))] text-slate-700 dark:border-white/10 dark:bg-[linear-gradient(135deg,rgba(251,191,36,0.18),rgba(15,23,42,0.42))] dark:text-amber-100'
        : 'border-[rgba(14,165,233,0.14)] bg-[linear-gradient(135deg,rgba(14,165,233,0.14),rgba(245,158,11,0.08))] text-slate-700 dark:border-white/10 dark:bg-[linear-gradient(135deg,rgba(14,165,233,0.2),rgba(245,158,11,0.12))] dark:text-sky-100';
@endphp

<div class="loe-auth-intro">
    <div class="loe-auth-intro__surface {{ $badgeClasses }}">
        <div class="loe-auth-intro__copy">
            <div class="loe-auth-intro__eyebrow">{{ $eyebrow }}</div>
            <h2 class="loe-auth-intro__headline">{{ $headline }}</h2>
            <p class="loe-auth-intro__description">
                Secure access to the {{ $panelLabel }} with a calmer, faster path back into your work.
            </p>
        </div>

        <div class="loe-auth-intro__highlights">
            @foreach ($highlights as $highlight)
                <div class="loe-auth-intro__highlight">
                    <span class="loe-auth-intro__dot"></span>
                    <span>{{ $highlight }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
