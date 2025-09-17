import { Controller } from "@hotwired/stimulus"

export default class OverlayTriggerLongPressController extends Controller {
    static values = {
        duration: { type: Number, default: 400 },
        target: String,
        preventDefault: { type: Boolean, default: true },
        stopPropagation: { type: Boolean, default: true },
        vibrate: { type: Boolean, default: false },
        vibrateDuration: { type: Number, default: 50 }
    }
    static classes = ["active", "triggered"]

    connect() {
        if (!this.hasTargetValue || !this.isValidTarget()) {
            console.warn('OverlayTriggerLongPress: target value is required and must be a valid selector')
            return
        }

        this.longPressTimer = null
        this.isLongPressed = false
        this.activeTimeouts = new Set()

        this.setupEventListeners()
    }

    disconnect() {
        this.cleanup()
    }

    setupEventListeners() {
        const events = [
            ['touchstart', this.handleTouchStart.bind(this), { passive: false }],
            ['touchend', this.handleTouchEnd.bind(this)],
            ['touchcancel', this.handleTouchEnd.bind(this)],
            ['mousedown', this.handleMouseDown.bind(this)],
            ['mouseup', this.handleMouseUp.bind(this)],
            ['mouseleave', this.handleMouseUp.bind(this)],
            ['contextmenu', this.preventContextMenu.bind(this)]
        ]

        events.forEach(([event, handler, options]) => {
            this.element.addEventListener(event, handler, options)
        })
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
        if (!this.isValidTarget()) {
            return
        }

        this.isLongPressed = false
        this.addActiveClasses()

        this.longPressTimer = this.scheduleTimeout(() => {
            this.isLongPressed = true
            this.triggerLongPress(event)
        }, this.durationValue)
    }

    endLongPress(event) {
        this.clearTimer()
        this.removeActiveClasses()

        if (this.isLongPressed && event.type !== 'touchcancel') {
            if (this.preventDefaultValue) {
                event.preventDefault()
            }
            if (this.stopPropagationValue) {
                event.stopPropagation()
            }
        }
    }

    triggerLongPress(event) {
        this.addTriggeredClasses()
        this.triggerVibration()

        this.dispatchOverlayEvent('overlay:open', {
            originalEvent: event,
            triggerElement: this.element,
            longPress: true
        })

        this.scheduleTimeout(() => {
            this.removeTriggeredClasses()
        }, 200)
    }

    addActiveClasses() {
        if (this.hasActiveClass) {
            this.element.classList.add(...this.activeClasses)
        }
    }

    removeActiveClasses() {
        if (this.hasActiveClass) {
            this.element.classList.remove(...this.activeClasses)
        }
    }

    addTriggeredClasses() {
        if (this.hasTriggeredClass) {
            this.element.classList.add(...this.triggeredClasses)
        }
    }

    removeTriggeredClasses() {
        if (this.hasTriggeredClass) {
            this.element.classList.remove(...this.triggeredClasses)
        }
    }

    triggerVibration() {
        if (this.vibrateValue && 'vibrate' in navigator) {
            navigator.vibrate(this.vibrateDurationValue)
        }
    }

    clearTimer() {
        if (this.longPressTimer) {
            clearTimeout(this.longPressTimer)
            this.longPressTimer = null
        }
    }

    scheduleTimeout(callback, delay) {
        const timeoutId = setTimeout(() => {
            this.activeTimeouts.delete(timeoutId)
            callback()
        }, delay)
        this.activeTimeouts.add(timeoutId)
        return timeoutId
    }

    cleanup() {
        this.clearTimer()
        this.activeTimeouts?.forEach(timeoutId => clearTimeout(timeoutId))
        this.activeTimeouts?.clear()
        this.removeActiveClasses()
        this.removeTriggeredClasses()
    }

    preventContextMenu(event) {
        if (this.preventDefaultValue) {
            event.preventDefault()
            event.stopPropagation()
        }
    }

    dispatchOverlayEvent(eventName, additionalDetail = {}) {
        const customEvent = new CustomEvent(eventName, {
            detail: {
                target: this.targetValue,
                timestamp: Date.now(),
                ...additionalDetail
            },
            bubbles: true,
            cancelable: true
        })
        document.dispatchEvent(customEvent)
    }

    isValidTarget() {
        return this.targetValue &&
               typeof this.targetValue === 'string' &&
               this.targetValue.trim() !== '' &&
               document.getElementById(this.targetValue)
    }
}