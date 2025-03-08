import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {

    connect() {
        // this.interval = setInterval(() => {
        //     Turbo.visit(this.element.getAttribute('data-url'));
        // }, 5000);

    }




    disconnect() {
        // Si le contrôleur est déconnecté, on arrête l'intervalle
        clearInterval(this.interval);
    }
}
