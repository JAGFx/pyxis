import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["searchForm", "actions"]
    static values = {
        formUrl: String,
        delay: { type: Number, default: 300 },
        isOpen: { type: Boolean, default: false },
        isFinished: { type: Boolean, default: false }
    }

    connect() {
        if (!this.hasSearchFormTarget) {
            this.disconnect();
            return;
        }

        // ✅ Écouter directement sur le frame au lieu du document
        this.searchFormTarget.addEventListener('turbo:frame-render', this.onFrameRender.bind(this))

        // ✅ Alternative : utiliser MutationObserver plus fiable
        this.setupMutationObserver()
    }

    setupMutationObserver() {
        this.observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Nouveau contenu ajouté - appliquer le fade
                    const addedElements = Array.from(mutation.addedNodes)
                        .filter(node => node.nodeType === Node.ELEMENT_NODE)

                    if (addedElements.length > 0) {
                        this.onFrameRender(addedElements[0])
                    }
                }
            })
        })

        this.observer.observe(this.searchFormTarget, {
            childList: true,
            subtree: true
        })
    }

    onFrameRender(element) {
        this.isFinishedValue = true

        // Commencer invisible
        element.style.opacity = '0'
        element.style.transform = 'translateY(1.5rem)'
        element.style.transition = 'opacity 800ms ease-in-out, transform 500ms ease-in-out'

        // Déclencher l'animation
        requestAnimationFrame(() => {
            element.style.opacity = '1'
            element.style.transform = 'translateY(0)'
        })

        const actions = this.actionsTargets;
        actions.forEach((action) => {
            action.classList.remove('opacity-100')
            action.classList.add('opacity-0', 'transition-all', 'duration-800', 'ease-in-out')
            action.disabled = true
        })
    }

    search() {
        if (this.isOpenValue) {
            this.close()
            return
        }

        this.searchFormTarget.classList.remove('h-0', 'opacity-0', 'mt-4')
        this.searchFormTarget.classList.add('h-auto', 'mt-4')

        setTimeout(() => {
            this.searchFormTarget.setAttribute('src', this.formUrlValue)
        }, 500)

        this.isOpenValue = true
    }

    close() {
        this.searchFormTarget.classList.add('h-0', 'opacity-0', 'mt-4') // TODO: Use tre height to prevent layout shift
        this.searchFormTarget.classList.remove('h-auto', 'mt-4')

        setTimeout(() => {
            this.searchFormTarget.removeAttribute('src')
            this.searchFormTarget.querySelector('#search-form-content')?.remove()
            this.searchFormTarget.querySelector('#search-placeholder')?.classList.remove('hidden')
        }, 500)

        this.isOpenValue = false
        this.isFinishedValue = false
    }

    reset() {
        const actions = this.actionsTargets;
        actions.forEach((action) => {
            action.classList.add('opacity-100')
            action.classList.remove('opacity-0')
            action.disabled = false
        })
        this.close();
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect()
        }
    }
}
