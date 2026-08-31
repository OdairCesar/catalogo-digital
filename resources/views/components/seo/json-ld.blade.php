@props([
    'data' => [],
])

@foreach ($data as $block)
    <script type="application/ld+json">{!! json_encode($block, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endforeach
