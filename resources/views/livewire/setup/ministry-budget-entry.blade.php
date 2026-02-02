<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Ministry Budget Entry') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Ministry Budget') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Fiscal Year') }}</label>
                            <select class="form-select" wire:model.live="fiscal_year_id" {{ $master_id ? 'disabled' : '' }}>
                                <option value="">{{ __('Select Fiscal Year') }}</option>
                                @foreach($fiscal_years as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->bn_name }}</option>
                                @endforeach
                            </select>
                            @error('fiscal_year_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Headquarters / Zone') }}</label>
                            <select class="form-select" wire:model.live="head_unit_id" {{ $master_id ? 'disabled' : '' }}>
                                <option value="">{{ __('Select Headquarters') }}</option>
                                @foreach($rpo_units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ bn_num($unit->code) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                             <label class="form-label">{{ __('Office / Unit') }}</label>
                             @if($head_unit_id)
                                <select class="form-select" wire:model.live="rpo_unit_id" {{ $master_id ? 'disabled' : '' }}>
                                    <option value="">{{ __('Select Office') }}</option>
                                    @foreach($child_units as $child)
                                        @php
                                            $disabled = in_array($child->id, $submitted_unit_ids) && !$master_id; 
                                        @endphp
                                        <option value="{{ $child->id }}" {{ $disabled ? 'disabled' : '' }} class="{{ $disabled ? 'text-muted' : '' }}">
                                            {{ $child->name }} 
                                            {{ $disabled ? '(Submitted)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                             @else
                                <select class="form-select" disabled>
                                    <option value="">{{ __('Select Headquarters First') }}</option>
                                </select>
                             @endif
                             @error('rpo_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end justify-content-between gap-2">
                            <div class="flex-grow-1">
                                <label class="form-label">{{ __('Remarks') }}</label>
                                <input type="text" class="form-control" wire:model="remarks" placeholder="{{ __('e.g. Initial Allocation') }}">
                            </div>
                            <button wire:click="save" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i> {{ __('Save Budget') }}
                            </button>
                        </div>
                    </div>

                    @if($master_id)
                        <div class="alert alert-info py-2 mb-3">
                            <i class="mdi mdi-information-outline me-1"></i>
                            <strong>{{ __('Editing Batch:') }}</strong> {{ bn_num(\App\Models\MinistryBudgetMaster::find($master_id)->batch_no) }} | 
                            <strong>{{ __('Type:') }}</strong> {{ \App\Models\MinistryBudgetMaster::find($master_id)->budgetType->name }}
                        </div>
                    @endif

                    <div class="table-responsive">
                       <table class="table table-centered align-middle table-nowrap mb-0 table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">{{ __('SL') }}</th>
                                    <th style="width: 150px;">{{ __('Economic Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    @if(count($original_budget_data) > 0)
                                        <th class="text-end" style="width: 150px;">{{ __('Original Budget') }}</th>
                                    @endif
                                    @if(count($previous_revised_data) > 0)
                                        <th class="text-end" style="width: 150px;">{{ __('Additional Budget') }}</th>
                                    @endif
                                    <th class="text-end" style="width: 150px;">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($economic_codes as $layer1)
                                    @php $rootIdx = $loop->iteration; @endphp
                                    <tr class="table-primary border-start border-4 border-primary">
                                        <td>{{ bn_num($rootIdx) }}</td>
                                        <td><strong>{{ bn_num($layer1->code) }}</strong></td>
                                        <td class="fw-bold text-primary">{{ $layer1->name }}</td>
                                        @if(count($original_budget_data) > 0)
                                            <td class="text-end fw-bold">
                                                @php
                                                    $layer1Ids = $layer1->children->flatMap->children->pluck('id')->toArray();
                                                    $layer1OrigTotal = collect($original_budget_data)->only($layer1Ids)->sum();
                                                @endphp
                                                {{ bn_comma_format($layer1OrigTotal, 2) }}
                                            </td>
                                        @endif
                                        @if(count($previous_revised_data) > 0)
                                            <td class="text-end fw-bold">
                                                @php
                                                    $layer1Ids = $layer1->children->flatMap->children->pluck('id')->toArray();
                                                    $layer1AddTotal = collect($previous_revised_data)->only($layer1Ids)->sum();
                                                @endphp
                                                {{ bn_comma_format($layer1AddTotal, 2) }}
                                            </td>
                                        @endif
                                        <td class="text-end fw-bold text-primary">
                                            @php
                                                $layer1Ids = $layer1->children->flatMap->children->pluck('id')->toArray();
                                                $layer1Total = collect($budget_data)->only($layer1Ids)->sum();
                                            @endphp
                                            {{ bn_comma_format($layer1Total, 2) }}
                                        </td>
                                    </tr>

                                        @foreach($layer1->children as $layer2)
                                        @php $subIdx = $loop->iteration; @endphp
                                        <tr class="table-light" wire:key="l2-{{ $layer2->id }}">
                                            <td style="padding-left: 20px;">{{ bn_num($rootIdx) . '.' . bn_num($subIdx) }}</td>
                                            <td>
                                                <i class="mdi mdi-arrow-right-bottom me-1 text-muted"></i>
                                                <span class="badge bg-info fs-12">{{ bn_num($layer2->code) }}</span>
                                            </td>
                                            <td class="fw-medium text-info">{{ $layer2->name }}</td>
                                            @if(count($original_budget_data) > 0)
                                                <td class="text-end">
                                                    @php
                                                        $layer2Ids = $layer2->children->pluck('id')->toArray();
                                                        $layer2OrigTotal = collect($original_budget_data)->only($layer2Ids)->sum();
                                                    @endphp
                                                    {{ bn_comma_format($layer2OrigTotal, 2) }}
                                                </td>
                                            @endif
                                            @if(count($previous_revised_data) > 0)
                                                <td class="text-end">
                                                    @php
                                                        $layer2Ids = $layer2->children->pluck('id')->toArray();
                                                        $layer2AddTotal = collect($previous_revised_data)->only($layer2Ids)->sum();
                                                    @endphp
                                                    {{ bn_comma_format($layer2AddTotal, 2) }}
                                                </td>
                                            @endif
                                            <td class="text-end fw-medium text-info">
                                                @php
                                                    $layer2Ids = $layer2->children->pluck('id')->toArray();
                                                    $layer2Total = collect($budget_data)->only($layer2Ids)->sum();
                                                @endphp
                                                {{ bn_comma_format($layer2Total, 2) }}
                                            </td>
                                        </tr>

                                        @foreach($layer2->children as $layer3)
                                            <tr wire:key="l3-{{ $layer3->id }}">
                                                <td style="padding-left: 40px;">{{ bn_num($rootIdx) . '.' . bn_num($subIdx) . '.' . bn_num($loop->iteration) }}</td>
                                                <td>
                                                    <i class="mdi mdi-subdirectory-arrow-right me-1 text-muted"></i>
                                                    {{ bn_num($layer3->code) }}
                                                </td>
                                                <td>{{ $layer3->name }}</td>
                                                @if(count($original_budget_data) > 0)
                                                    <td class="text-end text-muted">
                                                        {{ bn_comma_format($original_budget_data[$layer3->id] ?? 0, 2) }}
                                                    </td>
                                                @endif
                                                @if(count($previous_revised_data) > 0)
                                                    <td class="text-end text-muted">
                                                        {{ bn_comma_format($previous_revised_data[$layer3->id] ?? 0, 2) }}
                                                    </td>
                                                @endif
                                                <td style="width: 200px;">
                                                    <input type="number" step="0.01" class="form-control text-end font-size-15 fw-bold" 
                                                           wire:model.live.debounce.500ms="budget_data.{{ $layer3->id }}"
                                                           placeholder="0.00">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="3" class="text-end">{{ __('GRAND TOTAL') }}</th>
                                    @if(count($original_budget_data) > 0)
                                        <th class="text-end">{{ bn_comma_format(array_sum($original_budget_data), 2) }}</th>
                                    @endif
                                    @if(count($previous_revised_data) > 0)
                                        <th class="text-end">{{ bn_comma_format(array_sum($previous_revised_data), 2) }}</th>
                                    @endif
                                    <th class="text-end text-primary fs-14">{{ bn_comma_format(array_sum($budget_data), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="mt-4 text-end">
                        <button wire:click="save" class="btn btn-primary btn-lg">
                            <i class="mdi mdi-content-save me-1"></i> {{ __('Save Budget') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
