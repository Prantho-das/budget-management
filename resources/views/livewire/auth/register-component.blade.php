
    <div class="card overflow-hidden">
        <div class="bg-primary-subtle">
            <div class="row">
                <div class="col-7">
                    <div class="text-primary p-4">
                        <h5 class="text-primary">Free Register</h5>
                        <p>Get your free {{ config('app.name') }} account now.</p>
                    </div>
                </div>
                <div class="col-5 align-self-end">
                    <img src="{{ asset('assets/images/profile-img.png') }}" alt="" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="card-body pt-0"> 
            <div>
                <a href="{{ route('home') }}" wire:navigate>
                    <div class="avatar-md profile-user-wid mb-4">
                        <span class="avatar-title rounded-circle bg-light">
                            <img src="{{ asset('assets/images/logo.svg') }}" alt="" class="rounded-circle" height="34">
                        </span>
                    </div>
                </a>
            </div>
            <div class="p-2">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="alert alert-success text-center mb-4" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="needs-validation" wire:submit="register">
                    <div class="mb-3">
                        <label for="name" class="form-label">{{ __('Name') }}</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" wire:model="name" required autofocus placeholder="Enter name">
                        @error('name') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">{{ __('Email address') }}</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" wire:model="email" required placeholder="Enter email">
                        @error('email') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('Password') }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" wire:model="password" required placeholder="Enter password">
                        @error('password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">{{ __('Confirm password') }}</label>
                        <input type="password" class="form-control" id="password_confirmation" wire:model="password_confirmation" required placeholder="Enter confirm password">
                    </div>
                    
                    <div class="mt-4 d-grid">
                        <button class="btn btn-primary waves-effect waves-light" type="submit" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ __('Register') }}</span>
                            <span wire:loading>{{ __('Registering...') }}</span>
                        </button>
                    </div>

                    <div class="mt-4 text-center">
                        <p class="mb-0">By registering you agree to the {{ config('app.name') }} <a href="#" class="text-primary">Terms of Use</a></p>
                    </div>
                </form>
            </div>

        </div>
    </div>
    <div class="mt-5 text-center">
        
        <div>
            @if (Route::has('login'))
                <p>Already have an account ? <a href="{{ route('login') }}" class="fw-medium text-primary" wire:navigate> Login</a> </p>
            @endif
            <p>© {{ date('Y') }} {{ get_setting('site_name', 'Budget Management System') }}. Crafted with <i class="mdi mdi-heart text-danger"></i> by Themesbrand</p>
        </div>
    </div>
