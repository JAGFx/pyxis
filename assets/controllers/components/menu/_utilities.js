export class SearchFormUtilities {
    constructor(controller) {
        this.controller = controller
        this.placeholderHeight = null
        this.contentHeight = null
    }

    initializePlaceholderHeight() {
        this.ensureFormIsClosed()
        this.placeholderHeight = this.measureContentHeight()
        return this.placeholderHeight
    }

    getPlaceholderHeight() {
        return this.placeholderHeight
    }

    setContentHeight() {
        this.contentHeight = this.measureContentHeight()
        return this.contentHeight
    }

    getContentHeight() {
        return this.contentHeight
    }

    ensureFormIsClosed() {
        this.controller.searchFormTarget.style.height = this.controller.closedHeightValue
    }

    measureContentHeight() {
        const originalHeight = this.controller.searchFormTarget.style.height
        const originalOverflow = this.controller.searchFormTarget.style.overflow

        this.controller.searchFormTarget.style.overflow = 'visible'
        this.controller.searchFormTarget.style.height = 'auto'

        const measuredHeight = this.controller.searchFormTarget.scrollHeight + 'px'

        this.controller.searchFormTarget.style.height = originalHeight
        this.controller.searchFormTarget.style.overflow = originalOverflow

        return measuredHeight
    }

    hasNewElementNodes(mutation) {
        return mutation.type === 'childList' && mutation.addedNodes.length > 0
    }

    getNewElementNodes(mutation) {
        return Array.from(mutation.addedNodes)
            .filter(node => node.nodeType === Node.ELEMENT_NODE)
    }
}
