<!--
=============================================================
  FICHIER      : conseils.php
  AUTEUR       : Benoit — Developpeur Data & Visualisation
  PROJET       : FinTrack — ECAM-EPMI 2025-2026
  DESCRIPTION  : Page de conseils financiers dynamiques.
                 Analyse les donnees localStorage (data.js)
                 et genere des recommandations personnalisees
                 avec un systeme de score strict, des conseils
                 varies (non generiques) et un filtre par
                 periode (semaine, mois, annee, personnalise).
  DATE         : Avril 2026
=============================================================
-->
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Conseils financiers — FinTrack</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">

    <!-- Styles FinTrack -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Styles specifiques a la page conseils -->
    <style>
        /* -- Barre de filtres par periode -- */
        .filtre-periode {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .filtre-periode .filtre-btn {
            padding: 0.45rem 1.1rem;
            border-radius: var(--arrondi-full);
            border: 1px solid var(--bordure-couleur);
            background: var(--bg-carte);
            color: var(--texte-secondaire);
            font-size: var(--taille-sm);
            font-weight: 600;
            cursor: pointer;
            transition: all var(--transition-normale);
        }

        .filtre-periode .filtre-btn:hover {
            border-color: var(--couleur-primaire);
            color: var(--couleur-primaire);
        }

        .filtre-periode .filtre-btn.actif {
            background: var(--couleur-primaire);
            color: white;
            border-color: var(--couleur-primaire);
        }

        /* -- Dates personnalisees -- */
        .dates-personnalisees {
            display: none;
            gap: 0.75rem;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }

        .dates-personnalisees.visible {
            display: flex;
        }

        .dates-personnalisees label {
            font-size: var(--taille-sm);
            font-weight: 600;
            color: var(--texte-secondaire);
        }

        .dates-personnalisees input[type="date"] {
            padding: 0.35rem 0.75rem;
            border-radius: var(--arrondi-lg);
            border: 1px solid var(--bordure-couleur);
            background: var(--bg-carte);
            color: var(--texte-principal);
            font-size: var(--taille-sm);
        }

        /* -- Score financier global -- */
        .carte-score {
            background: linear-gradient(
                135deg,
                var(--couleur-primaire),
                #7c3aed
            );
            border-radius: var(--arrondi-2xl);
            padding:       2.5rem 2rem;
            color:         white;
            text-align:    center;
            margin-bottom: 2rem;
            position:      relative;
            overflow:      hidden;
        }

        .carte-score::before {
            content:       '';
            position:      absolute;
            top:           -40px;
            right:         -40px;
            width:         160px;
            height:        160px;
            background:    rgba(255,255,255,0.07);
            border-radius: 50%;
        }

        .carte-score .score-chiffre {
            font-size:   4rem;
            font-weight: 800;
            line-height: 1;
        }

        .carte-score .score-label {
            font-size:   1.1rem;
            opacity:     0.85;
            margin-top:  0.4rem;
        }

        .carte-score .score-barre-fond {
            background:    rgba(255,255,255,0.25);
            border-radius: var(--arrondi-full);
            height:        10px;
            margin:        1.25rem auto 0;
            max-width:     320px;
            overflow:      hidden;
        }

        .carte-score .score-barre-remplie {
            background:    white;
            height:        100%;
            border-radius: var(--arrondi-full);
            transition:    width 1s ease;
        }

        /* -- Carte d'un conseil individuel -- */
        .carte-conseil {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-xl);
            padding:       1.4rem 1.5rem;
            box-shadow:    var(--ombre-carte);
            display:       flex;
            gap:           1.1rem;
            align-items:   flex-start;
            transition:    all var(--transition-normale);
            margin-bottom: 1rem;
            border-left:   4px solid transparent;
        }

        .carte-conseil:hover {
            box-shadow: var(--ombre-carte-hover);
            transform:  translateX(3px);
        }

        /* Variantes de couleur selon la priorite */
        .carte-conseil.conseil-danger {
            border-left-color: var(--couleur-danger);
        }
        .carte-conseil.conseil-warning {
            border-left-color: var(--couleur-avertissement);
        }
        .carte-conseil.conseil-succes {
            border-left-color: var(--couleur-succes);
        }
        .carte-conseil.conseil-info {
            border-left-color: var(--couleur-info);
        }

        /* Icone du conseil */
        .conseil-icone {
            width:           48px;
            height:          48px;
            border-radius:   var(--arrondi-lg);
            display:         flex;
            align-items:     center;
            justify-content: center;
            font-size:       1.4rem;
            flex-shrink:     0;
        }

        .conseil-corps {
            flex: 1;
        }

        .conseil-titre {
            font-size:     var(--taille-md);
            font-weight:   700;
            color:         var(--texte-principal);
            margin-bottom: 0.3rem;
        }

        .conseil-texte {
            font-size:   var(--taille-sm);
            color:       var(--texte-secondaire);
            line-height: 1.6;
            margin:      0;
        }

        .conseil-detail {
            font-size:     var(--taille-xs);
            font-weight:   600;
            margin-top:    0.5rem;
            padding:       0.3rem 0.75rem;
            border-radius: var(--arrondi-full);
            display:       inline-block;
        }

        /* -- Jauges de budget par categorie -- */
        .jauge-budget {
            margin-bottom: 1.1rem;
        }

        .jauge-header {
            display:         flex;
            justify-content: space-between;
            align-items:     center;
            margin-bottom:   0.4rem;
        }

        .jauge-label {
            font-size:   var(--taille-sm);
            font-weight: 600;
            color:       var(--texte-principal);
        }

        .jauge-montant {
            font-size:   var(--taille-xs);
            color:       var(--texte-secondaire);
            font-weight: 500;
        }

        .jauge-barre-fond {
            background:    var(--bg-corps);
            border-radius: var(--arrondi-full);
            height:        10px;
            overflow:      hidden;
        }

        .jauge-barre-remplie {
            height:        100%;
            border-radius: var(--arrondi-full);
            transition:    width 1s ease;
        }

        /* -- Comparaison periode N vs periode N-1 -- */
        .carte-comparaison {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-xl);
            padding:       1.5rem;
            box-shadow:    var(--ombre-carte);
        }

        .comparaison-ligne {
            display:         flex;
            align-items:     center;
            justify-content: space-between;
            padding:         0.7rem 0;
            border-bottom:   1px solid var(--bordure-couleur);
            font-size:       var(--taille-sm);
        }

        .comparaison-ligne:last-child {
            border-bottom: none;
        }

        .comparaison-label {
            color:       var(--texte-secondaire);
            font-weight: 500;
        }

        .comparaison-valeurs {
            display:     flex;
            gap:         1.5rem;
            align-items: center;
        }

        .comparaison-mois {
            text-align:  right;
            font-weight: 600;
        }

        .comparaison-fleche {
            font-size:   1.1rem;
            font-weight: 700;
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
                        <i class="bi bi-lightbulb"></i>
                    </span>
                    Conseils financiers
                </h1>
                <p class="page-subtitle">
                    Analyse automatique de vos habitudes
                    et recommandations personnalisees
                </p>
            </div>

            <!-- Barre de filtres par periode -->
            <div class="filtre-periode" role="group"
                 aria-label="Filtrer par periode">
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
                <button class="filtre-btn"
                        data-periode="personnalise">
                    Personnalisé
                </button>
            </div>

            <!-- Champs dates personnalisees (masques par defaut) -->
            <div class="dates-personnalisees"
                 id="dates-personnalisees">
                <label for="date-du">Du :</label>
                <input type="date" id="date-du">
                <label for="date-au">Au :</label>
                <input type="date" id="date-au">
                <button class="filtre-btn actif"
                        id="btn-appliquer-dates">
                    Appliquer
                </button>
            </div>

            <div class="row g-4">

                <!-- =============================================
                     COLONNE GAUCHE : Score + Conseils
                ============================================= -->
                <div class="col-12 col-lg-8">

                    <!-- Score financier global -->
                    <div class="carte-score"
                         id="carte-score-global">
                        <div class="score-chiffre"
                             id="score-chiffre">—</div>
                        <div class="score-label"
                             id="score-label">
                            Calcul en cours...
                        </div>
                        <div class="score-barre-fond">
                            <div class="score-barre-remplie"
                                 id="score-barre"
                                 style="width: 0%"></div>
                        </div>
                        <div style="font-size: var(--taille-xs);
                                    opacity: 0.7;
                                    margin-top: 0.75rem;"
                             id="score-sous-texte">
                            Score sur 95 — basé sur vos
                            habitudes financieres
                        </div>
                    </div>

                    <!-- Titre section conseils -->
                    <h2 style="font-size: var(--taille-xl);
                               font-weight: 700;
                               margin-bottom: 1.25rem;">
                        <i class="bi bi-stars me-2"
                           style="color: var(--couleur-avertissement);">
                        </i>
                        Recommandations personnalisees
                    </h2>

                    <!-- Conseils generes dynamiquement par JS -->
                    <div id="liste-conseils">
                        <!-- Rempli par le script ci-dessous -->
                    </div>

                </div>

                <!-- =============================================
                     COLONNE DROITE : Jauges + Comparaison
                ============================================= -->
                <div class="col-12 col-lg-4">

                    <!-- Jauges de budget par categorie -->
                    <div class="carte mb-4">
                        <div class="carte-header">
                            <h3 class="carte-titre">
                                <i class="bi bi-bar-chart-steps me-2"></i>
                                Budget par categorie
                            </h3>
                        </div>
                        <div id="jauges-categories">
                            <!-- Rempli dynamiquement -->
                        </div>
                    </div>

                    <!-- Comparaison periode actuelle vs precedente -->
                    <div class="carte-comparaison">
                        <h3 class="carte-titre mb-3">
                            <i class="bi bi-calendar-month me-2"></i>
                            Comparaison avec la periode precedente
                        </h3>

                        <!-- En-tete des colonnes -->
                        <div class="comparaison-ligne"
                             style="border-bottom: 2px solid
                                    var(--bordure-couleur);">
                            <span class="comparaison-label"
                                  style="font-weight: 700;
                                         color: var(--texte-principal);">
                                Indicateur
                            </span>
                            <div class="comparaison-valeurs">
                                <span class="comparaison-mois"
                                      style="color: var(--texte-secondaire);
                                             font-size: var(--taille-xs);"
                                      id="label-mois-precedent">
                                    Precedent
                                </span>
                                <span style="opacity: 0;
                                             font-size: 1.1rem;">
                                    &rarr;
                                </span>
                                <span class="comparaison-mois"
                                      style="color: var(--couleur-primaire);
                                             font-size: var(--taille-xs);"
                                      id="label-mois-actuel">
                                    Actuel
                                </span>
                            </div>
                        </div>

                        <!-- Lignes remplies dynamiquement -->
                        <div id="lignes-comparaison"></div>
                    </div>

                </div>

            </div>
        </div>
    </main>


    <!-- Pied de page — pas d'emoji, juste "FinTrack" -->
    <footer class="footer-fintrack">
        <div class="container">
            <div class="footer-texte">
                <span class="footer-logo">FinTrack</span>
                <p class="mb-0">
                    Projet Web — ECAM-EPMI Cergy · 2025-2026
                </p>
            </div>
        </div>
    </footer>


    <!-- =============================================
         SCRIPTS
    ============================================= -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/data.js"></script>

    <!-- Script de la page conseils -->
    <script>
    // ============================================================
    // Script conseils : analyse dynamique et recommandations
    // Benoit — Basé sur les donnees localStorage (data.js)
    //
    // Fonctionnalites :
    //   - Filtre par periode (semaine, mois, annee, personnalise)
    //   - Score strict (max 95, jamais 100)
    //   - Conseils varies (tableaux aleatoires)
    //   - Conseils specifiques aux donnees utilisateur
    //   - Code couleur : vert/orange/rouge
    // ============================================================


    // -- 1. Budgets de reference par categorie (euros/mois) --
    // Servent a evaluer si une categorie depasse la norme
    const BUDGETS_REFERENCE = {
        'Alimentation & Courses':     350,
        'Logement & Loyer':           800,
        'Transport & Déplacements':   120,
        'Santé & Bien-être':           50,
        'Loisirs & Sorties':          100,
        'Vêtements & Mode':            60,
        'Abonnements & Services':      30,
        'Éducation & Formation':       80,
        'Cadeaux & Événements':        50,
        'Épargne & Investissement':   100
    };

    // Correspondance des categories utilisees dans les conseils
    const CATEGORIES_CONSEILS = {
        alimentation: 'Alimentation & Courses',
        abonnements:  'Abonnements & Services',
        epargne:      'Épargne & Investissement'
    };


    // ============================================================
    // -- 2. TABLEAUX DE CONSEILS VARIES (non generiques)
    // On pioche aleatoirement dans ces tableaux pour eviter
    // de toujours afficher le meme message.
    // ============================================================

    // -- Conseils sur le taux d'epargne --
    const CONSEILS_EPARGNE_EXCELLENT = [
        "Bravo ! Votre taux d'épargne de {taux}% est remarquable. "
            + "Pensez à diversifier vos placements (PEA, "
            + "assurance-vie, SCPI).",
        "Excellent contrôle ! Avec {taux}% d'épargne, vous "
            + "pourriez envisager un PEA ou une assurance-vie "
            + "pour faire fructifier cet excédent.",
        "Votre discipline financière est exemplaire ({taux}%). "
            + "Attention toutefois à ne pas sacrifier votre "
            + "qualité de vie — l'équilibre est la clé.",
        "Avec un taux de {taux}%, vous faites mieux que la "
            + "majorité des Français. Pensez à automatiser "
            + "vos virements d'épargne pour pérenniser "
            + "cette habitude."
    ];

    const CONSEILS_EPARGNE_MOYEN = [
        "Votre taux d'épargne de {taux}% est correct mais "
            + "perfectible. Essayez d'automatiser un virement "
            + "de 50 EUR supplémentaires en début de mois.",
        "Avec {taux}% d'épargne, vous êtes sur la bonne voie. "
            + "Identifiez une dépense récurrente non essentielle "
            + "que vous pourriez réduire.",
        "{taux}% d'épargne, c'est un bon début. La règle des "
            + "50/30/20 recommande 20% — il vous manque un "
            + "petit effort pour y arriver.",
        "Votre épargne de {taux}% pourrait progresser. Avez-vous "
            + "pensé à négocier vos abonnements ou changer "
            + "de fournisseur d'énergie ?"
    ];

    const CONSEILS_EPARGNE_FAIBLE = [
        "Votre taux d'épargne de {taux}% est insuffisant. "
            + "Commencez par identifier vos 3 plus gros postes "
            + "de dépenses et fixez un budget pour chacun.",
        "Avec seulement {taux}% d'épargne, votre marge de "
            + "manoeuvre est très faible. Essayez la méthode "
            + "des enveloppes pour limiter chaque catégorie.",
        "Alerte : {taux}% d'épargne ne suffit pas à constituer "
            + "un fonds d'urgence. Visez au minimum 10% en "
            + "réduisant les dépenses non essentielles.",
        "Votre épargne est critique ({taux}%). Pensez à "
            + "automatiser un petit virement dès réception "
            + "de vos revenus — même 20 EUR comptent."
    ];

    // -- Conseils sur l'alimentation --
    const CONSEILS_ALIMENTATION_ELEVE = [
        "Vos dépenses alimentaires de {montant} EUR/mois "
            + "({parJour} EUR/jour) sont élevées. Planifiez "
            + "vos repas à l'avance et limitez les livraisons.",
        "À {montant} EUR/mois en alimentation, pensez au "
            + "batch cooking du dimanche — ça peut diviser "
            + "votre budget par deux.",
        "{parJour} EUR/jour en alimentation, c'est beaucoup. "
            + "Essayez les courses en ligne pour éviter "
            + "les achats impulsifs."
    ];

    const CONSEILS_ALIMENTATION_MOYEN = [
        "Vos dépenses alimentaires de {montant} EUR/mois sont "
            + "raisonnables. Cuisiner plus souvent pourrait "
            + "encore les optimiser.",
        "À {montant} EUR/mois, votre budget alimentation est "
            + "correct. Pensez aux produits de saison pour "
            + "réduire encore un peu.",
        "Budget alimentation correct ({montant} EUR). "
            + "Avez-vous pensé aux applis anti-gaspi comme "
            + "Too Good To Go ?"
    ];

    const CONSEILS_ALIMENTATION_BAS = [
        "Bravo ! Vos dépenses alimentaires de {montant} EUR/mois "
            + "sont bien maîtrisées. Continuez ainsi.",
        "Budget alimentation exemplaire ({montant} EUR/mois). "
            + "Assurez-vous tout de même de manger "
            + "équilibré !",
        "{montant} EUR/mois en alimentation, c'est très bien "
            + "géré. Bravo pour votre discipline."
    ];

    // -- Conseils generaux varies --
    const CONSEILS_GENERAUX = [
        "Appliquez la règle des 50/30/20 : 50% besoins "
            + "essentiels, 30% envies, 20% épargne. C'est "
            + "la base d'une gestion saine.",
        "Automatisez vos finances : virement épargne le 1er "
            + "du mois, prélèvements le 5, budget courses "
            + "hebdomadaire fixe.",
        "Constituez un fonds d'urgence de 3 à 6 mois de "
            + "dépenses. C'est votre filet de sécurité "
            + "en cas d'imprévu.",
        "Faites un audit mensuel de 15 minutes : comparez "
            + "vos dépenses prévues vs réelles. Ce petit "
            + "rituel fait toute la différence.",
        "Négociez vos contrats annuels (assurance, énergie, "
            + "téléphone) — on peut souvent économiser "
            + "10 à 20% sans effort."
    ];

    // -- Remarques critiques (toujours affichees, meme si tout va bien) --
    const REMARQUES_CRITIQUES = [
        "Vous n'avez que {n} transactions cette période "
            + "— pensez à tout enregistrer pour un suivi précis.",
        "Même avec un bon score, une dépense imprévue peut "
            + "tout changer. Avez-vous un fonds d'urgence ?",
        "Votre score ne prend pas en compte l'inflation. "
            + "Vos dépenses réelles augmentent peut-être "
            + "sans que vous le remarquiez.",
        "Un bon score aujourd'hui ne garantit pas demain. "
            + "Revoyez vos objectifs financiers chaque trimestre.",
        "Attention : ce score est basé uniquement sur les "
            + "données enregistrées. Si des dépenses manquent, "
            + "la réalité peut être différente."
    ];


    // ============================================================
    // -- 3. FONCTIONS UTILITAIRES
    // ============================================================

    // Choisir un element aleatoire dans un tableau
    // Utilise un hash de la date pour varier chaque jour
    function choisirAleatoire(tableau) {
        var index = Math.floor(Math.random() * tableau.length);
        return tableau[index];
    }

    // Remplacer les placeholders {clé} dans une chaine
    // Exemple : remplacer("{taux}% c'est bien", {taux: 25})
    //   => "25% c'est bien"
    function remplacerPlaceholders(texte, valeurs) {
        var resultat = texte;
        for (var cle in valeurs) {
            if (valeurs.hasOwnProperty(cle)) {
                // Remplacer toutes les occurrences de {cle}
                resultat = resultat.split(
                    '{' + cle + '}'
                ).join(valeurs[cle]);
            }
        }
        return resultat;
    }

    // Formater un montant en euros (ex: 1 234,56 EUR)
    function formaterEuros(montant) {
        return montant.toLocaleString(
            'fr-FR',
            { style: 'currency', currency: 'EUR' }
        );
    }

    // Filtrer les transactions entre deux dates
    // dateDebut et dateFin sont des objets Date
    function filtrerParDates(transactions, dateDebut, dateFin) {
        return transactions.filter(function (t) {
            var dateTx = new Date(t.date);
            return dateTx >= dateDebut && dateTx <= dateFin;
        });
    }

    // Calculer les bornes de la periode precedente
    // (meme duree, juste avant la periode actuelle)
    function calculerPeriodePrecedente(dateDebut, dateFin) {
        var dureeMs = dateFin.getTime() - dateDebut.getTime();
        var finPrec = new Date(dateDebut.getTime() - 1);
        var debutPrec = new Date(finPrec.getTime() - dureeMs);
        return { debut: debutPrec, fin: finPrec };
    }


    // ============================================================
    // -- 4. CALCUL DES BORNES DE PERIODE
    // Selon le filtre choisi (semaine, mois, annee, personnalise)
    // ============================================================

    function calculerBornesPeriode(periode, dateDebut, dateFin) {
        var maintenant = new Date();
        var debut, fin;

        if (periode === 'semaine') {
            // Du lundi de cette semaine a aujourd'hui
            var jour = maintenant.getDay();
            // getDay() : 0=dimanche, 1=lundi ...
            var diffLundi = (jour === 0) ? 6 : jour - 1;
            debut = new Date(maintenant);
            debut.setDate(maintenant.getDate() - diffLundi);
            debut.setHours(0, 0, 0, 0);
            fin = new Date(maintenant);
            fin.setHours(23, 59, 59, 999);

        } else if (periode === 'mois') {
            // Du 1er du mois courant a aujourd'hui
            debut = new Date(
                maintenant.getFullYear(),
                maintenant.getMonth(),
                1
            );
            fin = new Date(maintenant);
            fin.setHours(23, 59, 59, 999);

        } else if (periode === 'annee') {
            // Du 1er janvier a aujourd'hui
            debut = new Date(maintenant.getFullYear(), 0, 1);
            fin = new Date(maintenant);
            fin.setHours(23, 59, 59, 999);

        } else if (periode === 'personnalise') {
            // Bornes fournies par l'utilisateur
            debut = dateDebut || new Date(
                maintenant.getFullYear(),
                maintenant.getMonth(),
                1
            );
            fin = dateFin || new Date(maintenant);
            fin.setHours(23, 59, 59, 999);

        } else {
            // Par defaut : mois courant
            debut = new Date(
                maintenant.getFullYear(),
                maintenant.getMonth(),
                1
            );
            fin = new Date(maintenant);
            fin.setHours(23, 59, 59, 999);
        }

        return { debut: debut, fin: fin };
    }

    // Libelle lisible pour la periode
    function getLibellePeriode(periode, debut, fin) {
        var opts = { day: 'numeric', month: 'short' };
        if (periode === 'semaine') {
            return 'Semaine du '
                + debut.toLocaleDateString('fr-FR', opts)
                + ' au '
                + fin.toLocaleDateString('fr-FR', opts);
        } else if (periode === 'mois') {
            return getNomMois(debut.getMonth())
                + ' ' + debut.getFullYear();
        } else if (periode === 'annee') {
            return 'Année ' + debut.getFullYear();
        } else {
            return debut.toLocaleDateString('fr-FR', opts)
                + ' — '
                + fin.toLocaleDateString('fr-FR', opts);
        }
    }


    // ============================================================
    // -- 5. SCORE FINANCIER STRICT (max 95, jamais 100)
    // Penalites multiples pour un score realiste
    // ============================================================

    function calculerScore(
        tauxEpargne, stats, nbTransactions, depCat
    ) {
        // Base : taux d'epargne (20% = 70 pts max)
        var score = Math.min(
            70,
            Math.max(0, Math.round(tauxEpargne * 3.5))
        );

        // Bonus si solde positif (+10 pts max)
        if (stats.solde > 0) {
            score += Math.min(10, Math.round(
                (stats.solde / stats.revenus) * 20
            ));
        }

        // Bonus si nombre de transactions suffisant (+10 pts)
        if (nbTransactions >= 15) {
            score += 10;
        } else if (nbTransactions >= 8) {
            score += 5;
        }
        // Sinon : pas de bonus (manque de suivi)

        // Bonus si aucune categorie > 40% du total (+5 pts)
        var totalDep = stats.depenses;
        var aucuneCategorieDominante = true;
        for (var cat in depCat) {
            if (depCat.hasOwnProperty(cat)) {
                var pctCat = (depCat[cat] / totalDep) * 100;
                if (pctCat > 40) {
                    aucuneCategorieDominante = false;
                    break;
                }
            }
        }
        if (aucuneCategorieDominante && totalDep > 0) {
            score += 5;
        }

        // Penalite : solde negatif (-20 pts)
        if (stats.solde < 0) {
            score = Math.max(0, score - 20);
        }

        // Penalite : trop peu de transactions (-10 pts)
        if (nbTransactions < 5 && nbTransactions > 0) {
            score = Math.max(0, score - 10);
        }

        // Penalite : pas de categorie epargne (-5 pts)
        var aEpargne = depCat[CATEGORIES_CONSEILS.epargne] > 0;
        if (!aEpargne && stats.revenus > 0) {
            score = Math.max(0, score - 5);
        }

        // Plafond strict a 95 — personne n'est parfait
        score = Math.min(95, score);

        // Plancher a 0
        score = Math.max(0, score);

        return score;
    }

    // Afficher le score dans la carte
    function afficherScoreFinancier(score, nbTransactions) {
        var elemChiffre = document.getElementById(
            'score-chiffre'
        );
        var elemLabel = document.getElementById(
            'score-label'
        );
        var elemBarre = document.getElementById(
            'score-barre'
        );

        if (elemChiffre) {
            elemChiffre.textContent = score + '/95';
        }
        if (elemBarre) {
            // Barre proportionnelle a 95
            var pctBarre = Math.round((score / 95) * 100);
            elemBarre.style.width = pctBarre + '%';
        }

        // Message selon le niveau du score
        var message = '';
        if (score >= 75) {
            message = '<i class="bi bi-star-fill"></i> '
                + 'Très bonne gestion ! Quelques détails '
                + 'à peaufiner (voir ci-dessous).';
        } else if (score >= 55) {
            message = '<i class="bi bi-hand-thumbs-up-fill"></i> '
                + 'Gestion correcte. Des points '
                + 'd\'amélioration identifiés.';
        } else if (score >= 35) {
            message = '<i class="bi bi-exclamation-triangle-fill">'
                + '</i> Attention — Votre gestion financière '
                + 'nécessite des ajustements.';
        } else {
            message = '<i class="bi bi-bell-fill"></i> '
                + 'Situation préoccupante — Consultez '
                + 'les conseils ci-dessous en priorité.';
        }

        if (elemLabel) {
            elemLabel.innerHTML = message;
        }

        // Texte sous la barre
        var sousTexte = document.getElementById(
            'score-sous-texte'
        );
        if (sousTexte) {
            sousTexte.textContent = 'Score sur 95 — basé sur '
                + nbTransactions + ' transaction(s) analysée(s)';
        }
    }


    // ============================================================
    // -- 6. GENERATION DES CONSEILS PERSONNALISES
    // Analyse les donnees et construit la liste des conseils
    // avec des messages varies et specifiques
    // ============================================================

    function genererConseils(
        statsActuel, statsPrec,
        depCat, tauxEpargne, nbTransactions
    ) {
        var conteneur = document.getElementById(
            'liste-conseils'
        );
        if (!conteneur) return;

        var conseils = [];

        // -------------------------------------------------------
        // Conseil CRITIQUE : depenses > revenus (deficit)
        // -------------------------------------------------------
        if (statsActuel.depenses > statsActuel.revenus
            && statsActuel.revenus > 0) {
            var deficit = statsActuel.depenses
                - statsActuel.revenus;
            conseils.push({
                type:   'danger',
                icone:  '<i class="bi bi-exclamation-octagon-fill"></i>',
                titre:  'Vous dépensez plus que vos revenus !',
                texte:  'Déficit de '
                    + formaterEuros(deficit)
                    + ' cette période. Vos dépenses ('
                    + formaterEuros(statsActuel.depenses)
                    + ') dépassent vos revenus ('
                    + formaterEuros(statsActuel.revenus)
                    + '). C\'est une situation à corriger '
                    + 'en urgence.',
                detail: 'Déficit : '
                    + formaterEuros(deficit),
                couleurDetail:
                    'var(--couleur-danger-clair)',
                couleurTexteDetail:
                    'var(--texte-danger)'
            });
        }

        // -------------------------------------------------------
        // Conseil : taux d'epargne (message varie)
        // -------------------------------------------------------
        if (statsActuel.revenus > 0) {
            var tauxStr = tauxEpargne.toFixed(1);
            var valeurs = { taux: tauxStr };

            if (tauxEpargne >= 20) {
                var msg = choisirAleatoire(
                    CONSEILS_EPARGNE_EXCELLENT
                );
                conseils.push({
                    type:   'succes',
                    icone:  '<i class="bi bi-cash-stack"></i>',
                    titre:  'Excellent taux d\'épargne',
                    texte:  remplacerPlaceholders(msg, valeurs),
                    detail: 'Épargne : '
                        + tauxStr + '% de vos revenus',
                    couleurDetail:
                        'var(--couleur-succes-clair)',
                    couleurTexteDetail:
                        'var(--texte-succes)'
                });
            } else if (tauxEpargne >= 10) {
                var msg2 = choisirAleatoire(
                    CONSEILS_EPARGNE_MOYEN
                );
                conseils.push({
                    type:   'warning',
                    icone:  '<i class="bi bi-bar-chart-fill"></i>',
                    titre:  'Taux d\'épargne à améliorer',
                    texte:  remplacerPlaceholders(msg2, valeurs),
                    detail: 'Objectif : 20% - Actuel : '
                        + tauxStr + '%',
                    couleurDetail:
                        'var(--couleur-avertissement-clair)',
                    couleurTexteDetail:
                        'var(--texte-avertissement)'
                });
            } else {
                var msg3 = choisirAleatoire(
                    CONSEILS_EPARGNE_FAIBLE
                );
                conseils.push({
                    type:   'danger',
                    icone:  '<i class="bi bi-exclamation-triangle-fill"></i>',
                    titre:  'Taux d\'épargne insuffisant',
                    texte:  remplacerPlaceholders(msg3, valeurs),
                    detail: 'Actuel : '
                        + tauxStr + '% — objectif : 20%',
                    couleurDetail:
                        'var(--couleur-danger-clair)',
                    couleurTexteDetail:
                        'var(--texte-danger)'
                });
            }
        }

        // -------------------------------------------------------
        // Conseil : categorie dominante (> 40% des depenses)
        // -------------------------------------------------------
        var totalDep = statsActuel.depenses;
        if (totalDep > 0) {
            for (var cat in depCat) {
                if (!depCat.hasOwnProperty(cat)) continue;
                var pctCat = (depCat[cat] / totalDep) * 100;
                if (pctCat > 40) {
                    conseils.push({
                        type:   'warning',
                        icone:  '<i class="bi bi-pie-chart-fill"></i>',
                        titre:  'Catégorie dominante : '
                            + cat,
                        texte:  'La catégorie "' + cat
                            + '" représente '
                            + pctCat.toFixed(0)
                            + '% de vos dépenses totales. '
                            + 'Une telle concentration est '
                            + 'risquée — diversifiez vos '
                            + 'postes de dépenses.',
                        detail: cat + ' : '
                            + pctCat.toFixed(0)
                            + '% du total',
                        couleurDetail:
                            'var(--couleur-avertissement-clair)',
                        couleurTexteDetail:
                            'var(--texte-avertissement)'
                    });
                }
            }
        }

        // -------------------------------------------------------
        // Conseil : depassement de budget par categorie (> 120%)
        // -------------------------------------------------------
        for (var catBudget in depCat) {
            if (!depCat.hasOwnProperty(catBudget)) continue;
            var montantCat = depCat[catBudget];
            var budget = BUDGETS_REFERENCE[catBudget];
            if (budget && montantCat > budget * 1.2) {
                var depassement = (
                    (montantCat - budget) / budget * 100
                ).toFixed(0);
                conseils.push({
                    type:   'danger',
                    icone:  '<i class="bi bi-circle-fill text-danger"></i>',
                    titre:  'Dépassement — ' + catBudget,
                    texte:  'Vous avez dépensé '
                        + formaterEuros(montantCat)
                        + ' en "' + catBudget
                        + '", soit ' + depassement
                        + '% de plus que le budget '
                        + 'recommandé de '
                        + budget + ' EUR.',
                    detail: 'Budget : ' + budget
                        + ' EUR - Dépensé : '
                        + montantCat.toFixed(0) + ' EUR',
                    couleurDetail:
                        'var(--couleur-danger-clair)',
                    couleurTexteDetail:
                        'var(--texte-danger)'
                });
            }
        }

        // -------------------------------------------------------
        // Conseil : poste alimentation (message varie)
        // -------------------------------------------------------
        var depAlimentation = depCat[
            CATEGORIES_CONSEILS.alimentation
        ] || 0;
        if (depAlimentation > 0) {
            var parJour = (depAlimentation / 30).toFixed(2);
            var valeursAlim = {
                montant: depAlimentation.toFixed(0),
                parJour: parJour
            };
            var niveau, texteAlim;

            if (depAlimentation > 300) {
                niveau = 'danger';
                texteAlim = remplacerPlaceholders(
                    choisirAleatoire(
                        CONSEILS_ALIMENTATION_ELEVE
                    ),
                    valeursAlim
                );
            } else if (depAlimentation > 200) {
                niveau = 'warning';
                texteAlim = remplacerPlaceholders(
                    choisirAleatoire(
                        CONSEILS_ALIMENTATION_MOYEN
                    ),
                    valeursAlim
                );
            } else {
                niveau = 'succes';
                texteAlim = remplacerPlaceholders(
                    choisirAleatoire(
                        CONSEILS_ALIMENTATION_BAS
                    ),
                    valeursAlim
                );
            }

            var couleurDetailMap = {
                succes:  'var(--couleur-succes-clair)',
                warning: 'var(--couleur-avertissement-clair)',
                danger:  'var(--couleur-danger-clair)'
            };
            var couleurTexteMap = {
                succes:  'var(--texte-succes)',
                warning: 'var(--texte-avertissement)',
                danger:  'var(--texte-danger)'
            };

            conseils.push({
                type:   niveau,
                icone:  '<i class="bi bi-cart-fill"></i>',
                titre:  'Poste Alimentation',
                texte:  texteAlim,
                detail: depAlimentation.toFixed(0)
                    + ' EUR/mois - '
                    + parJour + ' EUR/jour',
                couleurDetail:
                    couleurDetailMap[niveau],
                couleurTexteDetail:
                    couleurTexteMap[niveau]
            });
        }

        // -------------------------------------------------------
        // Conseil : abonnements
        // -------------------------------------------------------
        var depAbonnements = depCat[
            CATEGORIES_CONSEILS.abonnements
        ] || 0;
        if (depAbonnements > 0) {
            conseils.push({
                type: depAbonnements > 50
                    ? 'warning' : 'info',
                icone:  '<i class="bi bi-phone-fill"></i>',
                titre:  'Audit de vos abonnements',
                texte:  'Vous dépensez '
                    + depAbonnements.toFixed(2)
                    + ' EUR par mois en abonnements '
                    + '(streaming, musique, etc.). '
                    + 'Faites un audit et résiliez ceux '
                    + 'que vous n\'utilisez plus.',
                detail: 'Total abonnements : '
                    + depAbonnements.toFixed(2)
                    + ' EUR/mois',
                couleurDetail:
                    'var(--couleur-info-clair)',
                couleurTexteDetail:
                    'var(--couleur-info)'
            });
        }

        // -------------------------------------------------------
        // Conseil : comparaison avec la periode precedente
        // -------------------------------------------------------
        if (statsPrec.depenses > 0) {
            var diffDepenses =
                statsActuel.depenses - statsPrec.depenses;
            var pctDiff = (
                (diffDepenses / statsPrec.depenses) * 100
            ).toFixed(1);

            if (diffDepenses > 50) {
                conseils.push({
                    type:   'warning',
                    icone:  '<i class="bi bi-graph-up-arrow"></i>',
                    titre:  'Hausse des dépenses vs période '
                        + 'précédente',
                    texte:  'Vos dépenses ont augmenté de '
                        + Math.abs(pctDiff)
                        + '% par rapport à la période '
                        + 'précédente (+'
                        + diffDepenses.toFixed(0)
                        + ' EUR). Identifiez les catégories '
                        + 'responsables.',
                    detail: '+'
                        + diffDepenses.toFixed(0)
                        + ' EUR vs période précédente',
                    couleurDetail:
                        'var(--couleur-avertissement-clair)',
                    couleurTexteDetail:
                        'var(--texte-avertissement)'
                });
            } else if (diffDepenses < -50) {
                conseils.push({
                    type:   'succes',
                    icone:  '<i class="bi bi-graph-down-arrow"></i>',
                    titre:  'Baisse des dépenses vs période '
                        + 'précédente',
                    texte:  'Excellent ! Vos dépenses ont '
                        + 'diminué de '
                        + Math.abs(pctDiff)
                        + '% (-'
                        + Math.abs(diffDepenses).toFixed(0)
                        + ' EUR d\'économies). '
                        + 'Maintenez ce cap !',
                    detail: '-'
                        + Math.abs(diffDepenses).toFixed(0)
                        + ' EUR vs période précédente',
                    couleurDetail:
                        'var(--couleur-succes-clair)',
                    couleurTexteDetail:
                        'var(--texte-succes)'
                });
            }
        }

        // -------------------------------------------------------
        // Conseil : trop peu de transactions (manque de suivi)
        // -------------------------------------------------------
        if (nbTransactions < 10 && nbTransactions > 0) {
            conseils.push({
                type:   'warning',
                icone:  '<i class="bi bi-pencil-square"></i>',
                titre:  'Suivi incomplet',
                texte:  'Vous n\'avez enregistré que '
                    + nbTransactions
                    + ' transaction(s) cette période. '
                    + 'Pour un suivi précis, pensez '
                    + 'à noter toutes vos dépenses, '
                    + 'même les plus petites.',
                detail: nbTransactions
                    + ' transaction(s) enregistrée(s)',
                couleurDetail:
                    'var(--couleur-avertissement-clair)',
                couleurTexteDetail:
                    'var(--texte-avertissement)'
            });
        }

        // -------------------------------------------------------
        // Conseil : aucune transaction
        // -------------------------------------------------------
        if (nbTransactions === 0) {
            conseils.push({
                type:   'danger',
                icone:  '<i class="bi bi-inbox"></i>',
                titre:  'Aucune donnée pour cette période',
                texte:  'Aucune transaction enregistrée. '
                    + 'Ajoutez vos revenus et dépenses '
                    + 'via la page Opérations pour '
                    + 'obtenir des conseils personnalisés.',
                detail: '0 transaction',
                couleurDetail:
                    'var(--couleur-danger-clair)',
                couleurTexteDetail:
                    'var(--texte-danger)'
            });
        }

        // -------------------------------------------------------
        // Remarque critique (toujours affichee, meme si bon score)
        // Garantit qu'on ne donne jamais un bilan 100% positif
        // -------------------------------------------------------
        var remarque = remplacerPlaceholders(
            choisirAleatoire(REMARQUES_CRITIQUES),
            { n: nbTransactions }
        );
        conseils.push({
            type:   'info',
            icone:  '<i class="bi bi-info-circle-fill"></i>',
            titre:  'Point de vigilance',
            texte:  remarque,
            detail: 'Conseil permanent',
            couleurDetail:
                'var(--couleur-info-clair)',
            couleurTexteDetail:
                'var(--couleur-info)'
        });

        // -------------------------------------------------------
        // Conseil general varie (pioche aleatoire)
        // -------------------------------------------------------
        conseils.push({
            type:   'info',
            icone:  '<i class="bi bi-lightbulb-fill"></i>',
            titre:  'Conseil du jour',
            texte:  choisirAleatoire(CONSEILS_GENERAUX),
            detail: 'Bonne pratique financière',
            couleurDetail:
                'var(--couleur-info-clair)',
            couleurTexteDetail:
                'var(--couleur-info)'
        });

        // -- Rendu HTML des conseils dans le conteneur --
        conteneur.innerHTML = '';
        conseils.forEach(function (conseil) {
            var couleurIcone = {
                succes:  'icone-succes',
                danger:  'icone-danger',
                warning: 'icone-warning',
                info:    'icone-info'
            }[conseil.type] || 'icone-info';

            conteneur.insertAdjacentHTML('beforeend', `
                <div class="carte-conseil
                            conseil-${conseil.type}">
                    <div class="conseil-icone
                                ${couleurIcone}">
                        ${conseil.icone}
                    </div>
                    <div class="conseil-corps">
                        <div class="conseil-titre">
                            ${conseil.titre}
                        </div>
                        <p class="conseil-texte">
                            ${conseil.texte}
                        </p>
                        <span class="conseil-detail"
                              style="background:
                                ${conseil.couleurDetail};
                              color:
                                ${conseil.couleurTexteDetail};">
                            ${conseil.detail}
                        </span>
                    </div>
                </div>`);
        });
    }


    // ============================================================
    // -- 7. JAUGES DE BUDGET PAR CATEGORIE
    // Affiche une barre de progression pour chaque categorie
    // Vert = OK, Orange = attention, Rouge = depassement
    // ============================================================

    function afficherJaugesCategories(depCat) {
        var conteneur = document.getElementById(
            'jauges-categories'
        );
        if (!conteneur) return;

        conteneur.innerHTML = '';

        // Tri des categories par montant decroissant (top 7)
        var categoriesTriees = Object.entries(depCat)
            .sort(function (a, b) { return b[1] - a[1]; })
            .slice(0, 7);

        categoriesTriees.forEach(function (item) {
            var categorie = item[0];
            var montant = item[1];
            var budget = BUDGETS_REFERENCE[categorie] || 100;
            var pourcentage = Math.min(
                100, (montant / budget) * 100
            ).toFixed(0);

            // Code couleur : rouge/orange/vert
            var couleur;
            if (pourcentage >= 100) {
                couleur = 'var(--couleur-danger)';
            } else if (pourcentage >= 75) {
                couleur = 'var(--couleur-avertissement)';
            } else {
                couleur = 'var(--couleur-succes)';
            }

            conteneur.insertAdjacentHTML('beforeend', `
                <div class="jauge-budget">
                    <div class="jauge-header">
                        <span class="jauge-label">
                            ${categorie}
                        </span>
                        <span class="jauge-montant">
                            ${montant.toFixed(0)}
                            EUR / ${budget} EUR
                        </span>
                    </div>
                    <div class="jauge-barre-fond">
                        <div class="jauge-barre-remplie"
                             style="width: ${pourcentage}%;
                                    background: ${couleur};">
                        </div>
                    </div>
                </div>`);
        });
    }


    // ============================================================
    // -- 8. COMPARAISON PERIODE ACTUELLE VS PRECEDENTE
    // Affiche les lignes de comparaison entre les deux periodes
    // ============================================================

    function afficherComparaisonMensuelle(
        statsActuel, statsPrec,
        libActuel, libPrecedent
    ) {
        // Mise a jour des libelles de colonnes
        var lblMoisPrec = document.getElementById(
            'label-mois-precedent'
        );
        var lblMoisAct = document.getElementById(
            'label-mois-actuel'
        );
        if (lblMoisPrec) {
            lblMoisPrec.textContent = libPrecedent;
        }
        if (lblMoisAct) {
            lblMoisAct.textContent = libActuel;
        }

        var lignes = document.getElementById(
            'lignes-comparaison'
        );
        if (!lignes) return;

        var opts = { style: 'currency', currency: 'EUR' };

        // Definition des indicateurs a comparer
        var donnees = [
            {
                label:           'Revenus',
                valPrec:         statsPrec.revenus,
                valAct:          statsActuel.revenus,
                positifSiHausse: true
            },
            {
                label:           'Dépenses',
                valPrec:         statsPrec.depenses,
                valAct:          statsActuel.depenses,
                positifSiHausse: false
            },
            {
                label:           'Solde net',
                valPrec:         statsPrec.solde,
                valAct:          statsActuel.solde,
                positifSiHausse: true
            }
        ];

        lignes.innerHTML = '';

        // Construction des lignes de comparaison
        donnees.forEach(function (d) {
            var diff    = d.valAct - d.valPrec;
            var positif = d.positifSiHausse
                ? diff >= 0 : diff <= 0;
            // Vert si positif, rouge sinon
            var couleur = positif
                ? 'var(--couleur-succes)'
                : 'var(--couleur-danger)';
            var fleche  = diff > 0
                ? '\u2191' : diff < 0
                    ? '\u2193' : '\u2192';

            var valPrecFmt = d.valPrec.toLocaleString(
                'fr-FR', opts
            );
            var valActFmt = d.valAct.toLocaleString(
                'fr-FR', opts
            );

            lignes.insertAdjacentHTML('beforeend', `
                <div class="comparaison-ligne">
                    <span class="comparaison-label">
                        ${d.label}
                    </span>
                    <div class="comparaison-valeurs">
                        <span class="comparaison-mois"
                              style="color:
                                var(--texte-secondaire);">
                            ${valPrecFmt}
                        </span>
                        <span class="comparaison-fleche"
                              style="color: ${couleur};">
                            ${fleche}
                        </span>
                        <span class="comparaison-mois"
                              style="color: ${couleur};">
                            ${valActFmt}
                        </span>
                    </div>
                </div>`);
        });
    }


    // ============================================================
    // -- 9. FONCTION PRINCIPALE : RAFRAICHIR TOUTE LA PAGE
    // Appelee au chargement et a chaque changement de filtre
    // ============================================================

    function rafraichirConseils(periode, dateDebut, dateFin) {
        // Calculer les bornes de la periode selectionnee
        var bornes = calculerBornesPeriode(
            periode, dateDebut, dateFin
        );

        // Calculer les bornes de la periode precedente
        // (meme duree, juste avant)
        var bornesPrec = calculerPeriodePrecedente(
            bornes.debut, bornes.fin
        );

        // Recuperer TOUTES les transactions depuis localStorage
        var toutesTransactions = getTousTracks();

        // Filtrer sur la periode actuelle et precedente
        var txActuel = filtrerParDates(
            toutesTransactions, bornes.debut, bornes.fin
        );
        var txPrec = filtrerParDates(
            toutesTransactions, bornesPrec.debut, bornesPrec.fin
        );

        // Calculer les statistiques
        var statsActuel = calculerSolde(txActuel);
        var statsPrec   = calculerSolde(txPrec);
        var depCatActuel = calculerDepensesParCategorie(
            txActuel
        );
        var nbTransactions = txActuel.length;

        // Taux d'epargne de la periode
        var tauxEpargne = statsActuel.revenus > 0
            ? ((statsActuel.solde / statsActuel.revenus) * 100)
            : 0;

        // Calculer le score strict
        var score = calculerScore(
            tauxEpargne, statsActuel,
            nbTransactions, depCatActuel
        );

        // Afficher le score
        afficherScoreFinancier(score, nbTransactions);

        // Generer les conseils personnalises
        genererConseils(
            statsActuel, statsPrec,
            depCatActuel, tauxEpargne, nbTransactions
        );

        // Afficher les jauges de budget
        afficherJaugesCategories(depCatActuel);

        // Afficher la comparaison avec la periode precedente
        var libActuel = getLibellePeriode(
            periode, bornes.debut, bornes.fin
        );
        var libPrec = getLibellePeriode(
            periode, bornesPrec.debut, bornesPrec.fin
        );
        afficherComparaisonMensuelle(
            statsActuel, statsPrec, libActuel, libPrec
        );
    }


    // ============================================================
    // -- 10. INITIALISATION ET GESTION DES FILTRES
    // ============================================================

    document.addEventListener('DOMContentLoaded', function () {

        // Variable pour stocker la periode active
        var periodeActive = 'mois';

        // Lancer l'analyse initiale (mois courant)
        rafraichirConseils(periodeActive);

        // -- Gestion des boutons de filtre --
        var boutonsFiltre = document.querySelectorAll(
            '.filtre-periode .filtre-btn'
        );
        var blocDates = document.getElementById(
            'dates-personnalisees'
        );

        boutonsFiltre.forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                // Retirer la classe "actif" de tous les boutons
                boutonsFiltre.forEach(function (b) {
                    b.classList.remove('actif');
                });
                // Ajouter "actif" au bouton clique
                bouton.classList.add('actif');

                // Lire la periode depuis data-periode
                periodeActive = bouton.getAttribute(
                    'data-periode'
                );

                // Afficher/masquer les champs de dates
                if (periodeActive === 'personnalise') {
                    blocDates.classList.add('visible');
                    // Ne pas rafraichir tout de suite,
                    // attendre le clic sur "Appliquer"
                } else {
                    blocDates.classList.remove('visible');
                    // Rafraichir immediatement
                    rafraichirConseils(periodeActive);
                }
            });
        });

        // -- Bouton "Appliquer" pour les dates personnalisees --
        var btnAppliquer = document.getElementById(
            'btn-appliquer-dates'
        );
        btnAppliquer.addEventListener('click', function () {
            var inputDu = document.getElementById('date-du');
            var inputAu = document.getElementById('date-au');

            // Verifier que les deux dates sont renseignees
            if (!inputDu.value || !inputAu.value) {
                alert(
                    'Veuillez renseigner les deux dates '
                    + '(du / au).'
                );
                return;
            }

            var dateDebut = new Date(inputDu.value);
            var dateFin   = new Date(inputAu.value);

            // Verifier que la date de debut est avant la fin
            if (dateDebut > dateFin) {
                alert(
                    'La date de début doit être '
                    + 'antérieure à la date de fin.'
                );
                return;
            }

            // Rafraichir avec les dates personnalisees
            rafraichirConseils(
                'personnalise', dateDebut, dateFin
            );
        });
    });
    </script>

</body>
</html>
