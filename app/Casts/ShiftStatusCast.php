<?php

namespace App\Casts;

use App\Enums\ShiftStatus;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class ShiftStatusCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?ShiftStatus
    {
        if ($value === null) {
            return null;
        }

        $normalizedValue = $this->normalizeStatus((string) $value);
        $status = ShiftStatus::tryFrom($normalizedValue);

        if ($status === null) {
            throw new InvalidArgumentException(sprintf('Invalid shift status value [%s].', $value));
        }

        return $status;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ShiftStatus) {
            return $value->value;
        }

        $normalizedValue = $this->normalizeStatus((string) $value);
        $status = ShiftStatus::tryFrom($normalizedValue);

        if ($status === null) {
            throw new InvalidArgumentException(sprintf('Invalid shift status value [%s].', (string) $value));
        }

        return $status->value;
    }

    private function normalizeStatus(string $value): string
    {
        return strtolower(trim($value)) === 'close' ? ShiftStatus::Closed->value : strtolower(trim($value));
    }
}
