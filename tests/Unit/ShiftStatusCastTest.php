<?php

namespace Tests\Unit;

use App\Enums\ShiftStatus;
use App\Models\CashierShift;
use Tests\TestCase;

class ShiftStatusCastTest extends TestCase
{
    public function test_it_reads_legacy_close_value_as_closed_enum(): void
    {
        $shift = new CashierShift;
        $shift->setRawAttributes(['status' => 'close'], true);

        $this->assertSame(ShiftStatus::Closed, $shift->status);
    }

    public function test_it_normalizes_close_when_setting_status(): void
    {
        $shift = new CashierShift;
        $shift->status = 'close';

        $this->assertSame('closed', $shift->getAttributes()['status']);
    }
}
