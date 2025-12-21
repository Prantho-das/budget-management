<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('lang/{locale}', function ($locale) {
    if (! in_array($locale, ['en', 'bn'])) {
        abort(400);
    }
    App::setLocale($locale);
    Session::put('locale', $locale);
    return redirect()->back();
})->name('lang.switch');

Route::get('/dashboard', \App\Livewire\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('profile.edit');
    Route::get('settings/password', Password::class)->name('user-password.edit');
    Route::get('/settings/appearance', Appearance::class)->name('settings.appearance');

    // Setup Routes
    Route::get('/setup/fiscal-years', \App\Livewire\Setup\FiscalYears::class)->middleware('can:view-fiscal-years')->name('setup.fiscal-years');
    Route::get('/setup/permissions', \App\Livewire\Setup\Permissions::class)->middleware('can:view-permissions')->name('setup.permissions');
    Route::get('/setup/system-settings', \App\Livewire\Setup\SystemSettings::class)->middleware('can:view-system-settings')->name('setup.system-settings');
    Route::get('/setup/roles', \App\Livewire\Setup\Roles::class)->middleware('can:view-roles')->name('setup.roles');
    Route::get('/setup/offices', \App\Livewire\Setup\RpoUnits::class)->middleware('can:view-offices')->name('setup.rpo-units');
    Route::get('/setup/expenses', \App\Livewire\Setup\Expenses::class)->middleware('can:view-expenses')->name('setup.expenses');
    Route::get('/setup/economic-codes', \App\Livewire\Setup\EconomicCodes::class)->middleware('can:view-economic-codes')->name('setup.economic-codes');
    Route::get('/setup/budget-types', \App\Livewire\Setup\BudgetTypes::class)->middleware('can:view-budget-types')->name('setup.budget-types');
    Route::get('/setup/users', \App\Livewire\Setup\Users::class)->middleware('can:view-users')->name('setup.users');
    Route::get('settings/two-factor', TwoFactor::class)
        ->middleware(
            when(
                Features::canManageTwoFactorAuthentication()
                    && Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword'),
                ['password.confirm'],
                [],
            ),
        )
        ->name('two-factor.show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/budget/estimations', \App\Livewire\BudgetEstimations::class)->middleware('can:view-budget-estimations')->name('budget.estimations');
    Route::get('/budget/approvals', \App\Livewire\BudgetApprovals::class)->middleware('can:view-budget-estimations')->name('budget.approvals');
    Route::get('/budget/status', \App\Livewire\BudgetStatus::class)->middleware('can:view-budget-estimations')->name('budget.status');
    Route::get('/budget/summary', \App\Livewire\BudgetSummary::class)->middleware('can:view-budget-estimations')->name('budget.summary');
});
