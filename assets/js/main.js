// ============================================================
// FICHIER      : main.js
// AUTEUR       : Ayoub — Développeur Frontend JS
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : JS global : navigation active, animations scroll
//                (IntersectionObserver), compteurs animés,
//                tooltips Bootstrap, datepickers jQuery UI,
//                filtres de période, scroll vers le haut.
// DATE         : Mars 2026
// ============================================================


// ============================================================
// -- 1. Initialisation au chargement du DOM
// ============================================================

// Point d'entrée principal — lance toutes les initialisations
document.addEventListener('DOMContentLoaded', () => {
    initialiserNavActive();
    initialiserTooltips();
    initialiserScrollReveal();
    initialiserDatepickers();
    initialiserFiltresPeriode();
    initialiserScrollHaut();
    initialiserConfirmations();
    initialiserCompteursFigures();
});


// ============================================================
// -- 2. Navigation — lien actif selon la page courante
// ============================================================

// Marque le lien de navigation correspondant à la page actuelle
function initialiserNavActive() {
    const fichierActuel = window.location.pathname
        .split('/').pop() || 'index.html';
    document.querySelectorAll('.navbar-fintrack .nav-link')
        .forEach(lien => {
            const fichierLien = (lien.getAttribute('href') || '')
                .split('/').pop();
            if (fichierLien === fichierActuel ||
                (fichierActuel === '' &&
                 fichierLien === 'index.html')) {
                lien.classList.add('active');
            }
        });
}


// ============================================================
// -- 3. Tooltips Bootstrap 5
// ============================================================

// Active les tooltips Bootstrap sur les éléments concernés
function initialiserTooltips() {
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
        .forEach(el => {
            new bootstrap.Tooltip(el, { trigger: 'hover focus' });
        });
}


// ============================================================
// -- 4. Animations au scroll (IntersectionObserver)
// ============================================================

// Observe les éléments avec les classes .scroll-reveal,
// .scroll-from-left, .scroll-from-right, .scroll-scale
// et ajoute .visible au bon moment
function initialiserScrollReveal() {
    const selecteurs = '.scroll-reveal, .scroll-from-left, '
        + '.scroll-from-right, .scroll-scale';
    const elements = document.querySelectorAll(selecteurs);

    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entrees) => {
        entrees.forEach(entree => {
            if (entree.isIntersecting) {
                entree.target.classList.add('visible');
                observer.unobserve(entree.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    elements.forEach(el => observer.observe(el));
}

// Ajoute automatiquement .scroll-reveal sur les éléments
// principaux de toutes les pages sans modifier chaque HTML
(function ajouterClassesScroll() {
    document.addEventListener('DOMContentLoaded', () => {
        const cibles = [
            '.carte:not(.no-anim)',
            '.carte-stat:not(.no-anim)',
            '.carte-fonctionnalite',
            '.carte-membre',
            '.carte-conseil',
            '.alerte:not(.no-anim)',
            '.section-titre-wrapper'
        ];

        cibles.forEach(selecteur => {
            document.querySelectorAll(selecteur)
                .forEach((el, i) => {
                    if (!el.classList.contains('scroll-reveal') &&
                        !el.classList.contains('scroll-from-left') &&
                        !el.classList.contains('scroll-from-right')) {
                        el.classList.add('scroll-reveal');
                    }
                });
        });

        // Relance l'observer après ajout des classes
        initialiserScrollReveal();
    });
})();


// ============================================================
// -- 5. Compteurs animés (chiffres qui montent de 0 à la valeur)
// ============================================================

// Anime tous les éléments avec data-compteur="true"
function initialiserCompteursFigures() {
    const elements = document.querySelectorAll('[data-compteur]');
    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entrees) => {
        entrees.forEach(entree => {
            if (entree.isIntersecting) {
                const el = entree.target;
                const valeur = parseFloat(
                    el.getAttribute('data-compteur')
                ) || 0;
                const euros = el.getAttribute('data-euros')
                    === 'true';
                animerValeurCompteur(el, valeur, euros);
                observer.unobserve(el);
            }
        });
    }, { threshold: 0.5 });

    elements.forEach(el => observer.observe(el));
}

// Anime un élément de 0 à la valeur cible
function animerValeurCompteur(element, valeurCible,
    enEuros, duree) {
    duree = duree || 1100;
    const debut = performance.now();

    function step(now) {
        const progress = Math.min(
            (now - debut) / duree, 1
        );
        // easeOutCubic
        const ease = 1 - Math.pow(1 - progress, 3);
        const val  = valeurCible * ease;

        element.textContent = enEuros
            ? val.toLocaleString('fr-FR', {
                style: 'currency', currency: 'EUR'
            })
            : Math.round(val).toLocaleString('fr-FR');

        if (progress < 1) requestAnimationFrame(step);
    }

    requestAnimationFrame(step);
}


// ============================================================
// -- 6. Datepickers jQuery UI en français
// ============================================================

// Configure les champs de date avec jQuery UI datepicker
function initialiserDatepickers() {
    if (typeof $ === 'undefined' ||
        typeof $.fn.datepicker === 'undefined') return;

    $('.champ-date-fintrack').datepicker({
        dateFormat:   'dd/mm/yy',
        dayNamesMin:  ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
        monthNames:   [
            'Janvier', 'Février', 'Mars', 'Avril',
            'Mai', 'Juin', 'Juillet', 'Août',
            'Septembre', 'Octobre', 'Novembre', 'Décembre'
        ],
        monthNamesShort: [
            'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun',
            'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'
        ],
        firstDay:     1,
        changeMonth:  true,
        changeYear:   true,
        yearRange:    '2020:2030',
        maxDate:      new Date(),
        showAnim:     'fadeIn'
    });
}


// ============================================================
// -- 7. Filtres de période (Jour/Semaine/Mois/Année)
// ============================================================

// Gère les boutons de filtre par période
function initialiserFiltresPeriode() {
    document.querySelectorAll('.filtre-btn').forEach(bouton => {
        bouton.addEventListener('click', function () {
            const groupe = this.closest('.filtre-periode');
            if (groupe) {
                groupe.querySelectorAll('.filtre-btn')
                    .forEach(b => b.classList.remove('actif'));
            }
            this.classList.add('actif');

            const periode = this.getAttribute('data-periode');
            if (periode &&
                typeof filtrerParPeriode === 'function') {
                filtrerParPeriode(periode);
            }
        });
    });
}


// ============================================================
// -- 8. Bouton scroll vers le haut
// ============================================================

// Crée et gère le bouton de retour en haut de page
function initialiserScrollHaut() {
    const btn = document.createElement('button');
    btn.id        = 'btn-scroll-haut';
    btn.innerHTML = '↑';
    btn.setAttribute('title', 'Retour en haut');
    btn.setAttribute(
        'aria-label', 'Retour en haut de la page'
    );
    document.body.appendChild(btn);

    window.addEventListener('scroll', () => {
        btn.style.display = window.scrollY > 350
            ? 'flex' : 'none';
    });

    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
}


// ============================================================
// -- 9. Confirmations de suppression
// ============================================================

// Ajoute une confirmation avant les actions destructrices
function initialiserConfirmations() {
    document.querySelectorAll('[data-confirmer="true"]')
        .forEach(el => {
            el.addEventListener('click', e => {
                const msg = el.getAttribute('data-message')
                    || 'Confirmer cette action ?';
                if (!confirm(msg)) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        });
}


// ============================================================
// -- 10. Utilitaires partagés sur toutes les pages
// ============================================================

// Formate un montant en euros (fr-FR)
function formaterMontant(montant) {
    return parseFloat(montant || 0).toLocaleString('fr-FR', {
        style: 'currency', currency: 'EUR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Formate une date ISO en JJ/MM/AAAA
function formaterDate(date) {
    if (!date) return '—';
    const d = date instanceof Date ? date : new Date(date);
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    });
}

// Formate une date ISO en "15 mars 2026"
function formaterDateLongue(date) {
    if (!date) return '—';
    const d = date instanceof Date ? date : new Date(date);
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit', month: 'long', year: 'numeric'
    });
}

// Tronque un texte à la longueur maximale
function tronquerTexte(texte, max) {
    if (!texte || texte.length <= max) return texte;
    return texte.substring(0, max) + '...';
}

// Affiche une alerte dans un conteneur donné
function afficherAlerte(idConteneur, message, type) {
    const conteneur = document.getElementById(idConteneur);
    if (!conteneur) return;
    const icones = {
        succes: '✓', erreur: '✗', warning: '!', info: 'i'
    };
    conteneur.innerHTML = `
        <div class="alerte alerte-${type}" role="alert">
            <span class="alerte-icone">
                ${icones[type] || 'i'}
            </span>
            <span>${message}</span>
        </div>`;
    conteneur.scrollIntoView({
        behavior: 'smooth', block: 'center'
    });
    if (type !== 'erreur') {
        setTimeout(() => { conteneur.innerHTML = ''; }, 7000);
    }
}

// Gère l'affichage d'un loader dans un conteneur
function gererLoader(idConteneur, afficher) {
    const conteneur = document.getElementById(idConteneur);
    if (!conteneur) return;
    conteneur.innerHTML = afficher
        ? '<div class="loader-fintrack">'
          + '<div class="spinner"></div>'
          + '<span>Chargement...</span></div>'
        : '';
}
