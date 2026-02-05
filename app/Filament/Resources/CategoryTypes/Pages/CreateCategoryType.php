<?php

namespace App\Filament\Resources\CategoryTypes\Pages;

use App\Filament\Resources\CategoryTypes\CategoryTypeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCategoryType extends CreateRecord
{
    protected static string $resource = CategoryTypeResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
