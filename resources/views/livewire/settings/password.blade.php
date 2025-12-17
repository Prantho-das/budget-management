<section class="w-100">
    <x-settings.layout :heading="__('Update password')" :subheading="__('Ensure your account is using a long, random password to stay secure')">
        <form wire:submit="updatePassword" class="mt-4">
            <div class="mb-3">
                <label for="current_password" class="form-label">{{ __('Current password') }}</label>
                <input type="password" class="form-control" id="current_password" wire:model="current_password" required autocomplete="current-password">
                @error('current_password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">{{ __('New password') }}</label>
                <input type="password" class="form-control" id="password" wire:model="password" required autocomplete="new-password">
                @error('password') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                <input type="password" class="form-control" id="password_confirmation" wire:model="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="d-flex align-items-center gap-3">
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
