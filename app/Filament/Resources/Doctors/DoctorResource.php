<?php

namespace App\Filament\Resources\Doctors;

use App\Filament\Resources\Doctors\Pages\CreateDoctor;
use App\Filament\Resources\Doctors\Pages\EditDoctor;
use App\Filament\Resources\Doctors\Pages\ListDoctors;
use App\Filament\Resources\Doctors\Schemas\DoctorForm;
use App\Filament\Resources\Doctors\Tables\DoctorsTable;
use App\Models\Doctor;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class DoctorResource extends Resource
{
    public static function canAccess(): bool
    {
        return false; // Menyembunyikan menu Doctor dari sidebar tanpa menghapus route
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('doctors.create') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->can('doctors.update') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->can('doctors.delete') ?? false;
    }

    protected static ?string $model = Doctor::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static UnitEnum|string|null $navigationGroup = 'Master Data';

    protected static ?string $navigationLabel = 'Dokter';

    protected static ?string $modelLabel = 'Dokter';

    protected static ?string $pluralModelLabel = 'Dokter';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return DoctorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DoctorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDoctors::route('/'),
            'create' => CreateDoctor::route('/create'),
            'edit' => EditDoctor::route('/{record}/edit'),
        ];
    }
}
