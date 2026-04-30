<!--
=============================================================
  FICHIER      : dashboard.php
  AUTEUR       : Benoît — Développeur Data & Visualisation
                 Ayoub  — Développeur Frontend JS
  PROJET       : FinTrack — ECAM-EPMI 2025-2026
  DESCRIPTION  : Tableau de bord. Affiche 0 partout si aucun
                 track. Les données viennent de localStorage
                 via data.js. 3 graphiques Chart.js interactifs.
  DATE         : Mars 2026
=============================================================
-->
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Dashboard — FinTrack</title>
    <link rel="icon" type="image/x-icon"
          href="assets/img/favicon.ico">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <!-- Styles du projet -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Styles spécifiques au dashboard -->
    <style>
        .grille-stats {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(210px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }
        .barre-outils-dashboard {
            display:         flex;
            align-items:     center;
            justify-content: space-between;
            flex-wrap:       wrap;
            gap:             1rem;
            margin-bottom:   1.75rem;
        }
        .grille-graphes {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .graphe-courbe-wrapper {
            grid-column: 1 / -1;
        }
        .evolution-tag {
            display:     inline-flex;
            align-items: center;
            gap:         0.2rem;
            font-size:   var(--taille-xs);
            font-weight: 600;
            margin-top:  0.3rem;
        }
        /* Bannière premier track */
        .banniere-premier-track {
            background: linear-gradient(
                135deg,
                var(--couleur-primaire-clair) 0%,
                var(--couleur-succes-clair) 100%
            );
            border:        2px dashed var(--couleur-primaire);
            border-radius: var(--arrondi-xl);
            padding:       2.5rem 2rem;
            text-align:    center;
            margin-bottom: 2rem;
        }
        .banniere-premier-track h3 {
            font-family:   var(--police-titre);
            font-size:     var(--taille-2xl);
            font-weight:   700;
            color:         var(--couleur-primaire);
            margin-bottom: 0.75rem;
        }
        .banniere-premier-track p {
            color:         var(--texte-secondaire);
            font-size:     var(--taille-md);
            margin-bottom: 1.5rem;
        }
        /* --- Objectif budgétaire mensuel --- */
        .carte-budget-objectif {
            margin-bottom: 1.75rem;
        }
        .budget-barre-fond {
            background: var(--bg-corps);
            border-radius: var(--arrondi-full);
            height: 14px;
            overflow: hidden;
            margin: 0.75rem 0;
        }
        .budget-barre-remplie {
            height: 100%;
            border-radius: var(--arrondi-full);
            transition: width 0.8s ease;
        }
        .budget-montants {
            display: flex;
            justify-content: space-between;
            font-size: var(--taille-sm);
            color: var(--texte-secondaire);
        }
        .budget-input-groupe {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-top: 0.5rem;
        }
        .budget-input-groupe input {
            flex: 1;
            padding: 0.35rem 0.6rem;
            border: 1px solid var(--bordure);
            border-radius: var(--arrondi-md);
            font-size: var(--taille-sm);
            background: var(--bg-corps);
            color: var(--texte-primaire);
            max-width: 180px;
        }
        @media (max-width: 768px) {
            .grille-graphes {
                grid-template-columns: 1fr;
            }
            .graphe-courbe-wrapper {
                grid-column: 1;
            }
        }
    </style>
</head>
<body>

    <!-- Barre de navigation principale -->
    <nav class="navbar navbar-expand-lg navbar-fintrack">
        <div class="container">

            <!-- Logo FinTrack -->
            <a class="navbar-brand navbar-brand-fintrack"
               href="index.html">
                <img src="assets/img/logo.png"
                     alt="FinTrack"
                     class="logo-navbar"
                     style="height:40px; width:auto;">
                FinTrack
            </a>

            <!-- Bouton hamburger (mobile) -->
            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navMenu"
                    aria-controls="navMenu"
                    aria-expanded="false"
                    aria-label="Ouvrir le menu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Liens de navigation -->
            <div class="collapse navbar-collapse"
                 id="navMenu">
                <ul class="navbar-nav ms-auto
                           align-items-lg-center gap-1">

                    <!-- Accueil -->
                    <li class="nav-item">
                        <a class="nav-link"
                           href="index.html">
                            <i class="bi bi-house-door me-1">
                            </i>Accueil
                        </a>
                    </li>

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a class="nav-link"
                           href="dashboard.php">
                            <i class="bi bi-speedometer2 me-1">
                            </i>Dashboard
                        </a>
                    </li>

                    <!-- Menu déroulant Opérations -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           id="dropdownOperations"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-plus-circle me-1">
                            </i>Opérations
                        </a>
                        <ul class="dropdown-menu"
                            aria-labelledby=
                            "dropdownOperations">
                            <li>
                                <a class="dropdown-item"
                                   href="formulaire.php">
                                    <i class="bi bi-plus-circle me-2">
                                    </i>Nouveau track
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                   href="historique.php">
                                    <i class="bi bi-clock-history me-2">
                                    </i>Historique
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Menu déroulant Rapports -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle"
                           href="#"
                           id="dropdownRapports"
                           role="button"
                           data-bs-toggle="dropdown"
                           aria-expanded="false">
                            <i class="bi bi-file-earmark-pdf me-1">
                            </i>Rapports
                        </a>
                        <ul class="dropdown-menu"
                            aria-labelledby=
                            "dropdownRapports">
                            <li>
                                <a class="dropdown-item"
                                   href="conseils.php">
                                    <i class="bi bi-lightbulb me-2">
                                    </i>Conseils
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item"
                                   href="rapport.php">
                                    <i class="bi bi-file-earmark-pdf me-2">
                                    </i>Rapport PDF
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Lien Équipe (simple) -->
                    <li class="nav-item">
                        <a class="nav-link"
                           href="equipe.php">
                            <i class="bi bi-people me-1">
                            </i>Équipe
                        </a>
                    </li>

                </ul>

                <!-- Bouton bascule de thème -->
                <div class="ms-lg-3 mt-2 mt-lg-0">
                    <button class="btn-theme-toggle"
                            id="toggle-theme"
                            title="Changer de thème">
                        <span class="icone-theme">
                            <i class="bi bi-moon-fill"></i>
                        </span>
                    </button>
                </div>
            </div>

        </div>
    </nav>

    <!-- Contenu principal -->
    <main class="page-wrapper">
        <div class="container-fluid px-4">

            <!-- En-tête + filtre de période -->
            <div class="barre-outils-dashboard">
                <div class="page-header mb-0 scroll-from-left">
                    <h1 class="page-title">
                        <span class="titre-icone">
                            <i class="bi bi-speedometer2"></i>
                        </span>
                        Tableau de bord
                    </h1>
                    <p class="page-subtitle"
                       id="sous-titre-dashboard">
                        Vue d'ensemble de vos finances
                    </p>
                </div>
                <div class="filtre-periode scroll-from-right"
                     role="group">
                    <button class="filtre-btn"
                            data-periode="jour">
                        Jour
                    </button>
                    <button class="filtre-btn"
                            data-periode="semaine">
                        Semaine
                    </button>
                    <button class="filtre-btn actif"
                            data-periode="mois">
                        Mois
                    </button>
                    <button class="filtre-btn"
                            data-periode="annee">
                        Année
                    </button>
                </div>
            </div>

            <!-- Bannière premier track (affichée si aucune donnée) -->
            <div class="banniere-premier-track scroll-reveal"
                 id="banniere-vide"
                 style="display:none;">
                <div style="font-size:3.5rem; margin-bottom:1rem;">
                    <i class="bi bi-bar-chart"></i>
                </div>
                <h3>Bienvenue sur FinTrack !</h3>
                <p>
                    Vous n'avez pas encore enregistré de track.
                    <br>Commencez par ajouter votre premier
                    revenu ou dépense.
                </p>
                <a href="formulaire.php"
                   class="btn-fintrack btn-primaire btn-lg">
                    <i class="bi bi-plus-circle"></i>
                    Créer mon premier track
                </a>
            </div>

            <!-- Objectif budgétaire mensuel -->
            <div class="carte carte-budget-objectif
                        scroll-reveal">
                <div class="carte-header">
                    <h3 class="carte-titre">
                        <i class="bi bi-bullseye me-2"></i>
                        Objectif budgétaire
                    </h3>
                    <button class="btn-fintrack btn-outline"
                            id="btn-modifier-budget"
                            style="padding:0.3rem 0.7rem;
                                   font-size:var(--taille-xs);">
                        <i class="bi bi-pencil"></i> Modifier
                    </button>
                </div>
                <div id="zone-budget-objectif">
                    <!-- Rempli par JS -->
                </div>
            </div>

            <!-- 4 cartes statistiques -->
            <div class="grille-stats" id="grille-stats">
                <!-- Carte revenus -->
                <div class="carte-stat carte-stat-succes
                            scroll-reveal">
                    <div class="stat-icone icone-succes">
                        <i class="bi bi-arrow-up-circle-fill">
                        </i>
                    </div>
                    <div class="stat-corps">
                        <div class="stat-label">Revenus</div>
                        <div class="stat-valeur texte-succes"
                             id="stat-revenus">
                            0,00 &euro;
                        </div>
                        <div class="evolution-tag texte-succes"
                             id="evolution-revenus">
                            <i class="bi bi-dash"></i>
                            <span>vs mois dernier</span>
                        </div>
                    </div>
                </div>
                <!-- Carte dépenses -->
                <div class="carte-stat carte-stat-danger
                            scroll-reveal">
                    <div class="stat-icone icone-danger">
                        <i class="bi bi-arrow-down-circle-fill">
                        </i>
                    </div>
                    <div class="stat-corps">
                        <div class="stat-label">Dépenses</div>
                        <div class="stat-valeur texte-danger"
                             id="stat-depenses">
                            0,00 &euro;
                        </div>
                        <div class="evolution-tag"
                             id="evolution-depenses">
                            <i class="bi bi-dash"></i>
                            <span>vs mois dernier</span>
                        </div>
                    </div>
                </div>
                <!-- Carte solde net -->
                <div class="carte-stat carte-stat-info
                            scroll-reveal">
                    <div class="stat-icone icone-primaire">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="stat-corps">
                        <div class="stat-label">Solde net</div>
                        <div class="stat-valeur"
                             id="stat-solde">
                            0,00 &euro;
                        </div>
                        <div class="evolution-tag texte-discret">
                            <i class="bi bi-calendar3"></i>
                            <span>ce mois</span>
                        </div>
                    </div>
                </div>
                <!-- Carte nombre de tracks -->
                <div class="carte-stat carte-stat-warning
                            scroll-reveal">
                    <div class="stat-icone icone-or">
                        <i class="bi bi-receipt-cutoff"></i>
                    </div>
                    <div class="stat-corps">
                        <div class="stat-label">Tracks</div>
                        <div class="stat-valeur"
                             id="stat-nb-transactions">
                            0
                        </div>
                        <div class="evolution-tag texte-discret">
                            <i class="bi bi-list-check"></i>
                            <span>opérations</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Graphiques -->
            <div class="grille-graphes" id="zone-graphes">

                <!-- Courbe évolution du solde -->
                <div class="carte graphe-courbe-wrapper
                            scroll-reveal">
                    <div class="carte-header">
                        <h3 class="carte-titre">
                            <i class="bi bi-graph-up me-2"
                               style="color:var(--couleur-primaire)">
                            </i>
                            Évolution du solde
                        </h3>
                        <span class="badge-fintrack badge-info">
                            6 derniers mois
                        </span>
                    </div>
                    <div class="graphe-conteneur graphe-md">
                        <canvas id="graphe-courbe-evolution"
                                aria-label="Courbe évolution solde"
                                role="img">
                        </canvas>
                    </div>
                </div>

                <!-- Barres revenus vs dépenses -->
                <div class="carte scroll-reveal">
                    <div class="carte-header">
                        <h3 class="carte-titre">
                            <i class="bi bi-bar-chart me-2"
                               style="color:var(--couleur-succes)">
                            </i>
                            Revenus vs Dépenses
                        </h3>
                        <span class="badge-fintrack badge-info">
                            6 mois
                        </span>
                    </div>
                    <div class="graphe-conteneur graphe-lg">
                        <canvas id="graphe-barres-comparaison"
                                aria-label="Revenus vs Dépenses"
                                role="img">
                        </canvas>
                    </div>
                </div>

                <!-- Donut catégories de dépenses -->
                <div class="carte scroll-reveal">
                    <div class="carte-header">
                        <h3 class="carte-titre">
                            <i class="bi bi-pie-chart me-2"
                               style="color:var(--couleur-danger)">
                            </i>
                            Dépenses par catégorie
                        </h3>
                        <span class="badge-fintrack badge-categorie"
                              id="label-periode-camembert">
                            Ce mois
                        </span>
                    </div>
                    <div class="graphe-conteneur graphe-lg">
                        <canvas id="graphe-camembert-categories"
                                aria-label="Dépenses par catégorie"
                                role="img">
                        </canvas>
                    </div>
                </div>

            </div>

            <!-- Tableau des derniers tracks -->
            <div class="carte scroll-reveal">
                <div class="carte-header">
                    <h3 class="carte-titre">
                        <i class="bi bi-clock-history me-2"></i>
                        Derniers tracks
                    </h3>
                    <a href="historique.php"
                       class="btn-fintrack btn-outline"
                       style="padding:0.35rem 0.85rem;
                              font-size:var(--taille-xs);">
                        <i class="bi bi-list-ul"></i> Voir tout
                    </a>
                </div>
                <div class="tableau-wrapper">
                    <table class="tableau-fintrack">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Catégorie</th>
                                <th>Type</th>
                                <th class="col-montant">
                                    Montant
                                </th>
                            </tr>
                        </thead>
                        <tbody id="corps-derniers-tracks">
                            <tr>
                                <td colspan="5">
                                    <div class="etat-vide">
                                        <span class="etat-vide-icone">
                                            <i class="bi bi-inbox">
                                            </i>
                                        </span>
                                        <div class="etat-vide-titre">
                                            Aucun track pour l'instant
                                        </div>
                                        <p class="etat-vide-texte">
                                            Vos transactions apparaîtront
                                            ici après votre premier track.
                                        </p>
                                        <a href="formulaire.php"
                                           class="btn-fintrack btn-primaire">
                                            <i class="bi bi-plus-circle">
                                            </i> Créer un track
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

    <!-- Pied de page -->
    <footer class="footer-fintrack">
        <div class="container">
            <div class="footer-texte">
                <span class="footer-logo">FinTrack</span>
                <p class="mb-0">
                    Projet Web — ECAM-EPMI Cergy
                    · 2025-2026
                    · Ange · Ayoub · Ghita
                    · Benoît · Maroua
                </p>
            </div>
        </div>
    </footer>

    <!-- Bibliothèques externes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js">
    </script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js">
    </script>
    <!-- Scripts du projet -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/data.js"></script>
    <script src="assets/js/charts.js"></script>

    <!-- Script dashboard : gestion état vide + derniers tracks -->
    <script>
    // --- Objectif budgétaire (localStorage) ---
    const CLE_BUDGET = 'fintrack_budget_mensuel';

    /** Récupère le budget mensuel enregistré. */
    function getBudgetMensuel() {
        return parseFloat(
            localStorage.getItem(CLE_BUDGET)
        ) || 0;
    }

    /** Enregistre le budget mensuel. */
    function setBudgetMensuel(montant) {
        localStorage.setItem(CLE_BUDGET, String(montant));
    }

    /**
     * Calcule les dépenses du mois en cours à partir
     * des tracks stockés dans localStorage.
     */
    function getDepensesMoisCourant(tracks) {
        const now = new Date();
        const mois = now.getMonth();
        const annee = now.getFullYear();
        return tracks
            .filter(t => {
                const d = new Date(t.date);
                return t.type === 'depense'
                    && d.getMonth() === mois
                    && d.getFullYear() === annee;
            })
            .reduce((s, t) =>
                s + parseFloat(t.montant || 0), 0
            );
    }

    /**
     * Affiche la barre de progression du budget
     * ou le formulaire de saisie si aucun budget.
     */
    function afficherBudgetObjectif(tracks) {
        const zone =
            document.getElementById('zone-budget-objectif');
        if (!zone) return;

        const budget = getBudgetMensuel();

        // Pas de budget défini : formulaire de saisie
        if (budget <= 0) {
            zone.innerHTML = `
                <p style="margin:0 0 0.4rem;
                          color:var(--texte-secondaire);
                          font-size:var(--taille-sm);">
                    Définissez votre objectif mensuel
                </p>
                <div class="budget-input-groupe">
                    <input type="number" min="1" step="1"
                           id="input-budget-init"
                           placeholder="Ex : 1500">
                    <button class="btn-fintrack btn-primaire"
                            id="btn-sauver-budget-init"
                            style="padding:0.35rem 0.8rem;
                                   font-size:var(--taille-sm);">
                        <i class="bi bi-check-lg"></i>
                        Enregistrer
                    </button>
                </div>`;
            document.getElementById('btn-sauver-budget-init')
                .addEventListener('click', () => {
                    const v = parseFloat(
                        document.getElementById(
                            'input-budget-init'
                        ).value
                    );
                    if (v > 0) {
                        setBudgetMensuel(v);
                        afficherBudgetObjectif(tracks);
                    }
                });
            return;
        }

        // Budget défini : barre de progression
        const depense = getDepensesMoisCourant(tracks);
        const pct = Math.min(
            (depense / budget) * 100, 100
        );
        // Couleur selon le pourcentage
        let couleur = 'var(--couleur-succes)';
        if (pct >= 90)      couleur = 'var(--couleur-danger)';
        else if (pct >= 60) couleur = 'var(--couleur-or)';

        const fmtDep = depense.toLocaleString('fr-FR', {
            style: 'currency', currency: 'EUR'
        });
        const fmtBud = budget.toLocaleString('fr-FR', {
            style: 'currency', currency: 'EUR'
        });

        zone.innerHTML = `
            <div class="budget-barre-fond">
                <div class="budget-barre-remplie"
                     style="width:${pct.toFixed(1)}%;
                            background:${couleur};">
                </div>
            </div>
            <div class="budget-montants">
                <span>Dépensé : ${fmtDep}</span>
                <span>Objectif : ${fmtBud}</span>
                <span>${pct.toFixed(0)} %</span>
            </div>`;
    }

    document.addEventListener('DOMContentLoaded', () => {

        const tousLesTracks = getTousTracks();
        const aucunTrack    = tousLesTracks.length === 0;

        // Affiche la bannière si aucun track
        const banniere =
            document.getElementById('banniere-vide');
        if (banniere) {
            banniere.style.display =
                aucunTrack ? 'block' : 'none';
        }

        // Sous-titre dynamique avec le mois en cours
        const sousTitre =
            document.getElementById('sous-titre-dashboard');
        if (sousTitre) {
            const now = new Date();
            const nomMois = [
                'Janvier', 'Février', 'Mars',
                'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre',
                'Octobre', 'Novembre', 'Décembre'
            ];
            sousTitre.textContent =
                'Vue d\'ensemble · '
                + nomMois[now.getMonth()]
                + ' ' + now.getFullYear();
        }

        // Remplissage du tableau des derniers tracks
        const corps =
            document.getElementById('corps-derniers-tracks');
        if (corps && tousLesTracks.length > 0) {
            const derniers = getDerniersTracks(5);
            corps.innerHTML = '';
            derniers.forEach(t => {
                const estRev = t.type === 'revenu';
                const badge  = estRev
                    ? '<span class="badge-fintrack badge-revenu">'
                      + 'Revenu</span>'
                    : '<span class="badge-fintrack badge-depense">'
                      + 'Dépense</span>';
                const cats = Array.isArray(t.categorie)
                    ? t.categorie[0]
                    : t.categorie;
                const dateAff =
                    new Date(t.date)
                        .toLocaleDateString('fr-FR');
                const montAff =
                    parseFloat(t.montant)
                        .toLocaleString('fr-FR', {
                            style: 'currency',
                            currency: 'EUR'
                        });
                const classe = estRev
                    ? 'montant-revenu'
                    : 'montant-depense';
                const signe = estRev ? '+' : '-';
                corps.insertAdjacentHTML('beforeend', `
                    <tr>
                        <td>${dateAff}</td>
                        <td>${t.description || '\u2014'}</td>
                        <td>
                            <span class="badge-fintrack
                                         badge-categorie">
                                ${cats || '\u2014'}
                            </span>
                        </td>
                        <td>${badge}</td>
                        <td class="col-montant ${classe}">
                            ${signe}${montAff}
                        </td>
                    </tr>`);
            });
        }

        // Mise à jour des indicateurs d'évolution
        if (!aucunTrack) {
            const stats = calculerStatsGlobales();

            const mettreAJourEvol = (id, pct, inverser) => {
                const el = document.getElementById(id);
                if (!el) return;
                const p   = parseFloat(pct);
                const pos = inverser ? p <= 0 : p >= 0;
                const dir = p >= 0 ? 'up' : 'down';
                const pre = p >= 0 ? '+' : '';
                el.innerHTML =
                    '<i class="bi bi-arrow-' + dir
                    + '-short"></i>'
                    + '<span>' + pre + p
                    + '% vs mois dernier</span>';
                el.className =
                    'evolution-tag '
                    + (pos ? 'texte-succes' : 'texte-danger');
            };

            mettreAJourEvol(
                'evolution-revenus',
                stats.evolutionRevenus,
                false
            );
            mettreAJourEvol(
                'evolution-depenses',
                stats.evolutionDepenses,
                true
            );
        }

        // --- Affichage de l'objectif budgétaire ---
        afficherBudgetObjectif(tousLesTracks);

        // Bouton « Modifier » : saisie du nouveau budget
        const btnModif =
            document.getElementById('btn-modifier-budget');
        if (btnModif) {
            btnModif.addEventListener('click', () => {
                const actuel = getBudgetMensuel();
                const msg = actuel > 0
                    ? 'Budget actuel : '
                      + actuel.toLocaleString('fr-FR')
                      + ' €\nNouveau montant :'
                    : 'Entrez votre objectif mensuel (€) :';
                const saisie = prompt(msg);
                if (saisie !== null) {
                    const val = parseFloat(saisie);
                    if (val > 0) {
                        setBudgetMensuel(val);
                        afficherBudgetObjectif(
                            tousLesTracks
                        );
                    }
                }
            });
        }
    });
    </script>

</body>
</html>
