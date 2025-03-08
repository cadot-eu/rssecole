import './bootstrap.js';
import 'bootstrap/dist/css/bootstrap.min.css';
import './styles/app.css';
import './styles/commun.css';

// //pour les messages FLASH
// import Swal from 'sweetalert2';
// //prend dans la page les inputsavec la class flash et explode la valeur avec | pour récupérer le type et le message
// const flashes = document.querySelectorAll('.flash');
// flashes.forEach(flash => {
//     const [type, message] = flash.value.split('|');
//     Swal.fire({
//         icon: type,
//         title: message,
//         toast: true,
//         position: 'top-end',
//         showConfirmButton: false,
//         timer: 3000
//     });
// });

// //pour le rafraîchissement des fluxs
// moveArticles();
// confirmation();
// copyToClipboard();
// document.addEventListener("turbo:load", function () {
//     setInterval(() => {
//         fetch('/stream')
//             .then(response => response.text())
//             .then(html => {
//                 document.querySelector("#articles-list").innerHTML = html;
//                 moveArticles();
//                 confirmation();
//                 copyToClipboard();
//             });
//     }, 50000); // Rafraîchissement toutes les 5 secondes
// });

// //pour les confirmations
// function confirmation() {
//     document.querySelectorAll(".confirmation").forEach(function (button) {
//         button.addEventListener("click", function (event) {
//             if (!confirm("Es-tu sûr de vouloir supprimer cet élément ?")) {
//                 event.preventDefault(); // Annule l'envoi du formulaire si l'utilisateur clique sur Annuler
//             }
//         });
//     });
// };





// function moveArticles() {
//     // Sélectionnez tous les liens avec la classe .movelink
//     var moveLinks = document.querySelectorAll('.movelink');
//     // Parcourez chaque lien et ajoutez un écouteur d'événement de clic
//     moveLinks.forEach(function (link) {
//         link.addEventListener('click', function (event) {
//             // Empêchez le comportement par défaut du lien
//             event.preventDefault();

//             // Récupérez la valeur de l'input caché contenant les thèmes
//             var themesInput = document.querySelector('input[name="themes"]');
//             var themes = themesInput.value.split(';'); // Convertissez la chaîne en un tableau
//             // Explode avec | pour créer les boutons pour SweetAlert2
//             var themeOrigine = link.getAttribute('data-origine');
//             var swalButtons = themes.map(function (theme) {
//                 var [text, value] = theme.split('|');
//                 return {
//                     text: text,
//                     value: value,
//                 };

//             });

//             // Affichez le dialogue SweetAlert2 avec les options de thèmes
//             Swal.fire({
//                 title: 'Choisissez le thème de destination',
//                 input: 'radio',
//                 inputOptions: swalButtons.reduce(function (obj, button) {
//                     //on prend pas le thème d'origine
//                     if (button.value == themeOrigine) {
//                         return obj;
//                     }
//                     obj[button.value] = button.text;
//                     return obj;
//                 }, {}),
//                 inputValidator: function (value) {
//                     // Validation pour s'assurer qu'un thème est sélectionné
//                     return new Promise(function (resolve) {
//                         if (value) {
//                             resolve();
//                         } else {
//                             resolve('Veuillez sélectionner un thème');
//                         }
//                     });
//                 }
//             }).then(function (result) {
//                 if (result.value) {
//                     // Récupérez l'URL du lien cliqué et ajoutez le thème sélectionné
//                     var href = link.getAttribute('href');
//                     href += '/' + result.value;

//                     // Redirigez vers la nouvelle URL
//                     window.location.href = href;
//                 }
//             });
//         });
//     });
// };

// //création d'un copier dans le presse papier sur les class copyToClipboard
// function copyToClipboard() {
//     document.querySelectorAll('.copyToClipboard').forEach(function (button) {
//         button.addEventListener('click', function (event) {
//             event.preventDefault();
//             // Récupérez l'URL du lien cliqué
//             var href = button.getAttribute('data-clipboard-text');
//             // Copiez l'URL dans le presse-papier
//             navigator.clipboard.writeText(href);
//             // Affichez un message de confirmation avec sweetalert2
//             Swal.fire({
//                 icon: 'success',
//                 title: 'URL copiée dans le presse-papier',
//                 toast: true,
//                 position: 'top-end',
//                 showConfirmButton: false,
//                 timer: 3000
//             });
//         });
//     });
// }
