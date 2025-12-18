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
    Route::get('/setup/fiscal-years', \App\Livewire\Setup\FiscalYears::class)->name('setup.fiscal-years');
    Route::get('/setup/permissions', \App\Livewire\Setup\Permissions::class)->name('setup.permissions');
    Route::get('/setup/roles', \App\Livewire\Setup\Roles::class)->name('setup.roles');
    Route::get('/setup/offices', \App\Livewire\Setup\RpoUnits::class)->name('setup.rpo-units');
    Route::get('/setup/expense-categories', \App\Livewire\Setup\ExpenseCategories::class)->name('setup.expense-categories');
    Route::get('/setup/expenses', \App\Livewire\Setup\Expenses::class)->name('setup.expenses');
    Route::get('/setup/economic-codes', \App\Livewire\Setup\EconomicCodes::class)->name('setup.economic-codes');
    Route::get('/setup/users', \App\Livewire\Setup\Users::class)->name('setup.users');
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
    Route::get('/budget/estimations', \App\Livewire\BudgetEstimations::class)->name('budget.estimations');
    Route::get('/budget/approvals', \App\Livewire\BudgetApprovals::class)->name('budget.approvals');
});
