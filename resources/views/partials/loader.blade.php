<style>
    /* Global Loader */
    #global-loader {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(255, 255, 255, 0.95) !important;
        z-index: 2147483647 !important;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        flex-direction: column;
    }
    
    .loader-content {
        text-align: center;
        z-index: 2147483648 !important;
    }
    .spinner {
        width: 60px;
        height: 60px;
        border: 5px solid rgba(26, 115, 232, 0.1);
        border-top-color: #1a73e8;
        border-radius: 50%;
        animation: loader-spin 0.8s linear infinite;
        margin: 0 auto 20px;
    }
    .loading-text {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #1a202c;
        font-weight: 600;
        font-size: 18px;
        letter-spacing: -0.025em;
    }
    @keyframes loader-spin {
        to { transform: rotate(360deg); }
    }
</style>

<div id="global-loader" 
     x-data="{ 
        visible: false, 
        activeRequests: 0, 
        showTimestamp: 0,
        show() {
            if (!this.visible) {
                this.visible = true;
                this.showTimestamp = Date.now();
            }
            this.activeRequests++;
        },
        hide() {
            this.activeRequests--;
            if (this.activeRequests <= 0) {
                this.activeRequests = 0;
                const timeShown = Date.now() - this.showTimestamp;
                const remaining = Math.max(0, 400 - timeShown);

                setTimeout(() => {
                    if (this.activeRequests === 0) {
                        this.visible = false;
                    }
                }, remaining);
            }
        },
        forceHide() {
            this.activeRequests = 1;
            this.hide();
        }
     }"
     x-show="visible"
     x-on:loader-show.window="show()"
     x-on:loader-hide.window="hide()"
     x-on:loader-force-hide.window="forceHide()"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     wire:persist="global-loader"
     style="display: none;">
    <div class="loader-content">
        <div class="spinner"></div>
        <div class="loading-text">{{ __('Processing...') }}</div>
    </div>
</div>

<script data-navigate-once>
    (function() {
        const triggerShow = () => window.dispatchEvent(new CustomEvent('loader-show'));
        const triggerHide = () => window.dispatchEvent(new CustomEvent('loader-hide'));
        const triggerForceHide = () => window.dispatchEvent(new CustomEvent('loader-force-hide'));

        // 1. Livewire Hooks (Database/Action Coverage)
        document.addEventListener('livewire:init', () => {
            Livewire.hook('request', ({ respond, fail }) => {
                triggerShow();
                respond(() => triggerHide());
                fail(() => triggerHide());
            });
        });

        // 2. Navigation Hooks (wire:navigate)
        document.addEventListener('livewire:navigating', () => {
            triggerShow();
        });

        document.addEventListener('livewire:navigated', () => {
            triggerForceHide();
        });

        // 3. Standard Interception (Non-Livewire links/forms)
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            if (link && 
                link.href && 
                !link.hasAttribute('wire:navigate') && 
                !link.hasAttribute('wire:click') &&
                !link.hasAttribute('download') &&
                link.target !== '_blank' &&
                !link.href.includes('#') &&
                !link.href.startsWith('javascript:') &&
                link.origin === window.location.origin
            ) {
                if (e.button === 0 && !e.ctrlKey && !e.shiftKey && !e.altKey && !e.metaKey) {
                    triggerShow();
                }
            }
        });

        document.addEventListener('submit', (e) => {
            if (!e.target.hasAttribute('wire:submit')) {
                triggerShow();
            }
        });

        // Handle page restores
        window.addEventListener('pageshow', () => {
            triggerForceHide();
        });
    })();
</script>

<style>
    /* Styling for the default Livewire Navigation Progress Bar (NProgress) */
    /* This ensures that if NProgress is used, it also looks like our loader */
    
    #nprogress .bar {
        display: none !important;
    }

    #nprogress .spinner {
        display: flex !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: rgba(255, 255, 255, 0.95) !important;
        z-index: 2147483647 !important;
        align-items: center !important;
        justify-content: center !important;
        backdrop-filter: blur(10px) !important;
        -webkit-backdrop-filter: blur(10px) !important;
    }

    #nprogress .spinner-icon {
        width: 60px !important;
        height: 60px !important;
        border: 5px solid rgba(26, 115, 232, 0.1) !important;
        border-top-color: #1a73e8 !important;
        border-radius: 50% !important;
        animation: nprogress-spinner 0.8s linear infinite !important;
    }

    #nprogress .spinner::after {
        content: '{{ __("Processing...") }}';
        position: absolute;
        top: calc(50% + 50px);
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        color: #1a202c;
        font-weight: 600;
        font-size: 18px;
    }

    @keyframes nprogress-spinner {
        to { transform: rotate(360deg); }
    }

    /* Keep our manual loader visible if nprogress is busy */
    .nprogress-busy #global-loader {
        display: flex !important;
        opacity: 1 !important;
    }
</style>
