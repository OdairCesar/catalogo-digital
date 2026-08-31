<?php echo '<?xml version="1.0"?>'; ?>
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
<title>{{ $company->name }}</title>
<link>{{ route('home') }}</link>
<description>Catálogo de produtos de {{ $company->name }}</description>
@foreach ($items as $item)
<item>
  <g:id>{{ $item['id'] }}</g:id>
  <title>{{ $item['title'] }}</title>
  <description>{{ $item['description'] }}</description>
  <link>{{ $item['link'] }}</link>
@if ($item['image_link'])
  <g:image_link>{{ $item['image_link'] }}</g:image_link>
@endif
@foreach ($item['additional_image_links'] as $additionalImageLink)
  <g:additional_image_link>{{ $additionalImageLink }}</g:additional_image_link>
@endforeach
  <g:availability>{{ $item['availability'] }}</g:availability>
@if ($item['price'])
  <g:price>{{ $item['price'] }}</g:price>
@endif
@if ($item['sale_price'])
  <g:sale_price>{{ $item['sale_price'] }}</g:sale_price>
@endif
  <g:condition>{{ $item['condition'] }}</g:condition>
@if ($item['brand'])
  <g:brand>{{ $item['brand'] }}</g:brand>
@endif
@if ($item['gtin'])
  <g:gtin>{{ $item['gtin'] }}</g:gtin>
@endif
@if ($item['mpn'])
  <g:mpn>{{ $item['mpn'] }}</g:mpn>
@endif
@if ($item['identifier_exists'])
  <g:identifier_exists>{{ $item['identifier_exists'] }}</g:identifier_exists>
@endif
@if ($item['google_product_category'])
  <g:google_product_category>{{ $item['google_product_category'] }}</g:google_product_category>
@endif
@if ($item['gender'])
  <g:gender>{{ $item['gender'] }}</g:gender>
@endif
@if ($item['age_group'])
  <g:age_group>{{ $item['age_group'] }}</g:age_group>
@endif
@if ($item['color'])
  <g:color>{{ $item['color'] }}</g:color>
@endif
@if ($item['size'])
  <g:size>{{ $item['size'] }}</g:size>
@endif
@if ($item['material'])
  <g:material>{{ $item['material'] }}</g:material>
@endif
@if ($item['pattern'])
  <g:pattern>{{ $item['pattern'] }}</g:pattern>
@endif
@if ($item['item_group_id'])
  <g:item_group_id>{{ $item['item_group_id'] }}</g:item_group_id>
  <g:item_group_title>{{ $item['item_group_title'] }}</g:item_group_title>
@endif
@if ($item['shipping_weight'])
  <g:shipping_weight>{{ $item['shipping_weight'] }}</g:shipping_weight>
@endif
</item>
@endforeach
</channel>
</rss>
