import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = []

    connect() {
        if (typeof EventSource !== 'undefined') {
            this.startEventSource();
        } else {
            console.warn('EventSource not supported by this browser');
        }
    }

    startEventSource() {
        const eventSource = new EventSource(this.mercureUrl());

        eventSource.onmessage = this.handleEvent.bind(this);
        eventSource.onerror = this.handleError.bind(this, eventSource);
    }

    handleEvent(event) {
        const data = event.data;
        const parser = new DOMParser();
        const doc = parser.parseFromString(data, 'text/html');
        const turboStream = doc.querySelector('turbo-stream');

        if (turboStream) {
            document.body.appendChild(turboStream.cloneNode(true));
        }
    }

    handleError(eventSource, event) {
        console.error('EventSource failed:', event);
        eventSource.close();

        // Tentative de reconnexion après 5 secondes
        setTimeout(() => {
            this.startEventSource();
        }, 5000);
    }

    mercureUrl() {
        //return "{{ mercure('theme-updates')|escape('js') }}";
        return "http://localhost:3000/.well-known/mercure?topic=theme-updates&jwt=arowana";

    }
}
