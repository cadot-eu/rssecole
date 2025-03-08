import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = { priorite: String, articleid: String };

    connect() {
    }

    disconnect() {
        // Add any cleanup logic here
    }

    visit() {
        if (this.prioriteValue && this.articleidValue) {
            Turbo.visit(`/ArticlePriorite/${this.articleidValue}/${this.prioriteValue}`, { action: "replace" });
        }
    }
}

