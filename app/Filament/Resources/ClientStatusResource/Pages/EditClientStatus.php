<?php

namespace App\Filament\Resources\ClientStatusResource\Pages;

use App\Filament\Resources\ClientStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientStatus extends EditRecord
{
    protected static string $resource = ClientStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
