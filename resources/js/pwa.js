import { registerSW } from 'virtual:pwa-register'

// Expose a simple lifecycle so we can show a refresh prompt if desired
const updateSW = registerSW({
  immediate: true,
  onNeedRefresh() {
    // Dispatch a browser event so Blade/Livewire/Alpine can hook into it
    window.dispatchEvent(new CustomEvent('sw:need-refresh'))
  },
  onOfflineReady() {
    window.dispatchEvent(new CustomEvent('sw:offline-ready'))
  },
})

// Optional: re-export updater for manual control if needed elsewhere
export { updateSW }
