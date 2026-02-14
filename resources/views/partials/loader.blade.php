<style>
    /* Global Loader */
    #global-loader {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        backdrop-filter: blur(2px);
    }
    .spinner {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<div id="global-loader">
    <div class="spinner"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Initialize loader if not already initialized
        if (!window.loaderInitialized) {
            if (typeof Livewire !== 'undefined') {
                initLoader();
            } else {
                 // Try to init immediately for non-livewire pages too or wait for livewire
                initLoader(); 
                document.addEventListener('livewire:initialized', () => {
                   initLoader();
                });
            }
            window.loaderInitialized = true;
        }
    });

    function initLoader() {
        let loader = document.getElementById('global-loader');
        if (!loader) return;

        // Prevent multiple initializations of logic if initLoader is called multiple times
        if (loader.dataset.initialized) return;
        loader.dataset.initialized = "true";

        let activeRequests = 0;
        let showTime = 0;
        const minVisibleTime = 1000; // Minimum 1 second visibility

        const showLoader = () => {
            if (activeRequests === 0) {
                showTime = Date.now();
                loader.style.display = 'flex';
            }
            activeRequests++;
        };

        const hideLoader = () => {
            activeRequests--;
            if (activeRequests < 0) activeRequests = 0; 

            if (activeRequests === 0) {
                let elapsed = Date.now() - showTime;
                let remaining = minVisibleTime - elapsed;

                if (remaining > 0) {
                    setTimeout(() => {
                        if (activeRequests === 0) {
                            loader.style.display = 'none';
                        }
                    }, remaining);
                } else {
                    loader.style.display = 'none';
                }
            }
        };

        // Livewire v3 Hooks
        if (typeof Livewire !== 'undefined' && typeof Livewire.hook === 'function') {
            Livewire.hook('commit', ({ component, commit, succeed, fail, respond }) => {
                // Show loader for any explicit METHOD call (clicks, submits)
                if (commit.calls && commit.calls.length > 0) {
                    showLoader();

                    succeed(({ snapshot, effect }) => {
                        hideLoader();
                    });

                    fail(() => {
                        hideLoader();
                    });
                }
            });
        }

        // Livewire v2 Hooks (Fallback)
        try {
            if (window.livewire) {
                window.livewire.hook('message.sent', (message, component) => {
                    let hasMethod = false;
                    if (message.updateQueue) {
                        message.updateQueue.forEach(update => {
                            if (update.method) hasMethod = true; 
                        });
                    }
                    if (hasMethod) showLoader();
                });

                window.livewire.hook('message.processed', (message, component) => {
                    if (loader.style.display === 'flex') hideLoader(); 
                });
                window.livewire.hook('message.failed', (message, component) => {
                    if (loader.style.display === 'flex') hideLoader();
                });
            }
        } catch (e) {}

        // Handle Navigation (wire:navigate)
        document.addEventListener('livewire:navigating', () => {
            showLoader();
        });

        document.addEventListener('livewire:navigated', () => {
            hideLoader();
        });
        
        // Listen for standard form POST submits only (Non-Livewire)
        document.addEventListener('submit', function(e) {
            // Only show loader if it's NOT a Livewire form
            if (loader && e.target.method && e.target.method.toUpperCase() !== 'GET' && !e.target.hasAttribute('wire:submit')) {
                showLoader();
            }
        });
    }
</script>
