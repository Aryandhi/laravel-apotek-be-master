<?php

namespace App\Filament\Resources\StockOpnames\Tables;

use App\Enums\StockOpnameStatus;
use App\Services\StockOpnameService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StockOpnamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (StockOpnameStatus $state) => $state->color())
                    ->formatStateUsing(fn (StockOpnameStatus $state) => $state->label()),
                TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Item')
                    ->badge()
                    ->color('info')
                    ->alignCenter()
                    ->suffix(' produk'),
                TextColumn::make('total_difference')
                    ->label('Selisih')
                    ->getStateUsing(function ($record) {
                        $total = $record->items->sum('difference');

                        return $total;
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state > 0 => 'success',
                        $state < 0 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => $state > 0 ? "+{$state}" : $state)
                    ->alignCenter(),
                TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('approvedBy.name')
                    ->label('Disetujui Oleh')
                    ->placeholder('-')
                    ->toggleable(),
                TextColumn::make('approved_at')
                    ->label('Tgl Approval')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')
                    ->label('Catatan')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(StockOpnameStatus::class)
                    ->multiple(),
                SelectFilter::make('user')
                    ->label('Dibuat Oleh')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('approved_by')
                    ->label('Disetujui Oleh')
                    ->relationship('approvedBy', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                Action::make('submit')
                    ->label('Submit')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Submit untuk Approval')
                    ->modalDescription('Stock opname akan disubmit untuk di-review dan di-approve. Lanjutkan?')
                    ->visible(fn ($record) => in_array($record->status, [StockOpnameStatus::Draft, StockOpnameStatus::InProgress]))
                    ->action(function ($record) {
                        try {
                            app(StockOpnameService::class)->submitForApproval($record);
                            Notification::make()
                                ->success()
                                ->title('Berhasil')
                                ->body('Stock opname berhasil disubmit untuk approval.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Stock Opname')
                    ->modalDescription('Menyetujui stock opname akan menyesuaikan stok sesuai hasil perhitungan fisik. Tindakan ini tidak dapat dibatalkan. Lanjutkan?')
                    ->visible(fn ($record) => $record->status === StockOpnameStatus::PendingApproval)
                    ->action(function ($record) {
                        try {
                            app(StockOpnameService::class)->approve($record, auth()->id());
                            Notification::make()
                                ->success()
                                ->title('Berhasil')
                                ->body('Stock opname berhasil di-approve dan stok telah disesuaikan.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                Action::make('cancel')
                    ->label('Batalkan')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Stock Opname')
                    ->modalDescription('Yakin ingin membatalkan stock opname ini?')
                    ->visible(fn ($record) => ! in_array($record->status, [StockOpnameStatus::Approved, StockOpnameStatus::Cancelled]))
                    ->action(function ($record) {
                        try {
                            app(StockOpnameService::class)->cancel($record);
                            Notification::make()
                                ->success()
                                ->title('Berhasil')
                                ->body('Stock opname berhasil dibatalkan.')
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Gagal')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                ViewAction::make(),
                EditAction::make()
                    ->visible(fn ($record) => auth()->user()?->can('stock-opname.update') && ! in_array($record->status, [StockOpnameStatus::Approved, StockOpnameStatus::Cancelled])),
                DeleteAction::make()
                    ->visible(fn () => auth()->user()?->can('stock-opname.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->can('stock-opname.delete')),
                ]),
            ]);
    }
}
