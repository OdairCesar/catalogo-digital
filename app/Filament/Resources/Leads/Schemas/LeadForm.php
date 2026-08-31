<?php

namespace App\Filament\Resources\Leads\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source')
                    ->label('Origem')
                    ->disabled(),
                TextInput::make('name')
                    ->label('Nome')
                    ->disabled(),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->disabled(),
                TextInput::make('phone')
                    ->label('Telefone')
                    ->tel()
                    ->disabled(),
                Textarea::make('message')
                    ->label('Mensagem')
                    ->disabled()
                    ->columnSpanFull(),
                KeyValue::make('payload')
                    ->label('Detalhes adicionais')
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('source_url')
                    ->label('Enviado a partir de')
                    ->url()
                    ->disabled(),
                DateTimePicker::make('read_at')
                    ->label('Lido em'),
            ]);
    }
}
