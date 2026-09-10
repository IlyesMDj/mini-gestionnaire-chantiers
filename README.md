# Mini-Gestionnaire de Chantiers

Application web qui liste les chantiers d'une entreprise avec leurs équipements associés, et
permet de marquer un chantier comme terminé en AJAX, sans rechargement de page. Ce dépôt est
le rendu d'un test technique J4R.

## Stack

Symfony 6.4 LTS · PHP 8.1+ · MySQL 8 · Doctrine ORM 3 · Twig · Tailwind CSS (Play CDN) ·
JavaScript vanilla, sans étape de build.

## Prérequis

- PHP >= 8.1 en ligne de commande, avec les extensions `pdo_mysql`, `intl` et `mbstring`
- Composer 2
- Docker Desktop (option A ci-dessous) **ou** un MySQL 8 déjà installé (option B)
- Symfony CLI : facultatif, une alternative est fournie à chaque étape qui l'utilise

Le projet a été développé et vérifié avec PHP 8.3, Composer 2.10 et MySQL 8.0 en conteneur.

## Installation

### 1. Cloner le dépôt et installer les dépendances

```bash
git clone https://github.com/IlyesMDj/mini-gestionnaire-chantiers.git
```

```bash
cd mini-gestionnaire-chantiers
```

```bash
composer install
```

Toutes les commandes suivantes se lancent depuis la racine du projet.

### 2. Lancer la base de données

**Option A — MySQL 8 en conteneur (recommandée).**

```bash
docker compose up -d --wait
```

L'option `--wait` est importante : le service déclare un *healthcheck*, et la commande ne rend
la main que lorsque MySQL accepte réellement les connexions. Le conteneur expose le port 3306
et crée la base `chantiers`.

**Option B — MySQL 8 déjà installé sur la machine.**

Rien à lancer ici, passez à l'étape 3 pour déclarer vos identifiants.

### 3. Configurer l'accès à la base

**Avec l'option A, il n'y a rien à faire.** Les valeurs de `DATABASE_URL` présentes dans le
fichier versionné `.env` correspondent déjà au conteneur.

**Avec l'option B**, créez à la racine un fichier `.env.local` (il n'est pas versionné) :

```dotenv
DATABASE_URL="mysql://UTILISATEUR:MOT_DE_PASSE@127.0.0.1:3306/chantiers?serverVersion=8.0.32&charset=utf8mb4"
```

Remplacez `UTILISATEUR` et `MOT_DE_PASSE` par les identifiants de votre MySQL, et ajustez le
port si le vôtre n'écoute pas sur 3306. Ajustez également `serverVersion` si votre serveur
n'est pas en 8.0.32 : cette valeur sert à Doctrine pour générer le bon SQL.

### 4. Créer le schéma et charger les données

```bash
php bin/console doctrine:database:create --if-not-exists
```

```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

```bash
php bin/console doctrine:fixtures:load --no-interaction
```

Les fixtures insèrent un jeu de chantiers et d'équipements couvrant les trois statuts, dont un
chantier déjà terminé, nécessaire à la démonstration des erreurs.

### 5. Lancer le serveur

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

Le troisième argument `public/index.php` est le script de routage : il fait passer toutes les
URL par le contrôleur frontal de Symfony. Ne l'omettez pas.

Si le Symfony CLI est installé, l'équivalent est :

```bash
symfony serve -d
```

L'application est accessible sur **http://127.0.0.1:8000**.

## Utilisation

La page d'accueil affiche un tableau : nom, adresse, date de début, statut, équipements
associés et action. Le bouton « Marquer comme terminé » n'apparaît que sur les chantiers qui ne
sont pas encore terminés.

Cliquez dessus : le bouton passe en état de chargement, une requête `POST` part vers
`/chantiers/{id}/terminer`, puis le badge de statut est mis à jour à la réponse et le bouton
disparaît. Aucune page n'est rechargée.

## Gestion des erreurs AJAX

Chaque cas d'échec produit un message en français, affiché sous la ligne concernée. Dans tous
les cas, le bouton redevient cliquable avec son libellé d'origine : l'utilisateur peut
réessayer.

| Cas | Message affiché | Comment le reproduire |
|---|---|---|
| Serveur injoignable | Connexion impossible. Vérifiez votre réseau. | Devtools → Réseau → hors connexion, puis cliquer |
| Délai dépassé (8 s) | Le serveur met trop de temps à répondre. | Bouton « Délai dépassé » du panneau |
| Chantier déjà terminé (409) | Ce chantier est déjà terminé. | Bouton « Déjà terminé · 409 » du panneau |
| Token CSRF invalide (403) | Session expirée, rechargez la page. | Bouton « Token invalide · 403 » du panneau |
| Chantier inexistant (404) | Ce chantier n'existe plus. | Bouton « Chantier inexistant · 404 » du panneau |

Un panneau replié « Démonstration de la gestion d'erreurs » est disponible en bas de la page
d'accueil. Il rejoue quatre de ces cas en un clic, sans passer par les outils de développement.
Il ne modifie aucune donnée : ses boutons visent le chantier déjà terminé, ou un identifiant
inexistant.

Le badge de statut porte un `aria-live="polite"` et le message d'erreur un `role="alert"` : les
changements sont annoncés aux lecteurs d'écran, qui ne perçoivent pas une mise à jour du DOM.

## Tests

La configuration de test isole la base par un suffixe : les tests travaillent sur
`chantiers_test`, jamais sur les données de développement. Cette base doit donc être créée une
première fois.

```bash
php bin/console --env=test doctrine:database:create --if-not-exists
```

```bash
php bin/console --env=test doctrine:migrations:migrate --no-interaction
```

```bash
php bin/phpunit
```

Résultat attendu : `OK (7 tests, 11 assertions)`.

Sept tests : trois unitaires sur la règle métier de changement de statut, quatre fonctionnels
sur le contrat HTTP de l'endpoint (200, 409, 403, 405). Les tests créent et suppriment leurs
propres données ; ils ne dépendent pas des fixtures et sont rejouables à l'identique.

## Choix techniques

Chaque point suit le même format : le choix retenu, l'alternative écartée, la raison.

**1. `quantite` portée par `Equipement`, relation `ManyToMany` simple.**
L'alternative serait une entité de jonction portant une quantité par chantier, plus fidèle au
métier. L'énoncé place `quantite` sur `Equipement` : la lecture littérale a été retenue.
Conséquence assumée, on connaît le stock global de l'entreprise, pas sa répartition par
chantier.

**2. Mise à jour pessimiste plutôt qu'optimiste.**
Le bouton passe en état de chargement, puis le DOM est mis à jour après la réponse du serveur.
Une mise à jour optimiste afficherait le nouveau statut immédiatement, mais rendrait les échecs
invisibles, alors que leur traitement est au cœur de l'exercice.

**3. Passage à « Terminé » irréversible.**
Aucune consigne ne prévoit de retour arrière. L'invariant est porté par l'entité : une seconde
tentative lève une exception métier, que le contrôleur traduit en `409 Conflict`. Côté
affichage, le bouton n'est simplement pas rendu sur un chantier terminé.

**4. Tailwind CSS via Play CDN, JavaScript vanilla, aucune étape de build.**
L'énoncé propose « Bootstrap ou Tailwind pour aller vite sur le design ». Tailwind correctement
outillé impose un build qui scanne les templates ; le Play CDN l'évite, au prix d'une
compilation dans le navigateur. Il affiche d'ailleurs un avertissement en console rappelant
qu'il n'est pas destiné à la production. En production, le CLI Tailwind générerait une feuille
purgée, servie en statique.

**5. MySQL en conteneur, PHP exécuté en natif.**
Un Docker applicatif complet aurait été plus reproductible, mais plus lourd à démarrer et à
déboguer sur la durée de l'exercice. Le conteneur ne porte que la base, dont la version compte ;
les deux chemins d'installation sont documentés plus haut.

**6. Dépendances installées à l'unité plutôt que via le pack `webapp`.**
Ce pack embarque `asset-mapper`, `stimulus-bundle` et `ux-turbo`. Ces composants contredisent
l'annonce « JavaScript vanilla sans framework », et Turbo en particulier rendrait ambigu qui
produit réellement la mise à jour de la page. Chaque paquet a donc été ajouté explicitement.

## Structure du projet

```
src/Entity/          Chantier et Equipement, relation ManyToMany
src/Enum/            StatutChantier : en_attente, en_cours, termine
src/Exception/       ChantierDejaTermineException, l'erreur métier typée
src/Service/         ChantierStatusUpdater : applique la transition, persiste
src/Controller/      ChantierController : page de liste et endpoint JSON
templates/chantier/  index.html.twig : tableau et panneau de démonstration
public/js/           chantier-status.js : appel fetch et gestion des erreurs
migrations/          migration initiale du schéma
tests/               3 tests unitaires, 4 tests fonctionnels
```

La règle métier vit dans `Chantier::terminer()`, la persistance dans `ChantierStatusUpdater`,
et le contrôleur ne fait que traduire HTTP vers le métier, puis le métier vers HTTP.
