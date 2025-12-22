<div>
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 font-size-18">{{ __('System Settings') }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">{{ __('Setup') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('System Settings') }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if (session()->has('message'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bx bx-check-circle me-2"></i>{{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <!-- Tabs -->
                    <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}" 
                               wire:click="$set('activeTab', 'general')" 
                               role="tab">
                                <i class="bx bx-cog me-1"></i>
                                <span class="d-none d-sm-block">{{ __('General') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'appearance' ? 'active' : '' }}" 
                               wire:click="$set('activeTab', 'appearance')" 
                               role="tab">
                                <i class="bx bx-palette me-1"></i>
                                <span class="d-none d-sm-block">{{ __('Appearance') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'seo' ? 'active' : '' }}" 
                               wire:click="$set('activeTab', 'seo')" 
                               role="tab">
                                <i class="bx bx-search-alt me-1"></i>
                                <span class="d-none d-sm-block">{{ __('SEO') }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'social' ? 'active' : '' }}" 
                               wire:click="$set('activeTab', 'social')" 
                               role="tab">
                                <i class="bx bx-share-alt me-1"></i>
                                <span class="d-none d-sm-block">{{ __('Social Media') }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3 text-muted">
                        <form wire:submit.prevent="save">
                            <!-- General Settings -->
                            @if($activeTab === 'general')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Site Name') }}</label>
                                        <input type="text" class="form-control" wire:model="settings.site_name">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Site Title') }}</label>
                                        <input type="text" class="form-control" wire:model="settings.site_title">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Contact Email') }}</label>
                                        <input type="email" class="form-control" wire:model="settings.contact_email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Contact Phone') }}</label>
                                        <input type="text" class="form-control" wire:model="settings.contact_phone">
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Contact Address') }}</label>
                                        <textarea class="form-control" rows="3" wire:model="settings.contact_address"></textarea>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Footer Text') }}</label>
                                        <input type="text" class="form-control" wire:model="settings.footer_text">
                                        <small class="text-muted">{{ __('This text will appear in the footer of the dashboard.') }}</small>
                                    </div>
                                </div>
                            @endif

                            <!-- Appearance Settings -->
                            @if($activeTab === 'appearance')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Site Logo') }}</label>
                                        @if(isset($settings['site_logo']) && $settings['site_logo'])
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $settings['site_logo']) }}" 
                                                     alt="Logo" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 100px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" wire:model="logo" accept="image/*">
                                        <small class="text-muted">{{ __('Recommended size: 200x60 pixels') }}</small>
                                        @if ($logo)
                                            <div class="mt-2">
                                                <span class="badge bg-info">{{ __('New logo selected') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Favicon') }}</label>
                                        @if(isset($settings['site_favicon']) && $settings['site_favicon'])
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $settings['site_favicon']) }}" 
                                                     alt="Favicon" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 50px;">
                                            </div>
                                        @endif
                                        <input type="file" class="form-control" wire:model="favicon" accept="image/*">
                                        <small class="text-muted">{{ __('Recommended size: 32x32 pixels') }}</small>
                                        @if ($favicon)
                                            <div class="mt-2">
                                                <span class="badge bg-info">{{ __('New favicon selected') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- SEO Settings -->
                            @if($activeTab === 'seo')
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Meta Description') }}</label>
                                        <textarea class="form-control" rows="3" wire:model="settings.meta_description"></textarea>
                                        <small class="text-muted">{{ __('Recommended length: 150-160 characters') }}</small>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Meta Keywords') }}</label>
                                        <textarea class="form-control" rows="2" wire:model="settings.meta_keywords"></textarea>
                                        <small class="text-muted">{{ __('Separate keywords with commas') }}</small>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">{{ __('Meta Author') }}</label>
                                        <input type="text" class="form-control" wire:model="settings.meta_author">
                                    </div>
                                </div>
                            @endif

                            <!-- Social Media Settings -->
                            @if($activeTab === 'social')
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bx bxl-facebook-circle text-primary"></i> {{ __('Facebook URL') }}
                                        </label>
                                        <input type="url" class="form-control" wire:model="settings.facebook_url" placeholder="https://facebook.com/yourpage">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bx bxl-twitter text-info"></i> {{ __('Twitter URL') }}
                                        </label>
                                        <input type="url" class="form-control" wire:model="settings.twitter_url" placeholder="https://twitter.com/yourhandle">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bx bxl-linkedin-square text-primary"></i> {{ __('LinkedIn URL') }}
                                        </label>
                                        <input type="url" class="form-control" wire:model="settings.linkedin_url" placeholder="https://linkedin.com/company/yourcompany">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">
                                            <i class="bx bxl-youtube text-danger"></i> {{ __('YouTube URL') }}
                                        </label>
                                        <input type="url" class="form-control" wire:model="settings.youtube_url" placeholder="https://youtube.com/yourchannel">
                                    </div>
                                </div>
                            @endif

                            <!-- Save Button -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bx-save me-1"></i>{{ __('Save Settings') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
