import './bootstrap';
import './pwa';
import { App } from '@capacitor/app';

let capacitorDetected = false;

function applyCapacitorClass() {
    document.documentElement.classList.add('capacitor-app');
    document.body?.classList.add('capacitor-app');
}

async function detectCapacitor() {
    if (capacitorDetected) {
        applyCapacitorClass();
        return;
    }

    try {
        if (window.Capacitor?.isNativePlatform?.()) {
            capacitorDetected = true;
            applyCapacitorClass();
            return;
        }

        const info = await App.getInfo();
        if (info) {
            capacitorDetected = true;
            applyCapacitorClass();
        }
    } catch (error) {
        // Not running in Capacitor, continue normally
    }
}

document.addEventListener('DOMContentLoaded', detectCapacitor);
document.addEventListener('livewire:navigated', detectCapacitor);
window.addEventListener('pageshow', detectCapacitor);