<?php

namespace App\Filament\Resources\PublicHolidays;

use App\Filament\Resources\PublicHolidays\Pages\ManagePublicHolidays;
use App\Models\PublicHoliday;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PublicHolidayResource extends Resource
{
    protected static ?string $model = PublicHoliday::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Workforce Setup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                DatePicker::make('holiday_date')
                    ->required()
                    ->native(false)
                    ->unique(ignoreRecord: true),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Holiday overview')
                    ->description('The holiday name and the exact date it affects capacity.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Holiday name')
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedSparkles),
                        TextEntry::make('holiday_date')
                            ->date('M j, Y')
                            ->label('Date')
                            ->badge()
                            ->color('warning')
                            ->icon(Heroicon::OutlinedCalendar),
                    ]),
                Section::make('Activity')
                    ->description('Administrative timestamps for this holiday record.')
                    ->icon(Heroicon::OutlinedClock)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A')
                            ->label('Created')
                            ->icon(Heroicon::OutlinedPlusCircle),
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
                TextColumn::make('holiday_date')
                    ->date('M j, Y')
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
            'index' => ManagePublicHolidays::route('/'),
        ];
    }
}
