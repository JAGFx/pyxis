import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    triggerOpen(event) {
        // Dispatcher l'événement personnalisé
        const customEvent = new CustomEvent('overlay:open', {
            detail: { target: event.params.target  }
        })
        document.dispatchEvent(customEvent)
    }
}