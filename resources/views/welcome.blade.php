<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <title>Budget Management System | Precise Planning</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Professional Budget Management System" name="description" />
    
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />

    <style>
        .hero-section {
            padding: 150px 0 100px;
            background: linear-gradient(to right, #f8f9fa 50%, #ffffff 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .hero-title {
            font-weight: 700;
            font-size: 3.5rem;
            line-height: 1.2;
            color: #495057;
        }
        .hero-title span {
            color: #556ee6;
        }
        .hero-desc {
            font-size: 1.1rem;
            color: #74788d;
            margin: 25px 0;
        }
        .feature-card {
            border: none;
            transition: all 0.3s ease-in-out;
            padding: 20px;
            border-radius: 15px;
            background: #fff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            background: rgba(85, 110, 230, 0.1);
            color: #556ee6;
        }
        .stats-section {
            padding: 80px 0;
            background: #2a3042;
            color: #fff;
        }
        .stat-item h2 {
            font-weight: 700;
            color: #556ee6;
            margin-bottom: 10px;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #556ee6 !important;
        }
    </style>
</head>

<body data-topbar="dark" class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm px-lg-5">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">
                <i class="bx bx-pie-chart-alt-2 align-middle me-1"></i> BudgetSystem
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="topnav-menu-content">
                <ul class="navbar-nav me-auto">
                </ul>
                <div class="d-flex align-items-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" wire:navigate class="btn btn-primary btn-rounded waves-effect waves-light">
                                <i class="bx bx-home-circle me-1"></i> Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-link text-muted me-3 fw-medium">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" wire:navigate class="btn btn-primary btn-rounded waves-effect waves-light">Get Started</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">Smart <span>Budgeting</span> Solutions for Offices</h1>
                        <p class="hero-desc">Streamline your financial planning, hierarchical approvals, and user management with our enterprise-grade budget system. Built for precision and scale.</p>
                        <div class="d-flex gap-3 mt-4">
                            <a href="{{ route('login') }}" wire:navigate class="btn btn-primary btn-lg p-3 px-5 btn-rounded shadow-lg">Login to System</a>
                            <a href="#features" class="btn btn-light btn-lg p-3 px-5 btn-rounded">Learn More</a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-none d-lg-block">
                    <div class="hero-img ps-lg-5">
                        <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=1200&q=80" alt="Budget Analytics" class="img-fluid rounded-3 shadow-lg">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-white" id="features">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="text-center mb-5">
                        <h5 class="text-primary text-uppercase fw-bold mb-3">Features</h5>
                        <h2 class="fw-bold">Everything You Need to Manage Budgets</h2>
                        <p class="text-muted">Powerful tools designed specifically for government and administrative office hierarchies.</p>
                    </div>
                </div>
            </div>

            <div class="row pt-4">
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bx bx-copy-alt"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Budget Demand</h4>
                        <p class="text-muted">Submit precise budget demands for economic codes with historical comparison data for smarter decisions.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bx bx-check-shield"></i>
                        </div>
                        <h4 class="fw-bold mb-3">Workflow Approval</h4>
                        <p class="text-muted">Built-in hierarchical approval engine. Route requests from sub-offices to HQ with status tracking.</p>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <div class="feature-card text-center">
                        <div class="feature-icon mx-auto">
                            <i class="bx bx-user-circle"></i>
                        </div>
                        <h4 class="fw-bold mb-3">User Transfers</h4>
                        <p class="text-muted">Effortlessly manage office staff. Transfer users between RPO units while maintaining their roles and access levels.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container text-center">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold">50+</h2>
                        <p class="text-muted mb-0">Active Offices</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold">100%</h2>
                        <p class="text-muted mb-0">Transparency</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold">24/7</h2>
                        <p class="text-muted mb-0">System Uptime</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-5 bg-white border-top">
        <div class="container text-center">
            <p class="text-muted mb-0">&copy; {{ date('Y') }} Budget Management System. Designed for Administrative Excellence.</p>
        </div>
    </footer>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/node-waves/waves.min.js') }}"></script>

    <script src="{{ asset('assets/js/app.js') }}"></script>

</body>
</html>
