<!--
=============================================================
  FICHIER      : rapport.php
  AUTEUR       : Maroua -- Developpeuse PDF & Documentation
  PROJET       : FinTrack -- ECAM-EPMI 2025-2026
  DESCRIPTION  : Page de generation du rapport financier.
                 L'utilisateur choisit une periode (jour,
                 mois, annee ou personnalisee), previsualise
                 les statistiques, puis telecharge le PDF
                 complet via jsPDF (defini dans pdf.js).
  DATE         : Mars 2026
=============================================================
-->
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Rapport financier -- FinTrack</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Feuilles de styles FinTrack -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Styles specifiques a la page rapport -->
    <style>
        /* -- Selecteur de periode -- */
        .selecteur-periode {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-xl);
            padding:       1.75rem 2rem;
            box-shadow:    var(--ombre-carte);
            margin-bottom: 2rem;
        }

        /* -- Champs du selecteur : empiles sur mobile -- */
        .champs-periode {
            display:     flex;
            gap:         1rem;
            align-items: flex-end;
            flex-wrap:   wrap;
        }
        @media (max-width: 575.98px) {
            .champs-periode {
                flex-direction: column;
                align-items:    stretch;
            }
        }

        /* -- Previsualisation du rapport -- */
        .apercu-rapport {
            background:    var(--bg-carte);
            border:        2px dashed var(--couleur-primaire);
            border-radius: var(--arrondi-xl);
            padding:       2rem;
            margin-bottom: 2rem;
            position:      relative;
        }
        .apercu-rapport .watermark-pdf {
            position:       absolute;
            top:            1.25rem;
            right:          1.5rem;
            font-size:      var(--taille-xs);
            font-weight:    700;
            color:          var(--couleur-primaire);
            opacity:        0.5;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* -- En-tete du rapport simulee -- */
        .rapport-header-preview {
            background:      var(--couleur-primaire);
            border-radius:   var(--arrondi-lg);
            padding:         1.5rem;
            color:           white;
            margin-bottom:   1.5rem;
            display:         flex;
            justify-content: space-between;
            align-items:     center;
        }
        .rapport-header-preview .rh-titre {
            font-size:   var(--taille-xl);
            font-weight: 800;
        }
        .rapport-header-preview .rh-meta {
            font-size:  var(--taille-xs);
            opacity:    0.8;
            text-align: right;
        }

        /* -- Grille statistiques de previsualisation -- */
        .grille-apercu-stats {
            display: grid;
            grid-template-columns:
                repeat(auto-fit, minmax(150px, 1fr));
            gap:           1rem;
            margin-bottom: 1.5rem;
        }
        .apercu-stat {
            background:    var(--bg-corps);
            border-radius: var(--arrondi-lg);
            padding:       1rem;
            text-align:    center;
        }
        .apercu-stat .as-label {
            font-size:     var(--taille-xs);
            color:         var(--texte-secondaire);
            font-weight:   500;
            margin-bottom: 0.3rem;
        }
        .apercu-stat .as-valeur {
            font-size:   var(--taille-xl);
            font-weight: 800;
        }

        /* -- Zone de telechargement -- */
        .zone-telechargement {
            text-align:    center;
            padding:       1.5rem;
            background:    var(--couleur-primaire-clair);
            border-radius: var(--arrondi-xl);
        }

        /* -- Historique rapports (colonne laterale) -- */
        .liste-rapports-precedents {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-xl);
            overflow:      hidden;
            box-shadow:    var(--ombre-carte);
        }
        .rapport-ligne {
            display:         flex;
            align-items:     center;
            justify-content: space-between;
            padding:         1rem 1.5rem;
            border-bottom:   1px solid var(--bordure-couleur);
            transition:      background var(--transition-rapide);
        }
        .rapport-ligne:last-child { border-bottom: none; }
        .rapport-ligne:hover {
            background: var(--bg-tableau-hover);
        }
        .rapport-ligne .rl-icone {
            font-size:    1.5rem;
            margin-right: 0.75rem;
        }
        .rapport-ligne .rl-info .rl-titre {
            font-weight: 600;
            font-size:   var(--taille-sm);
            color:       var(--texte-principal);
        }
        .rapport-ligne .rl-info .rl-meta {
            font-size: var(--taille-xs);
            color:     var(--texte-secondaire);
        }
    </style>
    <link rel="icon"
          type="image/x-icon"
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


    <!-- ==========================================
         Contenu principal de la page
    ========================================== -->
    <main class="page-wrapper">
        <div class="container">

            <!-- En-tete de page -->
            <div class="page-header">
                <h1 class="page-title">
                    <span class="titre-icone">
                        <i class="bi bi-file-earmark-pdf"></i>
                    </span>
                    Rapport financier
                </h1>
                <p class="page-subtitle">
                    Choisissez une periode, previsualisez
                    et telechargez votre rapport PDF complet
                </p>
            </div>

            <div class="row g-4">

                <!-- Colonne principale -->
                <div class="col-12 col-lg-8">

                    <!-- ===== Selecteur de periode ===== -->
                    <div class="selecteur-periode">
                        <h3 style="font-size: var(--taille-lg);
                                   font-weight: 700;
                                   margin-bottom: 1.25rem;">
                            <i class="bi bi-calendar-range me-2"
                               style="color:var(--couleur-primaire);">
                            </i>
                            Choisir la periode du rapport
                        </h3>

                        <!-- Choix du type de periode -->
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-sm-3">
                                <input type="radio"
                                       class="btn-check"
                                       name="typePeriode"
                                       id="type-jour"
                                       value="jour"
                                       autocomplete="off">
                                <label class="btn btn-outline-primary w-100"
                                       for="type-jour">
                                    <i class="bi bi-calendar-day me-1"></i>
                                    Jour
                                </label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio"
                                       class="btn-check"
                                       name="typePeriode"
                                       id="type-mois"
                                       value="mois"
                                       autocomplete="off"
                                       checked>
                                <label class="btn btn-outline-primary w-100"
                                       for="type-mois">
                                    <i class="bi bi-calendar-month me-1"></i>
                                    Mois
                                </label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio"
                                       class="btn-check"
                                       name="typePeriode"
                                       id="type-annee"
                                       value="annee"
                                       autocomplete="off">
                                <label class="btn btn-outline-primary w-100"
                                       for="type-annee">
                                    <i class="bi bi-calendar me-1"></i>
                                    Annee
                                </label>
                            </div>
                            <div class="col-6 col-sm-3">
                                <input type="radio"
                                       class="btn-check"
                                       name="typePeriode"
                                       id="type-perso"
                                       value="personnalise"
                                       autocomplete="off">
                                <label class="btn btn-outline-primary w-100"
                                       for="type-perso">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    Personnalise
                                </label>
                            </div>
                        </div>

                        <!-- Champs dynamiques selon le type -->
                        <div class="champs-periode"
                             id="champs-periode">

                            <!-- Jour : un seul champ date -->
                            <div class="champ-jour d-none"
                                 id="champ-jour"
                                 style="flex:1; min-width:180px;">
                                <label class="form-label-fintrack"
                                       for="input-date-jour">
                                    Date
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="input-date-jour">
                            </div>

                            <!-- Mois : mois + annee (par defaut) -->
                            <div class="champ-mois"
                                 id="champ-mois"
                                 style="flex:1; min-width:160px;">
                                <label class="form-label-fintrack"
                                       for="select-mois-rapport">
                                    Mois
                                </label>
                                <select class="form-select-fintrack"
                                        id="select-mois-rapport">
                                    <option value="0">Janvier</option>
                                    <option value="1">Fevrier</option>
                                    <option value="2">Mars</option>
                                    <option value="3">Avril</option>
                                    <option value="4">Mai</option>
                                    <option value="5">Juin</option>
                                    <option value="6">Juillet</option>
                                    <option value="7">Aout</option>
                                    <option value="8">Septembre</option>
                                    <option value="9">Octobre</option>
                                    <option value="10">Novembre</option>
                                    <option value="11">Decembre</option>
                                </select>
                            </div>
                            <div class="champ-mois"
                                 id="champ-mois-annee"
                                 style="min-width:120px;">
                                <label class="form-label-fintrack"
                                       for="select-annee-rapport">
                                    Annee
                                </label>
                                <select class="form-select-fintrack"
                                        id="select-annee-rapport">
                                    <option value="2025">2025</option>
                                    <option value="2026" selected>
                                        2026
                                    </option>
                                </select>
                            </div>

                            <!-- Annee seule -->
                            <div class="champ-annee-seule d-none"
                                 id="champ-annee-seule"
                                 style="min-width:120px;">
                                <label class="form-label-fintrack"
                                       for="select-annee-seule">
                                    Annee
                                </label>
                                <select class="form-select-fintrack"
                                        id="select-annee-seule">
                                    <option value="2025">2025</option>
                                    <option value="2026" selected>
                                        2026
                                    </option>
                                </select>
                            </div>

                            <!-- Personnalise : du ... au ... -->
                            <div class="champ-perso d-none"
                                 id="champ-perso-debut"
                                 style="flex:1; min-width:160px;">
                                <label class="form-label-fintrack"
                                       for="input-date-debut">
                                    Du
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="input-date-debut">
                            </div>
                            <div class="champ-perso d-none"
                                 id="champ-perso-fin"
                                 style="flex:1; min-width:160px;">
                                <label class="form-label-fintrack"
                                       for="input-date-fin">
                                    Au
                                </label>
                                <input type="date"
                                       class="form-control"
                                       id="input-date-fin">
                            </div>

                            <!-- Bouton previsualiser -->
                            <div style="min-width:140px;">
                                <label class="form-label-fintrack"
                                       style="opacity:0;">
                                    Action
                                </label>
                                <button class="btn-fintrack btn-outline"
                                        id="btn-previsualiser"
                                        type="button">
                                    <i class="bi bi-eye"></i>
                                    Previsualiser
                                </button>
                            </div>
                        </div>
                    </div>


                    <!-- ===== Previsualisation du rapport ===== -->
                    <div class="apercu-rapport"
                         id="apercu-rapport">
                        <span class="watermark-pdf">
                            <i class="bi bi-file-earmark"></i>
                            Apercu PDF
                        </span>

                        <!-- En-tete simulee du PDF -->
                        <div class="rapport-header-preview">
                            <div>
                                <div style="font-size:var(--taille-sm);
                                            opacity:0.8;
                                            margin-bottom:0.2rem;">
                                    FinTrack
                                </div>
                                <div class="rh-titre"
                                     id="titre-apercu-rapport">
                                    Rapport financier
                                </div>
                            </div>
                            <div class="rh-meta">
                                ECAM-EPMI<br>2025-2026
                            </div>
                        </div>

                        <!-- Statistiques de previsualisation -->
                        <div class="grille-apercu-stats"
                             id="grille-apercu-stats">
                            <!-- Rempli dynamiquement par JS -->
                        </div>

                        <!-- Apercu des categories -->
                        <div id="apercu-categories">
                            <!-- Rempli dynamiquement par JS -->
                        </div>

                        <!-- Zone de telechargement -->
                        <div class="zone-telechargement">
                            <p style="font-weight:600;
                                      color:var(--couleur-primaire);
                                      margin-bottom:0.75rem;
                                      font-size:var(--taille-sm);">
                                <i class="bi bi-file-earmark-pdf me-2"></i>
                                Rapport pret a generer
                            </p>
                            <button class="btn-fintrack btn-primaire btn-lg"
                                    id="btn-export-rapport-pdf"
                                    type="button">
                                <i class="bi bi-download"></i>
                                Telecharger le rapport PDF
                            </button>
                            <p style="font-size:var(--taille-xs);
                                      color:var(--texte-secondaire);
                                      margin-top:0.75rem;
                                      margin-bottom:0;">
                                Format A4 -- toutes les transactions
                                de la periode -- bilan complet
                            </p>
                        </div>
                    </div>

                </div>


                <!-- Colonne droite : infos + rapports recents -->
                <div class="col-12 col-lg-4">

                    <!-- Informations sur le contenu du rapport -->
                    <div class="carte mb-4">
                        <div class="carte-header">
                            <h3 class="carte-titre">
                                <i class="bi bi-info-circle me-2"></i>
                                Contenu du rapport
                            </h3>
                        </div>
                        <ul style="list-style:none;
                                   padding:0; margin:0;
                                   font-size:var(--taille-sm);">
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                En-tete FinTrack avec logo
                            </li>
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                3 indicateurs cles
                                (revenus, depenses, solde)
                            </li>
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                Tableau des depenses par categorie
                            </li>
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                Barres de progression par categorie
                            </li>
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                Liste complete des transactions
                            </li>
                            <li style="padding:0.5rem 0;
                                       border-bottom:1px solid
                                           var(--bordure-couleur);
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                Ligne de totaux en bas
                            </li>
                            <li style="padding:0.5rem 0;
                                       display:flex; gap:0.5rem;
                                       color:var(--texte-secondaire);">
                                <i class="bi bi-check-lg"
                                   style="color:var(--couleur-succes);"></i>
                                Pagination automatique multi-pages
                            </li>
                        </ul>
                    </div>

                    <!-- Rapports des mois precedents -->
                    <div>
                        <h3 style="font-size:var(--taille-md);
                                   font-weight:700;
                                   margin-bottom:1rem;
                                   color:var(--texte-secondaire);">
                            <i class="bi bi-clock-history me-2"></i>
                            Rapports recents
                        </h3>
                        <div class="liste-rapports-precedents">
                            <div id="liste-rapports-prec"></div>
                        </div>
                    </div>

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
                    Projet Web -- ECAM-EPMI Cergy -- 2025-2026
                    -- Rapport genere par Maroua via jsPDF
                </p>
            </div>
        </div>
    </footer>


    <!-- Bibliotheques externes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- jsPDF (CDN) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <!-- Scripts FinTrack -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/data.js"></script>
    <script src="assets/js/pdf.js"></script>

    <!-- Script de la page rapport -->
    <script>
    // ============================================================
    // Script rapport : selecteur flexible de periode,
    // previsualisation et liste des rapports recents.
    // Maroua -- Liaison avec pdf.js pour le telechargement.
    // ============================================================

    document.addEventListener('DOMContentLoaded', function () {

        // -- Valeurs par defaut des champs date --
        const aujourdHui = new Date().toISOString().split('T')[0];
        document.getElementById('input-date-jour').value =
            aujourdHui;
        document.getElementById('input-date-debut').value =
            aujourdHui;
        document.getElementById('input-date-fin').value =
            aujourdHui;

        // -- Selectionner le mois courant dans le <select> --
        const moisActuel = new Date().getMonth();
        document.getElementById('select-mois-rapport').value =
            moisActuel;

        // -- Ecoute du changement de type de periode --
        document.querySelectorAll('input[name="typePeriode"]')
            .forEach(function (radio) {
                radio.addEventListener(
                    'change', basculerChampsPeriode
                );
            });

        // -- Bouton previsualiser --
        document.getElementById('btn-previsualiser')
            .addEventListener('click', mettreAJourApercu);

        // -- Mise a jour auto quand les selects changent --
        document.getElementById('select-mois-rapport')
            .addEventListener('change', mettreAJourApercu);
        document.getElementById('select-annee-rapport')
            .addEventListener('change', mettreAJourApercu);
        document.getElementById('select-annee-seule')
            .addEventListener('change', mettreAJourApercu);
        document.getElementById('input-date-jour')
            .addEventListener('change', mettreAJourApercu);
        document.getElementById('input-date-debut')
            .addEventListener('change', mettreAJourApercu);
        document.getElementById('input-date-fin')
            .addEventListener('change', mettreAJourApercu);

        // -- Bouton PDF --
        document.getElementById('btn-export-rapport-pdf')
            .addEventListener('click', telechargerRapportPeriode);

        // -- Premiere previsualisation --
        mettreAJourApercu();

        // -- Rapports recents (colonne laterale) --
        genererListeRapportsRecents();
    });


    // ==========================================================
    // Affiche/masque les champs selon le type de periode choisi
    // ==========================================================
    function basculerChampsPeriode() {
        const type = getTypePeriode();

        // Recupere tous les groupes de champs
        const jour   = document.getElementById('champ-jour');
        const mois   = document.getElementById('champ-mois');
        const moisAn = document.getElementById('champ-mois-annee');
        const annee  = document.getElementById('champ-annee-seule');
        const debP   = document.getElementById('champ-perso-debut');
        const finP   = document.getElementById('champ-perso-fin');

        // Tout masquer d'abord
        [jour, mois, moisAn, annee, debP, finP].forEach(
            function (el) { el.classList.add('d-none'); }
        );

        // Afficher selon le type
        if (type === 'jour') {
            jour.classList.remove('d-none');
        } else if (type === 'mois') {
            mois.classList.remove('d-none');
            moisAn.classList.remove('d-none');
        } else if (type === 'annee') {
            annee.classList.remove('d-none');
        } else if (type === 'personnalise') {
            debP.classList.remove('d-none');
            finP.classList.remove('d-none');
        }

        // Rafraichir l'apercu apres bascule
        mettreAJourApercu();
    }


    // ==========================================================
    // Retourne le type de periode selectionne
    // ==========================================================
    function getTypePeriode() {
        const coche = document.querySelector(
            'input[name="typePeriode"]:checked'
        );
        return coche ? coche.value : 'mois';
    }


    // ==========================================================
    // Retourne les transactions filtrees selon la periode
    // choisie. Parametres lus directement depuis le DOM.
    // ==========================================================
    function getTransactionsForPeriod() {
        const type  = getTypePeriode();
        const tous  = getTousTracks();

        if (type === 'jour') {
            // Filtre sur un seul jour
            const val = document.getElementById(
                'input-date-jour'
            ).value;
            if (!val) return [];
            return tous.filter(function (t) {
                return t.date === val;
            });

        } else if (type === 'mois') {
            // Filtre mois + annee
            const m = parseInt(
                document.getElementById(
                    'select-mois-rapport'
                ).value
            );
            const a = parseInt(
                document.getElementById(
                    'select-annee-rapport'
                ).value
            );
            return getTransactionsParMois(m, a);

        } else if (type === 'annee') {
            // Filtre sur l'annee entiere
            const a = parseInt(
                document.getElementById(
                    'select-annee-seule'
                ).value
            );
            return tous.filter(function (t) {
                return new Date(t.date).getFullYear() === a;
            });

        } else if (type === 'personnalise') {
            // Filtre entre deux dates (bornes incluses)
            const deb = document.getElementById(
                'input-date-debut'
            ).value;
            const fin = document.getElementById(
                'input-date-fin'
            ).value;
            if (!deb || !fin) return [];
            return tous.filter(function (t) {
                return t.date >= deb && t.date <= fin;
            });
        }

        return [];
    }


    // ==========================================================
    // Construit le libelle de la periode pour le titre
    // ==========================================================
    function getLibellePeriode() {
        const type = getTypePeriode();

        if (type === 'jour') {
            const v = document.getElementById(
                'input-date-jour'
            ).value;
            if (!v) return 'Aucune date';
            // Formate en "04 avril 2026"
            const d = new Date(v + 'T00:00:00');
            return d.toLocaleDateString('fr-FR', {
                day: 'numeric', month: 'long', year: 'numeric'
            });

        } else if (type === 'mois') {
            const m = parseInt(
                document.getElementById(
                    'select-mois-rapport'
                ).value
            );
            const a = document.getElementById(
                'select-annee-rapport'
            ).value;
            return getNomMois(m) + ' ' + a;

        } else if (type === 'annee') {
            return 'Annee ' +
                document.getElementById(
                    'select-annee-seule'
                ).value;

        } else if (type === 'personnalise') {
            const deb = document.getElementById(
                'input-date-debut'
            ).value;
            const fin = document.getElementById(
                'input-date-fin'
            ).value;
            if (!deb || !fin) return 'Periode incomplete';
            // Format court JJ/MM/AAAA
            const fD = deb.split('-').reverse().join('/');
            const fF = fin.split('-').reverse().join('/');
            return 'Du ' + fD + ' au ' + fF;
        }

        return '';
    }


    // ==========================================================
    // Met a jour la zone de previsualisation selon la periode
    // ==========================================================
    function mettreAJourApercu() {
        const libelle = getLibellePeriode();
        const txPeriode = getTransactionsForPeriod();
        const stats  = calculerSolde(txPeriode);
        const depCat = calculerDepensesParCategorie(txPeriode);
        const opts   = { style: 'currency', currency: 'EUR' };

        // -- Titre de l'apercu --
        const titreApercu =
            document.getElementById('titre-apercu-rapport');
        if (titreApercu) {
            titreApercu.textContent =
                'Rapport financier -- ' + libelle;
        }

        // -- Indicateurs statistiques --
        const grille =
            document.getElementById('grille-apercu-stats');
        if (grille) {
            grille.innerHTML = ''
                + '<div class="apercu-stat">'
                +   '<div class="as-label">Revenus</div>'
                +   '<div class="as-valeur texte-succes">'
                +     stats.revenus.toLocaleString('fr-FR', opts)
                +   '</div>'
                + '</div>'
                + '<div class="apercu-stat">'
                +   '<div class="as-label">Depenses</div>'
                +   '<div class="as-valeur texte-danger">'
                +     stats.depenses.toLocaleString('fr-FR', opts)
                +   '</div>'
                + '</div>'
                + '<div class="apercu-stat">'
                +   '<div class="as-label">Solde net</div>'
                +   '<div class="as-valeur '
                +     (stats.solde >= 0
                        ? 'texte-succes' : 'texte-danger')
                +   '">'
                +     stats.solde.toLocaleString('fr-FR', opts)
                +   '</div>'
                + '</div>'
                + '<div class="apercu-stat">'
                +   '<div class="as-label">Transactions</div>'
                +   '<div class="as-valeur"'
                +   ' style="color:var(--couleur-primaire);">'
                +     txPeriode.length
                +   '</div>'
                + '</div>';
        }

        // -- Top 3 categories de depenses --
        const categoriesApercu =
            document.getElementById('apercu-categories');
        if (!categoriesApercu) return;

        const top3 = Object.entries(depCat)
            .sort(function (a, b) { return b[1] - a[1]; })
            .slice(0, 3);

        if (top3.length === 0) {
            categoriesApercu.innerHTML =
                '<div class="etat-vide" style="padding:1rem;">'
                + '<span class="etat-vide-texte">'
                + 'Aucune depense pour cette periode.'
                + '</span></div>';
            return;
        }

        var html =
            '<div style="margin-bottom:1.25rem;">'
            + '<div style="font-size:var(--taille-xs);'
            + 'font-weight:700;color:var(--texte-secondaire);'
            + 'text-transform:uppercase;letter-spacing:0.5px;'
            + 'margin-bottom:0.75rem;">'
            + 'Top 3 depenses de la periode'
            + '</div>';

        top3.forEach(function (entry) {
            var cat = entry[0];
            var montant = entry[1];
            html +=
                '<div style="display:flex;'
                + 'justify-content:space-between;'
                + 'align-items:center;padding:0.5rem 0;'
                + 'border-bottom:1px solid '
                + 'var(--bordure-couleur);'
                + 'font-size:var(--taille-sm);">'
                + '<span style="color:var(--texte-principal);'
                + 'font-weight:500;">'
                + '<span class="badge-fintrack '
                + 'badge-categorie">'
                + cat + '</span></span>'
                + '<span style="font-weight:700;'
                + 'color:var(--couleur-danger);">'
                + montant.toLocaleString('fr-FR', opts)
                + '</span></div>';
        });

        html += '</div>';
        categoriesApercu.innerHTML = html;
    }


    // ==========================================================
    // Telecharge le PDF selon la periode selectionnee.
    // On repose sur genererRapportMensuelPDF(mois, annee)
    // qui existe dans pdf.js. Pour les periodes non-mensuelles,
    // on passe le mois du debut de la periode.
    // ==========================================================
    function telechargerRapportPeriode() {
        var type = getTypePeriode();

        if (type === 'mois') {
            // Cas nominal : mois + annee
            var m = parseInt(
                document.getElementById(
                    'select-mois-rapport'
                ).value
            );
            var a = parseInt(
                document.getElementById(
                    'select-annee-rapport'
                ).value
            );
            genererRapportMensuelPDF(m, a);

        } else if (type === 'jour') {
            // Telecharge le rapport du mois contenant ce jour
            var val = document.getElementById(
                'input-date-jour'
            ).value;
            if (!val) return;
            var d = new Date(val + 'T00:00:00');
            genererRapportMensuelPDF(
                d.getMonth(), d.getFullYear()
            );

        } else if (type === 'annee') {
            // Telecharge le rapport du mois courant de l'annee
            var aS = parseInt(
                document.getElementById(
                    'select-annee-seule'
                ).value
            );
            // On genere pour decembre de l'annee choisie
            genererRapportMensuelPDF(11, aS);

        } else if (type === 'personnalise') {
            // Telecharge le rapport du mois de la date debut
            var deb = document.getElementById(
                'input-date-debut'
            ).value;
            if (!deb) return;
            var dd = new Date(deb + 'T00:00:00');
            genererRapportMensuelPDF(
                dd.getMonth(), dd.getFullYear()
            );
        }
    }


    // ==========================================================
    // Telecharge le rapport PDF d'un mois specifique
    // (appele depuis les boutons de la liste laterale)
    // ==========================================================
    function telechargerRapportMois(mois, annee) {
        genererRapportMensuelPDF(mois, annee);
    }


    // ==========================================================
    // Genere la liste des rapports des 6 derniers mois
    // dans la colonne laterale droite.
    // ==========================================================
    function genererListeRapportsRecents() {
        var liste =
            document.getElementById('liste-rapports-prec');
        if (!liste) return;

        var maintenant = new Date();
        liste.innerHTML = '';

        // Cree une entree pour chacun des 6 derniers mois
        for (var i = 0; i < 6; i++) {
            var date = new Date(
                maintenant.getFullYear(),
                maintenant.getMonth() - i,
                1
            );
            var mois    = date.getMonth();
            var annee   = date.getFullYear();
            var nomMois = getNomMois(mois);

            // Stats du mois pour l'apercu lateral
            var txMois = getTransactionsParMois(mois, annee);
            var stats  = calculerSolde(txMois);

            // Couleur du solde (vert ou rouge)
            var couleurSolde = stats.solde >= 0
                ? 'var(--couleur-succes)'
                : 'var(--couleur-danger)';
            var soldeStr = stats.solde.toLocaleString(
                'fr-FR',
                { style: 'currency', currency: 'EUR' }
            );

            liste.insertAdjacentHTML('beforeend', ''
                + '<div class="rapport-ligne">'
                +   '<div style="display:flex;'
                +   'align-items:center;">'
                +     '<span class="rl-icone">'
                +       '<i class="bi bi-file-earmark"></i>'
                +     '</span>'
                +     '<div class="rl-info">'
                +       '<div class="rl-titre">'
                +         nomMois + ' ' + annee
                +       '</div>'
                +       '<div class="rl-meta">'
                +         txMois.length + ' transactions'
                +       '</div>'
                +     '</div>'
                +   '</div>'
                +   '<div style="display:flex;'
                +   'align-items:center;gap:0.75rem;">'
                +     '<span style="font-weight:700;'
                +     'font-size:var(--taille-sm);'
                +     'color:' + couleurSolde + ';">'
                +       soldeStr
                +     '</span>'
                +     '<button class="btn-fintrack btn-outline"'
                +     ' style="padding:0.25rem 0.6rem;'
                +     'font-size:0.7rem;"'
                +     ' onclick="telechargerRapportMois('
                +       mois + ',' + annee
                +     ')"'
                +     ' title="Telecharger ' + nomMois
                +       ' ' + annee + '">'
                +       '<i class="bi bi-download"></i>'
                +     '</button>'
                +   '</div>'
                + '</div>');
        }
    }
    </script>

</body>
</html>
