import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { url: String };

    connect() {
        this.element.addEventListener("click", this.visit.bind(this));
    }

    disconnect() {
        this.element.removeEventListener("click", this.visit.bind(this));
    }

    visit() {
        if (this.urlValue) {
            Turbo.visit(this.urlValue, { action: "replace" });
        }
    }
}
