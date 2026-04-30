<!--
=============================================================
  FICHIER      : historique.php
  AUTEUR       : Benoit -- Developpeur Data & Visualisation
  PROJET       : FinTrack -- ECAM-EPMI 2025-2026
  DESCRIPTION  : Page d'historique des transactions financieres.
                 Affiche la liste complete des transactions
                 statiques (data.js) avec filtres dynamiques
                 jQuery UI : par type, categorie, periode,
                 recherche textuelle et tri par colonne.
  DATE         : Mars 2026
=============================================================
-->
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Historique -- FinTrack</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <!-- jQuery UI CSS -->
    <link href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.min.css"
          rel="stylesheet">

    <!-- Feuilles de styles FinTrack -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Styles specifiques a l'historique -->
    <style>
        /* -- Barre de filtres -- */
        .barre-filtres {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-xl);
            padding:       1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow:    var(--ombre-carte);
        }

        .barre-filtres .filtres-titre {
            font-size:      var(--taille-sm);
            font-weight:    700;
            color:          var(--texte-secondaire);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom:  1rem;
        }

        /* Grille de filtres : 4 colonnes sur desktop */
        .grille-filtres {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 0.85rem;
            align-items: end;
        }

        /* Tablette : 2 colonnes */
        @media (max-width: 768px) {
            .grille-filtres {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Mobile : 1 colonne */
        @media (max-width: 480px) {
            .grille-filtres {
                grid-template-columns: 1fr;
            }
        }

        /* -- Resume des filtres actifs -- */
        .resume-filtres {
            display:       flex;
            align-items:   center;
            gap:           0.75rem;
            flex-wrap:     wrap;
            margin-bottom: 1rem;
            font-size:     var(--taille-sm);
        }

        .tag-filtre-actif {
            display:       inline-flex;
            align-items:   center;
            gap:           0.3rem;
            padding:       0.25rem 0.65rem;
            background:    var(--couleur-primaire-clair);
            color:         var(--couleur-primaire);
            border-radius: var(--arrondi-full);
            font-size:     var(--taille-xs);
            font-weight:   600;
        }

        /* -- En-tete du tableau avec tri -- */
        .tableau-fintrack thead th.triable {
            cursor:      pointer;
            user-select: none;
            white-space: nowrap;
        }

        .tableau-fintrack thead th.triable:hover {
            background: var(--couleur-primaire-clair);
            color:      var(--couleur-primaire);
        }

        .tableau-fintrack thead th .icone-tri {
            opacity:     0.4;
            font-size:   0.75rem;
            margin-left: 0.3rem;
        }

        .tableau-fintrack thead th.tri-actif .icone-tri {
            opacity: 1;
            color:   var(--couleur-primaire);
        }

        /* -- Wrapper du tableau : defilement horizontal mobile -- */
        .tableau-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* -- Barre de resume totaux -- */
        .barre-totaux {
            display:       flex;
            gap:           1.5rem;
            flex-wrap:     wrap;
            padding:       1rem 1.25rem;
            background:    var(--bg-corps);
            border-top:    1px solid var(--bordure-couleur);
            border-radius: 0 0 var(--arrondi-xl)
                           var(--arrondi-xl);
        }

        .total-item {
            display:     flex;
            align-items: center;
            gap:         0.5rem;
            font-size:   var(--taille-sm);
        }

        .total-item .total-label {
            color:       var(--texte-secondaire);
            font-weight: 500;
        }

        .total-item .total-valeur {
            font-weight: 700;
        }

        /* -- Pagination -- */
        .pagination-fintrack {
            display:         flex;
            align-items:     center;
            justify-content: center;
            gap:             0.4rem;
            margin-top:      1.25rem;
            flex-wrap:       wrap;
        }

        .btn-page {
            padding:       0.35rem 0.75rem;
            font-size:     var(--taille-xs);
            font-weight:   600;
            border:        1px solid var(--bordure-couleur);
            border-radius: var(--arrondi-md);
            background:    var(--bg-carte);
            color:         var(--texte-secondaire);
            cursor:        pointer;
            transition:    all var(--transition-rapide);
            font-family:   var(--police-principale);
        }

        .btn-page:hover,
        .btn-page.actif {
            background:   var(--couleur-primaire);
            color:        var(--texte-inverse);
            border-color: var(--couleur-primaire);
        }

        .btn-page:disabled {
            opacity: 0.4;
            cursor:  not-allowed;
        }

        /* -- Boutons d'actions rapides -- */
        .groupe-actions-historique {
            display:         flex;
            gap:             0.4rem;
            justify-content: center;
            flex-wrap:       nowrap;
        }

        .btn-action-rapide {
            padding:       0.35rem 0.6rem;
            font-size:     var(--taille-xs);
            font-weight:   600;
            border:        1px solid transparent;
            border-radius: var(--arrondi-md);
            cursor:        pointer;
            transition:    all var(--transition-rapide);
            display:       flex;
            align-items:   center;
            gap:           0.25rem;
            white-space:   nowrap;
            background:    var(--bg-carte);
            color:         var(--texte-secondaire);
            font-family:   var(--police-principale);
        }

        .btn-action-rapide:hover {
            transform:  translateY(-2px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        /* Variantes de couleur des boutons d'action */
        .btn-mail {
            border-color: var(--couleur-primaire);
            color:        var(--couleur-primaire);
        }
        .btn-mail:hover {
            background: var(--couleur-primaire);
            color:      white;
        }

        .btn-pdf {
            border-color: var(--couleur-danger);
            color:        var(--couleur-danger);
        }
        .btn-pdf:hover {
            background: var(--couleur-danger);
            color:      white;
        }

        .btn-detail {
            border-color: var(--couleur-succes);
            color:        var(--couleur-succes);
        }
        .btn-detail:hover {
            background: var(--couleur-succes);
            color:      white;
        }

        /* Bouton supprimer */
        .btn-suppr {
            border-color: var(--couleur-danger);
            color:        var(--couleur-danger);
        }
        .btn-suppr:hover {
            background: var(--couleur-danger);
            color:      white;
        }

        /* Responsive : boutons plus compacts sur mobile */
        @media (max-width: 768px) {
            .groupe-actions-historique {
                flex-wrap:  nowrap;
                gap:        0.2rem;
            }
            .btn-action-rapide {
                padding:   0.25rem 0.4rem;
                font-size: 0.65rem;
            }
        }
    </style>

    <link rel="icon" type="image/x-icon"
          href="assets/img/favicon.ico">
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


    <!-- =============================================
         CONTENU PRINCIPAL
    ============================================= -->
    <main class="page-wrapper">
        <div class="container">

            <!-- En-tete de page -->
            <div class="page-header">
                <h1 class="page-title">
                    <span class="titre-icone">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    Historique des transactions
                </h1>
                <p class="page-subtitle">
                    Consultez, filtrez et recherchez
                    parmi toutes vos operations financieres
                </p>
            </div>


            <!-- =============================================
                 BARRE DE FILTRES (jQuery UI)
            ============================================= -->
            <div class="barre-filtres">
                <div class="filtres-titre">
                    <i class="bi bi-funnel me-2"></i>
                    Filtres et recherche
                </div>

                <div class="grille-filtres">

                    <!-- Recherche textuelle -->
                    <div class="form-group-fintrack mb-0">
                        <label class="form-label-fintrack"
                               for="filtre-recherche">
                            Rechercher
                        </label>
                        <input type="text"
                               class="form-control-fintrack"
                               id="filtre-recherche"
                               placeholder="Description, categorie...">
                    </div>

                    <!-- Filtre par type -->
                    <div class="form-group-fintrack mb-0">
                        <label class="form-label-fintrack"
                               for="filtre-type">
                            Type
                        </label>
                        <select class="form-select-fintrack"
                                id="filtre-type">
                            <option value="">Tous les types</option>
                            <option value="revenu">Revenus</option>
                            <option value="depense">Depenses</option>
                        </select>
                    </div>

                    <!-- Filtre par categorie -->
                    <div class="form-group-fintrack mb-0">
                        <label class="form-label-fintrack"
                               for="filtre-categorie">
                            Categorie
                        </label>
                        <select class="form-select-fintrack"
                                id="filtre-categorie">
                            <option value="">Toutes</option>
                        </select>
                    </div>

                    <!-- Filtre par periode -->
                    <div class="form-group-fintrack mb-0">
                        <label class="form-label-fintrack"
                               for="filtre-periode-hist">
                            Periode
                        </label>
                        <select class="form-select-fintrack"
                                id="filtre-periode-hist">
                            <option value="">
                                Toutes les periodes
                            </option>
                            <option value="7">
                                7 derniers jours
                            </option>
                            <option value="30">
                                30 derniers jours
                            </option>
                            <option value="90">
                                3 derniers mois
                            </option>
                            <option value="180" selected>
                                6 derniers mois
                            </option>
                            <option value="365">
                                12 derniers mois
                            </option>
                        </select>
                    </div>

                </div>

                <!-- Boutons reinitialiser / appliquer -->
                <div class="d-flex gap-2 mt-3">
                    <button class="btn-fintrack btn-primaire"
                            id="btn-appliquer-filtres">
                        <i class="bi bi-funnel-fill"></i>
                        Appliquer les filtres
                    </button>
                    <button class="btn-fintrack btn-outline"
                            id="btn-reinit-filtres">
                        <i class="bi bi-x-circle"></i>
                        Reinitialiser
                    </button>
                </div>
            </div>


            <!-- Resume des filtres actifs -->
            <div class="resume-filtres"
                 id="resume-filtres"
                 style="display: none;">
                <span style="color: var(--texte-secondaire);
                             font-weight: 600;">
                    Filtres actifs :
                </span>
                <div id="tags-filtres-actifs"></div>
            </div>


            <!-- =============================================
                 TABLEAU DES TRANSACTIONS
            ============================================= -->
            <div class="tableau-wrapper">
                <table class="tableau-fintrack"
                       id="tableau-historique">
                    <thead>
                        <tr>
                            <th class="triable"
                                data-colonne="date">
                                Date
                                <i class="bi bi-arrow-down-up
                                          icone-tri"></i>
                            </th>
                            <th class="triable"
                                data-colonne="description">
                                Description
                                <i class="bi bi-arrow-down-up
                                          icone-tri"></i>
                            </th>
                            <th>Categorie</th>
                            <th>Type</th>
                            <th class="triable col-montant"
                                data-colonne="montant">
                                Montant
                                <i class="bi bi-arrow-down-up
                                          icone-tri"></i>
                            </th>
                            <th style="text-align: center;
                                       min-width: 200px;">
                                Actions rapides
                            </th>
                        </tr>
                    </thead>
                    <tbody id="corps-historique">
                        <!-- Rempli dynamiquement par JS -->
                    </tbody>
                </table>

                <!-- Barre de totaux en bas du tableau -->
                <div class="barre-totaux" id="barre-totaux">
                    <div class="total-item">
                        <span class="total-label">
                            Transactions affichees :
                        </span>
                        <span class="total-valeur"
                              id="total-nb">0</span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">
                            Total revenus :
                        </span>
                        <span class="total-valeur texte-succes"
                              id="total-revenus">
                            0,00 &euro;
                        </span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">
                            Total depenses :
                        </span>
                        <span class="total-valeur texte-danger"
                              id="total-depenses">
                            0,00 &euro;
                        </span>
                    </div>
                    <div class="total-item">
                        <span class="total-label">
                            Solde filtre :
                        </span>
                        <span class="total-valeur"
                              id="total-solde">
                            0,00 &euro;
                        </span>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div class="pagination-fintrack"
                 id="pagination-historique"></div>

        </div>
    </main>


    <!-- Pied de page -->
    <footer class="footer-fintrack">
        <div class="container">
            <div class="footer-texte">
                <span class="footer-logo">FinTrack</span>
                <p class="mb-0">
                    Projet Web -- ECAM-EPMI Cergy - 2025-2026
                </p>
            </div>
        </div>
    </footer>


    <!-- =============================================
         MODALE DE CONFIRMATION DE SUPPRESSION
    ============================================= -->
    <div class="modal fade"
         id="modal-confirmer-suppression"
         tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        Confirmer la suppression
                    </h5>
                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal">
                    </button>
                </div>
                <div class="modal-body">
                    Etes-vous sur de vouloir supprimer ce track ?
                    Cette action est irreversible.
                </div>
                <div class="modal-footer">
                    <button type="button"
                            class="btn-fintrack btn-outline"
                            data-bs-dismiss="modal">
                        Annuler
                    </button>
                    <button type="button"
                            class="btn-fintrack"
                            style="background:var(--couleur-danger);
                                   color:white;"
                            id="btn-confirmer-suppression">
                        Supprimer
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- =============================================
         SCRIPTS
    ============================================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>

    <!-- jsPDF (CDN) pour le bouton PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/data.js"></script>
    <script src="assets/js/pdf.js"></script>

    <!-- Script de l'historique : filtres, tri, pagination -->
    <script>
    // ============================================================
    // Script historique : filtres jQuery UI, tri et pagination
    // Toutes les transactions viennent de data.js
    // ============================================================

    // -- 1. Variables globales --

    // Copie triable et filtrable des transactions
    let transactionsFiltrees = [];

    // Parametres de tri courants
    let colonneTriActive = 'date';
    let sensTriActif     = 'desc';

    // Pagination
    const NB_PAR_PAGE = 10;
    let pageActuelle  = 1;

    // ID du track en attente de suppression (modale)
    let idSuppressionEnCours = null;


    // -- 2. Initialisation au chargement --

    document.addEventListener('DOMContentLoaded', function () {

        // Remplit le select des categories depuis data.js
        peuplerSelectCategories();

        // Active l'autocomplete jQuery UI
        initialiserAutocompleteRecherche();

        // Filtre initial (6 derniers mois par defaut)
        appliquerFiltres();

        // Bouton appliquer les filtres
        document.getElementById('btn-appliquer-filtres')
            .addEventListener('click', function () {
                pageActuelle = 1;
                appliquerFiltres();
            });

        // Bouton reinitialiser les filtres
        document.getElementById('btn-reinit-filtres')
            .addEventListener('click', reinitialiserFiltres);

        // En-tetes de colonnes triables
        document.querySelectorAll('.triable')
            .forEach(function (th) {
                th.addEventListener('click', function () {
                    var col = this.getAttribute('data-colonne');
                    if (colonneTriActive === col) {
                        // Inverse le sens si meme colonne
                        sensTriActif =
                            (sensTriActif === 'asc')
                                ? 'desc' : 'asc';
                    } else {
                        colonneTriActive = col;
                        sensTriActif     = 'desc';
                    }
                    trier();
                    afficherPage(pageActuelle);
                    mettreAJourIconesTri();
                });
            });

        // Filtre en temps reel sur la frappe
        document.getElementById('filtre-recherche')
            .addEventListener('input', function () {
                pageActuelle = 1;
                appliquerFiltres();
            });

        // Confirmation de suppression dans la modale
        document.getElementById('btn-confirmer-suppression')
            .addEventListener('click', function () {
                if (idSuppressionEnCours !== null) {
                    // Appelle supprimerTrack() de data.js
                    supprimerTrack(idSuppressionEnCours);
                    idSuppressionEnCours = null;

                    // Ferme la modale Bootstrap
                    var modale = bootstrap.Modal.getInstance(
                        document.getElementById(
                            'modal-confirmer-suppression'
                        )
                    );
                    if (modale) modale.hide();

                    // Rafraichit le tableau
                    appliquerFiltres();
                }
            });
    });


    // -- 3. Remplissage du select Categorie --

    // Normalise la categorie en tableau
    function normaliserCategories(categorie) {
        return Array.isArray(categorie)
            ? categorie : [categorie];
    }

    // Formate les categories en chaine lisible
    function formaterCategories(categorie) {
        return normaliserCategories(categorie)
            .filter(Boolean).join(', ');
    }

    // Verifie si la transaction correspond au filtre
    function correspondCategorie(catTransaction, catFiltre) {
        return normaliserCategories(catTransaction)
            .includes(catFiltre);
    }

    // Construit la liste des categories uniques
    function peuplerSelectCategories() {
        var selectCat =
            document.getElementById('filtre-categorie');

        var categoriesUniques = [...new Set(
            transactions.flatMap(function (t) {
                return normaliserCategories(t.categorie)
                    .filter(Boolean);
            })
        )].sort(function (a, b) {
            return a.localeCompare(b, 'fr');
        });

        categoriesUniques.forEach(function (cat) {
            var opt       = document.createElement('option');
            opt.value       = cat;
            opt.textContent = cat;
            selectCat.appendChild(opt);
        });
    }


    // -- 4. Autocomplete jQuery UI sur le champ recherche --

    // Propose les descriptions existantes comme suggestions
    function initialiserAutocompleteRecherche() {
        if (typeof $ === 'undefined') return;

        var descriptions = [...new Set(
            transactions.map(function (t) {
                return t.description;
            })
        )];

        $('#filtre-recherche').autocomplete({
            source:    descriptions,
            minLength: 2,
            delay:     200,
            select: function () {
                // Declenche le filtrage apres selection
                setTimeout(appliquerFiltres, 50);
            }
        });
    }


    // -- 5. Logique de filtrage principale --

    // Applique tous les filtres actifs et rafraichit le tableau
    function appliquerFiltres() {
        var recherche = document.getElementById(
            'filtre-recherche'
        ).value.trim().toLowerCase();

        var type = document.getElementById(
            'filtre-type'
        ).value;

        var categorie = document.getElementById(
            'filtre-categorie'
        ).value;

        var periodeStr = document.getElementById(
            'filtre-periode-hist'
        ).value;

        // Date limite selon la periode selectionnee
        var dateLimite = null;
        if (periodeStr) {
            dateLimite = new Date();
            dateLimite.setDate(
                dateLimite.getDate() - parseInt(periodeStr)
            );
        }

        // Application de tous les filtres en cascade
        transactionsFiltrees =
            transactions.filter(function (t) {
                var categoriesTexte =
                    formaterCategories(t.categorie)
                        .toLowerCase();

                // Filtre par type
                if (type && t.type !== type) return false;

                // Filtre par categorie
                if (categorie &&
                    !correspondCategorie(
                        t.categorie, categorie
                    )) {
                    return false;
                }

                // Filtre par periode
                if (dateLimite &&
                    new Date(t.date) < dateLimite) {
                    return false;
                }

                // Filtre par recherche textuelle
                if (recherche) {
                    var ok =
                        t.description.toLowerCase()
                            .includes(recherche) ||
                        categoriesTexte.includes(recherche);
                    if (!ok) return false;
                }

                return true;
            });

        // Tri sur les donnees filtrees
        trier();

        // Reinitialise la pagination
        pageActuelle = 1;

        // Affichage
        afficherPage(pageActuelle);
        afficherPagination();
        mettreAJourTotaux();
        mettreAJourResumeFiltres(
            recherche, type, categorie, periodeStr
        );
    }


    // -- 6. Tri des transactions filtrees --

    // Trie selon la colonne et le sens actifs
    function trier() {
        transactionsFiltrees.sort(function (a, b) {
            var valA, valB;

            switch (colonneTriActive) {
                case 'date':
                    valA = new Date(a.date);
                    valB = new Date(b.date);
                    break;
                case 'montant':
                    valA = a.montant;
                    valB = b.montant;
                    break;
                case 'description':
                    valA = a.description.toLowerCase();
                    valB = b.description.toLowerCase();
                    break;
                default:
                    return 0;
            }

            if (valA < valB) {
                return sensTriActif === 'asc' ? -1 : 1;
            }
            if (valA > valB) {
                return sensTriActif === 'asc' ? 1 : -1;
            }
            return 0;
        });
    }


    // -- 7. Affichage d'une page du tableau --

    // Affiche les transactions de la page demandee
    function afficherPage(page) {
        var corps = document.getElementById('corps-historique');
        var debut = (page - 1) * NB_PAR_PAGE;
        var fin   = debut + NB_PAR_PAGE;
        var tranche = transactionsFiltrees.slice(debut, fin);

        corps.innerHTML = '';

        // Cas tableau vide apres filtrage
        if (tranche.length === 0) {
            corps.innerHTML =
                '<tr><td colspan="6">'
                + '<div class="etat-vide">'
                + '<span class="etat-vide-icone">'
                + '<i class="bi bi-search"></i></span>'
                + '<span class="etat-vide-texte">'
                + 'Aucune transaction ne correspond '
                + 'a vos filtres.</span>'
                + '</div></td></tr>';
            return;
        }

        // Construction des lignes du tableau
        tranche.forEach(function (t) {
            var estRevenu     = t.type === 'revenu';
            var classeMontant = estRevenu
                ? 'montant-revenu' : 'montant-depense';
            var signe         = estRevenu ? '+' : '-';
            var categoriesAff =
                formaterCategories(t.categorie);

            // Badge type (revenu / depense)
            var badgeType = estRevenu
                ? '<span class="badge-fintrack badge-revenu">'
                  + 'Revenu</span>'
                : '<span class="badge-fintrack badge-depense">'
                  + 'Depense</span>';

            // Formatage date
            var dateStr = new Date(t.date)
                .toLocaleDateString('fr-FR', {
                    day:   '2-digit',
                    month: '2-digit',
                    year:  'numeric'
                });

            // Formatage montant
            var montantStr = t.montant
                .toLocaleString('fr-FR', {
                    style:    'currency',
                    currency: 'EUR'
                });

            // Boutons d'action rapides (Mail, PDF, Detail, Suppr)
            var boutonsActions =
                '<div class="groupe-actions-historique">'
                + '<button class="btn-action-rapide btn-mail"'
                + ' title="Envoyer par mail"'
                + ' onclick="envoyerTrackParMail(' + t.id + ')">'
                + '<i class="bi bi-envelope"></i> Mail'
                + '</button>'
                + '<button class="btn-action-rapide btn-pdf"'
                + ' title="Telecharger PDF"'
                + ' onclick="telechargerTrackPDF(' + t.id + ')">'
                + '<i class="bi bi-file-pdf"></i> PDF'
                + '</button>'
                + '<button class="btn-action-rapide btn-detail"'
                + ' title="Voir detail"'
                + ' onclick="afficherDetailTrack(' + t.id + ')">'
                + '<i class="bi bi-info-circle"></i> Detail'
                + '</button>'
                + '<button class="btn-action-rapide btn-suppr"'
                + ' title="Supprimer"'
                + ' onclick="supprimerTrackHistorique('
                + t.id + ')">'
                + '<i class="bi bi-trash"></i> Suppr.'
                + '</button>'
                + '</div>';

            // Insertion de la ligne
            corps.insertAdjacentHTML('beforeend',
                '<tr>'
                + '<td>' + dateStr + '</td>'
                + '<td>' + t.description + '</td>'
                + '<td><span class="badge-fintrack '
                + 'badge-categorie">'
                + categoriesAff + '</span></td>'
                + '<td>' + badgeType + '</td>'
                + '<td class="col-montant ' + classeMontant
                + '">' + signe + montantStr + '</td>'
                + '<td>' + boutonsActions + '</td>'
                + '</tr>');
        });
    }


    // -- 8. Pagination --

    // Genere les boutons de pagination sous le tableau
    function afficherPagination() {
        var conteneur = document.getElementById(
            'pagination-historique'
        );
        var totalPages = Math.ceil(
            transactionsFiltrees.length / NB_PAR_PAGE
        );
        conteneur.innerHTML = '';

        if (totalPages <= 1) return;

        // Bouton precedent
        var btnPrec = document.createElement('button');
        btnPrec.className   = 'btn-page';
        btnPrec.textContent = '\u2190 Precedent';
        btnPrec.disabled    = pageActuelle === 1;
        btnPrec.addEventListener('click', function () {
            if (pageActuelle > 1) {
                pageActuelle--;
                afficherPage(pageActuelle);
                afficherPagination();
            }
        });
        conteneur.appendChild(btnPrec);

        // Boutons numerotes (max 5 pages visibles)
        var debutP = Math.max(1, pageActuelle - 2);
        var finP   = Math.min(totalPages, debutP + 4);

        for (var p = debutP; p <= finP; p++) {
            var btn = document.createElement('button');
            btn.className = 'btn-page'
                + (p === pageActuelle ? ' actif' : '');
            btn.textContent = p;
            var pageCible = p;
            btn.addEventListener('click',
                (function (cible) {
                    return function () {
                        pageActuelle = cible;
                        afficherPage(pageActuelle);
                        afficherPagination();
                    };
                })(pageCible)
            );
            conteneur.appendChild(btn);
        }

        // Bouton suivant
        var btnSuiv = document.createElement('button');
        btnSuiv.className   = 'btn-page';
        btnSuiv.textContent = 'Suivant \u2192';
        btnSuiv.disabled    = pageActuelle === totalPages;
        btnSuiv.addEventListener('click', function () {
            if (pageActuelle < totalPages) {
                pageActuelle++;
                afficherPage(pageActuelle);
                afficherPagination();
            }
        });
        conteneur.appendChild(btnSuiv);

        // Indicateur de position
        var indicateur = document.createElement('span');
        indicateur.style.cssText =
            'font-size: var(--taille-xs); '
            + 'color: var(--texte-secondaire); '
            + 'align-self: center;';
        indicateur.textContent =
            'Page ' + pageActuelle + ' / ' + totalPages;
        conteneur.appendChild(indicateur);
    }


    // -- 9. Mise a jour de la barre des totaux --

    // Calcule et affiche les totaux des transactions filtrees
    function mettreAJourTotaux() {
        var totalRevenus = transactionsFiltrees
            .filter(function (t) {
                return t.type === 'revenu';
            })
            .reduce(function (s, t) {
                return s + t.montant;
            }, 0);

        var totalDepenses = transactionsFiltrees
            .filter(function (t) {
                return t.type === 'depense';
            })
            .reduce(function (s, t) {
                return s + t.montant;
            }, 0);

        var solde = totalRevenus - totalDepenses;
        var opts  = { style: 'currency', currency: 'EUR' };

        document.getElementById('total-nb')
            .textContent = transactionsFiltrees.length;

        document.getElementById('total-revenus')
            .textContent =
                totalRevenus.toLocaleString('fr-FR', opts);

        document.getElementById('total-depenses')
            .textContent =
                totalDepenses.toLocaleString('fr-FR', opts);

        var elemSolde =
            document.getElementById('total-solde');
        elemSolde.textContent =
            solde.toLocaleString('fr-FR', opts);
        elemSolde.className = 'total-valeur '
            + (solde >= 0 ? 'texte-succes' : 'texte-danger');
    }


    // -- 10. Resume des filtres actifs (tags visuels) --

    // Affiche les badges des filtres actifs
    function mettreAJourResumeFiltres(
        recherche, type, categorie, periode
    ) {
        var zone =
            document.getElementById('resume-filtres');
        var tags =
            document.getElementById('tags-filtres-actifs');
        tags.innerHTML = '';

        var filtresActifs = [];

        if (recherche) {
            filtresActifs.push(
                'Recherche : "' + recherche + '"'
            );
        }
        if (type) {
            filtresActifs.push(
                'Type : '
                + (type === 'revenu' ? 'Revenu' : 'Depense')
            );
        }
        if (categorie) {
            filtresActifs.push(
                'Categorie : ' + categorie
            );
        }
        if (periode) {
            filtresActifs.push(
                'Periode : ' + periode + ' jours'
            );
        }

        if (filtresActifs.length > 0) {
            filtresActifs.forEach(function (f) {
                tags.insertAdjacentHTML('beforeend',
                    '<span class="tag-filtre-actif">'
                    + '<i class="bi bi-tag-fill"></i> '
                    + f + '</span>');
            });
            zone.style.display = 'flex';
        } else {
            zone.style.display = 'none';
        }
    }


    // -- 11. Mise a jour des icones de tri --

    // Met a jour les icones des colonnes triables
    function mettreAJourIconesTri() {
        document.querySelectorAll('.triable')
            .forEach(function (th) {
                var colonne =
                    th.getAttribute('data-colonne');
                var icone = th.querySelector('.icone-tri');

                th.classList.remove('tri-actif');
                if (icone) {
                    icone.className =
                        'bi bi-arrow-down-up icone-tri';
                }

                if (colonne === colonneTriActive) {
                    th.classList.add('tri-actif');
                    if (icone) {
                        icone.className =
                            (sensTriActif === 'asc')
                                ? 'bi bi-sort-up icone-tri'
                                : 'bi bi-sort-down icone-tri';
                    }
                }
            });
    }


    // -- 12. Reinitialisation complete des filtres --

    // Remet tous les filtres a leur valeur par defaut
    function reinitialiserFiltres() {
        document.getElementById(
            'filtre-recherche').value    = '';
        document.getElementById(
            'filtre-type').value         = '';
        document.getElementById(
            'filtre-categorie').value    = '';
        document.getElementById(
            'filtre-periode-hist').value = '180';

        colonneTriActive = 'date';
        sensTriActif     = 'desc';
        pageActuelle     = 1;

        mettreAJourIconesTri();
        appliquerFiltres();
    }


    // -- 13. Actions rapides (Mail, PDF, Detail, Supprimer) --

    // Recupere une transaction par son ID
    function trouverTrack(id) {
        return transactions.find(
            function (t) { return t.id === id; }
        );
    }

    // Formate un montant en EUR
    function formatMontant(montant) {
        return montant.toLocaleString('fr-FR', {
            style: 'currency', currency: 'EUR'
        });
    }

    // Ouvre la modale de detail d'un track
    function afficherDetailTrack(id) {
        var track = trouverTrack(id);
        if (!track) return alert('Track non trouve');

        var dateStr = new Date(track.date)
            .toLocaleDateString('fr-FR', {
                year:  'numeric',
                month: 'long',
                day:   'numeric'
            });

        var catStr = Array.isArray(track.categorie)
            ? track.categorie.join(', ')
            : track.categorie;

        // Badge de type
        var badgeClass = (track.type === 'revenu')
            ? 'badge-fintrack badge-revenu'
            : 'badge-fintrack badge-depense';
        var badgeLabel = (track.type === 'revenu')
            ? 'Revenu' : 'Depense';

        // Classe du montant
        var montantClass = (track.type === 'revenu')
            ? 'montant-revenu' : 'montant-depense';
        var montantSigne = (track.type === 'revenu')
            ? '+' : '-';

        // Note optionnelle
        var noteHtml = track.note
            ? '<div class="detail-item">'
              + '<strong>Note :</strong> '
              + track.note + '</div>'
            : '';

        var html =
            '<div class="modal-backdrop fade show"'
            + ' style="display:block;"></div>'
            + '<div class="modal fade show"'
            + ' id="modal-detail-' + id + '"'
            + ' style="display:block;" tabindex="-1">'
            + '<div class="modal-dialog '
            + 'modal-dialog-centered">'
            + '<div class="modal-content">'
            + '<div class="modal-header">'
            + '<h5 class="modal-title">'
            + 'Details du track</h5>'
            + '<button type="button" class="btn-close"'
            + ' onclick="fermerModal(\'modal-detail-'
            + id + '\')"></button></div>'
            + '<div class="modal-body">'
            + '<div class="detail-item">'
            + '<strong>Date :</strong> '
            + dateStr + '</div>'
            + '<div class="detail-item">'
            + '<strong>Description :</strong> '
            + track.description + '</div>'
            + '<div class="detail-item">'
            + '<strong>Categorie(s) :</strong> '
            + catStr + '</div>'
            + '<div class="detail-item">'
            + '<strong>Type :</strong> '
            + '<span class="badge ' + badgeClass + '">'
            + badgeLabel + '</span></div>'
            + '<div class="detail-item">'
            + '<strong>Montant :</strong> '
            + '<span class="montant-detail '
            + montantClass + '">'
            + montantSigne
            + formatMontant(track.montant)
            + '</span></div>'
            + noteHtml
            + '</div>'
            + '<div class="modal-footer">'
            + '<button type="button"'
            + ' class="btn-fintrack btn-outline"'
            + ' onclick="fermerModal(\'modal-detail-'
            + id + '\')">Fermer</button>'
            + '<button type="button"'
            + ' class="btn-fintrack btn-primaire"'
            + ' onclick="envoyerTrackParMail(' + id + ')">'
            + '<i class="bi bi-envelope"></i>'
            + ' Envoyer par mail</button>'
            + '</div></div></div></div>';

        var modal = document.createElement('div');
        modal.innerHTML = html;
        document.body.appendChild(modal);
    }

    // Ferme une modale par son identifiant
    function fermerModal(modalId) {
        var modal =
            document.getElementById(modalId);
        if (modal && modal.parentElement) {
            modal.parentElement.remove();
        }
    }

    // Envoie un track par mail (PDF via PHPMailer)
    function envoyerTrackParMail(id) {
        var track = trouverTrack(id);
        if (!track) return alert('Track non trouve');

        var email = prompt(
            'Entrez votre adresse email :', ''
        );
        if (!email) return;

        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            return alert('Email invalide');
        }

        // Genere le PDF en base64 sans telecharger
        var base64 = genererTrackPDFBase64(track);
        if (!base64) {
            return alert(
                'Erreur de generation du PDF.'
            );
        }

        // Envoie via pdf.js vers php/envoyer_pdf.php
        envoyerPDFParEmail(
            base64, email, 'fintrack_track.pdf',
            function (rep) {
                if (rep.succes) {
                    alert('Email envoye avec succes !');
                } else {
                    alert('Erreur : '
                        + (rep.message
                            || 'Envoi echoue.'));
                }
            }
        );
    }

    // Telecharge un track en PDF (via pdf.js)
    function telechargerTrackPDF(id) {
        var track = trouverTrack(id);
        if (!track) return alert('Track non trouve');
        exporterTrackPDF(track);
    }

    // Ouvre la modale de confirmation avant suppression
    function supprimerTrackHistorique(id) {
        idSuppressionEnCours = id;
        var modale = new bootstrap.Modal(
            document.getElementById(
                'modal-confirmer-suppression'
            )
        );
        modale.show();
    }
    </script>

</body>
</html>
