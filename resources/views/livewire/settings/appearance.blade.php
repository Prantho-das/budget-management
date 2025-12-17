<section class="w-100">
    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        
        <div class="row" x-data="{ theme: 'light' }"> 
            <!-- Note: Actual theme switching logic needs to be implemented via JS or backend pref -->
            
            <div class="col-md-4">
                <div class="form-check card-radio">
                    <input id="theme-light" name="theme" type="radio" value="light" class="form-check-input" checked>
                    <label class="form-check-label" for="theme-light">
                        <i class="bx bx-sun d-block display-4 mb-2 text-warning"></i>
                        {{ __('Light') }}
                    </label>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-check card-radio">
                    <input id="theme-dark" name="theme" type="radio" value="dark" class="form-check-input">
                    <label class="form-check-label" for="theme-dark">
                        <i class="bx bx-moon d-block display-4 mb-2"></i>
                        {{ __('Dark') }}
                    </label>
                </div>
            </div>
            
             <div class="col-md-4">
                <div class="form-check card-radio">
                    <input id="theme-system" name="theme" type="radio" value="system" class="form-check-input">
                    <label class="form-check-label" for="theme-system">
                        <i class="bx bx-desktop d-block display-4 mb-2"></i>
                        {{ __('System') }}
                    </label>
                </div>
            </div>

        </div>

    </x-settings.layout>
</section>
