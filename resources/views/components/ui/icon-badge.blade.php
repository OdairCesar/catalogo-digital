@props([
    'icon',
    'bg' => 'bg-brand-navy',
])

<div {{ $attributes->class(["flex h-12 w-12 items-center justify-center rounded-xl text-white transition-transform duration-300 group-hover:scale-110 group-hover:-rotate-6", $bg]) }}>
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
        @includeIf("partials.icons.{$icon}")
    </svg>
</div>
