// ============================================================
// FICHIER      : assets/js/theme.js
// AUTEUR       : Ayoub — Développeur Frontend JS
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Gestion du toggle thème sombre / clair.
//                Le choix de l'utilisateur est sauvegardé dans
//                localStorage pour être conservé entre les pages.
//                Mise à jour automatique des graphiques Chart.js
//                au changement de thème.
// DATE         : Mars 2026
// ============================================================


// -- 1. Constantes --

// Clé utilisée pour stocker le thème dans localStorage
const CLE_THEME    = 'fintrack_theme';
const THEME_SOMBRE = 'dark';
const THEME_CLAIR  = 'light';


// -- 2. Application immédiate du thème (avant rendu visuel) --
// Placé en IIFE pour éviter tout flash blanc/noir au chargement de la page

(function appliquerThemeImmediatement() {
    const theme = localStorage.getItem(CLE_THEME) || THEME_CLAIR;
    document.documentElement.setAttribute('data-theme', theme);
})();


// -- 3. Fonctions principales --

// Retourne le thème actuellement actif ('dark' ou 'light')
function getThemeActuel() {
    return document.documentElement.getAttribute('data-theme') || THEME_CLAIR;
}

// Applique un thème, le sauvegarde et met à jour le bouton + les graphiques
function appliquerTheme(theme) {
    // Application sur l'élément <html> — active les variables CSS correspondantes
    document.documentElement.setAttribute('data-theme', theme);

    // Sauvegarde dans localStorage pour persister entre les pages
    localStorage.setItem(CLE_THEME, theme);

    // Mise à jour de l'icône dans la navbar
    mettreAJourBoutonTheme(theme);

    // Mise à jour des graphiques Chart.js si présents sur la page
    if (typeof mettreAJourCouleursGraphes === 'function') {
        mettreAJourCouleursGraphes();
    }
}

// Bascule entre thème clair et sombre
function basculerTheme() {
    const nouveauTheme = getThemeActuel() === THEME_SOMBRE ? THEME_CLAIR : THEME_SOMBRE;
    appliquerTheme(nouveauTheme);
}

// Met à jour l'icône et le titre du bouton toggle dans la navbar
function mettreAJourBoutonTheme(theme) {
    document.querySelectorAll('.btn-theme-toggle').forEach(function(bouton) {
        const icone = bouton.querySelector('.icone-theme');

        // Soleil en mode sombre (pour revenir au clair), lune en mode clair
        if (icone) {
            icone.innerHTML = theme === THEME_SOMBRE
                ? '<i class="bi bi-sun-fill"></i>'
                : '<i class="bi bi-moon-fill"></i>';
        }

        // Attributs d'accessibilité du bouton
        const titreBouton = theme === THEME_SOMBRE
            ? 'Passer en thème clair'
            : 'Passer en thème sombre';
        const labelBouton = theme === THEME_SOMBRE
            ? 'Activer le thème clair'
            : 'Activer le thème sombre';
        bouton.setAttribute('title', titreBouton);
        bouton.setAttribute('aria-label', labelBouton);
    });
}


// -- 4. Initialisation au chargement du DOM --

document.addEventListener('DOMContentLoaded', function () {

    // -- 4.1 Application du thème sauvegardé --
    appliquerTheme(localStorage.getItem(CLE_THEME) || THEME_CLAIR);

    // -- 4.2 Liaison des boutons toggle --
    document.querySelectorAll('.btn-theme-toggle').forEach(function(bouton) {
        bouton.addEventListener('click', basculerTheme);
    });

    // -- 4.3 Raccourci clavier Alt+T --
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 't') {
            e.preventDefault();
            basculerTheme();
        }
    });
});


// -- 5. Écoute des préférences système OS --
// Seulement si l'utilisateur n'a pas encore fait de choix manuel

(function ecouterPreferencesSysteme() {
    if (localStorage.getItem(CLE_THEME)) return; // Choix manuel déjà présent

    const media = window.matchMedia('(prefers-color-scheme: dark)');

    // Application initiale selon la préférence OS
    if (media.matches) {
        appliquerTheme(THEME_SOMBRE);
    }

    // Écoute des changements OS en temps réel
    media.addEventListener('change', function(e) {
        // On ne change que si l'utilisateur n'a pas fait de choix manuel entre-temps
        if (!localStorage.getItem(CLE_THEME)) {
            appliquerTheme(e.matches ? THEME_SOMBRE : THEME_CLAIR);
        }
    });
})();