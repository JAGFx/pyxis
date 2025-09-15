import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["searchForm", "actions"]
    static values = {
        formUrl: String,
        delay: { type: Number, default: 300 },
        isOpen: { type: Boolean, default: false },
        isFinished: { type: Boolean, default: false }
    }

    // Constants
    static CLOSED_HEIGHT = '0px'
    static TRANSITIONS = {
        HEIGHT: 'height 500ms ease-in-out',
        OPACITY: 'opacity 300ms ease-in-out',
        COMBINED: 'height 500ms ease-in-out, opacity 300ms ease-in-out',
        CONTENT: 'opacity 800ms ease-in-out, transform 500ms ease-in-out'
    }
    static CLASSES = {
        HIDDEN: ['h-0', 'opacity-0'],
        VISIBLE: ['mt-4'],
        ACTION_HIDDEN: ['opacity-0', 'transition-all', 'duration-800', 'ease-in-out'],
        ACTION_VISIBLE: ['opacity-100']
    }

    connect() {
        if (!this.hasSearchFormTarget) {
            this.disconnect()
            return
        }

        this.initializeState()
        this.setupEventListeners()
    }

    disconnect() {
        this.cleanupObserver()
    }

    search() {
        this.isOpenValue ? this.close() : this.open()
    }

    close() {
        this.animateClose()
        this.showActionButtons()
        this.scheduleCleanup()
        this.updateState(false, false)
    }

    reset() {
        this.showActionButtons()
        this.close()
    }

    // Private methods - State Management
    initializeState() {
        this.contentHeight = null
        this.hasLoadedContent = false
    }

    updateState(isOpen, isFinished) {
        this.isOpenValue = isOpen
        this.isFinishedValue = isFinished
    }

    // Private methods - Event Listeners
    setupEventListeners() {
        this.searchFormTarget.addEventListener('turbo:frame-render', this.onFrameRender.bind(this))
        this.setupMutationObserver()
    }

    setupMutationObserver() {
        this.observer = new MutationObserver(this.handleMutations.bind(this))
        this.observer.observe(this.searchFormTarget, {
            childList: true,
            subtree: true
        })
    }

    handleMutations(mutations) {
        mutations.forEach((mutation) => {
            if (this.hasNewElementNodes(mutation)) {
                const newElements = this.getNewElementNodes(mutation)
                if (newElements.length > 0) {
                    this.onFrameRender(newElements[0])
                }
            }
        })
    }

    hasNewElementNodes(mutation) {
        return mutation.type === 'childList' && mutation.addedNodes.length > 0
    }

    getNewElementNodes(mutation) {
        return Array.from(mutation.addedNodes)
            .filter(node => node.nodeType === Node.ELEMENT_NODE)
    }

    cleanupObserver() {
        if (this.observer) {
            this.observer.disconnect()
        }
    }

    // Private methods - Content Management
    onFrameRender(element) {
        this.updateContentState()
        this.animateContentTransition(element)
        this.hideActionButtons()
    }

    updateContentState() {
        this.isFinishedValue = true
        this.hasLoadedContent = true
        this.contentHeight = this.calculateContentHeight()
    }

    calculateContentHeight() {
        const { height, overflow } = this.preserveStyles(['height', 'overflow'])

        this.applyTemporaryStyles({
            overflow: 'visible',
            height: 'auto'
        })

        const calculatedHeight = this.searchFormTarget.scrollHeight + 'px'

        this.restoreStyles({ height, overflow })

        return calculatedHeight
    }

    preserveStyles(properties) {
        return properties.reduce((styles, prop) => {
            styles[prop] = this.searchFormTarget.style[prop]
            return styles
        }, {})
    }

    applyTemporaryStyles(styles) {
        Object.entries(styles).forEach(([prop, value]) => {
            this.searchFormTarget.style[prop] = value
        })
    }

    restoreStyles(styles) {
        this.applyTemporaryStyles(styles)
    }

    // Private methods - Animation
    open() {
        this.prepareForOpening()
        this.hideActionButtons()

        if (this.shouldUseCache()) {
            this.animateToFinalHeight()
        } else {
            this.loadContent()
        }

        this.updateState(true, false)
    }

    prepareForOpening() {
        this.removeClass(...this.constructor.CLASSES.HIDDEN)
        this.addClass(...this.constructor.CLASSES.VISIBLE)
        this.applyInitialOpenStyles()
    }

    applyInitialOpenStyles() {
        this.applyTemporaryStyles({
            height: this.constructor.CLOSED_HEIGHT,
            overflow: 'hidden',
            opacity: '1'
        })
    }

    shouldUseCache() {
        return this.hasLoadedContent && this.contentHeight
    }

    animateToFinalHeight() {
        this.searchFormTarget.style.transition = this.constructor.TRANSITIONS.COMBINED

        requestAnimationFrame(() => {
            this.searchFormTarget.style.height = this.contentHeight
        })
    }

    loadContent() {
        this.searchFormTarget.style.transition = this.constructor.TRANSITIONS.OPACITY
        this.searchFormTarget.setAttribute('src', this.formUrlValue)
    }

    animateContentTransition(element) {
        this.updateHeightToContent()
        this.animateElementFadeIn(element)
    }

    updateHeightToContent() {
        this.searchFormTarget.style.transition = this.constructor.TRANSITIONS.HEIGHT
        this.searchFormTarget.style.height = this.contentHeight
    }

    animateElementFadeIn(element) {
        this.prepareElementForAnimation(element)

        requestAnimationFrame(() => {
            this.triggerElementAnimation(element)
        })
    }

    prepareElementForAnimation(element) {
        Object.assign(element.style, {
            opacity: '0',
            transform: 'translateY(1.5rem)',
            transition: this.constructor.TRANSITIONS.CONTENT
        })
    }

    triggerElementAnimation(element) {
        Object.assign(element.style, {
            opacity: '1',
            transform: 'translateY(0)'
        })
    }

    animateClose() {
        this.searchFormTarget.style.transition = this.constructor.TRANSITIONS.COMBINED
        this.searchFormTarget.style.height = this.constructor.CLOSED_HEIGHT
        this.searchFormTarget.style.opacity = '0'
    }

    scheduleCleanup() {
        setTimeout(() => {
            this.performCleanup()
        }, 500)
    }

    performCleanup() {
        this.addClass(...this.constructor.CLASSES.HIDDEN)
        this.removeClass(...this.constructor.CLASSES.VISIBLE)
        this.clearInlineStyles()
    }

    clearInlineStyles() {
        const stylesToClear = ['height', 'overflow', 'transition']
        stylesToClear.forEach(style => {
            this.searchFormTarget.style[style] = ''
        })
    }

    // Private methods - Action Buttons Management
    hideActionButtons() {
        this.toggleActionButtons(false)
    }

    showActionButtons() {
        this.toggleActionButtons(true)
    }

    toggleActionButtons(show) {
        const actions = this.actionsTargets

        actions.forEach((action) => {
            if (show) {
                this.showSingleActionButton(action)
            } else {
                this.hideSingleActionButton(action)
            }
        })
    }

    hideSingleActionButton(action) {
        action.classList.remove(...this.constructor.CLASSES.ACTION_VISIBLE)
        action.classList.add(...this.constructor.CLASSES.ACTION_HIDDEN)
        action.disabled = true
    }

    showSingleActionButton(action) {
        action.classList.remove(...this.constructor.CLASSES.ACTION_HIDDEN)
        action.classList.add(...this.constructor.CLASSES.ACTION_VISIBLE)
        action.disabled = false
    }

    // Private methods - Utility
    addClass(...classes) {
        this.searchFormTarget.classList.add(...classes)
    }

    removeClass(...classes) {
        this.searchFormTarget.classList.remove(...classes)
    }
}
