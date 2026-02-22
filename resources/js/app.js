import './bootstrap';
import './pwa';import { App } from '@capacitor/app';

// Detect if running in Capacitor app and add class for styling
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const info = await App.getInfo();
        if (info) {
            document.documentElement.classList.add('capacitor-app');
        }
    } catch (error) {
        // Not running in Capacitor, continue normally
    }
});