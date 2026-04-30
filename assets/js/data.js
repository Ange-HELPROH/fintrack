// ============================================================
// FICHIER      : data.js
// AUTEUR       : Benoît — Développeur Data & Visualisation
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Gestion des données via localStorage.
//                Toutes les données partent de 0 au premier
//                arrivée. Les tracks de l'utilisateur sont
//                stockés localement et persistent entre sessions.
//                En Partie 2, sera remplacé par MySQL.
// DATE         : Mars 2026
// ============================================================


// ============================================================
// -- 1. Clé de stockage localStorage
// ============================================================

// Clé principale dans localStorage
const CLE_TRACKS = 'fintrack_tracks';

// Clé pour l'ID auto-incrémenté
const CLE_DERNIER_ID = 'fintrack_dernier_id';


// ============================================================
// -- 2. Fonctions CRUD — Lire / Écrire / Supprimer
// ============================================================

// Retourne tous les tracks depuis localStorage (tableau vide si aucun)
function getTousTracks() {
    const donnees = localStorage.getItem(CLE_TRACKS);
    if (!donnees) return [];
    try {
        return JSON.parse(donnees);
    } catch (e) {
        return [];
    }
}

// Ajoute un nouveau track dans localStorage
// Paramètre : track — objet avec type, catégorie, description,
// montant, date, note
// Retourne le track enrichi avec son id unique
function ajouterTrack(track) {
    const tousLesTracks = getTousTracks();

    // Génère un ID unique auto-incrémenté
    const dernierID = parseInt(
        localStorage.getItem(CLE_DERNIER_ID) || '0'
    );
    const nouvelID = dernierID + 1;
    localStorage.setItem(CLE_DERNIER_ID, String(nouvelID));

    // Enrichit le track avec l'ID et la date de création
    const trackComplet = {
        id:          nouvelID,
        type:        track.type,
        categorie:   Array.isArray(track.categorie)
            ? track.categorie : [track.categorie],
        description: track.description,
        montant:     parseFloat(track.montant),
        date:        track.date ||
            new Date().toISOString().split('T')[0],
        note:        track.note || '',
        cree_le:     new Date().toISOString()
    };

    tousLesTracks.push(trackComplet);
    localStorage.setItem(
        CLE_TRACKS, JSON.stringify(tousLesTracks)
    );

    return trackComplet;
}

// Supprime un track par son ID
function supprimerTrack(id) {
    const tousLesTracks = getTousTracks();
    const apres = tousLesTracks.filter(t => t.id !== id);
    localStorage.setItem(CLE_TRACKS, JSON.stringify(apres));
}

// Vide complètement tous les tracks (remise à zéro)
function reinitialiserTracks() {
    localStorage.removeItem(CLE_TRACKS);
    localStorage.removeItem(CLE_DERNIER_ID);
}

// Retourne le dernier track ajouté (le plus récent)
function getDernierTrack() {
    const tous = getTousTracks();
    if (tous.length === 0) return null;
    return tous[tous.length - 1];
}


// ============================================================
// -- 3. Compatibilité — alias "transactions" pour les anciens
//    scripts. On expose les tracks sous forme de tableau
//    "transactions" pour que charts.js et historique.php
//    continuent de fonctionner.
// ============================================================

// Propriété dynamique — toujours à jour depuis localStorage
Object.defineProperty(window, 'transactions', {
    get: function () { return getTousTracks(); },
    configurable: true
});


// ============================================================
// -- 4. Catégories disponibles (20 catégories larges)
// ============================================================

// 6 catégories de revenus
const categoriesRevenus = [
    'Salaire',
    'Freelance / Mission',
    'Allocations',
    'Remboursement',
    'Investissement',
    'Autre revenu'
];

// 20 catégories de dépenses larges
const categoriesDepenses = [
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


// ============================================================
// -- 5. Fonctions de filtrage
// ============================================================

// Filtre par type ('revenu' ou 'depense')
function getTracksParType(type) {
    return getTousTracks().filter(t => t.type === type);
}

// Filtre par mois et année
function getTracksParMois(mois, annee) {
    return getTousTracks().filter(t => {
        const d = new Date(t.date);
        return d.getMonth() === mois &&
            d.getFullYear() === annee;
    });
}

// Filtre sur les N derniers jours
function getTracksDerniersJours(nbJours) {
    const limite = new Date();
    limite.setDate(limite.getDate() - nbJours);
    return getTousTracks().filter(
        t => new Date(t.date) >= limite
    );
}

// Filtre par catégorie (cherche dans le tableau du track)
function getTracksParCategorie(categorie) {
    return getTousTracks().filter(t =>
        Array.isArray(t.categorie)
            ? t.categorie.includes(categorie)
            : t.categorie === categorie
    );
}

// Retourne les N derniers tracks (les plus récents en premier)
function getDerniersTracks(nb) {
    return getTousTracks()
        .slice()
        .sort((a, b) => new Date(b.date) - new Date(a.date))
        .slice(0, nb);
}

// Alias pour compatibilité avec les anciens noms de fonctions
function getTransactionsParType(type) {
    return getTracksParType(type);
}
function getTransactionsParMois(mois, annee) {
    return getTracksParMois(mois, annee);
}
function getTransactionsDerniersJours(n) {
    return getTracksDerniersJours(n);
}
function getDernieresTransactions(nb) {
    return getDerniersTracks(nb);
}


// ============================================================
// -- 6. Fonctions de calcul
// ============================================================

// Calcule le total d'un tableau de tracks
function calculerTotal(liste) {
    return liste.reduce(
        (s, t) => s + (parseFloat(t.montant) || 0), 0
    );
}

// Calcule revenus, dépenses et solde sur une liste
function calculerSolde(liste) {
    const revenus = calculerTotal(
        liste.filter(t => t.type === 'revenu')
    );
    const depenses = calculerTotal(
        liste.filter(t => t.type === 'depense')
    );
    return {
        revenus:  parseFloat(revenus.toFixed(2)),
        depenses: parseFloat(depenses.toFixed(2)),
        solde:    parseFloat((revenus - depenses).toFixed(2))
    };
}

// Calcule le total des dépenses par catégorie
function calculerDepensesParCategorie(liste) {
    const depenses = liste.filter(t => t.type === 'depense');
    const totaux   = {};

    depenses.forEach(t => {
        const cats = Array.isArray(t.categorie)
            ? t.categorie : [t.categorie];
        cats.forEach(cat => {
            if (!totaux[cat]) totaux[cat] = 0;
            // Si plusieurs catégories, répartition équitable
            totaux[cat] += t.montant / cats.length;
        });
    });

    // Arrondit les totaux
    Object.keys(totaux).forEach(k => {
        totaux[k] = parseFloat(totaux[k].toFixed(2));
    });

    return totaux;
}

// Calcule l'évolution sur les 6 derniers mois
function calculerEvolutionMensuelle() {
    const resultats  = [];
    const maintenant = new Date();

    for (let i = 5; i >= 0; i--) {
        const date = new Date(
            maintenant.getFullYear(),
            maintenant.getMonth() - i, 1
        );
        const mois  = date.getMonth();
        const annee = date.getFullYear();

        const tracksMois = getTracksParMois(mois, annee);
        const stats      = calculerSolde(tracksMois);

        resultats.push({
            mois:     getNomMois(mois).substring(0, 3),
            moisFull: getNomMois(mois),
            annee:    annee,
            revenus:  stats.revenus,
            depenses: stats.depenses,
            solde:    stats.solde
        });
    }

    return resultats;
}

// Calcule les stats globales du mois courant (cartes dashboard)
function calculerStatsGlobales() {
    const maintenant  = new Date();
    const moisActuel  = maintenant.getMonth();
    const anneeActuel = maintenant.getFullYear();
    const moisPrec    = moisActuel === 0
        ? 11 : moisActuel - 1;
    const anneePrec   = moisActuel === 0
        ? anneeActuel - 1 : anneeActuel;

    const tracksMoisAct = getTracksParMois(
        moisActuel, anneeActuel
    );
    const tracksMoisPrec = getTracksParMois(
        moisPrec, anneePrec
    );

    const statsAct  = calculerSolde(tracksMoisAct);
    const statsPrec = calculerSolde(tracksMoisPrec);

    return {
        revenusMoisActuel:  statsAct.revenus,
        depensesMoisActuel: statsAct.depenses,
        soldeMoisActuel:    statsAct.solde,
        nbTransactions:     tracksMoisAct.length,
        evolutionRevenus:   statsPrec.revenus > 0
            ? ((statsAct.revenus - statsPrec.revenus)
               / statsPrec.revenus * 100).toFixed(1)
            : 0,
        evolutionDepenses:  statsPrec.depenses > 0
            ? ((statsAct.depenses - statsPrec.depenses)
               / statsPrec.depenses * 100).toFixed(1)
            : 0
    };
}


// ============================================================
// -- 7. Données pour Chart.js
// ============================================================

// Données pour le graphique camembert (dépenses par catégorie)
function getDonneesGrapheCamembert() {
    const maintenant = new Date();
    const tracksMois = getTracksParMois(
        maintenant.getMonth(), maintenant.getFullYear()
    );
    const totaux = calculerDepensesParCategorie(tracksMois);

    const palette = [
        '#6C63FF', '#00C896', '#FF4757', '#F5A623',
        '#0EA5E9', '#8B5CF6', '#EC4899', '#14B8A6',
        '#F97316', '#84CC16', '#06B6D4', '#EF4444',
        '#10B981', '#F59E0B', '#3B82F6', '#A855F7',
        '#22D3EE', '#FB923C', '#34D399', '#FBBF24'
    ];

    const labels  = Object.keys(totaux);
    const donnees = Object.values(totaux);

    return {
        labels:   labels,
        donnees:  donnees,
        couleurs: palette.slice(0, labels.length)
    };
}

// Données pour le graphique barres (revenus vs dépenses)
function getDonneesGrapheBarres() {
    const evo = calculerEvolutionMensuelle();
    return {
        labels:          evo.map(e => e.mois),
        donneesRevenus:  evo.map(e => e.revenus),
        donneesDepenses: evo.map(e => e.depenses)
    };
}

// Données pour le graphique courbe (évolution solde)
function getDonneesGrapheCourbe() {
    const evo = calculerEvolutionMensuelle();
    return {
        labels:       evo.map(e => e.mois),
        donneesSolde: evo.map(e => e.solde)
    };
}


// ============================================================
// -- 8. Utilitaires date
// ============================================================

// Retourne le nom du mois (0 = Janvier)
function getNomMois(num) {
    const mois = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre',
        'Novembre', 'Décembre'
    ];
    return mois[num] || '—';
}

// Formate un montant en euros
function formaterMontantData(montant) {
    return parseFloat(montant || 0).toLocaleString('fr-FR', {
        style: 'currency', currency: 'EUR'
    });
}
