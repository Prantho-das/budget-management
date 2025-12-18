<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">Budget Estimation (Prakkalon)</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Budgeting</a></li>
                        <li class="breadcrumb-item active">Estimation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    @if(!$currentFiscalYear || !$currentOffice)
        <div class="alert alert-warning">
            Please ensure you have an Active Fiscal Year and at least one Office created.
        </div>
    @else

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="card-title">Fiscal Year: {{ $currentFiscalYear->name }}</h5>
                            <p class="card-title-desc mb-2">Office: {{ $currentOffice->name }} ({{ $currentOffice->code }})</p>
                            
                            <div class="mb-3" style="max-width: 300px;">
                                <label class="form-label font-size-13 text-muted">Budget Request Type</label>
                                <select wire:model="budget_type" wire:change="loadDemands" class="form-select form-select-sm" {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                    <option value="Main Budget">Main Budget</option>
                                    <option value="Supplementary Budget 1">Supplementary Budget 1</option>
                                    <option value="Supplementary Budget 2">Supplementary Budget 2</option>
                                    <option value="Supplementary Budget 3">Supplementary Budget 3</option>
                                </select>
                            </div>

                            <p class="mb-0">Status: 
                                @php
                                    $badgeClass = match($status) {
                                        'draft' => 'secondary',
                                        'submitted' => 'primary',
                                        'partially_approved' => 'info',
                                        'district_approved' => 'info',
                                        'hq_approved' => 'success',
                                        'approved' => 'success',
                                        'rejected' => 'danger',
                                        default => 'warning'
                                    };
                                @endphp
                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </span>
                            </p>
                        </div>
                        <div>
                            @if($status === 'draft' || $status === 'rejected')
                                <button wire:click="saveDraft" class="btn btn-secondary waves-effect waves-light me-2">Save Draft</button>
                                <button wire:click="submit" class="btn btn-primary waves-effect waves-light">Submit Budget</button>
                            @else
                                <button class="btn btn-success disabled">
                                    <i class="bx bx-check-double me-1"></i> Submitted
                                </button>
                            @endif
                        </div>
                    </div>

                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th style="width: 15%;">Economic Code</th>
                                    <th>Description</th>
                                    <th style="width: 15%;">Prev. Year (Prakkalon)</th>
                                    <th style="width: 20%;">Demand Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($economicCodes as $code)
                                    <tr>
                                        <td><strong>{{ $code->code }}</strong></td>
                                        <td>
                                            {{ $code->name }} <br>
                                            <small class="text-muted">{{ $code->description }}</small>
                                        </td>
                                        <td class="text-end">
                                            @if(isset($previousDemands[$code->id]))
                                                <strong>৳ {{ number_format($previousDemands[$code->id], 2) }}</strong>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-text">৳</span>
                                                <input type="number" 
                                                       step="0.01" 
                                                       class="form-control" 
                                                       wire:model.defer="demands.{{ $code->id }}" 
                                                       placeholder="0.00"
                                                       {{ $status !== 'draft' && $status !== 'rejected' ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                         @if($status === 'draft')
                            <button wire:click="saveDraft" class="btn btn-secondary waves-effect waves-light">Save Draft</button>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
