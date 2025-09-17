export class SearchFormAnimations {
    constructor(controller) {
        this.controller = controller
    }

    animateToPlaceholderHeight() {
        this.controller.searchFormTarget.style.transition = this.controller.combinedTransitionValue

        requestAnimationFrame(() => {
            this.controller.searchFormTarget.style.height = this.controller.utilities.getPlaceholderHeight()
        })
    }

    animateToContentHeight() {
        this.controller.searchFormTarget.style.transition = this.controller.heightTransitionValue
        this.controller.searchFormTarget.style.height = this.controller.utilities.getContentHeight()
    }

    animateToNewContent(element) {
        this.controller.searchFormTarget.style.transition = this.controller.heightTransitionValue
        this.controller.searchFormTarget.style.height = this.controller.utilities.getContentHeight()

        if (!element) {
            return
        }

        this.animateElementFadeIn(element)
    }

    animateElementFadeIn(element) {
        element.style.opacity = '0'
        element.style.transform = 'translateY(1.5rem)'
        element.style.transition = this.controller.contentTransitionValue

        requestAnimationFrame(() => {
            element.style.opacity = '1'
            element.style.transform = 'translateY(0)'
        })
    }

    animateFormClosure() {
        this.controller.searchFormTarget.style.transition = this.controller.combinedTransitionValue
        this.controller.searchFormTarget.style.height = this.controller.closedHeightValue
        this.controller.searchFormTarget.style.opacity = '0'
    }

    schedulePostCloseCleanup() {
        this.controller.scheduleTimeout(() => {
            this.controller.domManipulation.performPostCloseCleanup()
        }, 500)
    }
}
