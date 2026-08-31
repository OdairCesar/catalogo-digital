<?php

namespace App\Filament\Resources\ServiceClusters\Pages;

use App\Actions\Support\GenerateUniqueSlug;
use App\Enums\ServiceClusterStatus;
use App\Filament\Resources\ServiceClusters\Schemas\GenerateAiServiceClusterForm;
use App\Filament\Resources\ServiceClusters\ServiceClusterResource;
use App\Jobs\GenerateServiceClusterContent;
use App\Models\ServiceCluster;
use App\Services\ServiceCluster\ServiceClusterAiBrief;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;

/**
 * @property-read Schema $form
 */
class GenerateAiServiceCluster extends Page
{
    protected static string $resource = ServiceClusterResource::class;

    protected string $view = 'filament.resources.service-clusters.pages.generate-ai-service-cluster';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function getTitle(): string
    {
        return 'Gerar cluster com IA';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return GenerateAiServiceClusterForm::configure($schema)->statePath('data');
    }

    public function generate(GenerateUniqueSlug $generateSlug): void
    {
        $brief = ServiceClusterAiBrief::fromArray($this->form->getState());

        $cluster = ServiceCluster::query()->create([
            'service_id' => $brief->serviceId,
            'slug' => $generateSlug(ServiceCluster::class, $brief->topic),
            'name' => $brief->topic,
            'title' => $brief->topic,
            'status' => ServiceClusterStatus::Generating,
            'ai_brief' => $brief->toArray(),
        ]);

        GenerateServiceClusterContent::dispatch($cluster);

        Notification::make()
            ->title('Geração iniciada')
            ->body('A IA está escrevendo o cluster. O rascunho aparecerá em instantes na tela de edição.')
            ->success()
            ->send();

        $this->redirect(ServiceClusterResource::getUrl('edit', ['record' => $cluster]));
    }
}
