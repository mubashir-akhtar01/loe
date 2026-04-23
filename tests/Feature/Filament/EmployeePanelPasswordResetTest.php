<?php

use App\Models\User;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset;
use Filament\Auth\Pages\PasswordReset\ResetPassword;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('employee');
    Filament::bootCurrentPanel();
});

afterEach(function () {
    Filament::setCurrentPanel(null);
});

test('employee login screen shows a forgot password link', function () {
    $this->get(route('filament.employee.auth.login'))
        ->assertOk()
        ->assertSee(route('filament.employee.auth.password-reset.request'), escape: false);
});

test('employee password reset request screen can be rendered', function () {
    $this->get(route('filament.employee.auth.password-reset.request'))
        ->assertOk();
});

test('employees can request a password reset link from the employee panel', function () {
    Notification::fake();

    $employee = User::factory()->create();

    Livewire::test(RequestPasswordReset::class)
        ->set('data.email', $employee->email)
        ->call('request');

    Notification::assertSentTo($employee, FilamentResetPassword::class, function (FilamentResetPassword $notification) use ($employee) {
        expect($notification->url)
            ->toContain('/employee/password-reset/reset')
            ->toContain('email='.urlencode($employee->email))
            ->toContain('token=');

        return true;
    });
});

test('employee password reset screen can be rendered from the notification link', function () {
    Notification::fake();

    $employee = User::factory()->create();
    $resetUrl = null;

    Livewire::test(RequestPasswordReset::class)
        ->set('data.email', $employee->email)
        ->call('request');

    Notification::assertSentTo($employee, FilamentResetPassword::class, function (FilamentResetPassword $notification) use (&$resetUrl) {
        $resetUrl = $notification->url;

        return true;
    });

    $this->get($resetUrl)
        ->assertOk();
});

test('employees can reset their password from the employee panel', function () {
    Notification::fake();

    $employee = User::factory()->create([
        'password' => Hash::make('password'),
    ]);
    $resetUrl = null;

    Livewire::test(RequestPasswordReset::class)
        ->set('data.email', $employee->email)
        ->call('request');

    Notification::assertSentTo($employee, FilamentResetPassword::class, function (FilamentResetPassword $notification) use (&$resetUrl) {
        $resetUrl = $notification->url;

        return true;
    });

    parse_str((string) parse_url($resetUrl, PHP_URL_QUERY), $query);

    Livewire::withQueryParams($query)
        ->test(ResetPassword::class, [
            'email' => $query['email'],
            'token' => $query['token'],
        ])
        ->set('password', 'new-password')
        ->set('passwordConfirmation', 'new-password')
        ->call('resetPassword')
        ->assertRedirect(route('filament.employee.auth.login'));

    expect(Hash::check('new-password', $employee->refresh()->password))->toBeTrue();
});
