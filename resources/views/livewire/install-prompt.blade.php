<div>
    <!-- Install Prompt -->
    <div id="install-prompt" class="hidden fixed bottom-4 left-4 right-4 z-40 bg-indigo-600 border border-gray-200 rounded-lg shadow-lg p-4 mx-auto max-w-sm">
        <div class="flex items-start space-x-3">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-gray-900">Install Scripture App</h3>
                <p class="text-xs text-gray-600 mt-1">Add to your home screen for quick access and offline use.</p>
            </div>
        </div>
        
        <div class="flex space-x-2 mt-3">
            <button id="install-button" class="flex-1 bg-indigo-600 text-white text-sm py-2 px-3 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 transition duration-150">
                Install
            </button>
            <button id="dismiss-install" class="flex-shrink-0 text-gray-500 text-sm py-2 px-3 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition duration-150">
                Later
            </button>
        </div>
    </div>

    <!-- Floating Install Button (when prompt is not shown) -->
    <button id="floating-install-button" class="hidden fixed bottom-6 right-6 z-40 bg-indigo-600 text-white w-14 h-14 rounded-full shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition duration-150 transform hover:scale-105">
        <svg class="w-6 h-6 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>

    <script>
    let deferredPrompt;
    let installPromptShown = false;

    console.log('InstallPrompt script loaded');

    // Listen for the beforeinstallprompt event
    window.addEventListener('beforeinstallprompt', (e) => {
        console.log('beforeinstallprompt event fired');
        // Prevent the mini-infobar from appearing on mobile
        e.preventDefault();
        // Save the event so it can be triggered later
        deferredPrompt = e;
        
        // Check if user has previously dismissed the prompt
        if (!localStorage.getItem('pwa-install-dismissed')) {
            console.log('Showing install prompt');
            showInstallPrompt();
        } else {
            console.log('Showing floating button');
            showFloatingButton();
        }
    });

    // Handle install button click
    document.getElementById('install-button').addEventListener('click', async () => {
        if (deferredPrompt) {
            // Show the install prompt
            deferredPrompt.prompt();
            
            // Wait for the user to respond to the prompt
            const result = await deferredPrompt.userChoice;
            
            if (result.outcome === 'accepted') {
                console.log('User accepted the install prompt');
                hideInstallPrompt();
                hideFloatingButton();
            } else {
                console.log('User dismissed the install prompt');
                hideInstallPrompt();
                showFloatingButton();
            }
            
            // Clear the deferredPrompt so it can be garbage collected
            deferredPrompt = null;
        }
    });

    // Handle dismiss button click
    document.getElementById('dismiss-install').addEventListener('click', () => {
        hideInstallPrompt();
        showFloatingButton();
        // Remember that user dismissed it (but allow them to trigger it again via floating button)
        localStorage.setItem('pwa-install-dismissed', 'true');
    });

    // Handle floating button click
    document.getElementById('floating-install-button').addEventListener('click', () => {
        hideFloatingButton();
        showInstallPrompt();
    });

    // Check if app is already installed
    window.addEventListener('appinstalled', () => {
        console.log('PWA was installed');
        hideInstallPrompt();
        hideFloatingButton();
        localStorage.setItem('pwa-installed', 'true');
    });

    // Hide prompts if already installed
    if (localStorage.getItem('pwa-installed') === 'true' || window.navigator.standalone === true) {
        hideInstallPrompt();
        hideFloatingButton();
    }

    function showInstallPrompt() {
        console.log('showInstallPrompt called');
        document.getElementById('install-prompt').classList.remove('hidden');
        installPromptShown = true;
    }

    function hideInstallPrompt() {
        console.log('hideInstallPrompt called');
        document.getElementById('install-prompt').classList.add('hidden');
        installPromptShown = false;
    }

    function showFloatingButton() {
        console.log('showFloatingButton called, deferredPrompt:', !!deferredPrompt);
        if (!installPromptShown && deferredPrompt && !localStorage.getItem('pwa-installed')) {
            document.getElementById('floating-install-button').classList.remove('hidden');
        }
    }

    function hideFloatingButton() {
        console.log('hideFloatingButton called');
        document.getElementById('floating-install-button').classList.add('hidden');
    }

    // For iOS Safari - show a different prompt since beforeinstallprompt doesn't work
    function isiOS() {
        return /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
    }

    function isInStandaloneMode() {
        return (window.navigator.standalone === true);
    }

    // Show iOS-specific install instructions
    if (isiOS() && !isInStandaloneMode() && !localStorage.getItem('ios-install-dismissed')) {
        setTimeout(() => {
            const iosPrompt = document.createElement('div');
            iosPrompt.id = 'ios-install-prompt';
            iosPrompt.className = 'fixed top-0 left-0 right-0 z-50 bg-blue-600 text-white p-3 text-center text-sm';
            iosPrompt.innerHTML = `
                <div class="max-w-sm mx-auto">
                    <span>📱 To install: tap Share button, then "Add to Home Screen"</span>
                    <button onclick="this.parentElement.parentElement.style.display='none'; localStorage.setItem('ios-install-dismissed', 'true');" class="ml-2 text-blue-200 hover:text-white">✕</button>
                </div>
            `;
            document.body.appendChild(iosPrompt);
        }, 2000);
    }
    </script>
</div>
