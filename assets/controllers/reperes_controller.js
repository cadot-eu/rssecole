// assets/controllers/image-lightbox_controller.js
import { Controller } from '@hotwired/stimulus';
import { Popover } from 'bootstrap';
import GLightbox from 'glightbox';
import 'glightbox/dist/css/glightbox.min.css';
import Swal from 'sweetalert2';
export default class extends Controller {
    connect() {
        document.querySelectorAll('.buttonp').forEach(button => {
            button.addEventListener('click', (event) => {
                if (event.ctrlKey) {
                    let html = event.target.parentNode.innerHTML;
                    html = html.replace(button.outerHTML, '');
                    addPubAFter(html);
                }
                else {
                    const number = button.getAttribute('data-number');
                    reperes(number);
                }
            });
        });
        //on lance reperes sur le texte sélectionné
        document.addEventListener('mouseup', () => {
            reperes();
        });

    };



}

async function addPubAFter(html) {
    try {
        const idArticle = document.querySelector('#idArticle').value;
        const response = await fetch(`/FluxBas/${idArticle}`, {
            method: 'POST',
            headers: {
                'Accept': 'text/vnd.turbo-stream.html',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ selection: html })
        });
        const htmlreponse = await response.text();
        Turbo.renderStreamMessage(htmlreponse);
    } catch (error) {
        console.error('Erreur:', error);
        Swal.fire('Erreur', 'Une erreur est survenue', 'error');
    }
}

function reperes(number = null) {
    let selectedText = null;
    let selectedHtml = null;
    let savedRange = null;
    if (number) {
        selectedText = "P:" + number;
        selectedHtml = selectedText;
    }
    else {
        const selection = window.getSelection();
        if (!selection.rangeCount) return;
        selectedText = selection.toString().trim();
        // Save selection

        if (window.getSelection) {
            const sel = window.getSelection();
            if (sel.getRangeAt && sel.rangeCount) {
                savedRange = sel.getRangeAt(0);
            }
        }

        // Get HTML content of selection
        const container = document.createElement('div');
        const clonedRange = savedRange.cloneContents();
        container.appendChild(clonedRange);
        selectedHtml = container.innerHTML;
    }

    if (selectedText) {


        // Get article ID
        const id = document.querySelector('#article').getAttribute('attr-id');

        // Show action dialog
        Swal.fire({
            title: `Action pour : "${selectedText.substring(0, 30)}${selectedText.length > 30 ? '...' : ''}"`,
            icon: 'question',
            showCancelButton: true,
            cancelButtonText: 'Annuler',
            showCloseButton: true,
            html: ['orthographe', 'important', 'explique', 'question'].map((text) => {
                return `<button id="${text}" class="autre-btn text-black swal2-confirm swal2-styled bg-${text}" style="display: inline-block; margin-left: 10px;">${text}</button>`;
            }).join(' ')
        });

        // Function to restore selection
        const restoreSelection = () => {
            if (savedRange) {
                if (window.getSelection) {
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(savedRange);
                }
            }
        };

        // Add event listener for Important button
        document.querySelectorAll('.autre-btn').forEach(button => {
            const idbutton = button.getAttribute('id');
            button.addEventListener('click', async () => {
                if (idbutton === 'question') {
                    // Afficher le champ de question
                    const questionResult = await Swal.fire({
                        title: 'Poser une question',
                        input: 'text',
                        inputPlaceholder: 'Votre question',
                        showCancelButton: true,
                        confirmButtonText: 'Confirmer',
                        cancelButtonText: 'Annuler',
                        focusConfirm: false,
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                    });

                    if (questionResult.isConfirmed && questionResult.value) {
                        try {
                            const response = await fetch(`/Articlequestion/${id}`, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'text/vnd.turbo-stream.html',
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    question: questionResult.value,
                                    selection: selectedHtml
                                })
                            });
                            const html = await response.text();
                            Turbo.renderStreamMessage(html);
                        } catch (error) {
                            console.error('Erreur:', error);
                            Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                        }
                    } else {
                        restoreSelection();
                    }
                } else {
                    Swal.close();
                    try {
                        const response = await fetch(`/ArticleMarque/${id}/${idbutton}`, {
                            method: 'POST',
                            headers: {
                                'Accept': 'text/vnd.turbo-stream.html',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ selection: selectedHtml })
                        });
                        const html = await response.text();
                        Turbo.renderStreamMessage(html);
                    } catch (error) {
                        console.error('Erreur:', error);
                        Swal.fire('Erreur', 'Une erreur est survenue', 'error');
                    }
                }
            });
        });


    }

}