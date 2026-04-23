<?php

namespace App\Filament\Resources\MonthlyLoeClosures\Pages;

use App\Filament\Resources\MonthlyLoeClosures\MonthlyLoeClosureResource;
use App\MonthlyLoeClosureType;
use App\Services\Loe\MonthlyLoeClosureManager;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Validation\ValidationException;

class ManageMonthlyLoeClosures extends ManageRecords
{
    protected static string $resource = MonthlyLoeClosureResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('closeMonth')
                ->label('Close month')
                ->icon('heroicon-o-lock-closed')
                ->schema([
                    Select::make('year')
                        ->required()
                        ->options(collect(range(now()->year - 1, now()->year + 1))
                            ->mapWithKeys(fn (int $year): array => [$year => $year])
                            ->all())
                        ->default(now()->subMonth()->year),
                    Select::make('month')
                        ->required()
                        ->options(collect(range(1, 12))
                            ->mapWithKeys(fn (int $month): array => [$month => str_pad((string) $month, 2, '0', STR_PAD_LEFT)])
                            ->all())
                        ->default(now()->subMonth()->month),
                    Textarea::make('notes')
                        ->rows(4),
                ])
                ->action(function (array $data, MonthlyLoeClosureManager $closureManager): void {
                    if (! $closureManager->canManuallyClose((int) $data['year'], (int) $data['month'])) {
                        throw ValidationException::withMessages([
                            'month' => 'This month is not eligible for manual closure right now.',
                        ]);
                    }

                    $closureManager->closeMonth(
                        (int) $data['year'],
                        (int) $data['month'],
                        MonthlyLoeClosureType::Manual,
                        actor: auth()->user(),
                        notes: $data['notes'] ?? null,
                    );

                    Notification::make()
                        ->title('Month closed successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
