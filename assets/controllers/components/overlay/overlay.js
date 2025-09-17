import { Controller } from "@hotwired/stimulus"

export default class OverlayController extends Controller {
    static targets = ["content"]
    static values = {
        open: { type: Boolean, default: false }
    }

    connect() {
        document.addEventListener('overlay:open', this.handleOpen.bind(this))
        document.addEventListener('overlay:close', this.handleClose.bind(this))
        this.updateVisibility()
    }

    disconnect() {
        document.removeEventListener('overlay:open', this.handleOpen.bind(this))
        document.removeEventListener('overlay:close', this.handleClose.bind(this))
    }

    open() {
        this.openValue = true
        this.updateVisibility()
        this.dispatch('opened', { detail: { overlay: this.element } })
    }

    close() {
        this.openValue = false
        this.updateVisibility()
        this.dispatch('closed', { detail: { overlay: this.element } })
    }

    handleOpen(event) {
        if (event.detail.target === this.element.id) {
            this.open()
        }
    }

    handleClose(event) {
        if (event.detail.target === this.element.id) {
            this.close()
        }
    }

    updateVisibility() {
        if (this.openValue) {
            this.element.classList.add('overlay--open')
            this.element.classList.remove('overlay--closed')
            document.body.classList.add('overlay-active')
        } else {
            this.element.classList.remove('overlay--open')
            this.element.classList.add('overlay--closed')
            document.body.classList.remove('overlay-active')
        }
    }
}