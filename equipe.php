<!--
=============================================================
  FICHIER      : equipe.php
  AUTEUR       : Ange TEUFACK — Chef de projet & UI/UX Designer
  PROJET       : FinTrack — ECAM-EPMI 2025-2026
  DESCRIPTION  : Page Équipe. Affiche les 5 membres avec photos,
                 rôles et technos. Formulaire de contact en bas
                 (isolé, pas trop mis en avant).
  DATE         : Mars 2026
=============================================================
-->
<?php
// -- Traitement formulaire de contact (si soumis depuis cette page) --
$erreurs_contact   = [];
$succes_contact    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['form_contact'])
) {
    function nett($v) {
        return htmlspecialchars(strip_tags(trim($v)));
    }
    $nom_c     = nett($_POST['nom']     ?? '');
    $email_c   = nett($_POST['email']   ?? '');
    $sujet_c   = nett($_POST['sujet']   ?? '');
    $message_c = nett($_POST['message'] ?? '');

    if (strlen($nom_c) < 2)
        $erreurs_contact[] = 'Nom trop court.';
    if (!filter_var($email_c, FILTER_VALIDATE_EMAIL))
        $erreurs_contact[] = 'Email invalide.';
    if (strlen($sujet_c) < 3)
        $erreurs_contact[] = 'Sujet trop court.';
    if (strlen($message_c) < 10)
        $erreurs_contact[] = 'Message trop court (min 10 car.).';

    // Piège honeypot anti-spam
    if (!empty($_POST['site_web'])) {
        $succes_contact = 'Message envoyé !';
    }

    if (empty($erreurs_contact) && empty($succes_contact)) {
        require_once 'php/traitement_contact.php';
        $ok = envoyerEmailContact([
            'nom'     => $nom_c,
            'email'   => $email_c,
            'sujet'   => $sujet_c,
            'message' => $message_c
        ]);
        $succes_contact = $ok
            ? 'Message envoyé ! Nous vous répondrons sous 24-48h.'
            : '';
        if (!$ok)
            $erreurs_contact[] = 'Erreur envoi. Réessayez.';
    }
}

// -- Tableau des membres de l'équipe --
$membres = [
    [
        'prenom'  => 'Ange',
        'nom'     => 'TEUFACK',
        'photo'   => '',
        'role'    => 'Chef de projet & UI/UX Designer',
        'desc'    => 'Responsable de la coordination de l\'équipe, '
                   . 'de la charte graphique, du design des interfaces '
                   . 'et du système de thèmes clair/sombre.',
        'techs'   => ['HTML5', 'CSS3', 'Bootstrap 5'],
        'couleur' => '#6C63FF',
        'initiale'=> 'A',
        'icone'   => 'bi-palette'
    ],
    [
        'prenom'  => 'Ayoub',
        'nom'     => '',
        'photo'   => '',
        'role'    => 'Développeur Frontend JS',
        'desc'    => 'Développe toutes les interactions dynamiques '
                   . 'du site : toggle thème, filtres jQuery UI, '
                   . 'animations au scroll, compteurs animés '
                   . 'et navigation.',
        'techs'   => ['JavaScript ES6+', 'jQuery UI', 'Vue.js'],
        'couleur' => '#0EA5E9',
        'initiale'=> 'A',
        'icone'   => 'bi-code-slash'
    ],
    [
        'prenom'  => 'Ghita',
        'nom'     => '',
        'photo'   => '',
        'role'    => 'Développeuse Backend PHP',
        'desc'    => 'Gère toute la logique serveur : validation '
                   . 'des formulaires, sécurisation des données, '
                   . 'configuration SMTP et envoi d\'emails '
                   . 'via PHPMailer.',
        'techs'   => ['PHP 8', 'PHPMailer', 'SMTP'],
        'couleur' => '#F5A623',
        'initiale'=> 'G',
        'icone'   => 'bi-shield-check'
    ],
    [
        'prenom'  => 'Benoit',
        'nom'     => '',
        'photo'   => '',
        'role'    => 'Développeur Data & Visualisation',
        'desc'    => 'Responsable des données localStorage, '
                   . 'des 3 graphiques Chart.js interactifs, '
                   . 'de l\'historique filtré et des conseils '
                   . 'financiers automatiques.',
        'techs'   => ['Chart.js', 'JavaScript', 'localStorage'],
        'couleur' => '#00C896',
        'initiale'=> 'B',
        'icone'   => 'bi-bar-chart-line'
    ],
    [
        'prenom'  => 'Maroua',
        'nom'     => '',
        'photo'   => '',
        'role'    => 'Développeuse PDF & Documentation',
        'desc'    => 'Crée les exports PDF via jsPDF (reçus de '
                   . 'tracks et rapports mensuels), rédige la '
                   . 'documentation et prépare l\'archive finale '
                   . 'pour le rendu.',
        'techs'   => ['jsPDF', 'Markdown', 'Documentation'],
        'couleur' => '#FF4757',
        'initiale'=> 'M',
        'icone'   => 'bi-file-earmark-pdf'
    ]
];
?>
<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Notre Équipe — FinTrack</title>
    <link rel="icon" type="image/x-icon"
          href="assets/img/favicon.ico">
    <!-- Bootstrap CSS + Icônes -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
          rel="stylesheet">
    <!-- Styles du projet -->
    <link rel="stylesheet" href="assets/css/theme.css">
    <link rel="stylesheet" href="assets/css/style.css">

    <style>
        /* -- Héro équipe -- */
        .hero-equipe {
            background:    var(--degrade-hero);
            padding:       4rem 0 3rem;
            text-align:    center;
            margin-bottom: 3rem;
        }

        .hero-equipe .hero-titre {
            font-family:    var(--police-hero);
            font-size:      clamp(2.5rem, 5vw, 4rem);
            letter-spacing: 3px;
            color:          var(--couleur-primaire);
            margin-bottom:  0.75rem;
        }

        .hero-equipe p {
            font-family: var(--police-corps);
            font-size:   var(--taille-lg);
            color:       var(--texte-secondaire);
            max-width:   560px;
            margin:      0 auto;
        }

        /* -- Carte membre -- */
        .carte-membre-detail {
            background:    var(--bg-carte);
            border:        1px solid var(--bordure-carte);
            border-radius: var(--arrondi-2xl);
            overflow:      hidden;
            box-shadow:    var(--ombre-carte);
            transition:    all var(--transition-spring);
            height:        100%;
        }

        .carte-membre-detail:hover {
            transform:  translateY(-6px);
            box-shadow: var(--ombre-carte-hover);
        }

        /* Photo du membre */
        .photo-wrapper {
            position: relative;
            height:   220px;
            overflow: hidden;
        }

        .photo-membre {
            width:           100%;
            height:          100%;
            object-fit:      cover;
            object-position: center top;
            transition:      transform var(--transition-lente);
        }

        .carte-membre-detail:hover .photo-membre {
            transform: scale(1.05);
        }

        /* Avatar de secours si pas de photo */
        .avatar-fallback {
            width:           100%;
            height:          100%;
            display:         flex;
            align-items:     center;
            justify-content: center;
            font-family:     var(--police-hero);
            font-size:       4rem;
            letter-spacing:  2px;
            color:           white;
        }

        /* Bandeau rôle sur la photo */
        .bandeau-role {
            position:       absolute;
            bottom:         0;
            left:           0;
            right:          0;
            background:     linear-gradient(
                transparent,
                rgba(13, 17, 23, 0.85)
            );
            color:          white;
            padding:        1.5rem 1.25rem 0.85rem;
            font-family:    var(--police-corps);
            font-size:      var(--taille-xs);
            font-weight:    600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Corps de la carte */
        .corps-membre {
            padding: 1.35rem 1.5rem 1.5rem;
        }

        .nom-membre {
            font-family:   var(--police-titre);
            font-size:     var(--taille-xl);
            font-weight:   900;
            color:         var(--texte-principal);
            margin-bottom: 0.6rem;
        }

        .desc-membre {
            font-family:   var(--police-corps);
            font-size:     var(--taille-sm);
            color:         var(--texte-secondaire);
            line-height:   1.65;
            margin-bottom: 1rem;
        }

        /* Badges technos */
        .techs-membre {
            display:   flex;
            flex-wrap: wrap;
            gap:       0.4rem;
        }

        .badge-tech-membre {
            font-family:   var(--police-corps);
            font-size:     0.7rem;
            font-weight:   700;
            padding:       0.22rem 0.6rem;
            border-radius: var(--arrondi-full);
            background:    var(--couleur-primaire-clair);
            color:         var(--couleur-primaire);
            border:        1px solid
                           var(--couleur-primaire-clair);
        }

        /* -- Section contact -- */
        .section-contact-bas {
            background: var(--bg-section-alt);
            border-top: 1px solid var(--bordure-couleur);
            padding:    3rem 0;
            margin-top: 4rem;
        }

        .conteneur-contact-bas {
            max-width: 580px;
            margin:    0 auto;
        }

        /* Compteur de caractères */
        .compteur-msg {
            font-size:  var(--taille-xs);
            color:      var(--texte-clair);
            text-align: right;
            margin-top: 0.2rem;
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

    <!-- Section héro de la page équipe -->
    <section class="hero-equipe">
        <div class="container">
            <div class="scroll-reveal">
                <h1 class="hero-titre">NOTRE ÉQUIPE</h1>
                <p>
                    5 étudiants en 2e année prépa ingénieur
                    à l'ECAM-EPMI de Cergy, réunis autour
                    du projet FinTrack.
                </p>
            </div>
        </div>
    </section>

    <!-- Grille des membres -->
    <main>
        <div class="container pb-5">

            <div class="row g-4 membres-grid">
                <?php foreach ($membres as $i => $m): ?>
                <div class="col-12 col-sm-6 col-lg-4
                            scroll-reveal">
                    <div class="carte-membre-detail">

                        <!-- Photo du membre -->
                        <div class="photo-wrapper">
                            <img src="<?= $m['photo'] ?>"
                                 alt="Photo de <?= $m['prenom'] ?>"
                                 class="photo-membre"
                                 onerror="this.style.display='none';
                                          this.nextElementSibling
                                              .style.display='flex';">
                            <!-- Avatar de secours -->
                            <div class="avatar-fallback"
                                 style="display:none;
                                        background:linear-gradient(
                                            135deg,
                                            <?= $m['couleur'] ?>,
                                            <?= $m['couleur'] ?>88
                                        );">
                                <?= $m['initiale'] ?>
                            </div>
                            <!-- Bandeau rôle -->
                            <div class="bandeau-role">
                                <i class="bi <?= $m['icone'] ?>
                                   me-1"></i>
                                <?= $m['role'] ?>
                            </div>
                        </div>

                        <!-- Corps de la carte -->
                        <div class="corps-membre">
                            <div class="nom-membre">
                                <?= $m['prenom'] ?><?= $m['nom']
                                    ? ' ' . $m['nom'] : '' ?>
                            </div>
                            <p class="desc-membre">
                                <?= $m['desc'] ?>
                            </p>
                            <div class="techs-membre">
                                <?php foreach ($m['techs'] as $tech): ?>
                                    <span class="badge-tech-membre">
                                        <?= $tech ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Statistiques du projet -->
            <div class="row g-4 mt-4">
                <div class="col-12 col-sm-6 col-md-3
                            scroll-reveal">
                    <div class="carte"
                         style="text-align:center;
                                padding:1.5rem 1rem;">
                        <div class="stats-nombre
                                    stats-primaire">5</div>
                        <div class="stats-label">Membres</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3
                            scroll-reveal">
                    <div class="carte"
                         style="text-align:center;
                                padding:1.5rem 1rem;">
                        <div class="stats-nombre
                                    stats-succes">7</div>
                        <div class="stats-label">Pages</div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3
                            scroll-reveal">
                    <div class="carte"
                         style="text-align:center;
                                padding:1.5rem 1rem;">
                        <div class="stats-nombre
                                    stats-or">7</div>
                        <div class="stats-label">
                            Librairies
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3
                            scroll-reveal">
                    <div class="carte"
                         style="text-align:center;
                                padding:1.5rem 1rem;">
                        <div class="stats-nombre
                                    stats-danger">1</div>
                        <div class="stats-label">Projet</div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Section formulaire de contact (en bas, isolée) -->
        <section class="section-contact-bas">
            <div class="container">
                <div class="conteneur-contact-bas">

                    <div class="text-center mb-4 scroll-reveal">
                        <h2>Une question ? Contactez-nous</h2>
                        <p>
                            Remplissez ce formulaire, nous vous
                            répondrons sous 24 à 48 heures.
                        </p>
                    </div>

                    <!-- Affichage des erreurs -->
                    <?php if (!empty($erreurs_contact)): ?>
                        <div class="alerte alerte-erreur">
                            <span class="alerte-icone">
                                <i class="bi bi-x-circle"></i>
                            </span>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($erreurs_contact as $e): ?>
                                    <li><?= $e ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <!-- Affichage du succès -->
                    <?php if (!empty($succes_contact)): ?>
                        <div class="alerte alerte-succes">
                            <span class="alerte-icone">
                                <i class="bi bi-check-circle"></i>
                            </span>
                            <strong><?= $succes_contact ?></strong>
                        </div>
                    <?php endif; ?>

                    <!-- Formulaire de contact -->
                    <div class="carte scroll-reveal">
                        <form method="POST"
                              action="equipe.php"
                              novalidate>
                            <input type="hidden"
                                   name="form_contact"
                                   value="1">
                            <!-- Piège honeypot anti-spam -->
                            <div class="honeypot">
                                <input type="text"
                                       name="site_web"
                                       tabindex="-1"
                                       autocomplete="off">
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label-fintrack
                                                  champ-obligatoire"
                                           for="nom-c">
                                        Nom
                                    </label>
                                    <input type="text"
                                           class="form-control-fintrack"
                                           id="nom-c"
                                           name="nom"
                                           placeholder="Prénom Nom"
                                           required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label-fintrack
                                                  champ-obligatoire"
                                           for="email-c">
                                        Email
                                    </label>
                                    <input type="email"
                                           class="form-control-fintrack"
                                           id="email-c"
                                           name="email"
                                           placeholder="votre@email.com"
                                           required>
                                </div>
                            </div>
                            <div class="form-group-fintrack">
                                <label class="form-label-fintrack
                                              champ-obligatoire"
                                       for="sujet-c">
                                    Sujet
                                </label>
                                <input type="text"
                                       class="form-control-fintrack"
                                       id="sujet-c"
                                       name="sujet"
                                       placeholder="Objet de votre message"
                                       maxlength="150"
                                       required>
                            </div>
                            <div class="form-group-fintrack">
                                <label class="form-label-fintrack
                                              champ-obligatoire"
                                       for="message-c">
                                    Message
                                </label>
                                <textarea class="form-control-fintrack"
                                          id="message-c"
                                          name="message"
                                          rows="4"
                                          maxlength="2000"
                                          placeholder="Votre message..."
                                          required></textarea>
                                <div class="compteur-msg"
                                     id="compteur-msg-c">
                                    0 / 2000
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn-fintrack
                                           btn-primaire btn-bloc">
                                <i class="bi bi-send"></i>
                                Envoyer le message
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <!-- Pied de page -->
    <footer class="footer-fintrack">
        <div class="container">
            <div class="footer-texte">
                <span class="footer-logo">FinTrack</span>
                <p class="mb-1">
                    Projet Web — ECAM-EPMI Cergy · 2025-2026
                </p>
                <p style="font-size:var(--taille-xs);">
                    Ange · Ayoub · Ghita · Benoit · Maroua
                </p>
            </div>
        </div>
    </footer>

    <!-- Scripts externes -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Scripts du projet -->
    <script src="assets/js/theme.js"></script>
    <script src="assets/js/main.js"></script>
    <!-- Compteur de caractères du formulaire -->
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const ta = document.getElementById('message-c');
        const ct = document.getElementById('compteur-msg-c');
        if (ta && ct) {
            ta.addEventListener('input', () => {
                ct.textContent =
                    ta.value.length + ' / 2000';
                ct.style.color =
                    ta.value.length > 1800
                        ? 'var(--couleur-avertissement)'
                        : 'var(--texte-clair)';
            });
        }
    });
    </script>
</body>
</html>
