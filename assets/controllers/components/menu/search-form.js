import { Controller } from "@hotwired/stimulus"
import { SearchFormUtilities } from "./_utilities.js"
import { SearchFormDomManipulation } from "./_dom-manipulation.js"
import { SearchFormAnimations } from "./_animations.js"

export default class SearchFormController extends Controller {
    static targets = ["searchForm", "actions"]
    static values = {
        formUrl: String,
        delay: { type: Number, default: 300 },
        isOpen: { type: Boolean, default: false },
        closedHeight: { type: String, default: '0px' },
        heightTransition: { type: String, default: 'height 500ms ease-in-out' },
        opacityTransition: { type: String, default: 'opacity 300ms ease-in-out' },
        combinedTransition: { type: String, default: 'height 500ms ease-in-out, opacity 300ms ease-in-out' },
        contentTransition: { type: String, default: 'opacity 800ms ease-in-out, transform 500ms ease-in-out' }
    }
    static classes = ["hidden", "visible", "actionHidden", "actionVisible"]

    connect() {
        if (!this.hasSearchFormTarget) {
            this.disconnect()
            return
        }

        this.utilities = new SearchFormUtilities(this)
        this.domManipulation = new SearchFormDomManipulation(this)
        this.animations = new SearchFormAnimations(this)

        this.hasLoadedContent = false
        this.activeTimeouts = new Set()

        this.utilities.initializePlaceholderHeight()

        this.domManipulation.setupFormRenderListener()
        this.domManipulation.setupContentMutationObserver()
    }

    disconnect() {
        this.activeTimeouts?.forEach(timeoutId => clearTimeout(timeoutId))
        this.activeTimeouts?.clear()

        if (this.observer) {
            this.observer.disconnect()
        }

        this.utilities = null
        this.domManipulation = null
        this.animations = null
    }

    search() {
        this.isOpenValue ? this.close() : this.open()
    }

    close() {
        this.animations.animateFormClosure()
        this.domManipulation.showAllActionButtons()
        this.animations.schedulePostCloseCleanup()
        this.isOpenValue = false
    }

    reset() {
        this.domManipulation.showAllActionButtons()
        this.close()
    }

    submit() {
        this.reset()
    }

    open() {
        this.domManipulation.prepareFormForOpening()
        this.domManipulation.hideAllActionButtons()
        this.animations.animateToPlaceholderHeight()
        this.scheduleContentLoading()
        this.isOpenValue = true
    }

    scheduleContentLoading() {
        if (this.hasLoadedContent && this.utilities.getContentHeight()) {
            this.scheduleTimeout(() => {
                this.replaceWithCachedContent()
            }, this.delayValue)
            return
        }

        this.scheduleTimeout(() => {
            this.domManipulation.loadFormContent()
        }, this.delayValue)
    }

    replaceWithCachedContent() {
        this.animations.animateToContentHeight()
    }

    scheduleTimeout(callback, delay) {
        const timeoutId = setTimeout(() => {
            this.activeTimeouts.delete(timeoutId)
            callback()
        }, delay)
        this.activeTimeouts.add(timeoutId)
        return timeoutId
    }
}
