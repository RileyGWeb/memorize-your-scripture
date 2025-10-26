// Capacitor platform detection
import { Capacitor } from '@capacitor/core';

// Add platform class to body when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (Capacitor.isNativePlatform()) {
        document.body.classList.add('capacitor-app');
        
        // Get the platform (ios or android)
        const platform = Capacitor.getPlatform();
        document.body.classList.add(`capacitor-${platform}`);
        
        console.log('Running in Capacitor on:', platform);
    } else {
        console.log('Running in web browser');
    }
});
