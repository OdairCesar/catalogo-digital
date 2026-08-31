<x-filament-panels::page>
    <form wire:submit="analyze">
        {{ $this->form }}

        <x-filament::button type="submit" class="mt-6">
            Analisar planilha
        </x-filament::button>
    </form>
</x-filament-panels::page>
