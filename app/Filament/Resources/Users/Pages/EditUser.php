<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterSave(): void
    {
        $currentRole = $this->record->role instanceof UserRole
            ? $this->record->role->value
            : UserRole::Cashier->value;

        $resolvedRole = $this->resolveLegacyRoleValue(
            $this->record->roles()->pluck('name')->all(),
            $currentRole
        );

        if ($currentRole !== $resolvedRole) {
            $this->record->forceFill(['role' => $resolvedRole])->saveQuietly();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    private function resolveLegacyRoleValue(array $roleNames, string $fallbackRole): string
    {
        if ($roleNames === []) {
            return $fallbackRole;
        }

        $normalizedRoleNames = array_map(
            static fn (string $roleName): string => strtolower(trim($roleName)),
            array_unique($roleNames)
        );

        if (in_array('super admin', $normalizedRoleNames, true) || in_array('owner', $normalizedRoleNames, true)) {
            return UserRole::Owner->value;
        }

        if (in_array('admin', $normalizedRoleNames, true)) {
            return UserRole::Admin->value;
        }

        if (in_array('apoteker', $normalizedRoleNames, true) || in_array('pharmacist', $normalizedRoleNames, true)) {
            return UserRole::Pharmacist->value;
        }

        if (in_array('asisten', $normalizedRoleNames, true) || in_array('assistant', $normalizedRoleNames, true)) {
            return UserRole::Assistant->value;
        }

        if (in_array('kasir', $normalizedRoleNames, true) || in_array('cashier', $normalizedRoleNames, true)) {
            return UserRole::Cashier->value;
        }

        if (in_array('gudang', $normalizedRoleNames, true) || in_array('inventory', $normalizedRoleNames, true)) {
            return UserRole::Inventory->value;
        }

        return $fallbackRole;
    }
}
