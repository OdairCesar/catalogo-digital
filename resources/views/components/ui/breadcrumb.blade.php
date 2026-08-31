@props([
    'items' => [],
])

@if (count($items) > 0)
    <nav aria-label="Breadcrumb" {{ $attributes->class(['border-b border-slate-800/10 bg-slate-50 px-5 py-3 sm:px-8 min-[960px]:px-14']) }}>
        <ol class="flex flex-wrap items-center gap-1.5 text-[13px] text-slate-500">
            @foreach ($items as $index => $item)
                <li class="flex items-center gap-1.5">
                    @if ($index > 0)
                        <svg class="h-3 w-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    @endif

                    @if (! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}" title="{{ $item['label'] }}" class="hover:text-brand-purple">{{ $item['label'] }}</a>
                    @else
                        <span aria-current="page" class="font-medium text-slate-800">{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
