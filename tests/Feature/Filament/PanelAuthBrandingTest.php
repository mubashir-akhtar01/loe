<?php

test('admin auth screens render the custom branding block', function () {
    $this->get(route('filament.admin.auth.login'))
        ->assertOk()
        ->assertSee('Step into the command center')
        ->assertSee('Admin panel');

    $this->get(route('filament.admin.auth.password-reset.request'))
        ->assertOk()
        ->assertSee('Get back to the command center')
        ->assertSee('Admin panel');
});

test('employee auth screens render the custom branding block', function () {
    $this->get(route('filament.employee.auth.login'))
        ->assertOk()
        ->assertSee('Walk back into your reporting rhythm')
        ->assertSee('Employee panel');

    $this->get(route('filament.employee.auth.password-reset.request'))
        ->assertOk()
        ->assertSee('Reconnect to your monthly workspace')
        ->assertSee('Employee panel');
});
