<?php

namespace Tests\Unit;

use App\Filament\Resources\StockOpnames\Pages\EditStockOpname;
use ReflectionMethod;
use Tests\TestCase;

class EditStockOpnameTest extends TestCase
{
    public function test_restore_full_items_from_source_replaces_filtered_items_with_full_list(): void
    {
        $page = new EditStockOpname;
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

        $method = new ReflectionMethod($page, 'restoreFullItemsFromSource');
        $method->setAccessible(true);
        $method->invoke($page);

        $this->assertCount(3, $page->data['items']);
        $this->assertArrayHasKey('b', $page->data['items']);
        $this->assertArrayHasKey('c', $page->data['items']);
    }

    public function test_restore_full_items_from_source_is_noop_without_items_source(): void
    {
        $page = new EditStockOpname;
        $page->data = [
            'items' => [
                'a' => ['product_id' => 1],
            ],
        ];

        $method = new ReflectionMethod($page, 'restoreFullItemsFromSource');
        $method->setAccessible(true);
        $method->invoke($page);

        $this->assertCount(1, $page->data['items']);
    }
}
