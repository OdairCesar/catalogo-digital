@props(['fields' => []])

@if (!empty($fields))
    <dl {{ $attributes->class(['mt-3 space-y-1 text-sm']) }}>
        @foreach ($fields as $field)
            <div class="flex gap-1.5">
                <dt class="font-semibold">{{ $field['label'] ?? '' }}:</dt>
                <dd>{{ $field['value'] ?? '' }}</dd>
            </div>
        @endforeach
    </dl>
@endif
