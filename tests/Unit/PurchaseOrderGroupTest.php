<?php

namespace Tests\Unit;

use App\Enums\CategoryType;
use App\Enums\PurchaseOrderGroup;
use Tests\TestCase;

class PurchaseOrderGroupTest extends TestCase
{
    public function test_reguler_category_types_map_to_reguler_group(): void
    {
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::ObatBebas->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::ObatBebasTerbatas->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::ObatKeras->purchaseOrderGroup());
    }

    public function test_strict_category_types_map_to_their_own_group(): void
    {
        $this->assertSame(PurchaseOrderGroup::Narkotika, CategoryType::Narkotika->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Psikotropika, CategoryType::Psikotropika->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Prekursor, CategoryType::Prekursor->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Oot, CategoryType::Oot->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Alkes, CategoryType::Alkes->purchaseOrderGroup());
    }

    public function test_other_category_types_default_to_reguler_group(): void
    {
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::Kosmetik->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::Suplemen->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::ObatTradisional->purchaseOrderGroup());
        $this->assertSame(PurchaseOrderGroup::Reguler, CategoryType::Lainnya->purchaseOrderGroup());
    }

    public function test_narkotika_and_psikotropika_are_limited_to_one_item_per_document(): void
    {
        $this->assertSame(1, PurchaseOrderGroup::Narkotika->maxItemsPerDocument());
        $this->assertSame(1, PurchaseOrderGroup::Psikotropika->maxItemsPerDocument());
    }

    public function test_other_groups_have_no_item_limit(): void
    {
        $this->assertNull(PurchaseOrderGroup::Reguler->maxItemsPerDocument());
        $this->assertNull(PurchaseOrderGroup::Oot->maxItemsPerDocument());
        $this->assertNull(PurchaseOrderGroup::Prekursor->maxItemsPerDocument());
        $this->assertNull(PurchaseOrderGroup::Alkes->maxItemsPerDocument());
    }

    public function test_narkotika_and_psikotropika_use_the_narcotic_print_template(): void
    {
        $this->assertTrue(PurchaseOrderGroup::Narkotika->usesNarcoticTemplate());
        $this->assertTrue(PurchaseOrderGroup::Psikotropika->usesNarcoticTemplate());
        $this->assertFalse(PurchaseOrderGroup::Reguler->usesNarcoticTemplate());
        $this->assertFalse(PurchaseOrderGroup::Oot->usesNarcoticTemplate());
        $this->assertFalse(PurchaseOrderGroup::Prekursor->usesNarcoticTemplate());
        $this->assertFalse(PurchaseOrderGroup::Alkes->usesNarcoticTemplate());
    }
}
