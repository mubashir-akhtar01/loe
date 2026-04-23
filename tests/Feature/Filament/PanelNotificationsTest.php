<?php

use Filament\Facades\Filament;

test('admin and employee panels have database notifications enabled', function () {
    expect(Filament::getPanel('admin')->hasDatabaseNotifications())->toBeTrue();
    expect(Filament::getPanel('employee')->hasDatabaseNotifications())->toBeTrue();
});
