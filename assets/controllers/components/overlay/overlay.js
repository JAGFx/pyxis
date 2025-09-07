import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static values = {
        open: {
            type: Boolean,
            defaultValue: false
        }
    }
    static targets = ['content']

    connect() {
        // Écouter les événements personnalisés pour ouvrir/fermer
        document.addEventListener('overlay:open', this.handleOpen.bind(this))
        document.addEventListener('overlay:close', this.handleClose.bind(this))

        // Initialiser l'état
        this.updateVisibility()
    }

    disconnect() {
        document.removeEventListener('overlay:open', this.handleOpen)
        document.removeEventListener('overlay:close', this.handleClose.bind(this))
    }

    // Action pour ouvrir l'overlay
    open() {
        this.openValue = true
        this.updateVisibility()
        this.dispatch('opened', { detail: { overlay: this.element } })
    }

    // Action pour fermer l'overlay
    close() {
        this.openValue = false
        this.updateVisibility()
        this.dispatch('closed', { detail: { overlay: this.element } })
    }

    // Gérer les événements externes
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

    // Mettre à jour la visibilité
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