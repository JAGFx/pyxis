import {Controller} from "@hotwired/stimulus";

export default class extends Controller {
    static targets = ["element"]
    static values = {
        duration: { type: Number, default: 400 },
        target: String,
    }

    connect() {
        this.element.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: false })
        this.element.addEventListener('touchend', this.handleTouchEnd.bind(this))
        this.element.addEventListener('touchcancel', this.handleTouchEnd.bind(this))
        this.element.addEventListener('mousedown', this.handleMouseDown.bind(this))
        this.element.addEventListener('mouseup', this.handleMouseUp.bind(this))
        this.element.addEventListener('mouseleave', this.handleMouseUp.bind(this))
        this.element.addEventListener('contextmenu', this.preventContextMenu.bind(this))

        this.longPressTimer = null
        this.isLongPressed = false
    }

    disconnect() {
        this.clearTimer()
    }

    handleTouchStart(event) {
        this.startLongPress(event)
    }

    handleTouchEnd(event) {
        this.endLongPress(event)
    }

    handleMouseDown(event) {
        if (event.button === 0) {
            this.startLongPress(event)
        }
    }

    handleMouseUp(event) {
        this.endLongPress(event)
    }

    startLongPress(event) {
        this.isLongPressed = false
        this.element.classList.add('long-press-active')

        this.longPressTimer = setTimeout(() => {
            this.isLongPressed = true
            this.triggerLongPress(event)
        }, this.durationValue)
    }

    endLongPress(event) {
        this.clearTimer()
        this.element.classList.remove('long-press-active')

        if (this.isLongPressed) {
            event.preventDefault()
            event.stopPropagation()
        }
    }

    triggerLongPress(event) {
        const customEvent = new CustomEvent('overlay:open', {
            detail: {
                originalEvent: event,
                target: this.targetValue,
                controller: this
            },
            bubbles: true,
            cancelable: true
        })

        document.dispatchEvent(customEvent)
        this.element.classList.add('long-press-triggered')
    }

    clearTimer() {
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer)
            this.longPressTimer = null
        }
    }

    preventContextMenu(event) {
        event.preventDefault()
        event.stopPropagation()
    }
}