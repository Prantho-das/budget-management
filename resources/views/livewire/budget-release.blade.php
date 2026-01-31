  <div>
      <style>
          @media print {

              .vertical-menu,
              .navbar-header,
              .footer,
              .card-body.border-bottom,
              .btn,
              .breadcrumb {
                  display: none !important;
              }

              .main-content {
                  margin: 0 !important;
                  padding: 0 !important;
              }

              .card {
                  border: none !important;
                  box-shadow: none !important;
              }

              .table-responsive {
                  overflow: visible !important;
              }

              .page-title-box h4 {
                  text-align: center;
                  width: 100%;
                  margin-bottom: 20px;
              }

              .office-wise-budget-table .title-box-inner {
                  font-size: 10px !important;
              }

              .office-wise-budget-table .card .table-title-box .title-box-inner .ministry,
              .office-wise-budget-table .card .table-title-box .title-box-inner .title {
                  font-size: 10px !important;
              }

              .table td,
              table th {
                  padding: 0.3rem !important;
                  font-size: 10px !important;
              }
          }
      </style>

      <div class="row">
          <div class="col-12">
              <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                  <h4 class="mb-sm-0 font-size-18">{{ __('Budget Release (Head Quarter)') }}</h4>

                  <div class="page-title-right d-flex align-items-center">
                      <button type="button" class="btn btn-primary btn-sm me-2" onclick="window.print()">
                          <i class="bx bx-printer"></i> {{ __('Print Report') }}
                      </button>
                      <ol class="breadcrumb m-0 d-none d-sm-flex">
                          <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Budgeting') }}</a></li>
                          <li class="breadcrumb-item active">{{ __('Release') }}</li>
                      </ol>
                  </div>
              </div>
          </div>
      </div>

      @if(collect($ministryBudgetSummary)->isEmpty())
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-outline me-2"></i>
                        <strong>{{ __('Warning') }}:</strong> {{ __('No Ministry Budget allocation found for this Fiscal Year and Budget Type.') }} 
                        {{ __('Please ensure Ministry Budget Entry is completed first.') }}
                        <a href="{{ route('setup.ministry-budget-entry') }}" class="alert-link">{{ __('Go to Entry') }}</a>
                    </div>
                </div>
            </div>
      @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white border-bottom-0">
                         <h5 class="card-title text-primary mb-0"><i class="bx bx-buildings me-1"></i> {{ __('Ministry Budget Status Summary') }}</h5>
                    </div>
                    <div class="card-body">
                         <div class="table-responsive">
                            <table class="table table-sm align-middle table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>{{ __('Economic Code') }}</th>
                                        <th class="text-end">{{ __('Allocated (Ministry)') }}</th>
                                        <th class="text-end">{{ __('Already Released') }}</th>
                                        <th class="text-end">{{ __('Remaining') }}</th>
                                        <th class="text-end" style="width: 200px;">{{ __('Usage') }} %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ministryBudgetSummary as $codeId => $data)
                                        <tr>
                                            <td><strong>{{ $data['code'] }}</strong> - {{ explode(' - ', $data['code_name'])[1] ?? '' }}</td>
                                            <td class="text-end">{{ number_format($data['allocated']) }}</td>
                                            <td class="text-end">{{ number_format($data['released']) }}</td>
                                            <td class="text-end">
                                                <span class="badge {{ $data['remaining'] > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} font-size-12">
                                                    {{ number_format($data['remaining']) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="font-size-12 me-2">{{ number_format($data['usage_percent'], 1) }}%</span>
                                                    <div class="progress flex-grow-1" style="height: 6px;">
                                                        <div class="progress-bar {{ $data['usage_percent'] > 90 ? 'bg-danger' : ($data['usage_percent'] > 75 ? 'bg-warning' : 'bg-success') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ min($data['usage_percent'], 100) }}%">
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr class="fw-bold bg-light">
                                        <td>{{ __('Total') }}</td>
                                        <td class="text-end">{{ number_format(collect($ministryBudgetSummary)->sum('allocated')) }}</td>
                                        <td class="text-end">{{ number_format(collect($ministryBudgetSummary)->sum('released')) }}</td>
                                        <td class="text-end">{{ number_format(collect($ministryBudgetSummary)->sum('remaining')) }}</td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      @endif
 
      <div class="row">
          <div class="col-sm-12 col-md-12 col-lg-12">
              <div class="unitoffice-entry-table office-wise-budget-table">
                  <div class="card shadow-sm border-0">
                      <div class="card-body">
                          <div class="table-title-box">
                              <div class="title-box-inner">
                                  <div class="title">পরিচালন ব্যায়ের প্রাথমিক প্রাক্কলন ও প্রক্ষেপন </div>
                                  <div class="ministry">
                                      <span>মন্ত্রণালয় / বিভাগ</span> : ১৬১ সুরক্ষা সেনা বিভাগ,স্বরাষ্ট্র মন্ত্রণালয়
                                  </div>
                                  <div class="ministry">
                                      <span>অধিদপ্তর</span> : ১৬১০৫ ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর
                                  </div>
                                  <div class="ministry">
                                      <span>প্রাতিষ্ঠানিক ইউনিট</span> : ১৬১০৫০৩ ইমিগ্রেশন ও পাসপোর্ট অধিদপ্তর
                                  </div>
                              </div>
                          </div>
                          <div class="amt-flex">
                              <div class="left-text">সাধারণ কার্যক্রম</div>
                              <div class="left-text">(অংকসমূহ হাজার টাকায়)</div>
                          </div>
                          <div class="table-responsive">
                              <table class="table table-bordered align-middle custom-budget-table">
                                  <thead>
                                      <tr class="table-primary text-center">
                                          <th rowspan="2">অর্থনৈতিক গ্রুপ / কোড</th>
                                          <th rowspan="2">বিবরণ</th>
                                          <th colspan="3">প্রকৃত ব্যায়</th>
                                          <th>বাজেট</th>
                                          <th>প্রস্তাবিত <br> সংশোধিত</th>
                                          <th>প্রাক্কলন</th>
                                          <th colspan="2">প্রক্ষেপন</th>

                                          <th rowspan="2">ব্যাখ্যামূলক <br> মন্তব্য</th>
                                      </tr>
                                      <tr>
                                          <th>2022-2023</th>
                                          <th>2023-24</th>

                                          <th>প্রথম ৬ মাস <br> 2024-25</th>
                                          <th>2024-25</th>
                                          <th>2024-25</th>
                                          <th>2025-26</th>
                                          <th>2026-27</th>
                                          <th>2027-28</th>
                                      </tr>
                                      <tr>
                                          <th>1</th>
                                          <th>2</th>
                                          <th>3</th>
                                          <th>4</th>
                                          <th>5</th>
                                          <th>6</th>
                                          <th>7</th>
                                          <th>8</th>
                                          <th>9</th>
                                          <th>10</th>
                                          <th>11</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @foreach ($economicCodes as $code)
                                          <tr class="{{ $code->parent_id == null ? 'bg-soft-primary fw-bold' : '' }}">
                                              <td class="text-center">
                                                  <span
                                                      class="badge bg-{{ $code->parent_id ? 'secondary' : 'primary' }}-subtle text-{{ $code->parent_id ? 'secondary' : 'primary' }}">
                                                      {{ $code->code }}
                                                  </span>
                                              </td>
                                              <td>{{ $code->name }}</td>

                                              {{-- Historical Data --}}
                                              @for ($i = 0; $i < 3; $i++)
                                                  <td class="text-end">
                                                      @php $val = $historicalData["year_{$i}"][$code->id] ?? 0; @endphp
                                                      {{ $val > 0 ? number_format($val, 0) : '-' }}
                                                  </td>
                                              @endfor

                                              {{-- Budget Year Data --}}
                                              @php
                                                  $data = $currentDemands[$code->id] ?? null;
                                                  $demand = $data ? $data->demand : 0;
                                                  $approved = $data ? $data->approved : 0;
                                                  $released = $currentReleased[$code->id] ?? 0;
                                                  $balance = $approved - $released;
                                              @endphp
                                              <td class="text-end text-info fw-bold">
                                                  {{ $demand > 0 ? number_format($demand, 0) : '-' }}</td>
                                              <td class="text-end text-success fw-bold">
                                                  {{ $approved > 0 ? number_format($approved, 0) : '-' }}</td>
                                              <td class="text-end text-warning fw-bold">
                                                  {{ $released > 0 ? number_format($released, 0) : '-' }}</td>
                                              <td class="text-end fw-bold">
                                                  {{ $balance > 0 ? number_format($balance, 0) : '-' }}</td>
                                              <td> </td>
                                              <td> </td>
                                          </tr>
                                      @endforeach
                                  </tbody>
                                  <tfoot class="bg-light fw-bold">
                                      <tr>
                                          <td colspan="2" class="text-center">{{ __('Grand Total') }}</td>
                                          @for ($i = 0; $i < 3; $i++)
                                              <td class="text-end">
                                                  @php
                                                      $totalY = 0;
                                                      foreach ($economicCodes as $c) {
                                                          if ($c->parent_id == null) {
                                                              $totalY += $historicalData["year_{$i}"][$c->id] ?? 0;
                                                          }
                                                      }
                                                  @endphp
                                                  {{ $totalY > 0 ? number_format($totalY, 0) : '-' }}
                                              </td>
                                          @endfor
                                          <td class="text-end text-info">
                                              @php
                                                  $totalD = 0;
                                                  foreach ($economicCodes as $c) {
                                                      if ($c->parent_id == null) {
                                                          $totalD += $currentDemands[$c->id]->demand ?? 0;
                                                      }
                                                  }
                                              @endphp
                                              {{ $totalD > 0 ? number_format($totalD, 0) : '-' }}
                                          </td>
                                          <td class="text-end text-success">
                                              @php
                                                  $totalA = 0;
                                                  foreach ($economicCodes as $c) {
                                                      if ($c->parent_id == null) {
                                                          $totalA += $currentDemands[$c->id]->approved ?? 0;
                                                      }
                                                  }
                                              @endphp
                                              {{ $totalA > 0 ? number_format($totalA, 0) : '-' }}
                                          </td>
                                          <td class="text-end text-warning">
                                              @php
                                                  $totalR = 0;
                                                  foreach ($economicCodes as $c) {
                                                      if ($c->parent_id == null) {
                                                          $totalR += $currentReleased[$c->id] ?? 0;
                                                      }
                                                  }
                                              @endphp
                                              {{ $totalR > 0 ? number_format($totalR, 0) : '-' }}
                                          </td>
                                          <td class="text-end">
                                              {{ $totalA - $totalR > 0 ? number_format($totalA - $totalR, 0) : '-' }}
                                          </td>
                                          <td></td>
                                          <td></td>
                                      </tr>
                                  </tfoot>
                              </table>
                          </div>
                      </div>
                  </div>
              </div>
          </div>
      </div>
  </div>
  </div>
