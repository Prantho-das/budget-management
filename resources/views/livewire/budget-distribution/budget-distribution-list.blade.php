<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('Budget Distribution List') }}</h4>

                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budget') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('Distribution List') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-md-6">
                            <h4 class="card-title">{{ __('Parent Offices Budget Demand') }} ({{ $selectedFy?->name }})</h4>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="fy-select">{{ __('Filter by Fiscal Year') }}</label>
                                <select id="fy-select" class="form-select" wire:model.live="fiscal_year_id">
                                    @foreach($fiscalYears as $fy)
                                        <option value="{{ $fy->id }}">{{ $fy->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('Code') }}</th>
                                    <th>{{ __('Parent Office Name') }}</th>
                                    <th class="text-center">{{ __('Sub-offices') }}</th>
                                    <th class="text-end">{{ __('Total Budget Demand (Self + Children)') }}</th>
                                    <th class="text-center">{{ __('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($offices as $office)
                                    <tr>
                                        <td>{{ $office['code'] }}</td>
                                        <td>{{ $office['name'] }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-soft-info text-info font-size-12">
                                                {{ $office['sub_office_count'] }}
                                            </span>
                                        </td>
                                        <td class="text-end text-primary font-weight-bold">
                                            {{ number_format($office['total_demand'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('budget.distribution.entry', ['office_id' => $office['id']]) }}" class="btn btn-primary btn-sm btn-rounded waves-effect waves-light">
                                                <i class="bx bx-edit-alt me-1"></i> {{ __('Approval') }}
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-italic">{{ __('No data found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
