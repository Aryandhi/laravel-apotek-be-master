<?php

namespace Tests\Unit;

use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use Tests\TestCase;

class CreateStockOpnameTest extends TestCase
{
    public function test_get_progress_summary_counts_items_and_groups_by_product(): void
    {
        $page = new CreateStockOpname();

        $items = [
            [
                'product_id' => 1,
                'product_batch_id' => 10,
                'physical_stock' => 10,
                'difference' => 2,
            ],
            [
                'product_id' => 1,
                'product_batch_id' => 11,
                'physical_stock' => 4,
                'difference' => -1,
            ],
            [
                'product_id' => 2,
                'product_batch_id' => 12,
                'physical_stock' => 7,
                'difference' => 0,
            ],
        ];

        $summary = $page->getProgressSummary($items, [1 => 'Paracetamol', 2 => 'Amoxicillin']);

        $this->assertSame(3, $summary['total_items']);
        $this->assertSame(2, $summary['products'][1]['count']);
        $this->assertSame(1, $summary['products'][2]['count']);
        $this->assertSame(2, $summary['with_discrepancy']);
    }

    public function test_get_filtered_items_for_stock_opname_filters_by_selected_product(): void
    {
        $page = new CreateStockOpname();

        $items = [
            [
                'product_id' => 1,
                'product_batch_id' => 10,
            ],
            [
                'product_id' => 2,
                'product_batch_id' => 11,
            ],
        ];

        $filteredItems = $page->getFilteredItemsForStockOpname($items, 1, '');

        $this->assertCount(1, $filteredItems);
        $this->assertSame(1, $filteredItems[0]['product_id']);
    }
}
