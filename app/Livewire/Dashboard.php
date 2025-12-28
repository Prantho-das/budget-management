<?php

namespace App\Livewire;

use App\Models\BudgetAllocation;
use App\Models\BudgetEstimation;
use App\Models\Expense;
use App\Models\FiscalYear;
use App\Models\RpoUnit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Dashboard extends Component
{
  public $fiscalYear;
  public $totalEstimated = 0;
  public $totalAllocated = 0;
  public $totalExpense = 0;
  public $budgetRemaining = 0;

  public $budgetOverview = [];
  public $expenseTrends = [];
  public $officeBreakdown = [];
  public $recentActivities = [];
  public $pendingApprovals = 0;

  public function mount()
  {
    $this->fiscalYear = FiscalYear::where('status', 'active')->first()
      ?? FiscalYear::orderBy('end_date', 'desc')->first();

    if ($this->fiscalYear) {
      $this->calculateStats();
      $this->prepareCharts();
      $this->loadRecentActivity();
    }
  }

  public function calculateStats()
  {
    // Budget Estimations (Approved amounts)
    $this->totalEstimated = BudgetEstimation::where('fiscal_year_id', $this->fiscalYear->id)
      ->sum('amount_approved');

    // Budget Allocations
    $this->totalAllocated = BudgetAllocation::where('fiscal_year_id', $this->fiscalYear->id)
      ->sum('amount');

    // Expenses
    $this->totalExpense = Expense::where('fiscal_year_id', $this->fiscalYear->id)
      ->sum('amount');

    $this->budgetRemaining = $this->totalAllocated - $this->totalExpense;

    // Count pending approvals usually means estimations not yet fully approved/released
    // Assuming 'submitted' or intermediate stages count as pending
    $this->pendingApprovals = BudgetEstimation::where('fiscal_year_id', $this->fiscalYear->id)
      ->whereIn('current_stage', ['submitted', 'reviewed']) // Adjust stages based on actual workflow
      ->count();
  }

  public function prepareCharts()
  {
    // 1. Budget Overview (Estimated vs Allocated vs Expense)
    $this->budgetOverview = [
      'series' => [
        [
          'name' => 'Amount',
          'data' => [$this->totalEstimated, $this->totalAllocated, $this->totalExpense]
        ]
      ],
      'categories' => ['Estimated', 'Allocated', 'Expense']
    ];

    // 2. Expense Trends (Monthly)
    $driver = DB::getDriverName();

    $dateExpression = $driver === 'sqlite'
      ? "strftime('%Y-%m', date)"
      : "DATE_FORMAT(date, '%Y-%m')";

    $monthlyExpenses = Expense::where('fiscal_year_id', $this->fiscalYear->id)
      ->selectRaw("SUM(amount) as total, {$dateExpression} as month")
      ->groupByRaw($dateExpression)
      ->orderBy('month')
      ->get();

    // If using MySQL in production, use DATE_FORMAT(date, '%Y-%m')
    // For broad compatibility in dev (often sqlite) vs prod, simple grouping might need check.
    // Let's assume standard Laravel DB query aggregation or collection mapping.

    $expensesByMonth = Expense::where('fiscal_year_id', $this->fiscalYear->id)
      ->get()
      ->groupBy(function ($date) {
        return \Carbon\Carbon::parse($date->date)->format('M Y');
      })
      ->map(function ($row) {
        return $row->sum('amount');
      });

    $this->expenseTrends = [
      'series' => [
        [
          'name' => 'Expenses',
          'data' => $expensesByMonth->values()->toArray()
        ]
      ],
      'categories' => $expensesByMonth->keys()->toArray()
    ];

    // 3. Office Breakdown (Top 5 Offices by Allocation)
    $officeAllocations = BudgetAllocation::where('fiscal_year_id', $this->fiscalYear->id)
      ->with('office')
      ->select('rpo_unit_id', DB::raw('SUM(amount) as total'))
      ->groupBy('rpo_unit_id')
      ->orderByDesc('total')
      ->take(5)
      ->get();

    $this->officeBreakdown = [
      'series' => $officeAllocations->pluck('total')->toArray(),
      'labels' => $officeAllocations->pluck('office.name')->toArray()
    ];
  }

  public function loadRecentActivity()
  {
    // Using BudgetEstimation as a proxy for activity for now, 
    // or actually query Spatie ActivityLog if available/desired.
    // Let's use latest Estimations for now as "Relevant Activity"
    $this->recentActivities = BudgetEstimation::with(['office', 'budgetType'])
      ->where('fiscal_year_id', $this->fiscalYear->id)
      ->orderBy('updated_at', 'desc')
      ->take(5)
      ->get();
  }

  public function render()
  {
    return view('livewire.dashboard')->extends('layouts.skot')->section('content');
  }
}
