document.addEventListener('click', async (event) => {
    const bouton = event.target.closest('[data-terminer-btn]');
    if (!bouton) {
        return;
    }

    const ligne = bouton.closest('[data-chantier-row]');
    const badge = ligne.querySelector('[data-statut-badge]');

    passerEnChargement(bouton);

    const reponse = await fetch(bouton.dataset.url, {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
    });

    appliquerSucces(badge, bouton, await reponse.json());
});

function passerEnChargement(bouton) {
    bouton.disabled = true;
    bouton.dataset.libelleInitial = bouton.textContent.trim();
    bouton.innerHTML =
        '<span class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2'
        + ' border-current border-t-transparent" aria-hidden="true"></span>Envoi…';
}

function appliquerSucces(badge, bouton, donnees) {
    badge.textContent = donnees.statutLabel;
    badge.className = donnees.statutClasse;
    bouton.remove();
}
