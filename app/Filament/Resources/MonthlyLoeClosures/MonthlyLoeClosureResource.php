<?php

namespace App\Filament\Resources\MonthlyLoeClosures;

use App\Filament\Resources\MonthlyLoeClosures\Pages\ManageMonthlyLoeClosures;
use App\Models\MonthlyLoeClosure;
use App\MonthlyLoeClosureType;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MonthlyLoeClosureResource extends Resource
{
    protected static ?string $model = MonthlyLoeClosure::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Closure details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('closure_month')
                            ->label('Month')
                            ->formatStateUsing(fn (int $state): string => str_pad((string) $state, 2, '0', STR_PAD_LEFT)),
                        TextEntry::make('closure_year')
                            ->label('Year'),
                        TextEntry::make('closure_type')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                        TextEntry::make('closed_by.name')
                            ->label('Closed by')
                            ->placeholder('System'),
                        TextEntry::make('closed_at')
                            ->dateTime('M j, Y g:i A'),
                        TextEntry::make('notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('closure_month')
                    ->label('Month')
                    ->formatStateUsing(fn (int $state): string => str_pad((string) $state, 2, '0', STR_PAD_LEFT))
                    ->sortable(),
                TextColumn::make('closure_year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('closure_type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),
                TextColumn::make('closed_by.name')
                    ->label('Closed by')
                    ->placeholder('System'),
                TextColumn::make('closed_at')
                    ->dateTime('M j, Y g:i A')
                    ->sortable(),
            ])
            ->filters([
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('closedBy');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMonthlyLoeClosures::route('/'),
        ];
    }
}
