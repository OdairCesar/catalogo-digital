<x-mail::message>
# Nova submissão em "{{ $toolTitle }}"

**Nome:** {{ $submission->name }}

**E-mail:** {{ $submission->email }}

@if ($submission->phone)
**Telefone:** {{ $submission->phone }}
@endif

@if (! empty($submission->result))
**Resultado gerado:**

@foreach ($submission->result as $key => $value)
- **{{ $key }}:** {{ is_array($value) ? implode(', ', array_map(fn ($item) => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item, $value)) : $value }}
@endforeach
@endif

@if ($submission->source_url)
**Enviado a partir de:** {{ $submission->source_url }}
@endif

O detalhe completo pode ser consultado no painel administrativo, em Ferramentas &gt; Submissões.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
