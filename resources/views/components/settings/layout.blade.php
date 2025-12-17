<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('profile.edit') }}" wire:navigate class="list-group-item list-group-item-action {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                        <i class="bx bx-user me-1"></i> {{ __('Profile') }}
                    </a>
                    <a href="{{ route('user-password.edit') }}" wire:navigate class="list-group-item list-group-item-action {{ request()->routeIs('user-password.edit') ? 'active' : '' }}">
                        <i class="bx bx-key me-1"></i> {{ __('Password') }}
                    </a>
                    @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                        <a href="{{ route('two-factor.show') }}" wire:navigate class="list-group-item list-group-item-action {{ request()->routeIs('two-factor.show') ? 'active' : '' }}">
                            <i class="bx bx-shield-quarter me-1"></i> {{ __('Two-Factor Auth') }}
                        </a>
                    @endif
                    <a href="{{ route('settings.appearance') }}" wire:navigate class="list-group-item list-group-item-action {{ request()->routeIs('settings.appearance') ? 'active' : '' }}">
                        <i class="bx bx-moon me-1"></i> {{ __('Appearance') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                @if(isset($heading))
                    <h4 class="card-title mb-3">{{ $heading }}</h4>
                @endif
                @if(isset($subheading))
                    <p class="card-title-desc mb-4">{{ $subheading }}</p>
                @endif

                {{ $slot }}
            </div>
        </div>
    </div>
</div>
