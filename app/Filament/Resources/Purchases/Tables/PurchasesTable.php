<?php

namespace App\Filament\Resources\Purchases\Tables;

use App\Enums\BatchStatus;
use App\Enums\PurchaseStatus;
use App\Enums\StockMovementType;
use App\Models\PaymentMethod;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchasePayment;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Jatuh Tempo')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($record) => $record->due_date && $record->due_date->isPast() && ! $record->isPaid() ? 'danger' : null)
                    ->description(fn ($record) => $record->due_date && $record->due_date->isPast() && ! $record->isPaid() ? 'Jatuh tempo!' : null)
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PurchaseStatus $state) => $state->color())
                    ->formatStateUsing(fn (PurchaseStatus $state) => $state->label()),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Item')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->suffix(' produk'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Dibayar')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_status')
                    ->label('Pembayaran')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        if ($record->paid_amount >= $record->total) {
                            return 'Lunas';
                        }
                        if ($record->paid_amount > 0) {
                            return 'Sebagian';
                        }

                        return 'Belum Bayar';
                    })
                    ->color(fn ($state) => match ($state) {
                        'Lunas' => 'success',
                        'Sebagian' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('discount')
                    ->label('Diskon')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax')
                    ->label('Pajak')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseStatus::class)
                    ->multiple(),
                SelectFilter::make('supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('unpaid')
                    ->label('Belum Lunas')
                    ->query(fn (Builder $query) => $query->whereColumn('paid_amount', '<', 'total')),
                Filter::make('paid')
                    ->label('Sudah Lunas')
                    ->query(fn (Builder $query) => $query->whereColumn('paid_amount', '>=', 'total')),
                Filter::make('overdue')
                    ->label('Jatuh Tempo')
                    ->query(fn (Builder $query) => $query
                        ->where('due_date', '<', now())
                        ->whereColumn('paid_amount', '<', 'total')),
                Filter::make('this_month')
                    ->label('Bulan Ini')
                    ->query(fn (Builder $query) => $query->whereMonth('date', now()->month)->whereYear('date', now()->year)),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('purchases.update')),
                Action::make('payment')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('warning')
                    ->visible(fn (Purchase $record) => auth()->user()?->can('purchases.update') && $record->paid_amount < $record->total)
                    ->form([
                        TextInput::make('amount')
                            ->label('Jumlah Bayar')
                            ->numeric()
                            ->required()
                            ->prefix('Rp')
                            ->default(fn (Purchase $record) => $record->total - $record->paid_amount),
                        Select::make('payment_method_id')
                            ->label('Metode Pembayaran')
                            ->options(fn () => PaymentMethod::where('is_active', true)->pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        TextInput::make('reference_number')
                            ->label('No. Referensi')
                            ->placeholder('cth: TRF-001'),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(2),
                    ])
                    ->action(function (Purchase $record, array $data): void {
                        Log::info('=== PURCHASE PAYMENT START ===', [
                            'purchase_id' => $record->id,
                            'invoice' => $record->invoice_number,
                            'data' => $data,
                        ]);

                        try {
                            DB::transaction(function () use ($record, $data) {
                                PurchasePayment::create([
                                    'purchase_id' => $record->id,
                                    'amount' => $data['amount'],
                                    'payment_method_id' => $data['payment_method_id'],
                                    'payment_date' => now(),
                                    'reference_number' => $data['reference_number'] ?? null,
                                    'notes' => $data['notes'] ?? null,
                                    'user_id' => auth()->id(),
                                ]);

                                $record->update([
                                    'paid_amount' => $record->paid_amount + $data['amount'],
                                ]);
                            });

                            Log::info('=== PURCHASE PAYMENT SUCCESS ===');

                            Notification::make()
                                ->title('Pembayaran berhasil')
                                ->body('Pembayaran sebesar Rp '.number_format($data['amount'], 0, ',', '.').' telah dicatat.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('=== PURCHASE PAYMENT ERROR ===', [
                                'message' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);

                            Notification::make()
                                ->title('Gagal menyimpan pembayaran')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('receive')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-truck')
                    ->color('success')
                    ->visible(fn (Purchase $record) => auth()->user()?->can('purchases.update') && $record->status !== PurchaseStatus::Received && $record->status !== PurchaseStatus::Cancelled)
                    ->requiresConfirmation()
                    ->modalHeading('Terima Barang')
                    ->modalDescription(fn (Purchase $record) => "Apakah Anda yakin ingin menerima semua barang dari pembelian {$record->invoice_number}? Stok akan ditambahkan secara otomatis.")
                    ->modalSubmitActionLabel('Ya, Terima Barang')
                    ->action(function (Purchase $record): void {
                        Log::info('=== RECEIVE PURCHASE START ===', [
                            'purchase_id' => $record->id,
                            'invoice' => $record->invoice_number,
                        ]);

                        try {
                            DB::transaction(function () use ($record) {
                                foreach ($record->items as $item) {
                                    $remainingQty = $item->quantity - $item->received_quantity;

                                    if ($remainingQty <= 0) {
                                        continue;
                                    }

                                    $batch = ProductBatch::create([
                                        'product_id' => $item->product_id,
                                        'batch_number' => $item->batch_number ?: 'BTH-'.now()->format('Ymd').'-'.$item->id,
                                        'expired_date' => $item->expired_date ?: now()->addYears(2),
                                        'purchase_price' => $item->purchase_price,
                                        'selling_price' => $item->selling_price ?: $item->product->selling_price,
                                        'stock' => $remainingQty,
                                        'initial_stock' => $remainingQty,
                                        'supplier_id' => $record->supplier_id,
                                        'purchase_id' => $record->id,
                                        'status' => BatchStatus::Active,
                                    ]);

                                    StockMovement::create([
                                        'product_batch_id' => $batch->id,
                                        'type' => StockMovementType::Purchase,
                                        'quantity' => $remainingQty,
                                        'stock_before' => 0,
                                        'stock_after' => $remainingQty,
                                        'reference_type' => Purchase::class,
                                        'reference_id' => $record->id,
                                        'notes' => "Penerimaan dari pembelian {$record->invoice_number}",
                                        'user_id' => auth()->id(),
                                    ]);

                                    $item->update([
                                        'received_quantity' => $item->quantity,
                                    ]);
                                }

                                $record->update([
                                    'status' => PurchaseStatus::Received,
                                ]);
                            });

                            Log::info('=== RECEIVE PURCHASE SUCCESS ===');

                            Notification::make()
                                ->title('Barang berhasil diterima')
                                ->body('Semua barang telah diterima dan stok telah diperbarui.')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Log::error('=== RECEIVE PURCHASE ERROR ===', [
                                'message' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);

                            Notification::make()
                                ->title('Gagal menerima barang')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('purchases.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('purchases.delete')),
                ]),
            ]);
    }
}
