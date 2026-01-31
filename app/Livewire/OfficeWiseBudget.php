<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RpoUnit;
use App\Models\FiscalYear;
use App\Models\BudgetType;
use App\Models\EconomicCode;
use App\Models\BudgetEstimation;
use App\Models\BudgetAllocation;
use App\Models\Expense;
use Illuminate\Support\Facades\DB;

class OfficeWiseBudget extends Component
{
    public $fiscal_year_id;
    public $budget_type_id;
    public $economic_code_id;
    public $selected_office_id;

    public function mount()
    {
        // Default to current active fiscal year using helper
        $this->fiscal_year_id = get_active_fiscal_year_id();

        // Default to a budget type (e.g. Revenue)
        $firstType = BudgetType::first();
        $this->budget_type_id = $firstType ? $firstType->id : null;
    }

    public function updateAmount($officeId, $amount, $field = 'demand')
    {
        if (!$this->economic_code_id) {
            $this->dispatch('alert', ['type' => 'error', 'message' => __('Please select an Economic Code to edit amounts.')]);
            return;
        }

        $amount = (float) $amount;

        $dbField = match ($field) {
            'demand' => 'amount_demand',
            'revised' => 'revised_amount',
            'projection_1' => 'projection_1',
            'projection_2' => 'projection_2',
            'projection_3' => 'projection_3',
            default => 'amount_demand'
        };

        BudgetEstimation::updateOrCreate(
            [
                'target_office_id' => $officeId,
                'fiscal_year_id' => $this->fiscal_year_id,
                'budget_type_id' => $this->budget_type_id,
                'economic_code_id' => $this->economic_code_id
            ],
            [
                $dbField => $amount
            ]
        );

        $this->dispatch('alert', ['type' => 'success', 'message' => __('Amount updated.')]);
    }

    public function moveAllToDraft()
    {
        $workflow = new \App\Services\BudgetWorkflowService();

        $query = BudgetEstimation::where('fiscal_year_id', $this->fiscal_year_id);

        if ($this->budget_type_id) {
            $query->where('budget_type_id', $this->budget_type_id);
        }

        if ($this->economic_code_id) {
            $query->where('economic_code_id', $this->economic_code_id);
        }

        $estimations = $query->get();

        if ($estimations->isEmpty()) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => __('No budget found to move to draft.')]);
            return;
        }

        $workflow->rejectBatch($estimations, __('Moved back to draft by Ministry Admin (Bulk Action)'));

        $this->dispatch('alert', ['type' => 'success', 'message' => __('All matching budgets moved back to draft.')]);
    }

    public function render()
    {
        abort_if(auth()->user()->cannot('release-budget'), 403);

        $fiscalYears = FiscalYear::orderBy('start_date', 'desc')->get();
        $budgetTypes = BudgetType::where('status', true)->orderBy('order_priority')->get();
        $selectedFy = FiscalYear::find($this->fiscal_year_id);

        $economicCodes = $this->getHierarchicalEconomicCodes();

        $fullPrevYears = FiscalYear::where('end_date', '<', $selectedFy->start_date)
            ->orderBy('end_date', 'desc')
            ->take(2)
            ->get()
            ->reverse()
            ->values();

        // Fetch Raw Data for everything upfront
        $pastFyIds = $fullPrevYears->pluck('id')->toArray();
        $relevantFyIds = array_merge($pastFyIds, [$this->fiscal_year_id]);

        $rawBudgets = BudgetEstimation::whereIn('fiscal_year_id', $relevantFyIds)
            ->when($this->budget_type_id, fn($q) => $q->where('budget_type_id', $this->budget_type_id))
            ->when($this->economic_code_id, fn($q) => $q->where('economic_code_id', $this->economic_code_id))
            ->get()
            ->groupBy('target_office_id');

        $rawExpenses = Expense::whereIn('fiscal_year_id', $relevantFyIds)
            ->when($this->budget_type_id, fn($q) => $q->where('budget_type_id', $this->budget_type_id))
            ->when($this->economic_code_id, fn($q) => $q->where('economic_code_id', $this->economic_code_id))
            ->get()
            ->groupBy('rpo_unit_id');

        // Recursive tree building
        $rootOffices = RpoUnit::with(['children' => fn($q) => $q->orderBy('code')])
            ->whereNull('parent_id')
            ->when($this->selected_office_id, fn($q) => $q->where('id', $this->selected_office_id))
            ->orderBy('code')
            ->get();

        $hierarchicalData = [];
        foreach ($rootOffices as $root) {
            $hierarchicalData[] = $this->calculateNodeData($root, 0, $rawBudgets, $rawExpenses, $fullPrevYears);
        }

        $flattenedTable = $this->flattenTreeWithSubtotals($hierarchicalData);

        // Grand Totals Calculation
        $totals = $this->calculateGrandTotals($hierarchicalData);

        // Validation against Ministry Budget
        $validator = new \App\Services\MinistryBudgetValidationService();
        $ministryLimits = $validator->getMinistryBudgetSummary(
            $this->fiscal_year_id,
           $this->selected_office_id, // Filter by selected office if set, else Global
            $this->budget_type_id
        );

        return view('livewire.office-wise-budget', [
            'fiscalYears' => $fiscalYears,
            'budgetTypes' => $budgetTypes,
            'economicCodes' => $economicCodes,
            'allOffices' => RpoUnit::orderBy('name')->get(),
            'selectedOffice' => $this->selected_office_id ? RpoUnit::find($this->selected_office_id) : null,
            'selectedFy' => $selectedFy,
            'fullPrevYears' => $fullPrevYears,
            'flattenedTable' => $flattenedTable,
            'totals' => $totals,
            'ministryLimits' => $ministryLimits
        ])->extends('layouts.skot')->section('content');
    }
    private function getHierarchicalEconomicCodes()
    {
        $allCodes = EconomicCode::with(['children' => fn($q) => $q->orderBy('code')])
            ->whereNull('parent_id')
            ->orderBy('code')
            ->get();

        $ordered = [];
        foreach ($allCodes as $root) {
            $ordered[] = $root;
            foreach ($root->children as $child) {
                $ordered[] = $child;
            }
        }
        return $ordered;
    }

    private function calculateNodeData($office, $depth, $rawBudgets, $rawExpenses, $fullPrevYears)
    {
        $nodeData = [
            'history_full_1' => 0,
            'history_full_2' => 0,
            'history_part_1' => 0,
            'history_part_2' => 0,
            'demand' => 0,
            'approved' => 0,
            'revised' => 0,
            'projection_1' => 0,
            'projection_2' => 0,
            'projection_3' => 0,
        ];

        // A. Direct Data
        $myBudgets = $rawBudgets->get($office->id, collect())->where('fiscal_year_id', $this->fiscal_year_id);
        $nodeData['demand'] = $myBudgets->sum('amount_demand');
        $nodeData['approved'] = $myBudgets->sum('amount_approved');
        $nodeData['revised'] = $myBudgets->sum('revised_amount');
        $nodeData['projection_1'] = $myBudgets->sum('projection_1');
        $nodeData['projection_2'] = $myBudgets->sum('projection_2');
        $nodeData['projection_3'] = $myBudgets->sum('projection_3');

        $myExpenses = $rawExpenses->get($office->id, collect());
        foreach ($fullPrevYears as $idx => $fy) {
            $field = $idx === 0 ? 'history_full_1' : 'history_full_2';
            $nodeData[$field] = $myExpenses->where('fiscal_year_id', $fy->id)->sum('amount');
        }

        if ($fullPrevYears->count() >= 2) {
            $fy1 = $fullPrevYears[1];
            $nodeData['history_part_1'] = $myExpenses->where('fiscal_year_id', $fy1->id)
                ->filter(fn($e) => \Carbon\Carbon::parse($e->date)->month >= 7)->sum('amount');
        }
        $nodeData['history_part_2'] = $myExpenses->where('fiscal_year_id', $this->fiscal_year_id)
            ->filter(fn($e) => \Carbon\Carbon::parse($e->date)->month >= 7)->sum('amount');

        $own = array_merge($nodeData, $this->calculateSuggestions($nodeData));

        // B. Aggregation
        $children = [];
        $aggregated = $nodeData;
        foreach ($office->children as $child) {
            $childNode = $this->calculateNodeData($child, $depth + 1, $rawBudgets, $rawExpenses, $fullPrevYears);
            $children[] = $childNode;
            foreach ($nodeData as $key => $val) {
                $aggregated[$key] += $childNode['aggregated'][$key];
            }
        }

        $aggregated = array_merge($aggregated, $this->calculateSuggestions($aggregated));

        return [
            'office' => $office,
            'depth' => $depth,
            'own' => $own,
            'aggregated' => $aggregated,
            'children' => $children
        ];
    }

    private function calculateSuggestions($data)
    {
        $est = round($data['history_full_2'] * 1.10, 0);
        $p1 = round(($data['projection_1'] > 0 ? $data['projection_1'] : $est) * 1.10, 0);
        $p2 = round(($data['projection_2'] > 0 ? $data['projection_2'] : $p1) * 1.10, 0);
        return [
            'estimation_suggestion' => $est,
            'projection1_suggestion' => $p1,
            'projection2_suggestion' => $p2,
        ];
    }

    private function flattenTreeWithSubtotals($tree)
    {
        $flattened = [];
        $this->processFlattening($tree, $flattened);
        return $flattened;
    }

    private function processFlattening($nodes, &$flattened)
    {
        foreach ($nodes as $node) {
            $flattened[] = [
                'type' => 'office',
                'office' => $node['office'],
                'depth' => $node['depth'],
                'data' => $node['own'],
                'has_children' => !empty($node['children'])
            ];

            if (!empty($node['children'])) {
                $this->processFlattening($node['children'], $flattened);
                $flattened[] = [
                    'type' => 'subtotal',
                    'office' => $node['office'],
                    'depth' => $node['depth'],
                    'data' => $node['aggregated'],
                    'has_children' => false
                ];
            }
        }
    }

    private function calculateGrandTotals($tree)
    {
        $totals = ['h1' => 0, 'h2' => 0, 'hp1' => 0, 'hp2' => 0, 'demand' => 0, 'revised' => 0, 'p1' => 0, 'p2' => 0, 'p3' => 0];
        foreach ($tree as $root) {
            $row = $root['aggregated'];
            $totals['h1'] += $row['history_full_1'];
            $totals['h2'] += $row['history_full_2'];
            $totals['hp1'] += $row['history_part_1'];
            $totals['hp2'] += $row['history_part_2'];
            $totals['demand'] += $row['demand'];
            $totals['revised'] += $row['revised'];
            $totals['p1'] += ($row['projection_1'] > 0 ? $row['projection_1'] : $row['estimation_suggestion']);
            $totals['p2'] += ($row['projection_2'] > 0 ? $row['projection_2'] : $row['projection1_suggestion']);
            $totals['p3'] += ($row['projection_3'] > 0 ? $row['projection_3'] : $row['projection2_suggestion']);
        }
        return $totals;
    }
}
