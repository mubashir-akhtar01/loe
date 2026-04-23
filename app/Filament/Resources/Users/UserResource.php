<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\Department;
use App\Models\User;
use App\UserRole;
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

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Workforce Setup';

    protected static ?string $modelLabel = 'Employee';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Select::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
                Select::make('role')
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn (UserRole $role): array => [$role->value => ucfirst($role->value)],
                    )->all())
                    ->required()
                    ->native(false),
                DatePicker::make('joining_date')
                    ->native(false),
                Toggle::make('is_active')
                    ->default(true)
                    ->inline(false),
                TextInput::make('password')
                    ->password()
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->required(fn (string $operation): bool => $operation === 'create'),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Employee overview')
                    ->description('Identity, role, and organizational placement.')
                    ->icon(Heroicon::OutlinedUserCircle)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Employee name')
                            ->weight('bold')
                            ->icon(Heroicon::OutlinedUser),
                        TextEntry::make('email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->copyable(),
                        TextEntry::make('department.name')
                            ->label('Department')
                            ->placeholder('Unassigned')
                            ->badge()
                            ->color('info')
                            ->icon(Heroicon::OutlinedBuildingOffice2),
                        TextEntry::make('role')
                            ->badge()
                            ->color(fn (UserRole|string $state): string => ($state instanceof UserRole ? $state->value : $state) === UserRole::Admin->value ? 'warning' : 'success')
                            ->icon(Heroicon::OutlinedShieldCheck)
                            ->formatStateUsing(fn (UserRole|string $state): string => ucfirst($state instanceof UserRole ? $state->value : $state)),
                    ]),
                Section::make('Employment details')
                    ->description('Start date, account state, and audit details.')
                    ->icon(Heroicon::OutlinedIdentification)
                    ->compact()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('joining_date')
                            ->date('M j, Y')
                            ->placeholder('Not set')
                            ->icon(Heroicon::OutlinedCalendarDays),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->trueColor('success')
                            ->falseColor('gray'),
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
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Department')
                    ->placeholder('Unassigned')
                    ->sortable(),
                TextColumn::make('role')
                    ->badge()
                    ->formatStateUsing(fn (UserRole|string $state): string => ucfirst($state instanceof UserRole ? $state->value : $state)),
                TextColumn::make('joining_date')
                    ->date('M j, Y')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
            ])
            ->filters([
                SelectFilter::make('department_id')
                    ->label('Department')
                    ->options(Department::query()->orderBy('name')->pluck('name', 'id')->all()),
                SelectFilter::make('role')
                    ->options(collect(UserRole::cases())->mapWithKeys(
                        fn (UserRole $role): array => [$role->value => ucfirst($role->value)],
                    )->all()),
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
        return parent::getEloquentQuery()->with('department');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
