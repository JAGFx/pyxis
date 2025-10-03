export class SearchFormDomManipulation {
    constructor(controller) {
        this.controller = controller
    }

    prepareFormForOpening() {
        this.controller.searchFormTarget.classList.remove(...this.controller.hiddenClasses)
        this.controller.searchFormTarget.classList.add(...this.controller.visibleClasses)

        this.controller.searchFormTarget.style.height = this.controller.closedHeightValue
        this.controller.searchFormTarget.style.overflow = 'hidden'
        this.controller.searchFormTarget.style.opacity = '1'
    }

    performPostCloseCleanup() {
        this.controller.searchFormTarget.classList.add(...this.controller.hiddenClasses)
        this.controller.searchFormTarget.classList.remove(...this.controller.visibleClasses)

        this.controller.searchFormTarget.style.height = ''
        this.controller.searchFormTarget.style.overflow = ''
        this.controller.searchFormTarget.style.transition = ''
    }

    hideAllActionButtons() {
        this.controller.actionsTargets.forEach((action) => {
            action.classList.remove(...this.controller.actionVisibleTransitionValue)
            action.classList.add(...this.controller.actionHiddenTransitionValue)
            action.disabled = true
        })
    }

    showAllActionButtons() {
        this.controller.actionsTargets.forEach((action) => {
            action.classList.remove(...this.controller.actionHiddenTransitionValue)
            action.classList.add(...this.controller.actionVisibleTransitionValue)
            action.disabled = false
        })
    }

    loadFormContent() {
        this.controller.searchFormTarget.style.transition = this.controller.opacityTransitionValue
        this.controller.searchFormTarget.setAttribute('src', this.controller.formUrlValue)
    }

    setupFormRenderListener() {
        this.controller.searchFormTarget.addEventListener('turbo:frame-render', this.handleFormContentLoaded.bind(this))
    }

    setupContentMutationObserver() {
        this.controller.observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (!this.controller.utilities.hasNewElementNodes(mutation)) {
                    return
                }

                const newElements = this.controller.utilities.getNewElementNodes(mutation)

                if (newElements.length === 0) {
                    return
                }

                this.handleFormContentLoaded(newElements[0])
            })
        })

        this.controller.observer.observe(this.controller.searchFormTarget, {
            childList: true,
            subtree: true
        })
    }

    handleFormContentLoaded(element) {
        this.controller.utilities.setContentHeight()
        this.controller.hasLoadedContent = true

        this.controller.animations.animateToNewContent(element)
        this.hideAllActionButtons()
    }
}
