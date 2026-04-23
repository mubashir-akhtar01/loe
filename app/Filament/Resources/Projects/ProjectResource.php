<?php

namespace App\Filament\Resources\Projects;

use App\Filament\Pages\ProjectAnalytics;
use App\Filament\Resources\Projects\Pages\ManageProjects;
use App\Models\Project;
use App\ProjectStatus;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Workforce Setup';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('status')
                    ->options(collect(ProjectStatus::cases())->mapWithKeys(
                        fn (ProjectStatus $status): array => [$status->value => ucfirst($status->value)],
                    )->all())
                    ->required()
                    ->native(false),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project overview')
                    ->description('Status and staffing footprint for this project.')
                    ->icon(Heroicon::OutlinedBriefcase)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Project name')
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedTag),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (ProjectStatus|string $state): string => match ($state instanceof ProjectStatus ? $state->value : $state) {
                                'active' => 'success',
                                'inactive' => 'warning',
                                'closed' => 'gray',
                                default => 'gray',
                            })
                            ->icon(Heroicon::OutlinedSignal)
                            ->formatStateUsing(fn (ProjectStatus|string $state): string => ucfirst($state instanceof ProjectStatus ? $state->value : $state)),
                        TextEntry::make('project_assignments_count')
                            ->label('Assignments')
                            ->state(fn (Project $record): int => $record->projectAssignments()->count())
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedUsers),
                    ]),
                Section::make('Activity')
                    ->description('When this project record entered the system.')
                    ->icon(Heroicon::OutlinedClock)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A')
                            ->label('Created')
                            ->icon(Heroicon::OutlinedCalendarDays),
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
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (ProjectStatus|string $state): string => ucfirst($state instanceof ProjectStatus ? $state->value : $state)),
                TextColumn::make('project_assignments_count')
                    ->counts('projectAssignments')
                    ->label('Assignments')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(ProjectStatus::cases())->mapWithKeys(
                        fn (ProjectStatus $status): array => [$status->value => ucfirst($status->value)],
                    )->all()),
            ])
            ->recordActions([
                Action::make('analytics')
                    ->label('Analytics')
                    ->icon(Heroicon::OutlinedChartBarSquare)
                    ->url(fn (Project $record): string => ProjectAnalytics::getUrl([
                        'project_id' => $record->id,
                    ])),
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
            'index' => ManageProjects::route('/'),
        ];
    }
}
