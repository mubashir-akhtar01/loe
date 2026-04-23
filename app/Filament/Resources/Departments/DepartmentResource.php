<?php

namespace App\Filament\Resources\Departments;

use App\Filament\Resources\Departments\Pages\ManageDepartments;
use App\Models\Department;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Workforce Setup';

    protected static ?string $modelLabel = 'Department';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Department overview')
                    ->description('Core identity and current team size.')
                    ->icon(Heroicon::OutlinedBuildingOffice2)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Department name')
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedTag),
                        TextEntry::make('users_count')
                            ->label('Employees')
                            ->state(fn (Department $record): int => $record->users()->count())
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedUsers),
                    ]),
                Section::make('Activity')
                    ->description('Lifecycle details for this department record.')
                    ->icon(Heroicon::OutlinedClock)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A')
                            ->label('Created')
                            ->icon(Heroicon::OutlinedCalendarDays),
                        TextEntry::make('updated_at')
                            ->since()
                            ->label('Last updated')
                            ->icon(Heroicon::OutlinedArrowPath),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Employees')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('lg'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageDepartments::route('/'),
        ];
    }
}
