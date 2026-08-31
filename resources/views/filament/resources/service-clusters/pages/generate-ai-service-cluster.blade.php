<x-filament-panels::page>
    <form wire:submit="generate">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6">
            Gerar cluster com IA
        </x-filament::button>
    </form>
</x-filament-panels::page>
