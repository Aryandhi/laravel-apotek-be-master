<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Enums\PurchaseOrderGroup;
use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('po_number')
                    ->label('No. Surat Pesanan')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('title')
                    ->label('Judul Surat Pesanan')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('created_at')
                    ->label('Tanggal Terbuat')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Jumlah Produk')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->suffix(' item'),
                TextColumn::make('supplier.name')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (PurchaseOrderStatus $state) => $state->color())
                    ->formatStateUsing(fn (PurchaseOrderStatus $state) => $state->label()),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseOrderStatus::class)
                    ->multiple(),
                SelectFilter::make('group')
                    ->label('Tipe SP')
                    ->options(PurchaseOrderGroup::class)
                    ->multiple(),
                SelectFilter::make('supplier')
                    ->label('Supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
                EditAction::make()
                    ->visible(fn () => auth()->user()?->can('purchase-orders.update') ?? false)
                    ->disabled(fn (PurchaseOrder $record) => ! $record->status->isEditable()),
                Action::make('submitApproval')
                    ->label('Ajukan Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (PurchaseOrder $record) => (auth()->user()?->can('purchase-orders.update') ?? false)
                        && $record->status === PurchaseOrderStatus::Draft)
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update(['status' => PurchaseOrderStatus::Approval])),
                Action::make('approveOrder')
                    ->label('Setujui & Order')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (PurchaseOrder $record) => (auth()->user()?->can('purchase-orders.approve') ?? false)
                        && $record->status === PurchaseOrderStatus::Approval)
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update(['status' => PurchaseOrderStatus::Order])),
                Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->visible(fn () => auth()->user()?->can('purchase-orders.print') ?? false)
                    ->disabled(fn (PurchaseOrder $record) => ! $record->status->isPrintable())
                    ->url(fn (PurchaseOrder $record) => route('purchase-orders.print', $record))
                    ->openUrlInNewTab(),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('purchase-orders.delete') ?? false)
                    ->disabled(fn (PurchaseOrder $record) => ! $record->status->isDeletable()),
            ]);
    }
}
