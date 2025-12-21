<section class="w-100">
    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6">
            <!-- Profile Photo -->
            <div class="mb-4">
                <label class="form-label" for="photo">{{ __('Profile Photo') }}</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="position-relative">
                        @if ($photo)
                            <img src="{{ $photo->temporaryUrl() }}" class="rounded-circle img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                        @else
                            <img src="{{ auth()->user()->profile_photo_url }}" class="rounded-circle img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                        @endif
                        
                        <div wire:loading wire:target="photo" class="position-absolute top-50 start-50 translate-middle">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <input type="file" id="photo" wire:model="photo" class="d-none" accept="image/*">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('photo').click()">
                            {{ __('Change Photo') }}
                        </button>
                        <p class="text-muted font-size-12 mt-1 mb-0">{{ __('Allowed JPG, GIF or PNG. Max size of 1MB') }}</p>
                        @error('photo') <span class="text-danger font-size-12">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input type="text" class="form-control" id="name" wire:model="name" required autofocus autocomplete="name">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input type="email" class="form-control" id="email" wire:model="email" required autocomplete="email">
                @error('email') <span class="text-danger">{{ $message }}</span> @enderror

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div class="mt-2">
                        <p class="text-muted">
                            {{ __('Your email address is unverified.') }}
                            <button type="button" class="btn btn-link p-0" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <div class="alert alert-success">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        {{-- 
        <hr class="my-4">
        <h5 class="mb-3 text-danger">{{ __('Delete Account') }}</h5>
        <livewire:settings.delete-user-form /> 
        --}}
    </x-settings.layout>
</section>
