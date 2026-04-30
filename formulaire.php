<?php
// ============================================================
// FICHIER      : formulaire.php
// AUTEUR       : Ghita — Développeure Backend PHP
//                Ayoub — Développeur Frontend JS
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Formulaire "Nouveau track". 20 catégories
//                multi-select. Validation PHP. Email PHPMailer.
//                PDF jsPDF. Boutons : Rapport, Recevoir par mail.
// DATE         : Mars 2026
// ============================================================

// Initialisation des variables du formulaire
$erreurs        = [];
$message_succes = '';
$valeurs        = [
    'date' => '', 'type' => '', 'categorie' => [],
    'description' => '', 'montant' => '',
    'email' => '', 'note' => ''
];
$donnees_js     = '{}';

// Listes des catégories revenus et dépenses
$cats_revenus  = [
    'Salaire', 'Freelance / Mission', 'Allocations',
    'Remboursement', 'Investissement', 'Autre revenu'
];
$cats_depenses = [
    'Alimentation & Courses',
    'Logement & Loyer',
    'Transport & Déplacements',
    'Santé & Bien-être',
    'Loisirs & Sorties',
    'Vêtements & Mode',
    'Abonnements & Services',
    'Éducation & Formation',
    'Cadeaux & Événements',
    'Épargne & Investissement',
    'Téléphonie & Internet',
    'Entretien & Réparations',
    'Animaux de compagnie',
    'Voyages & Vacances',
    'Sport & Fitness',
    'Beauté & Soins personnels',
    'Mobilier & Équipement maison',
    'Jeux & Divertissement numérique',
    'Dons & Associations',
    'Impôts & Charges administratives'
];

// Traitement du formulaire à la soumission POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    function nettoyer($v) {
        return htmlspecialchars(strip_tags(trim($v)));
    }

    $valeurs['date']        = nettoyer(
        $_POST['date'] ?? ''
    );
    $valeurs['type']        = nettoyer(
        $_POST['type'] ?? ''
    );
    $valeurs['description'] = nettoyer(
        $_POST['description'] ?? ''
    );
    $valeurs['montant']     = nettoyer(
        $_POST['montant'] ?? ''
    );
    $valeurs['email']       = nettoyer(
        $_POST['email'] ?? ''
    );
    $valeurs['note']        = nettoyer(
        $_POST['note'] ?? ''
    );

    // Catégories (tableau venant d'une liste fixe)
    $cats_brutes = $_POST['categorie'] ?? [];
    if (is_array($cats_brutes)) {
        // Les catégories viennent d'une liste fixe, pas de saisie libre
        $valeurs['categorie'] = array_map('trim', $cats_brutes);
    } else {
        $valeurs['categorie'] = [trim($cats_brutes)];
    }
    $valeurs['categorie'] = array_filter(
        $valeurs['categorie']
    );

    // Gestion de la catégorie "Autre" personnalisée
    $cat_autre_texte = nettoyer(
        $_POST['categorie_autre_texte'] ?? ''
    );
    if (
        in_array('__autre__', $valeurs['categorie'])
        && !empty($cat_autre_texte)
    ) {
        // Remplacer le marqueur par le texte saisi
        $valeurs['categorie'] = array_map(
            function ($c) use ($cat_autre_texte) {
                return $c === '__autre__'
                    ? $cat_autre_texte : $c;
            },
            $valeurs['categorie']
        );
    } else {
        // Retirer le marqueur si pas de texte
        $valeurs['categorie'] = array_filter(
            $valeurs['categorie'],
            fn($c) => $c !== '__autre__'
        );
    }

    // Validations des champs obligatoires
    if (empty($valeurs['date']))
        $erreurs[] = 'La date est obligatoire.';
    elseif (strtotime($valeurs['date']) > time())
        $erreurs[] = 'La date ne peut pas être dans le futur.';

    if (!in_array($valeurs['type'], ['revenu', 'depense']))
        $erreurs[] = 'Le type est invalide.';

    if (empty($valeurs['categorie']))
        $erreurs[] = 'Sélectionnez au moins une catégorie.';

    if (
        empty($valeurs['description'])
        || strlen($valeurs['description']) < 3
    )
        $erreurs[] = 'Description : au moins 3 caractères.';
    elseif (strlen($valeurs['description']) > 255)
        $erreurs[] = 'Description : 255 caractères max.';

    if (empty($valeurs['montant']))
        $erreurs[] = 'Le montant est obligatoire.';
    elseif (
        !is_numeric($valeurs['montant'])
        || floatval($valeurs['montant']) <= 0
    )
        $erreurs[] = 'Le montant doit être un nombre positif.';

    if (
        !empty($valeurs['email'])
        && !filter_var(
            $valeurs['email'], FILTER_VALIDATE_EMAIL
        )
    )
        $erreurs[] = 'Adresse email invalide.';

    // Succès : envoi email et préparation des données JS
    if (empty($erreurs)) {
        if (!empty($valeurs['email'])) {
            require_once 'php/traitement_formulaire.php';
            envoyerEmailConfirmationTransaction($valeurs);
        }

        $message_succes = 'Track enregistré avec succès !';

        $donnees_js = json_encode([
            'date'        => $valeurs['date'],
            'type'        => $valeurs['type'],
            'categorie'   => array_values(
                $valeurs['categorie']
            ),
            'description' => $valeurs['description'],
            'montant'     => $valeurs['montant'],
            'note'        => $valeurs['note']
        ], JSON_UNESCAPED_UNICODE);
    }
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Nouveau track — FinTrack</title>
    <link rel="icon" type="image/x-icon"
          href="assets/img/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <!-- Styles spécifiques au formulaire -->
    <style>
        /* Conteneur principal centré */
        .conteneur-formulaire {
            max-width: 700px;
            margin: 0 auto;
        }

        /* Indicateur visuel du type de track */
        .indicateur-type {
            display:       flex;
            align-items:   center;
            gap:           1rem;
            padding:       1rem 1.25rem;
            border-radius: var(--arrondi-lg);
            border:        2px solid transparent;
            margin-bottom: 1.5rem;
            transition:    all var(--transition-normale);
        }
        .indicateur-type.type-revenu {
            background:   var(--couleur-succes-clair);
            border-color: var(--couleur-succes);
        }
        .indicateur-type.type-depense {
            background:   var(--couleur-danger-clair);
            border-color: var(--couleur-danger);
        }
        .indicateur-type.type-neutre {
            background:   var(--couleur-primaire-clair);
            border-color: var(--couleur-primaire);
        }

        /* Grille flexible description + montant */
        .form-grille-2 {
            display:     flex;
            gap:         1.25rem;
            align-items: flex-end;
        }
        .form-grille-2 > div:first-child {
            flex: 1;
        }
        .form-grille-2 > div:last-child {
            flex:           1;
            display:        flex;
            flex-direction: column;
        }
        /* Mobile : empiler en colonne */
        @media (max-width: 576px) {
            .form-grille-2 {
                flex-direction: column;
                align-items:    stretch;
            }
            .form-grille-2 > div:last-child {
                flex-direction: row;
                gap:            1rem;
            }
        }

        /* Boutons après soumission réussie */
        .boutons-apres-track {
            display:         flex;
            gap:             1rem;
            flex-wrap:       wrap;
            justify-content: center;
            padding:         1.75rem;
            background:      linear-gradient(
                135deg,
                var(--couleur-primaire-clair),
                var(--couleur-succes-clair)
            );
            border-radius:   var(--arrondi-xl);
            margin-top:      1.5rem;
            border:          2px solid
                             var(--couleur-primaire);
        }
        .boutons-apres-track .titre-apres {
            width:         100%;
            text-align:    center;
            font-family:   var(--police-ui);
            font-weight:   700;
            font-size:     var(--taille-lg);
            color:         var(--couleur-primaire);
            margin-bottom: 0.5rem;
        }

        /* Zone email pour recevoir le track */
        .zone-email-track {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-lg);
            padding:       1.25rem;
            margin-top:    1rem;
        }

        /* Compteur de caractères */
        .compteur-chars {
            font-size:  var(--taille-xs);
            color:      var(--texte-clair);
            text-align: right;
            margin-top: 0.2rem;
        }

        /* --- Bouton collapse (chevron) --- */
        .btn-collapse-cats {
            background:      var(--bg-carte);
            border:          1px solid
                             var(--bordure-carte);
            border-radius:   var(--arrondi-md);
            padding:         0.6rem 0.8rem;
            cursor:          pointer;
            transition:      all
                             var(--transition-rapide);
            display:         flex;
            align-items:     center;
            justify-content: center;
            min-width:       40px;
        }
        .btn-collapse-cats:hover {
            background:   var(--couleur-primaire-clair);
            border-color: var(--couleur-primaire);
        }
        .btn-collapse-cats i {
            color:      var(--couleur-primaire);
            transition: transform
                        var(--transition-rapide);
        }
        /* Rotation icône quand ouvert */
        .btn-collapse-cats.open i {
            transform: rotate(180deg);
        }

        /* --- Wrapper catégories : animation --- */
        .multi-select-wrapper {
            overflow:       hidden;
            transition:     max-height 0.35s ease,
                            opacity 0.25s ease;
            opacity:        1;
            position:       relative;
        }
        /* État fermé (replié) */
        .multi-select-wrapper.collapsed {
            max-height:     0 !important;
            opacity:        0;
            pointer-events: none;
        }
        /* État ouvert */
        .multi-select-wrapper.expanded {
            opacity:        1;
            pointer-events: auto;
        }

        /* Bouton fermer (X) dans le wrapper */
        .btn-close-cats {
            position:        absolute;
            top:             6px;
            right:           6px;
            background:      var(--bg-carte);
            border:          1px solid
                             var(--bordure-carte);
            border-radius:   50%;
            width:           26px;
            height:          26px;
            display:         flex;
            align-items:     center;
            justify-content: center;
            cursor:          pointer;
            font-size:       0.75rem;
            color:           var(--texte-clair);
            transition:      all
                             var(--transition-rapide);
            z-index:         2;
            line-height:     1;
        }
        .btn-close-cats:hover {
            background:   var(--couleur-danger-clair);
            border-color: var(--couleur-danger);
            color:        var(--couleur-danger);
        }

        /* --- Élément "Tous" pleine largeur --- */
        .multi-select-item-tous {
            width:         100%;
            background:    var(--couleur-primaire);
            color:         #fff;
            border-radius: var(--arrondi-md);
            padding:       0.55rem 0.75rem;
            margin-bottom: 0.5rem;
            display:       flex;
            align-items:   center;
            gap:           0.5rem;
            cursor:        pointer;
            font-weight:   600;
            transition:    all
                           var(--transition-rapide);
        }
        .multi-select-item-tous:hover {
            opacity: 0.9;
        }
        .multi-select-item-tous
            input[type="checkbox"] {
            accent-color: #fff;
        }
        .multi-select-item-tous label {
            cursor: pointer;
            color:  #fff;
            flex:   1;
        }

        /* --- Élément "Autre" pleine largeur --- */
        .multi-select-item-autre {
            width:         100%;
            background:    var(--bg-carte);
            border:        1px dashed
                           var(--bordure-carte);
            border-radius: var(--arrondi-md);
            padding:       0.55rem 0.75rem;
            margin-top:    0.5rem;
            display:       flex;
            align-items:   center;
            gap:           0.5rem;
            cursor:        pointer;
            font-style:    italic;
            flex-wrap:     wrap;
            transition:    all
                           var(--transition-rapide);
        }
        .multi-select-item-autre.selected {
            border-color: var(--couleur-primaire);
            background:   var(--couleur-primaire-clair);
        }
        .multi-select-item-autre label {
            cursor: pointer;
            flex:   1;
        }

        /* Champ texte catégorie personnalisée */
        .input-autre-categorie {
            width:         100%;
            margin-top:    0.4rem;
            padding:       0.45rem 0.65rem;
            border:        1px solid
                           var(--bordure-carte);
            border-radius: var(--arrondi-md);
            font-size:     var(--taille-sm);
            background:    var(--bg-carte);
            color:         var(--texte-principal);
            transition:    border-color
                           var(--transition-rapide);
        }
        .input-autre-categorie:focus {
            outline:      none;
            border-color: var(--couleur-primaire);
        }

        /* --- Grille catégories responsive --- */
        .cats-grid {
            display:               grid;
            grid-template-columns: 1fr 1fr;
            gap:                   0.35rem;
            padding-top:           0.25rem;
        }
        /* Mobile : une seule colonne */
        @media (max-width: 576px) {
            .cats-grid {
                grid-template-columns: 1fr;
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

    <!-- Contenu principal du formulaire -->
    <main class="page-wrapper">
        <div class="container">
            <div class="conteneur-formulaire">

                <!-- En-tête de page -->
                <div class="page-header scroll-from-left">
                    <h1 class="page-title">
                        <span class="titre-icone">
                            <i class="bi bi-plus-circle">
                            </i>
                        </span>
                        Nouveau track
                    </h1>
                    <p class="page-subtitle">
                        Enregistrez un revenu ou une
                        dépense · confirmation email
                        · export PDF
                    </p>
                </div>

                <!-- Messages PHP (erreurs / succès) -->
                <div id="zone-messages">
                    <?php if (!empty($erreurs)): ?>
                        <div class="alerte alerte-erreur"
                             role="alert">
                            <span class="alerte-icone">
                                <i class="bi bi-x-circle">
                                </i>
                            </span>
                            <div>
                                <strong>
                                    Corrigez les erreurs :
                                </strong>
                                <ul class="mb-0 mt-1 ps-3">
                                <?php foreach (
                                    $erreurs as $e
                                ): ?>
                                    <li><?= $e ?></li>
                                <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($message_succes)): ?>
                        <div class="alerte alerte-succes"
                             role="alert">
                            <span class="alerte-icone">
                                <i class="bi
                                   bi-check-circle"></i>
                            </span>
                            <strong>
                                <?= $message_succes ?>
                            </strong>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- FORMULAIRE -->
                <div class="carte scroll-reveal">

                    <!-- Indicateur du type de track -->
                    <div class="indicateur-type type-neutre"
                         id="indicateur-type">
                        <span id="icone-indicateur">
                            <i class="bi bi-credit-card">
                            </i>
                        </span>
                        <div>
                            <div id="texte-indicateur">
                                Sélectionnez un type
                            </div>
                            <div id="sous-texte-indicateur">
                                Revenu ou Dépense
                            </div>
                        </div>
                    </div>

                    <form method="POST"
                          action="formulaire.php"
                          id="form-track" novalidate>

                        <!-- Type + Date -->
                        <div class="form-grille-2">
                            <div
                              class="form-group-fintrack">
                                <label
                                  class="form-label-fintrack
                                         champ-obligatoire"
                                  for="type-transaction">
                                    Type de track
                                </label>
                                <select
                                  class=
                                  "form-select-fintrack"
                                  id="type-transaction"
                                  name="type" required>
                                    <option value="">
                                        — Choisir —
                                    </option>
                                    <option
                                      value="revenu"
                                      <?= $valeurs['type']
                                          === 'revenu'
                                          ? 'selected'
                                          : '' ?>>
                                        Revenu
                                    </option>
                                    <option
                                      value="depense"
                                      <?= $valeurs['type']
                                          === 'depense'
                                          ? 'selected'
                                          : '' ?>>
                                        Dépense
                                    </option>
                                </select>
                            </div>
                            <div
                              class="form-group-fintrack">
                                <label
                                  class="form-label-fintrack
                                         champ-obligatoire"
                                  for="date-transaction">
                                    Date
                                </label>
                                <input type="date"
                                  class=
                                  "form-control-fintrack"
                                  id="date-transaction"
                                  name="date"
                                  value="<?=
                                    $valeurs['date']
                                  ?>"
                                  max="<?=
                                    date('Y-m-d')
                                  ?>"
                                  required>
                            </div>
                        </div>

                        <!-- Catégories multi-select -->
                        <div class="form-group-fintrack">
                            <label
                              class="form-label-fintrack
                                     champ-obligatoire">
                                Catégories
                                <span style="
                                  font-size:
                                    var(--taille-xs);
                                  color:
                                    var(--texte-clair);
                                  font-weight:400;">
                                    (plusieurs choix)
                                </span>
                            </label>

                            <!-- Bouton chevron -->
                            <div style="
                              display:flex;
                              gap:0.75rem;
                              margin-bottom:0.75rem;">
                                <button type="button"
                                  class=
                                  "btn-collapse-cats"
                                  id="btn-collapse-cats"
                                  title="Déplier/replier">
                                    <i class="bi
                                       bi-chevron-down"
                                       id=
                                       "icone-collapse">
                                    </i>
                                </button>
                            </div>

                            <!-- Wrapper multi-select -->
                            <div
                              class="multi-select-wrapper
                                     collapsed"
                              id="wrapper-categories">
                                <div style="
                                  color:
                                    var(--texte-clair);
                                  font-size:
                                    var(--taille-sm);
                                  padding:0.5rem;
                                  text-align:center;">
                                    Sélectionnez d'abord
                                    un type de track
                                </div>
                            </div>

                            <!-- Champ caché pour
                                 catégorie "Autre" -->
                            <input type="hidden"
                              name=
                              "categorie_autre_texte"
                              id=
                              "categorie-autre-texte"
                              value="">

                            <!-- Résumé catégories -->
                            <div id="resume-categories"
                              style="
                                font-size:
                                  var(--taille-xs);
                                color:
                                  var(
                                  --couleur-primaire);
                                margin-top:0.35rem;
                                font-weight:600;">
                            </div>
                        </div>

                        <!-- Description + Montant -->
                        <div class="form-grille-2">
                            <div
                              class="form-group-fintrack">
                                <label
                                  class="form-label-fintrack
                                         champ-obligatoire"
                                  for="description">
                                    Description
                                </label>
                                <input type="text"
                                  class=
                                  "form-control-fintrack"
                                  id="description"
                                  name="description"
                                  value="<?=
                                    $valeurs[
                                        'description'
                                    ]
                                  ?>"
                                  placeholder=
                                  "Ex: Courses, Salaire..."
                                  maxlength="255"
                                  required>
                                <div
                                  class="compteur-chars"
                                  id="compteur-desc">
                                    0 / 255
                                </div>
                            </div>
                            <div
                              class="form-group-fintrack">
                                <label
                                  class="form-label-fintrack
                                         champ-obligatoire"
                                  for="montant">
                                    Montant (€)
                                </label>
                                <input type="number"
                                  class=
                                  "form-control-fintrack"
                                  id="montant"
                                  name="montant"
                                  value="<?=
                                    $valeurs['montant']
                                  ?>"
                                  placeholder="0.00"
                                  min="0.01"
                                  step="0.01"
                                  required>
                                <div
                                  id="affichage-montant"
                                  style="
                                    font-size:
                                      var(--taille-xs);
                                    color:var(
                                      --couleur-primaire
                                    );
                                    margin-top:0.35rem;
                                    font-weight:600;
                                    margin-left:auto;
                                    text-align:right;">
                                </div>
                            </div>
                        </div>

                        <!-- Note optionnelle -->
                        <div class="form-group-fintrack">
                            <label
                              class="form-label-fintrack"
                              for="note">
                                Note (optionnel)
                            </label>
                            <textarea
                              class=
                              "form-control-fintrack"
                              id="note" name="note"
                              placeholder=
                              "Commentaire, détails..."
                              rows="2"
                              maxlength="500"><?=
                                $valeurs['note']
                            ?></textarea>
                        </div>

                        <!-- Email optionnel -->
                        <div class="form-group-fintrack">
                            <label
                              class="form-label-fintrack"
                              for="email">
                                Email de confirmation
                                <span style="
                                  font-size:
                                    var(--taille-xs);
                                  color:
                                    var(--texte-clair);
                                  font-weight:400;">
                                    (facultatif)
                                </span>
                            </label>
                            <input type="email"
                              class=
                              "form-control-fintrack"
                              id="email" name="email"
                              value="<?=
                                $valeurs['email']
                              ?>"
                              placeholder=
                              "votre@email.com">
                            <div
                              class=
                              "message-erreur-champ"
                              id="erreur-email">
                            </div>
                        </div>

                        <hr class="separateur">

                        <!-- Boutons d'action -->
                        <div class="d-flex gap-3
                                    flex-wrap">
                            <button type="submit"
                              class="btn-fintrack
                                     btn-primaire
                                     btn-lg"
                              id="btn-submit">
                                <i class="bi
                                   bi-check-circle">
                                </i>
                                Enregistrer le track
                            </button>
                            <button type="reset"
                              class="btn-fintrack
                                     btn-outline"
                              id="btn-reset">
                                <i class="bi
                                   bi-arrow-counterclockwise">
                                </i>
                                Réinitialiser
                            </button>
                            <a href="dashboard.php"
                               class="btn-fintrack
                                      btn-outline">
                                <i class="bi
                                   bi-arrow-left"></i>
                                Dashboard
                            </a>
                        </div>

                    </form>

                    <?php if (!empty($message_succes)): ?>
                    <!-- Boutons après soumission -->
                    <div class="boutons-apres-track"
                         id="boutons-apres-track">
                        <div class="titre-apres">
                            Track enregistré !
                            Que voulez-vous faire ?
                        </div>

                        <!-- Voir le rapport -->
                        <a href="rapport.php"
                           class="btn-fintrack
                                  btn-primaire">
                            <i class="bi
                               bi-file-earmark-bar-graph">
                            </i>
                            Voir mon rapport
                        </a>

                        <!-- Exporter en PDF -->
                        <button
                          class="btn-fintrack
                                 btn-outline"
                          id=
                          "btn-export-transaction-pdf">
                            <i class="bi
                               bi-file-earmark-pdf">
                            </i>
                            Télécharger le reçu PDF
                        </button>

                        <!-- Recevoir par email -->
                        <button
                          class="btn-fintrack
                                 btn-outline"
                          id="btn-ouvrir-email-track"
                          onclick="
                            document.getElementById(
                              'zone-email-track'
                            ).style.display = 'block';
                            this.style.display = 'none';
                          ">
                            <i class="bi
                               bi-envelope-arrow-up">
                            </i>
                            Recevoir par mail
                        </button>

                        <!-- Nouveau track -->
                        <a href="formulaire.php"
                           class="btn-fintrack"
                           style="
                             background:
                               var(--couleur-or);
                             color:white;">
                            <i class="bi
                               bi-plus-circle"></i>
                            Nouveau track
                        </a>
                    </div>

                    <!-- Zone saisie email -->
                    <div class="zone-email-track"
                         id="zone-email-track"
                         style="display:none;">
                        <label
                          class="form-label-fintrack"
                          for="email-envoi-track">
                            <i class="bi bi-envelope
                                      me-2"></i>
                            Entrez votre email pour
                            recevoir le track en PDF
                        </label>
                        <div class="d-flex gap-2">
                            <input type="email"
                              class=
                              "form-control-fintrack"
                              id="email-envoi-track"
                              placeholder=
                              "votre@email.com"
                              style="flex:1;">
                            <button
                              class="btn-fintrack
                                     btn-primaire"
                              id=
                              "btn-envoyer-email-track">
                                <i class="bi bi-send">
                                </i>
                                Envoyer
                            </button>
                        </div>
                        <div id="msg-envoi-email"
                             style="
                               margin-top:0.5rem;
                               font-size:
                                 var(--taille-sm);">
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </main>

    <!-- Pied de page -->
    <footer class="footer-fintrack">
        <div class="container">
            <div class="footer-texte">
                <span class="footer-logo">
                    FinTrack
                </span>
                <p class="mb-0">
                    Projet Web — ECAM-EPMI Cergy
                    · 2025-2026
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts externes -->
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>
    <script
      src="https://code.jquery.com/jquery-3.7.1.min.js">
    </script>
    <script
      src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js">
    </script>
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/data.js"></script>
    <script src="assets/js/pdf.js"></script>

    <script>
    // ================================================
    // Script formulaire : catégories multi-select,
    // indicateur de type, localStorage, envoi email
    // ================================================

    // Catégories PHP injectées en JS
    const catsRevenus  = <?= json_encode(
        $cats_revenus, JSON_UNESCAPED_UNICODE
    ) ?>;
    const catsDepenses = <?= json_encode(
        $cats_depenses, JSON_UNESCAPED_UNICODE
    ) ?>;

    // Données du track soumis (depuis PHP)
    const donneesPHP = <?= $donnees_js ?>;

    document.addEventListener('DOMContentLoaded', () => {

        // --- Références aux éléments DOM ---
        const selectType = document.getElementById(
            'type-transaction'
        );
        const wrapperCats = document.getElementById(
            'wrapper-categories'
        );
        const resumeCats = document.getElementById(
            'resume-categories'
        );
        const indicateur = document.getElementById(
            'indicateur-type'
        );
        const iconeInd = document.getElementById(
            'icone-indicateur'
        );
        const texteInd = document.getElementById(
            'texte-indicateur'
        );
        const sousTexteInd = document.getElementById(
            'sous-texte-indicateur'
        );
        const inputDesc = document.getElementById(
            'description'
        );
        const compteurDesc = document.getElementById(
            'compteur-desc'
        );
        const champDate = document.getElementById(
            'date-transaction'
        );
        const champEmail = document.getElementById(
            'email'
        );
        const erreurEmail = document.getElementById(
            'erreur-email'
        );
        // Champ caché pour le texte "Autre"
        const champAutreTexte = document.getElementById(
            'categorie-autre-texte'
        );

        // Date du jour par défaut si vide
        if (champDate && !champDate.value) {
            champDate.value = new Date()
                .toISOString().split('T')[0];
        }

        // ==============================================
        // Animation ouverture / fermeture du wrapper
        // ==============================================

        // Référence au bouton chevron
        const btnCollapse = document.getElementById(
            'btn-collapse-cats'
        );

        /** Ouvre le panneau catégories */
        function ouvrirWrapper() {
            wrapperCats.classList.remove('collapsed');
            wrapperCats.classList.add('expanded');
            // Hauteur réelle du contenu
            wrapperCats.style.maxHeight =
                wrapperCats.scrollHeight + 'px';
            btnCollapse.classList.add('open');
        }

        /** Ferme le panneau catégories */
        function fermerWrapper() {
            // Fixer la hauteur actuelle d'abord
            wrapperCats.style.maxHeight =
                wrapperCats.scrollHeight + 'px';
            // Forcer un reflow pour la transition
            void wrapperCats.offsetHeight;
            wrapperCats.classList.remove('expanded');
            wrapperCats.classList.add('collapsed');
            wrapperCats.style.maxHeight = '0';
            btnCollapse.classList.remove('open');
        }

        /** Basculer ouvert/fermé */
        function toggleWrapper() {
            if (
                wrapperCats.classList
                    .contains('collapsed')
            ) {
                ouvrirWrapper();
            } else {
                fermerWrapper();
            }
        }

        // Clic sur le bouton chevron
        if (btnCollapse) {
            btnCollapse.addEventListener(
                'click',
                (e) => {
                    e.preventDefault();
                    toggleWrapper();
                }
            );
        }

        // ==============================================
        // Construction des catégories
        // - "Tous" en premier (sélect./désélect. tout)
        // - Catégories normales dans une grille
        // - "Autre" en dernier (champ texte libre)
        // - Bouton X pour fermer
        // ==============================================
        function construireCategories(
            type, catsDejaSelect
        ) {
            // Choisir la bonne liste
            const cats = type === 'revenu'
                ? catsRevenus : catsDepenses;
            wrapperCats.innerHTML = '';

            // --- Bouton fermer (X) ---
            const btnFermer =
                document.createElement('button');
            btnFermer.type = 'button';
            btnFermer.className = 'btn-close-cats';
            btnFermer.title = 'Fermer';
            btnFermer.innerHTML = '&times;';
            btnFermer.addEventListener(
                'click', () => fermerWrapper()
            );
            wrapperCats.appendChild(btnFermer);

            // --- Élément "Tous" (pleine largeur) ---
            const divTous =
                document.createElement('div');
            divTous.className =
                'multi-select-item-tous';
            divTous.innerHTML = `
                <input type="checkbox"
                       id="cat-tous">
                <label for="cat-tous">
                    Tous
                </label>`;
            const cbTous =
                divTous.querySelector('input');

            // Coche/décoche toutes les catégories
            cbTous.addEventListener('change', () => {
                // Cases de la grille
                const allCbs = wrapperCats
                    .querySelectorAll(
                        '.cats-grid '
                        + 'input[type="checkbox"]'
                    );
                allCbs.forEach(cb => {
                    cb.checked = cbTous.checked;
                    const item = cb.closest(
                        '.multi-select-item'
                    );
                    if (item) {
                        item.classList.toggle(
                            'selected',
                            cbTous.checked
                        );
                    }
                });
                // Case "Autre" aussi
                const cbAutre =
                    wrapperCats.querySelector(
                        '#cat-autre'
                    );
                if (cbAutre) {
                    cbAutre.checked = cbTous.checked;
                    cbAutre.closest(
                        '.multi-select-item-autre'
                    ).classList.toggle(
                        'selected', cbTous.checked
                    );
                    // Afficher/masquer champ texte
                    const inp = document.getElementById(
                        'input-autre-cat'
                    );
                    if (inp) {
                        inp.style.display =
                            cbTous.checked
                                ? 'block' : 'none';
                    }
                }
                mettreAJourResume();
            });

            // Clic sur le div "Tous" entier
            divTous.addEventListener('click', (e) => {
                if (
                    e.target !== cbTous
                    && e.target.tagName !== 'LABEL'
                ) {
                    cbTous.checked = !cbTous.checked;
                    cbTous.dispatchEvent(
                        new Event('change')
                    );
                }
            });
            wrapperCats.appendChild(divTous);

            // --- Grille des catégories ---
            const grille =
                document.createElement('div');
            grille.className = 'cats-grid';

            cats.forEach(cat => {
                const estSelect =
                    catsDejaSelect.includes(cat);
                const div =
                    document.createElement('div');
                div.className =
                    'multi-select-item'
                    + (estSelect ? ' selected' : '');
                // ID sans caractères spéciaux
                const idSafe = cat.replace(
                    /[^a-zA-Z0-9]/g, ''
                );
                div.innerHTML = `
                    <input type="checkbox"
                           name="categorie[]"
                           value="${cat}"
                           id="cat-${idSafe}"
                           ${estSelect
                               ? 'checked' : ''}>
                    <label for="cat-${idSafe}"
                           style="cursor:pointer;
                                  flex:1;">
                        ${cat}
                    </label>`;

                const cb = div.querySelector('input');
                // Changement d'état
                cb.addEventListener('change', () => {
                    div.classList.toggle(
                        'selected', cb.checked
                    );
                    syncTous();
                    mettreAJourResume();
                });
                // Clic sur le div entier
                div.addEventListener('click', e => {
                    if (
                        e.target !== cb
                        && e.target.tagName !== 'LABEL'
                    ) {
                        cb.checked = !cb.checked;
                        div.classList.toggle(
                            'selected', cb.checked
                        );
                        syncTous();
                        mettreAJourResume();
                    }
                });
                grille.appendChild(div);
            });
            wrapperCats.appendChild(grille);

            // --- Élément "Autre" (pleine largeur) ---
            const divAutre =
                document.createElement('div');
            divAutre.className =
                'multi-select-item-autre';
            divAutre.innerHTML = `
                <input type="checkbox"
                       name="categorie[]"
                       value="__autre__"
                       id="cat-autre">
                <label for="cat-autre"
                       style="cursor:pointer;
                              flex:1;">
                    Autre...
                </label>
                <input type="text"
                       id="input-autre-cat"
                       class="input-autre-categorie"
                       placeholder=
                       "Saisissez votre catégorie"
                       style="display:none;"
                       maxlength="60">`;

            const cbAutre =
                divAutre.querySelector('#cat-autre');
            const inputAutre =
                divAutre.querySelector(
                    '#input-autre-cat'
                );

            // Affiche/masque le champ texte
            cbAutre.addEventListener('change', () => {
                divAutre.classList.toggle(
                    'selected', cbAutre.checked
                );
                inputAutre.style.display =
                    cbAutre.checked
                        ? 'block' : 'none';
                if (cbAutre.checked) {
                    inputAutre.focus();
                }
                syncTous();
                mettreAJourResume();
            });

            // Synchronise le champ caché du form
            inputAutre.addEventListener(
                'input',
                () => {
                    champAutreTexte.value =
                        inputAutre.value;
                }
            );

            // Clic sur le div "Autre" entier
            divAutre.addEventListener('click', e => {
                if (
                    e.target !== cbAutre
                    && e.target.tagName !== 'LABEL'
                    && e.target !== inputAutre
                ) {
                    cbAutre.checked =
                        !cbAutre.checked;
                    cbAutre.dispatchEvent(
                        new Event('change')
                    );
                }
            });
            wrapperCats.appendChild(divAutre);

            // Résumé initial
            mettreAJourResume();

            // Ouvrir le wrapper automatiquement
            ouvrirWrapper();

            // Recalculer la hauteur après affichage
            requestAnimationFrame(() => {
                if (
                    wrapperCats.classList
                        .contains('expanded')
                ) {
                    wrapperCats.style.maxHeight =
                        wrapperCats.scrollHeight
                        + 'px';
                }
            });
        }

        /**
         * Synchronise "Tous" : coché si toutes
         * les autres cases le sont aussi.
         */
        function syncTous() {
            const cbTous =
                wrapperCats.querySelector(
                    '#cat-tous'
                );
            if (!cbTous) return;
            const allCbs =
                wrapperCats.querySelectorAll(
                    '.cats-grid '
                    + 'input[type="checkbox"]'
                );
            const cbAutre =
                wrapperCats.querySelector(
                    '#cat-autre'
                );
            let toutCoche = true;
            allCbs.forEach(cb => {
                if (!cb.checked) toutCoche = false;
            });
            if (cbAutre && !cbAutre.checked) {
                toutCoche = false;
            }
            cbTous.checked = toutCoche;
        }

        /**
         * Met à jour le résumé sous le wrapper
         * (nombre de catégories sélectionnées).
         */
        function mettreAJourResume() {
            // Compter hors "Tous"
            const nb = wrapperCats.querySelectorAll(
                'input[name="categorie[]"]:checked'
            ).length;
            resumeCats.textContent = nb > 0
                ? nb + ' catégorie(s) sélectionnée(s)'
                : '';
        }

        // ==============================================
        // Changement de type : reconstruit catégories
        // ==============================================
        selectType.addEventListener(
            'change',
            function () {
                const type = this.value;
                if (type === 'revenu') {
                    construireCategories(type, []);
                    indicateur.className =
                        'indicateur-type type-revenu';
                    iconeInd.textContent = '+';
                    texteInd.textContent =
                        'Track : Revenu';
                    sousTexteInd.textContent =
                        'Salaire, freelance, '
                        + 'allocations...';
                } else if (type === 'depense') {
                    construireCategories(type, []);
                    indicateur.className =
                        'indicateur-type type-depense';
                    iconeInd.textContent = '-';
                    texteInd.textContent =
                        'Track : Dépense';
                    sousTexteInd.textContent =
                        'Courses, loyer, transport...';
                } else {
                    // Aucun type sélectionné
                    indicateur.className =
                        'indicateur-type type-neutre';
                    iconeInd.innerHTML =
                        '<i class="bi '
                        + 'bi-credit-card"></i>';
                    texteInd.textContent =
                        'Sélectionnez un type';
                    sousTexteInd.textContent =
                        'Revenu ou Dépense';
                    wrapperCats.innerHTML =
                        '<div style="'
                        + 'color:var(--texte-clair);'
                        + 'font-size:'
                        + 'var(--taille-sm);'
                        + 'padding:0.5rem;'
                        + 'text-align:center;">'
                        + 'Sélectionnez d\'abord '
                        + 'un type</div>';
                    fermerWrapper();
                    resumeCats.textContent = '';
                }
            }
        );

        // ==============================================
        // Re-soumission avec erreur : reconstruire
        // les catégories pré-sélectionnées (PHP)
        // ==============================================
        if (
            selectType.value
            && '<?= !empty($valeurs["type"])
                ? "true" : "false" ?>' === 'true'
        ) {
            const catsPHP = <?= json_encode(
                array_values($valeurs['categorie']),
                JSON_UNESCAPED_UNICODE
            ) ?>;
            construireCategories(
                selectType.value, catsPHP
            );
            const type = selectType.value;
            if (type === 'revenu') {
                indicateur.className =
                    'indicateur-type type-revenu';
                iconeInd.textContent = '+';
                texteInd.textContent =
                    'Track : Revenu';
            } else if (type === 'depense') {
                indicateur.className =
                    'indicateur-type type-depense';
                iconeInd.textContent = '-';
                texteInd.textContent =
                    'Track : Dépense';
            }
        }

        // ==============================================
        // Compteur de caractères description
        // ==============================================
        inputDesc.addEventListener(
            'input',
            function () {
                compteurDesc.textContent =
                    this.value.length + ' / 255';
                compteurDesc.style.color =
                    this.value.length > 230
                        ? 'var(--couleur-avertissement)'
                        : 'var(--texte-clair)';
            }
        );

        // ==============================================
        // Validation email côté client
        // ==============================================
        if (champEmail) {
            champEmail.addEventListener(
                'blur',
                function () {
                    const re =
                        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (
                        this.value
                        && !re.test(this.value)
                    ) {
                        erreurEmail.textContent =
                            'Email invalide.';
                        this.classList.add(
                            'champ-invalide'
                        );
                    } else {
                        erreurEmail.textContent = '';
                        this.classList.remove(
                            'champ-invalide'
                        );
                        if (this.value) {
                            this.classList.add(
                                'champ-valide'
                            );
                        }
                    }
                }
            );
        }

        // ==============================================
        // Sauvegarde localStorage après succès
        // ==============================================
        if (donneesPHP && donneesPHP.montant) {
            const trackSauvegarde =
                ajouterTrack(donneesPHP);

            // Pré-remplir les champs pour pdf.js
            const elMontant =
                document.getElementById('montant');
            const elType = document.getElementById(
                'type-transaction'
            );
            const elDate = document.getElementById(
                'date-transaction'
            );
            const elDesc = document.getElementById(
                'description'
            );
            if (elMontant)
                elMontant.value =
                    donneesPHP.montant;
            if (elType)
                elType.value = donneesPHP.type;
            if (elDate)
                elDate.value = donneesPHP.date;
            if (elDesc)
                elDesc.value =
                    donneesPHP.description;
        }

        // ==============================================
        // Envoi du reçu PDF par email
        // ==============================================
        const btnEnvoyerEmail =
            document.getElementById(
                'btn-envoyer-email-track'
            );
        if (btnEnvoyerEmail) {
            btnEnvoyerEmail.addEventListener(
                'click',
                () => {
                    const emailSaisi =
                        document.getElementById(
                            'email-envoi-track'
                        ).value.trim();
                    const msgEnvoi =
                        document.getElementById(
                            'msg-envoi-email'
                        );
                    const re =
                        /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

                    // Vérification email
                    if (
                        !emailSaisi
                        || !re.test(emailSaisi)
                    ) {
                        msgEnvoi.innerHTML =
                            '<span style="color:'
                            + 'var(--couleur-danger)'
                            + ';">'
                            + 'Entrez un email valide.'
                            + '</span>';
                        return;
                    }

                    // Message d'attente
                    msgEnvoi.innerHTML =
                        '<span style="color:'
                        + 'var(--texte-secondaire)'
                        + ';">'
                        + 'Génération et envoi...'
                        + '</span>';
                    btnEnvoyerEmail.disabled = true;

                    // Générer le PDF et l'envoyer
                    const base64 =
                        genererTrackPDFBase64(
                            donneesPHP
                        );
                    if (base64) {
                        envoyerPDFParEmail(
                            base64,
                            emailSaisi,
                            'fintrack_track.pdf',
                            (rep) => {
                                btnEnvoyerEmail
                                    .disabled = false;
                                if (rep.succes) {
                                    msgEnvoi.innerHTML =
                                        '<span style='
                                        + '"color:var('
                                        + '--couleur-'
                                        + 'succes);">'
                                        + '<i class='
                                        + '"bi bi-check'
                                        + '-circle">'
                                        + '</i> Email '
                                        + 'envoyé !'
                                        + '</span>';
                                } else {
                                    msgEnvoi.innerHTML =
                                        '<span style='
                                        + '"color:var('
                                        + '--couleur-'
                                        + 'danger);">'
                                        + '<i class='
                                        + '"bi bi-x-'
                                        + 'circle"></i>'
                                        + ' '
                                        + (rep.message
                                          || 'Erreur.')
                                        + '</span>';
                                }
                            }
                        );
                    } else {
                        btnEnvoyerEmail
                            .disabled = false;
                        msgEnvoi.innerHTML =
                            '<span style="color:'
                            + 'var(--couleur-danger)'
                            + ';">'
                            + '<i class="bi '
                            + 'bi-x-circle"></i> '
                            + 'Erreur PDF.</span>';
                    }
                }
            );
        }

        // ==============================================
        // Formatage du montant (séparateurs fr-FR)
        // ==============================================
        const inputMontant =
            document.getElementById('montant');
        const affichageMontant =
            document.getElementById(
                'affichage-montant'
            );

        /** Formate un nombre en euros (fr-FR) */
        function formatMontantAffichage(valeur) {
            if (!valeur || valeur === '0') return '';
            const num = parseFloat(valeur);
            if (isNaN(num)) return '';
            return num.toLocaleString('fr-FR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        if (inputMontant && affichageMontant) {
            inputMontant.addEventListener(
                'input',
                () => {
                    affichageMontant.textContent =
                        inputMontant.value
                            ? formatMontantAffichage(
                                inputMontant.value
                              ) + ' \u20AC'
                            : '';
                }
            );
            // Initialiser si montant pré-rempli
            if (inputMontant.value) {
                affichageMontant.textContent =
                    formatMontantAffichage(
                        inputMontant.value
                    ) + ' \u20AC';
            }
        }

    });
    </script>

</body>
</html>
