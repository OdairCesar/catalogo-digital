<?php

namespace App\Filament\Pages;

use App\Enums\NavigationGroup;
use App\Enums\SectionType;
use App\Models\SectionTypeSetting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * @property-read Schema $form
 */
class SectionTypeSettings extends Page
{
    protected string $view = 'filament.pages.section-type-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static string|UnitEnum|null $navigationGroup = NavigationGroup::Secoes;

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Ativar/desativar seções';

    protected static ?string $title = 'Ativar/desativar seções';

    /** @var array<string, bool>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(
            collect(SectionType::togglables())
                ->mapWithKeys(fn (SectionType $type): array => [$type->value => SectionTypeSetting::isEnabled($type)])
                ->all(),
        );
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make(
                    collect(SectionType::togglables())
                        ->map(fn (SectionType $type): Toggle => Toggle::make($type->value)->label($type->getLabel()))
                        ->all(),
                )
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')->submit('save'),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach (SectionType::togglables() as $type) {
            SectionTypeSetting::query()->updateOrCreate(
                ['type' => $type],
                ['enabled' => (bool) ($data[$type->value] ?? true)],
            );
        }

        Notification::make()->title('Configurações salvas')->success()->send();
    }
}
