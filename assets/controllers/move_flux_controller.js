// assets/controllers/move_articles_controller.js
import { Controller } from '@hotwired/stimulus';
import Swal from 'sweetalert2';

export default class extends Controller {
    static values = {
        theme: String,
        formaction: String
    }
    move() {
        const themesInput = document.querySelector('input[name="themes"]');
        if (!themesInput) return;
        const themes = themesInput.value.split(';');
        const swalButtons = themes.map(theme => {
            const [text, value] = theme.split('|');
            return { text, value };
        });
        Swal.fire({
            title: 'Choisissez le thème de destination',
            input: 'radio',
            inputOptions: swalButtons.reduce((obj, button) => {
                if (button.value !== this.themeValue) {
                    obj[button.value] = button.text;
                }
                return obj;
            }, {}),
            inputValidator: value => {
                return new Promise(resolve => {
                    if (value) {
                        resolve();
                    } else {
                        resolve('Veuillez sélectionner un thème');
                    }
                });
            }
        }).then(result => {
            if (result.value) {
                Turbo.visit(this.formactionValue + '/' + result.value, {
                    method: 'get',
                });
            }
        });
    }
}
