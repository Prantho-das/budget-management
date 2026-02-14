<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use App\Livewire\Settings\TwoFactor;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Features;
use App\Models\BudgetEstimation;
use Illuminate\Http\Request;
use Rakibhstu\Banglanumber\NumberToBangla;
// use App\Http\Controllers\iclockController;


Route::get('/', function () {
    info("=============data get");
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
    Route::get('/setup/expenses/create', \App\Livewire\Setup\ExpenseCreate::class)->middleware('can:create-expenses')->name('setup.expenses.create');
    Route::get('/setup/economic-codes', \App\Livewire\Setup\EconomicCodes::class)->middleware('can:view-economic-codes')->name('setup.economic-codes');
    Route::get('/setup/budget-types', \App\Livewire\Setup\BudgetTypes::class)->middleware('can:view-budget-types')->name('setup.budget-types');
    Route::get('/setup/users', \App\Livewire\Setup\Users::class)->middleware('can:view-users')->name('setup.users');
    Route::get('/setup/workflow', \App\Livewire\Setup\WorkflowManagement::class)->middleware('can:view-system-settings')->name('setup.workflow');
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
    Route::get('/budget/approvals', \App\Livewire\BudgetApprovals::class)->name('budget.approvals');
    Route::get('/budget/release', \App\Livewire\BudgetRelease::class)->middleware('can:release-budget')->name('budget.release');
    Route::get('/budget/office-wise', \App\Livewire\OfficeWiseBudget::class)->middleware('can:release-budget')->name('budget.office-wise');
    Route::get('/budget/status', \App\Livewire\BudgetStatus::class)->middleware('can:view-budget-estimations')->name('budget.status');
    Route::get('/budget/summary', \App\Livewire\BudgetSummary::class)->middleware('can:view-budget-estimations')->name('budget.summary');

    // Budget Distribution
    Route::get('/budget/distribution/list', \App\Livewire\BudgetDistribution\BudgetDistributionList::class)->name('budget.distribution.list');
    Route::get('/budget/distribution/entry', \App\Livewire\BudgetDistribution\BudgetDistributionEntry::class)->name('budget.distribution.entry');
    Route::get('/setup/ministry-budgets', \App\Livewire\Setup\MinistryBudgetList::class)->name('setup.ministry-budget-list');
    Route::get('/setup/ministry-budget-entry/{master_id?}', \App\Livewire\Setup\MinistryBudgetEntry::class)->name('setup.ministry-budget-entry');
});


// make a route for HQ budget view
