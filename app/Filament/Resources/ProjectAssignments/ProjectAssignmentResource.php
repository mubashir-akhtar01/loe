<?php

namespace App\Filament\Resources\ProjectAssignments;

use App\Filament\Resources\ProjectAssignments\Pages\ManageProjectAssignments;
use App\Models\ProjectAssignment;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProjectAssignmentResource extends Resource
{
    protected static ?string $model = ProjectAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Workforce Setup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('expected_percentage')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                DatePicker::make('starts_on')
                    ->native(false),
                DatePicker::make('ends_on')
                    ->native(false),
                Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Assignment overview')
                    ->description('Who is assigned, where, and at what expected effort.')
                    ->icon(Heroicon::OutlinedLink)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Employee')
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedUser),
                        TextEntry::make('project.name')
                            ->label('Project')
                            ->icon(Heroicon::OutlinedBriefcase),
                        TextEntry::make('expected_percentage')
                            ->label('Expected allocation')
                            ->suffix('%')
                            ->placeholder('Not set')
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedChartBar),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ]),
                Section::make('Assignment window')
                    ->description('Assignment timing and lifecycle details.')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('starts_on')
                            ->date('M j, Y')
                            ->placeholder('No start date')
                            ->icon(Heroicon::OutlinedPlay),
                        TextEntry::make('ends_on')
                            ->date('M j, Y')
                            ->placeholder('Open-ended')
                            ->icon(Heroicon::OutlinedStop),
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A')
                            ->label('Created')
                            ->icon(Heroicon::OutlinedClock),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('expected_percentage')
                    ->label('Expected')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('starts_on')
                    ->date('M j, Y')
                    ->sortable(),
                TextColumn::make('ends_on')
                    ->date('M j, Y')
                    ->placeholder('Open-ended')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name'),
                SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('xl'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['project', 'user']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProjectAssignments::route('/'),
        ];
    }
}
