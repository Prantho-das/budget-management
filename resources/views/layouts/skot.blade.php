<!doctype html>
<html lang="en">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <!-- App js -->
    {{-- <script src="{{ asset('assets/js/plugin.js') }}"></script> --}}
    <link rel="stylesheet" href="{{ asset('assets/solaimanlipi-font/bangla-font.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome pro/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>


        /* Global Table Font Resize */
        .table, .table th, .table td {
            font-size: 13px !important;
        }
        .table-sm th, .table-sm td {
            font-size: 12px !important;
        }

        /* Hide numeric spin buttons globally */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type="number"] {
            -moz-appearance: textfield;
        }
    </style>
</head>

<body data-sidebar="dark">
    @include('partials.loader')

    <!-- Begin page -->
    <div id="layout-wrapper">
        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box">
                        <a href="{{ route('dashboard') }}" wire:navigate class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('/storage/' . get_setting('site_logo')) ?: asset('assets/images/logo.svg') }}"
                                    alt="">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('/storage/' . get_setting('site_logo')) ?: asset('assets/images/logo-dark.png') }}"
                                    alt="">
                            </span>
                        </a>

                        <a href="{{ route('dashboard') }}" wire:navigate class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('/storage/' . get_setting('site_logo')) ?: asset('assets/images/logo-light.svg') }}"
                                    alt="">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('/storage/' . get_setting('site_logo')) ?: asset('assets/images/logo-light.png') }}"
                                    alt="">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="fas fa-bars"></i>
                    </button>

                </div>

                <div class="d-flex align-items-center">
                    @auth
                        <div class="d-inline-block">
                            <h5 class="mb-0 font-size-14 text-uppercase fw-bold text-primary">
                                <i class="bx bx-buildings me-1"></i>
                                {{ Auth::user()->office->name ?? __('No Office') }}
                            </h5>
                        </div>
                    @endauth
                    <div class="dropdown d-inline-block" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" class="btn header-item waves-effect" @click="open = !open"
                            :class="{ 'show': open }" aria-haspopup="true" aria-expanded="false">
                            @if (app()->getLocale() == 'bn')
                                <span class="align-middle fw-bold">বাংলা</span>
                            @else
                                {{-- <img id="header-lang-img" src="{{ asset('assets/images/flags/us.jpg') }}" alt="Header Language" height="16"> --}}
                                <span class="align-middle fw-bold">En</span>
                            @endif
                        </button>
                        <div class="dropdown-menu dropdown-menu-en" :class="{ 'show': open }" style="display: none;"
                            x-show="open" x-transition>

                            <!-- item-->
                            <a href="{{ route('lang.switch', 'en') }}" class="dropdown-item notify-item language"
                                data-lang="en" wire:navigate>
                                {{-- <img src="{{ asset('assets/images/flags/us.jpg') }}" alt="user-image" class="me-1" height="12">  --}}
                                <span class="align-middle">{{ __('English') }}</span>
                            </a>

                            <a href="{{ route('lang.switch', 'bn') }}" class="dropdown-item notify-item language"
                                data-lang="bn" wire:navigate>
                                <span class="align-middle">{{ __('Bangla') }}</span>
                            </a>
                        </div>
                    </div>



                    <div class="dropdown d-inline-block" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" class="btn header-item waves-effect" id="page-header-user-dropdown"
                            @click="open = !open" :class="{ 'show': open }" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ Auth::user()->profile_photo_url }}"
                                alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ms-1" key="t-henry">{{ Auth::user()->name }}</span>
                            <i class="mdi mdi-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-en" :class="{ 'show': open }" style="display: none;"
                            x-show="open" x-transition>
                            <!-- item-->
                            <a class="dropdown-item" href="{{ route('profile.edit') }}" wire:navigate><i
                                    class="bx bx-user font-size-16 align-middle me-1"></i> <span
                                    key="t-profile">Profile</span></a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="javascript:void(0);"
                                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                    class="bx bx-power-off font-size-16 align-middle me-1 text-danger"></i> <span
                                    key="t-logout">Logout</span></a>

                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">

                        @can('view-dashboard')
                        <li>
                            <a href="{{ route('dashboard') }}" wire:navigate class="waves-effect">
                                <i class="bx bx-home-circle"></i>
                                <span key="t-dashboards">{{ __('Dashboard') }}</span>
                            </a>
                        </li>
                        @endcan
                        @canany(['view-budget-estimations', 'approve-budget', 'release-budget', 'view-budget-status', 'view-budget-summary'])
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-file"></i>
                                    <span key="t-utility">{{ __('Budget Demand') }}</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @can('create-budget-estimations')
                                        <li><a href="{{ route('budget.estimations') }}" wire:navigate
                                                key="t-estimations">{{ __('Entry') }}</a></li>
                                    @endcan
                                    @canany(['approve-budget', 'reject-budget', 'release-budget'])
                                        <li><a href="{{ route('budget.approvals') }}" wire:navigate
                                                key="t-approvals">{{ __('Approval') }}</a></li>
                                    @endcanany
                                    {{-- @can('release-budget')
                                        <li>
                                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                                <i class="bx bx-layer"></i>
                                                <span key="t-preparation">{{ __('Budget Preparation') }}</span>
                                            </a>
                                            <ul class="sub-menu" aria-expanded="false">
                                                <li><a href="{{ route('budget.release') }}" wire:navigate
                                                        key="t-release">{{ __('Budget Release') }}</a></li>
                                                <li><a href="{{ route('budget.office-wise') }}" wire:navigate
                                                        key="t-office-wise">{{ __('Office-wise Budget') }}</a></li>
                                            </ul>
                                        </li>
                                    @endcan --}}
                                    @can('view-budget-status')
                                        <li><a href="{{ route('budget.status') }}" wire:navigate
                                                key="t-status">{{ __('Budget Status') }}</a></li>
                                    @endcan
                                    @can('view-budget-summary')
                                        <li><a href="{{ route('budget.summary') }}" wire:navigate
                                                key="t-summary">{{ __('My Budget Summary') }}</a></li>
                                    @endcan
                                </ul>
                            </li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-share-alt"></i>
                                    <span key="t-budget-distribution">{{ __('Budget Distribution') }}</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @can('budget-distribution-single')
                                    <li>
                                            <a href="{{ route('budget.distribution.entry') }}" wire:navigate
                                                key="t-distribution-entry">{{ __('Adjustment Create') }}</a></li>
                                                                                       @endcan
                                                    @can('view-budget-distribution')
                                                    <li><a href="{{ route('budget.distribution.list') }}" wire:navigate
                                                    key="t-distribution-list">{{ __('Adjustment') }}</a></li>
                                                    @endcan
                                      
                                </ul>
                            </li>
                        @endcan

                        @canany(['release-budget'])
                        <li>
                            <a href="javascript: void(0);" class="has-arrow waves-effect">
                                <i class="bx bx-layer"></i>
                                <span key="t-preparation">{{ __('Budget Preparation') }}</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                {{-- <li><a href="{{ route('budget.release') }}" wire:navigate
                                        key="t-release">{{ __('Budget Summary') }}</a></li> --}}
                                @can('release-budget')
                                <li><a href="{{ route('setup.ministry-budget-list') }}" wire:navigate
                                        key="t-ministry-entry">{{ __('Ministry Budget Entry List') }}</a></li>
                                @endcan
                                @can('release-budget')
                                <li><a href="{{ route('budget.office-wise') }}" wire:navigate
                        key="t-office-wise">{{ __('Ministry Budget Preparation') }}</a></li>
                                @endcan

                            </ul>
                        </li>
                    @endcan
                        @can('view-expenses')
                            <li>
                                <a href="{{ route('setup.expenses') }}" wire:navigate class="waves-effect">
                                    <i class="bx bx-wallet"></i>
                                    <span key="t-expenses">{{ __('Expenses') }}</span>
                                </a>
                            </li>
                        @endcan

                        @canany(['view-users', 'view-roles', 'view-permissions'])
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-user-circle"></i>
                                    <span key="t-user-management">{{ __('Access Control') }}</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @can('view-users')
                                        <li><a href="{{ route('setup.users') }}" wire:navigate
                                                key="t-users">{{ __('Users') }}</a></li>
                                    @endcan
                                    @can('view-roles')
                                        <li><a href="{{ route('setup.roles') }}" wire:navigate
                                                key="t-roles">{{ __('Roles') }}</a></li>
                                    @endcan
                                    @can('view-permissions')
                                        <li><a href="{{ route('setup.permissions') }}" wire:navigate
                                                key="t-permissions">{{ __('Permissions') }}</a></li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan

                        @canany([
        'view-fiscal-years',
        'view-budget-types',
        'view-offices',
        'view-economic-codes',
        'view-system-settings'
    ])
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-cog"></i>
                                    <span key="t-setup">{{ __('Settings') }}</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    @can('view-fiscal-years')
                                        <li><a href="{{ route('setup.fiscal-years') }}" wire:navigate
                                                key="t-fiscal-years">{{ __('Fiscal Years') }}</a></li>
                                    @endcan
                                    @can('view-budget-types')
                                        <li><a href="{{ route('setup.budget-types') }}" wire:navigate
                                                key="t-budget-types">{{ __('Budget Types') }}</a></li>
                                    @endcan
                                    @can('view-offices')
                                        <li><a href="{{ route('setup.rpo-units') }}" wire:navigate
                                                key="t-offices">{{ __('Offices') }}</a></li>
                                    @endcan
                                    @can('view-economic-codes')
                                        <li><a href="{{ route('setup.economic-codes') }}" wire:navigate
                                                key="t-economic-codes">{{ __('Economic Codes') }}</a></li>
                                    @endcan
                                    @can('view-system-settings')
                                        <li><a href="{{ route('setup.system-settings') }}" wire:navigate
                                                key="t-system-settings">{{ __('System Settings') }}</a></li>
                                        <li><a href="{{ route('setup.workflow') }}" wire:navigate
                                                key="t-workflow">{{ __('Workflow Setup') }}</a></li>
                                    @endcan
                                </ul>
                            </li>
                        @endcan


                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->



        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">
                    {{ $slot ?? '' }}
                    @yield('content')
                </div>
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->

            <!-- Transaction Modal -->
            <div class="modal fade transaction-detailModal" tabindex="-1" role="dialog"
                aria-labelledby="transaction-detailModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="transaction-detailModalLabel">Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="mb-2">Product id: <span class="text-primary">#SK2540</span></p>
                            <p class="mb-4">Billing Name: <span class="text-primary">Neal Matthews</span></p>

                            <div class="table-responsive">
                                <table class="table align-middle table-nowrap">
                                    <thead>
                                        <tr>
                                            <th scope="col">Product</th>
                                            <th scope="col">Product Name</th>
                                            <th scope="col">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">
                                                <div>
                                                    <img src="{{ asset('assets/images/product/img-7.png') }}"
                                                        alt="" class="avatar-sm">
                                                </div>
                                            </th>
                                            <td>
                                                <div>
                                                    <h5 class="text-truncate font-size-14">Wireless Headphone (Black)
                                                    </h5>
                                                    <p class="text-muted mb-0">$ 225 x 1</p>
                                                </div>
                                            </td>
                                            <td>$ 255</td>
                                        </tr>
                                        <tr>
                                            <th scope="row">
                                                <div>
                                                    <img src="{{ asset('assets/images/product/img-4.png') }}"
                                                        alt="" class="avatar-sm">
                                                </div>
                                            </th>
                                            <td>
                                                <div>
                                                    <h5 class="text-truncate font-size-14">Phone patterned cases</h5>
                                                    <p class="text-muted mb-0">$ 145 x 1</p>
                                                </div>
                                            </td>
                                            <td>$ 145</td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <h6 class="m-0 text-right">Sub Total:</h6>
                                            </td>
                                            <td>
                                                $ 400
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <h6 class="m-0 text-right">Shipping:</h6>
                                            </td>
                                            <td>
                                                Free
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="2">
                                                <h6 class="m-0 text-right">Total:</h6>
                                            </td>
                                            <td>
                                                $ 400
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end modal -->

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            {{ date('Y') }} © {{ get_setting('site_name', 'Budget Management System') }}.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                {{ get_setting('footer_text', 'Design & Develop by NBT') }}
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <!-- Right Sidebar -->
    <div class="right-bar">
        <div data-simplebar class="h-100">
            <div class="rightbar-title d-flex align-items-center px-3 py-4">

                <h5 class="m-0 me-2">Settings</h5>

                <a href="javascript:void(0);" class="right-bar-toggle ms-auto">
                    <i class="mdi mdi-close noti-icon"></i>
                </a>
            </div>

            <!-- Settings -->
            <hr class="mt-0" />
            <h6 class="text-center mb-0">Choose Layouts</h6>

            <div class="p-4">
                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-1.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="light-mode-switch" checked>
                    <label class="form-check-label" for="light-mode-switch">Light Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-2.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="dark-mode-switch">
                    <label class="form-check-label" for="dark-mode-switch">Dark Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-3.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input theme-choice" type="checkbox" id="rtl-mode-switch">
                    <label class="form-check-label" for="rtl-mode-switch">RTL Mode</label>
                </div>

                <div class="mb-2">
                    <img src="{{ asset('assets/images/layouts/layout-4.jpg') }}" class="img-thumbnail"
                        alt="layout images">
                </div>
                <div class="form-check form-switch mb-5">
                    {{-- <input class="form-check-input theme-choice" type="checkbox" id="dark-rtl-mode-switch"> --}}
                    <label class="form-check-label" for="dark-rtl-mode-switch">Dark RTL Mode</label>
                </div>


            </div>

        </div> <!-- end slimscroll-menu-->
    </div>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- apexcharts -->
    <script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>



    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    <script src="{{ asset('assets/js/custom.js') }}"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    @stack('scripts')
    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

    <script>


        // Initialize Select2
        function initSelect2() {
            $('.custom-select2').each(function() {
                let $el = $(this);
                
                // If already initialized, destroy first or skip
                if ($el.hasClass('select2-hidden-accessible')) {
                    $el.select2('destroy');
                }

                $el.select2({
                    dropdownParent: $el.closest('.modal-content').length ? $el.closest('.modal-content') : $(document.body),
                    width: '100%',
                    placeholder: $el.attr('placeholder') || 'Select an option'
                });

                // Sync with Livewire
                $el.on('change', function (e) {
                    let model = $el.attr('wire:model');
                    if (model) {
                         // Find the closest Livewire component
                        let component = Livewire.find($el.closest('[wire\\:id]').attr('wire:id'));
                        if (component) {
                            component.set(model, $el.val());
                        }
                    }
                });
            });
        }

        // Initialize on page load
        $(document).ready(initSelect2);

        // Re-initialize on Livewire navigation
        document.addEventListener('livewire:navigated', initSelect2);

        // Re-initialize on specific events
        document.addEventListener('livewire:initialized', () => {
             Livewire.on('select2-reinit', () => {
                setTimeout(initSelect2, 100);
            });

            // Alternative: hook into all commits to catch dynamically added items
            Livewire.hook('commit', ({ component, commit, succeed, fail, respond }) => {
                succeed(() => {
                    setTimeout(initSelect2, 50);
                });
            });
        });
    </script>
    
</body>

</html>
