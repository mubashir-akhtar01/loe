<?php

namespace App\Filament\Resources\MonthlyLoeReports;

use App\Filament\Resources\MonthlyLoeReports\Pages\ManageMonthlyLoeReports;
use App\Filament\Pages\EmployeeAnalytics;
use App\Models\Department;
use App\Models\MonthlyLoeReport;
use App\MonthlyLoeReportStatus;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class MonthlyLoeReportResource extends Resource
{
    protected static ?string $model = MonthlyLoeReport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Reporting';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('report_notes')
                    ->rows(5),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Summary')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Employee'),
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->placeholder('Unassigned'),
                        TextEntry::make('status')
                            ->badge()
                            ->formatStateUsing(
                                fn (MonthlyLoeReportStatus|string $state): string => ucfirst(
                                    $state instanceof MonthlyLoeReportStatus ? $state->value : $state,
                                ),
                            ),
                        TextEntry::make('period')
                            ->label('Reporting Period')
                            ->state(fn (MonthlyLoeReport $record): string => sprintf('%02d/%d', $record->report_month, $record->report_year)),
                        TextEntry::make('submitted_at')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Not submitted'),
                        TextEntry::make('closed_at')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Open'),
                    ]),
                Section::make('Totals')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('total_hours')->suffix('h'),
                        TextEntry::make('total_days')->suffix('d'),
                        TextEntry::make('total_percentage')->suffix('%'),
                        TextEntry::make('open_to_new_projects_percentage')
                            ->label('Open to New Projects')
                            ->suffix('%'),
                        TextEntry::make('time_off_hours')->suffix('h'),
                        TextEntry::make('time_off_percentage')->suffix('%'),
                    ]),
                Section::make('Line Items')
                    ->schema([
                        RepeatableEntry::make('lines')
                            ->schema([
                                TextEntry::make('line_type')
                                    ->badge()
                                    ->formatStateUsing(
                                        fn (\App\MonthlyLoeReportLineType|string $state): string => str(
                                            $state instanceof \App\MonthlyLoeReportLineType ? $state->value : $state
                                        )->replace('_', ' ')->title()->toString(),
                                    ),
                                TextEntry::make('project.name')
                                    ->label('Project')
                                    ->placeholder('System line'),
                                TextEntry::make('entered_hours')
                                    ->suffix('h'),
                                TextEntry::make('calculated_percentage')
                                    ->label('Allocation')
                                    ->suffix('%'),
                                TextEntry::make('expected_percentage')
                                    ->label('Expected')
                                    ->suffix('%')
                                    ->placeholder('N/A'),
                                TextEntry::make('line_notes')
                                    ->label('Notes')
                                    ->placeholder('No notes')
                                    ->columnSpanFull(),
                            ])
                            ->columns(5),
                    ]),
                Section::make('Activity')
                    ->schema([
                        TextEntry::make('activity_log')
                            ->state(fn (MonthlyLoeReport $record): array => $record->activities
                                ->sortByDesc('created_at')
                                ->map(fn ($activity): string => trim(sprintf(
                                    '%s - %s%s',
                                    $activity->created_at?->format('M j, Y g:i A') ?? 'Unknown',
                                    $activity->description ?? str($activity->action->value)->replace('_', ' ')->title()->toString(),
                                    $activity->user ? ' (' . $activity->user->name . ')' : '',
                                )))
                                ->values()
                                ->all())
                            ->bulleted()
                            ->listWithLineBreaks()
                            ->placeholder('No activity recorded yet.'),
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
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('Unassigned')
                    ->sortable(),
                TextColumn::make('report_month')
                    ->label('Month')
                    ->formatStateUsing(fn (int $state): string => str_pad((string) $state, 2, '0', STR_PAD_LEFT))
                    ->sortable(),
                TextColumn::make('report_year')
                    ->label('Year')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(
                        fn (MonthlyLoeReportStatus|string $state): string => ucfirst(
                            $state instanceof MonthlyLoeReportStatus ? $state->value : $state,
                        ),
                    ),
                TextColumn::make('total_percentage')
                    ->label('Total')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('open_to_new_projects_percentage')
                    ->label('Open')
                    ->suffix('%')
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->dateTime('M j, Y')
                    ->placeholder('Draft')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('user_id')
                    ->label('Employee')
                    ->relationship('user', 'name'),
                SelectFilter::make('status')
                    ->options(collect(MonthlyLoeReportStatus::cases())->mapWithKeys(
                        fn (MonthlyLoeReportStatus $status): array => [$status->value => ucfirst($status->value)],
                    )->all()),
                SelectFilter::make('report_month')
                    ->default(now()->month)
                    ->options(collect(range(1, 12))->mapWithKeys(
                        fn (int $month): array => [$month => str_pad((string) $month, 2, '0', STR_PAD_LEFT)],
                    )->all()),
                SelectFilter::make('report_year')
                    ->default(now()->year)
                    ->options(MonthlyLoeReport::query()
                        ->select('report_year')
                        ->distinct()
                        ->orderByDesc('report_year')
                        ->pluck('report_year', 'report_year')
                        ->all()),
            ])
            ->recordActions([
                Action::make('employeeAnalytics')
                    ->label('Employee Analytics')
                    ->icon(Heroicon::OutlinedChartBarSquare)
                    ->url(fn (MonthlyLoeReport $record): string => EmployeeAnalytics::getUrl([
                        'department_ids' => [$record->department_id],
                        'employee_id' => $record->user_id,
                        'report_month' => $record->report_month,
                        'report_year' => $record->report_year,
                    ])),
                ViewAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'activities.user',
            'department',
            'lines.project',
            'user',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMonthlyLoeReports::route('/'),
        ];
    }
}
