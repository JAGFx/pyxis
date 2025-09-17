import { Controller } from "@hotwired/stimulus"

export default class OverlayTriggerClickController extends Controller {
    triggerOpen(event) {
        const customEvent = new CustomEvent('overlay:open', {
            detail: { target: event.params.target }
        })
        document.dispatchEvent(customEvent)
    }
}