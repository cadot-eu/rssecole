//une fois la page chargé on change le title des .bg-orthographe en demandant à /definition/mot
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.bg-orthographe').forEach(elt => {
        const mot = elt.textContent;

    });

})