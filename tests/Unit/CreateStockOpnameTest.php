<?php

namespace Tests\Unit;

use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use Tests\TestCase;

class CreateStockOpnameTest extends TestCase
{
    public function test_get_progress_summary_counts_items_and_groups_by_product(): void
    {
        $page = new CreateStockOpname;

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
        $page = new CreateStockOpname;

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

    public function test_merge_visible_items_into_source_preserves_items_hidden_by_filter(): void
    {
        $page = new CreateStockOpname;

        $source = [
            'a' => ['product_id' => 1, 'physical_stock' => 5],
            'b' => ['product_id' => 2, 'physical_stock' => 3],
            'c' => ['product_id' => 3, 'physical_stock' => 8],
        ];

        // Only product 1 is visible under the active filter; the user edits it.
        $visibleItems = [
            'a' => ['product_id' => 1, 'physical_stock' => 99],
        ];

        $merged = $page->mergeVisibleItemsIntoSource($source, $visibleItems, 1, '');

        $this->assertCount(3, $merged);
        $this->assertSame(99, $merged['a']['physical_stock']);
        $this->assertSame(3, $merged['b']['physical_stock']);
        $this->assertSame(8, $merged['c']['physical_stock']);
    }

    public function test_restore_full_items_from_source_replaces_filtered_items_with_full_list(): void
    {
        $page = new CreateStockOpname;
        $page->data = [
            'items' => [
                'a' => ['product_id' => 1],
            ],
            'items_source' => [
                'a' => ['product_id' => 1],
                'b' => ['product_id' => 2],
                'c' => ['product_id' => 3],
            ],
        ];

        $method = new \ReflectionMethod($page, 'restoreFullItemsFromSource');
        $method->setAccessible(true);
        $method->invoke($page);

        $this->assertCount(3, $page->data['items']);
        $this->assertArrayHasKey('b', $page->data['items']);
        $this->assertArrayHasKey('c', $page->data['items']);
    }

    public function test_merge_visible_items_into_source_removes_deleted_item(): void
    {
        $page = new CreateStockOpname;

        $source = [
            'a' => ['product_id' => 1, 'physical_stock' => 5],
            'b' => ['product_id' => 2, 'physical_stock' => 3],
        ];

        // Item 'a' was deleted via the delete icon while the "product 1" filter was active.
        $visibleItems = [];

        $merged = $page->mergeVisibleItemsIntoSource($source, $visibleItems, 1, '');

        $this->assertCount(1, $merged);
        $this->assertArrayNotHasKey('a', $merged);
        $this->assertArrayHasKey('b', $merged);
    }
}
