import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["searchForm", "searchButton"]
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

        this.searchFormTarget.addEventListener('turbo:frame-load', this.onFrameLoad.bind(this))
    }

    search() {
        if( this.isOpenValue ) {
            this.close();
            return;
        }

        this.searchFormTarget.classList.remove('max-h-0', 'opacity-0', 'mt-4')
        this.searchFormTarget.classList.add('max-h-46', 'opacity-100', 'mt-4')

        setTimeout(() => {
            this.searchFormTarget.setAttribute('src', this.formUrlValue)
        }, 500);

        this.isOpenValue = true;
    }

    close() {
        this.searchFormTarget.classList.add('max-h-0', 'opacity-0', 'mt-4')
        this.searchFormTarget.classList.remove('max-h-46', 'opacity-100', 'mt-4')

        setTimeout(() => {
            this.searchFormTarget.removeAttribute('src')
        }, 500);

        this.isOpenValue = false;
        this.isFinishedValue = false;
    }

    onFrameLoad() {
        this.isFinishedValue = true;
    }
}