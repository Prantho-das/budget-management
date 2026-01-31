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
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Fiscal Year') }}</label>
                            <select class="form-select" wire:model.live="fiscal_year_id">
                                <option value="">{{ __('Select Fiscal Year') }}</option>
                                @foreach($fiscal_years as $fy)
                                    <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                @endforeach
                            </select>
                            @error('fiscal_year_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('Headquarters Unit') }}</label>
                            <select class="form-select" wire:model.live="rpo_unit_id">
                                <option value="">{{ __('Select Unit') }}</option>
                                @foreach($rpo_units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->code }})</option>
                                @endforeach
                            </select>
                            @error('rpo_unit_id') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end justify-content-between">
                            <div class="mb-2">
                                @if($budget_type)
                                    <span class="badge {{ $budget_type == 'original' ? 'bg-primary' : 'bg-warning' }} font-size-14">
                                        {{ ucfirst($budget_type) }} {{ __('Budget') }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary font-size-14">{{ __('New Entry (Original)') }}</span>
                                @endif
                            </div>
                            <button wire:click="save" class="btn btn-primary">
                                <i class="mdi mdi-content-save me-1"></i> {{ __('Save Budget') }}
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                       <table class="table table-centered align-middle table-nowrap mb-0 table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 80px;">{{ __('SL') }}</th>
                                    <th style="width: 150px;">{{ __('Economic Code') }}</th>
                                    <th>{{ __('Name') }}</th>
                                    <th class="text-end" style="width: 200px;">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($economic_codes as $layer1)
                                    @php $rootIdx = $loop->iteration; @endphp
                                    <tr class="table-primary border-start border-4 border-primary">
                                        <td>{{ $rootIdx }}</td>
                                        <td><strong>{{ $layer1->code }}</strong></td>
                                        <td class="fw-bold text-primary">{{ $layer1->name }}</td>
                                        <td>
                                            <!-- Readonly total could go here -->
                                        </td>
                                    </tr>

                                    @foreach($layer1->children as $layer2)
                                        @php $subIdx = $loop->iteration; @endphp
                                        <tr class="table-light">
                                            <td style="padding-left: 20px;">{{ $rootIdx }}.{{ $subIdx }}</td>
                                            <td>
                                                <i class="mdi mdi-arrow-right-bottom me-1 text-muted"></i>
                                                <span class="badge bg-info fs-12">{{ $layer2->code }}</span>
                                            </td>
                                            <td class="fw-medium text-info">{{ $layer2->name }}</td>
                                            <td>
                                                <!-- Readonly total could go here -->
                                            </td>
                                        </tr>

                                        @foreach($layer2->children as $layer3)
                                            <tr>
                                                <td style="padding-left: 40px;">{{ $rootIdx }}.{{ $subIdx }}.{{ $loop->iteration }}</td>
                                                <td>
                                                    <i class="mdi mdi-subdirectory-arrow-right me-1 text-muted"></i>
                                                    {{ $layer3->code }}
                                                </td>
                                                <td>{{ $layer3->name }}</td>
                                                <td>
                                                    <input type="number" step="0.01" class="form-control form-control-sm text-end" 
                                                           wire:model="budget_data.{{ $layer3->id }}"
                                                           placeholder="0.00">
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3 text-end">
                        <button wire:click="save" class="btn btn-primary">
                            <i class="mdi mdi-content-save me-1"></i> {{ __('Save Budget') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
