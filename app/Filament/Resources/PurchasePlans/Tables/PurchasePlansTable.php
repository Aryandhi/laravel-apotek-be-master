<?php

namespace App\Filament\Resources\PurchasePlans\Tables;

use App\Models\PurchasePlanItem;
use App\Models\Supplier;
use App\Services\PurchaseOrderGenerationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class PurchasePlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                return $query
                    ->active()
                    ->with(['category', 'baseUnit', 'purchasePlanItem'])
                    ->withSum(['activeBatches as stock_qty'], 'stock')
                    ->whereRaw(
                        'COALESCE((select sum(stock) from product_batches
                            where product_batches.product_id = products.id
                            and product_batches.status = ?
                            and product_batches.stock > 0), 0) <= products.min_stock',
                        ['active']
                    );
            })
            ->columns([
                TextColumn::make('code')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('category.type')
                    ->label('Tipe Kategori')
                    ->badge()
                    ->state(fn ($record) => $record->category?->type?->label() ?? '-'),
                TextColumn::make('stock_qty')
                    ->label('Stok')
                    ->state(fn ($record) => (int) ($record->stock_qty ?? 0))
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('min_stock')
                    ->label('Min. Stok')
                    ->sortable()
                    ->alignEnd(),
                SelectColumn::make('purchase_plan_supplier_id')
                    ->label('Supplier')
                    ->placeholder('Pilih supplier')
                    ->options(fn () => Supplier::query()->active()->orderBy('name')->pluck('name', 'id'))
                    ->selectablePlaceholder(true)
                    ->updateStateUsing(function ($record, $state) {
                        $record->purchase_plan_supplier_id = $state ?: null;

                        return $state;
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn ($record) => $record->purchase_plan_supplier_id ? 'Siap Dibuat SP' : 'Menunggu Supplier')
                    ->color(fn ($record) => $record->purchase_plan_supplier_id ? 'success' : 'gray'),
            ])
            ->headerActions([
                Action::make('generatePurchaseOrders')
                    ->label('Buat Surat Pesanan')
                    ->icon(Heroicon::OutlinedDocumentPlus)
                    ->color('primary')
                    ->visible(fn () => auth()->user()?->can('purchase-plans.manage') ?? false)
                    ->disabled(fn () => ! PurchasePlanItem::query()->whereNotNull('supplier_id')->exists())
                    ->requiresConfirmation()
                    ->modalHeading('Buat Surat Pesanan')
                    ->modalDescription('Sistem akan memecah pesanan menjadi beberapa Surat Pesanan berdasarkan Supplier dan Tipe Kategori sesuai regulasi. Lanjutkan?')
                    ->modalSubmitActionLabel('Ya')
                    ->action(function (PurchaseOrderGenerationService $service) {
                        try {
                            $orders = $service->generate(auth()->id());
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Gagal membuat Surat Pesanan')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Surat Pesanan berhasil dibuat')
                            ->body($orders->count().' dokumen Surat Pesanan telah dibuat.')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('name');
    }
}
