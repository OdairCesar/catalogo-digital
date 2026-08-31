@php
    use App\Enums\ProductImportStatus;
    use App\Filament\Resources\Products\ProductResource;
    use App\Filament\Resources\Products\Schemas\ReviewProductImportForm;
    use Filament\Support\Icons\Heroicon;

    $status = $this->import->status;
    $isProcessing = in_array($status, [ProductImportStatus::Pending, ProductImportStatus::Mapping, ProductImportStatus::Importing], true);
@endphp

<x-filament-panels::page>
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-x-6 gap-y-1 flex-wrap text-sm text-gray-600 dark:text-gray-400">
            <span class="inline-flex items-center gap-1.5">
                <x-filament::icon :icon="Heroicon::OutlinedDocumentText" class="h-4 w-4 shrink-0" />
                {{ $this->import->original_filename }}
            </span>

            <span class="inline-flex items-center gap-1.5">
                <x-filament::icon :icon="Heroicon::OutlinedBuildingOffice2" class="h-4 w-4 shrink-0" />
                {{ $this->import->company->name }}
            </span>
        </div>

        <x-filament::badge :color="$status->getColor()">
            {{ $status->getLabel() }}
        </x-filament::badge>
    </div>

    @if ($isProcessing)
        <x-filament::section wire:poll.5s class="mt-6">
            <div class="flex flex-col items-center gap-3 py-10 text-center">
                <x-filament::loading-indicator class="h-8 w-8 text-primary-500" />

                <p class="text-sm font-medium text-gray-950 dark:text-white">
                    {{ $status->getLabel() }}
                </p>

                <p class="max-w-sm text-sm text-gray-500 dark:text-gray-400">
                    Esta página se atualiza sozinha assim que terminar — não precisa recarregar.
                </p>
            </div>
        </x-filament::section>
    @elseif ($status === ProductImportStatus::Failed)
        <x-filament::callout
            class="mt-6"
            color="danger"
            :icon="Heroicon::OutlinedXCircle"
            heading="A importação falhou"
            :description="$this->import->ai_error ?? 'Não foi possível concluir a importação.'"
        >
            <x-slot name="footer">
                <x-filament::button tag="a" :href="ProductResource::getUrl('import')" color="danger" outlined>
                    Tentar novamente
                </x-filament::button>
            </x-slot>
        </x-filament::callout>
    @elseif ($status === ProductImportStatus::AwaitingReview)
        @php
            $preview = $this->getPreview();
            $toCreate = $preview ? collect($preview->products)->whereNull('existingProductId')->count() : 0;
            $toUpdate = $preview ? collect($preview->products)->whereNotNull('existingProductId')->count() : 0;
            $shown = $preview ? array_slice($preview->products, 0, 50) : [];
        @endphp

        <div class="mt-6 flex flex-col gap-6">
            <x-filament::section>
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-950 dark:text-white">
                            {{ $toCreate }} produto(s) novo(s), {{ $toUpdate }} atualização(ões)
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Confira embaixo e confirme.</p>
                    </div>

                    <x-filament::button wire:click="confirm" :icon="Heroicon::OutlinedCheckCircle" size="lg">
                        Confirmar importação
                    </x-filament::button>
                </div>

                @if ($preview && $preview->warnings !== [])
                    <x-filament::callout
                        class="mt-4"
                        color="warning"
                        :icon="Heroicon::OutlinedExclamationTriangle"
                        heading="Antes de confirmar"
                    >
                        <x-slot name="description">
                            <ul class="list-disc space-y-1 pl-4">
                                @foreach ($preview->warnings as $warning)
                                    <li>{{ $warning }}</li>
                                @endforeach
                            </ul>
                        </x-slot>
                    </x-filament::callout>
                @endif
            </x-filament::section>

            <x-filament::section heading="Como interpretamos sua planilha">
                <ul class="divide-y divide-gray-100 text-sm dark:divide-white/5">
                    @php $groupingHeader = $this->groupingHeader(); @endphp

                    <li class="flex items-center justify-between gap-4 py-2">
                        <span class="text-gray-500 dark:text-gray-400">Variações do mesmo produto</span>
                        <span class="flex items-center gap-2">
                            <span class="font-medium text-gray-950 dark:text-white">
                                {{ $groupingHeader !== null ? "Agrupadas por \"{$groupingHeader}\"" : 'Cada linha é um produto diferente' }}
                            </span>
                            {{ ($this->editGroupingAction)([]) }}
                        </span>
                    </li>

                    @foreach ($this->mappingColumns() as $column)
                        <li class="flex items-center justify-between gap-4 py-2">
                            <span class="text-gray-500 dark:text-gray-400">{{ $column->header }}</span>
                            <span class="flex items-center gap-2">
                                <span class="font-medium text-gray-950 dark:text-white">
                                    {{ ReviewProductImportForm::summaryLine($column) }}
                                </span>
                                {{ ($this->editColumnAction)(['header' => $column->header]) }}
                            </span>
                        </li>
                    @endforeach
                </ul>
            </x-filament::section>

            @if ($preview)
                <x-filament::section heading="Produtos">
                    @if ($shown === [])
                        <x-filament::empty-state
                            :icon="Heroicon::OutlinedTableCells"
                            heading="Nenhum produto identificado"
                            description="Revise o mapeamento das colunas acima — nenhuma linha da planilha resultou em um produto."
                        />
                    @else
                        <div class="fi-ta-ctn overflow-x-auto rounded-xl border border-gray-200 dark:border-white/10">
                            <table class="w-full text-start text-sm">
                                <thead class="border-b border-gray-200 dark:border-white/10">
                                    <tr>
                                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Título</th>
                                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Ação</th>
                                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Categoria</th>
                                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Variações</th>
                                        <th class="px-4 py-2.5 text-start font-medium text-gray-500 dark:text-gray-400">Avisos</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                                    @foreach ($shown as $product)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                            <td class="px-4 py-2.5 font-medium text-gray-950 dark:text-white">
                                                {{ $product->fields['title'] ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5">
                                                <x-filament::badge :color="$product->existingProductId ? 'warning' : 'success'" size="sm">
                                                    {{ $product->existingProductId ? 'Atualizar' : 'Criar' }}
                                                </x-filament::badge>
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                {{ $product->category?->name ?? '—' }}
                                            </td>
                                            <td class="px-4 py-2.5 text-gray-600 dark:text-gray-400">
                                                {{ count($product->variants) }}
                                            </td>
                                            <td class="px-4 py-2.5 text-warning-600 dark:text-warning-400">
                                                {{ implode(', ', $product->warnings) ?: '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if (count($preview->products) > 50)
                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                Mostrando os primeiros 50 de {{ count($preview->products) }} produtos.
                            </p>
                        @endif
                    @endif
                </x-filament::section>
            @endif
        </div>
    @elseif ($status === ProductImportStatus::Completed)
        @php $result = $this->import->result ?? []; @endphp

        <div class="mt-6 flex flex-col gap-6">
            <x-filament::callout
                color="success"
                :icon="Heroicon::OutlinedCheckCircle"
                heading="Importação concluída"
                :description="($result['created'] ?? 0).' criado(s), '.($result['updated'] ?? 0).' atualizado(s), '.($result['skipped'] ?? 0).' ignorado(s).'"
            >
                <x-slot name="footer">
                    <div class="flex flex-wrap gap-3">
                        <x-filament::button tag="a" :href="ProductResource::getUrl()" :icon="Heroicon::OutlinedCube">
                            Ver produtos
                        </x-filament::button>

                        <x-filament::button tag="a" :href="ProductResource::getUrl('import')" color="gray" :icon="Heroicon::OutlinedArrowUpTray">
                            Nova importação
                        </x-filament::button>
                    </div>
                </x-slot>
            </x-filament::callout>

            @if (! empty($result['errors']))
                <x-filament::callout
                    color="danger"
                    :icon="Heroicon::OutlinedXCircle"
                    heading="Algumas linhas não foram importadas"
                >
                    <x-slot name="description">
                        <ul class="list-disc space-y-1 pl-4">
                            @foreach ($result['errors'] as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-slot>
                </x-filament::callout>
            @endif
        </div>
    @endif
</x-filament-panels::page>
