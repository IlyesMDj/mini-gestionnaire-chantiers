const DELAI_MAX_MS = 8000;

const MESSAGES = {
    404: "Ce chantier n'existe plus.",
    reseau: 'Connexion impossible. Vérifiez votre réseau.',
    delaiDepasse: 'Le serveur met trop de temps à répondre.',
    defaut: 'Une erreur serveur est survenue.',
};

document.addEventListener('click', async (event) => {
    const bouton = event.target.closest('[data-terminer-btn]');
    if (!bouton) {
        return;
    }

    const ligne = bouton.closest('[data-chantier-row]');
    const badge = ligne.querySelector('[data-statut-badge]');
    const zoneErreur = ligne.querySelector('[data-erreur]');

    masquerErreur(zoneErreur);
    passerEnChargement(bouton);

    // Sans AbortController, fetch attendrait indéfiniment un serveur qui ne répond jamais.
    const controleur = new AbortController();
    const minuterie = setTimeout(() => controleur.abort(), DELAI_MAX_MS);

    try {
        const donnees = await envoyerTerminaison(bouton.dataset, controleur.signal);
        appliquerSucces(badge, bouton, donnees);
    } catch (erreur) {
        afficherErreur(zoneErreur, messagePour(erreur));
        reactiverBouton(bouton);
    } finally {
        clearTimeout(minuterie);
    }
});

async function envoyerTerminaison(donneesBouton, signal) {
    const reponse = await fetch(donneesBouton.url, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-Token': donneesBouton.csrf,
        },
        signal,
    });

    // fetch ne rejette pas sur un code 4xx ou 5xx : il faut tester reponse.ok soi-même.
    if (!reponse.ok) {
        const erreur = await reponse.json().catch(() => ({}));

        throw new Error(erreur.message ?? MESSAGES[reponse.status] ?? MESSAGES.defaut);
    }

    return reponse.json();
}

function messagePour(erreur) {
    if (erreur.name === 'AbortError') {
        return MESSAGES.delaiDepasse;
    }

    if (erreur instanceof TypeError) {
        return MESSAGES.reseau;
    }

    return erreur.message;
}

function passerEnChargement(bouton) {
    bouton.disabled = true;
    bouton.dataset.libelleInitial = bouton.textContent.trim();
    bouton.innerHTML =
        '<span class="mr-1 inline-block h-3 w-3 animate-spin rounded-full border-2'
        + ' border-current border-t-transparent" aria-hidden="true"></span>Envoi…';
}

function reactiverBouton(bouton) {
    bouton.disabled = false;
    bouton.textContent = bouton.dataset.libelleInitial;
}

function appliquerSucces(badge, bouton, donnees) {
    badge.textContent = donnees.statutLabel;
    badge.className = donnees.statutClasse;
    bouton.remove();
}

function afficherErreur(zone, message) {
    zone.textContent = message;
    zone.classList.remove('hidden');
}

function masquerErreur(zone) {
    zone.textContent = '';
    zone.classList.add('hidden');
}
