<x-mail::message>
# Novo lead recebido

**Origem:** {{ $lead->source }}

**Nome:** {{ $lead->name }}

**E-mail:** {{ $lead->email }}

@if ($lead->phone)
**Telefone:** {{ $lead->phone }}
@endif

@if ($lead->message)
**Mensagem:**

{{ $lead->message }}
@endif

@if (! empty($lead->payload))
**Detalhes adicionais:**

@foreach ($lead->payload as $key => $value)
- **{{ $key }}:** {{ $value }}
@endforeach
@endif

@if ($lead->source_url)
**Enviado a partir de:** {{ $lead->source_url }}
@endif

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
