// ============================================================
// FICHIER      : charts.js
// AUTEUR       : Benoît — Développeur Data & Visualisation
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Graphiques Chart.js interactifs compatibles
//                localStorage. Gère l'état vide (0 partout),
//                met à jour les cartes stat, filtre par période.
//                Compatible thème clair/sombre.
// DATE         : Mars 2026
// ============================================================


// ============================================================
// -- 1. Instances Chart.js globales
// ============================================================

let grapheCourbeEvolution     = null;
let grapheBarresComparaison   = null;
let grapheCamembertCategories = null;


// ============================================================
// -- 2. Couleurs du thème actif
// ============================================================

// Lit les variables CSS pour adapter les couleurs des graphes
function getCouleursDuTheme() {
    const s = getComputedStyle(document.documentElement);
    return {
        primaire: s.getPropertyValue(
            '--couleur-primaire').trim() || '#6C63FF',
        succes: s.getPropertyValue(
            '--couleur-succes').trim() || '#00C896',
        danger: s.getPropertyValue(
            '--couleur-danger').trim() || '#FF4757',
        or: s.getPropertyValue(
            '--couleur-or').trim() || '#F5A623',
        info: s.getPropertyValue(
            '--couleur-info').trim() || '#0EA5E9',
        texteSecondaire: s.getPropertyValue(
            '--texte-secondaire').trim() || '#4A5568',
        textePrincipal: s.getPropertyValue(
            '--texte-principal').trim() || '#0D1117',
        bordure: s.getPropertyValue(
            '--bordure-couleur').trim() || '#E2E8F4',
        bgCarte: s.getPropertyValue(
            '--bg-carte').trim() || '#FFFFFF',
        bgCorps: s.getPropertyValue(
            '--bg-corps').trim() || '#F8FAFF'
    };
}

// Options communes à tous les graphiques
function getOptionsCommunes() {
    const c = getCouleursDuTheme();
    return {
        responsive:          true,
        maintainAspectRatio: false,
        animation: {
            duration: 700, easing: 'easeInOutQuart'
        },
        plugins: {
            legend: {
                display:  true,
                position: 'bottom',
                labels: {
                    color: c.texteSecondaire,
                    font: {
                        size: 12,
                        family: "'Work Sans', sans-serif"
                    },
                    padding:       16,
                    boxWidth:      12,
                    boxHeight:     12,
                    usePointStyle: true
                }
            },
            tooltip: {
                backgroundColor: c.bgCarte,
                titleColor:      c.textePrincipal,
                bodyColor:       c.texteSecondaire,
                borderColor:     c.bordure,
                borderWidth:     1,
                padding:         12,
                cornerRadius:    10,
                titleFont: {
                    weight: '700',
                    family: "'Work Sans', sans-serif"
                },
                bodyFont: {
                    family: "'Rajdhani', sans-serif",
                    size: 14
                },
                callbacks: {
                    label: ctx => {
                        const v = ctx.parsed.y ?? ctx.parsed;
                        if (typeof v === 'number') {
                            return ' ' + v.toLocaleString(
                                'fr-FR', {
                                    style: 'currency',
                                    currency: 'EUR'
                                }
                            );
                        }
                        return ctx.formattedValue;
                    }
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: c.texteSecondaire,
                    font: {
                        family: "'Work Sans', sans-serif",
                        size: 11
                    }
                },
                grid: {
                    color: c.bordure,
                    drawBorder: false
                }
            },
            y: {
                ticks: {
                    color: c.texteSecondaire,
                    font: {
                        family: "'Rajdhani', sans-serif",
                        size: 12
                    },
                    callback: v =>
                        v.toLocaleString('fr-FR') + ' \u20AC'
                },
                grid: {
                    color: c.bordure,
                    drawBorder: false
                }
            }
        }
    };
}


// ============================================================
// -- 3. Graphique Courbe — Évolution du solde
// ============================================================

// Initialise le graphique en courbe pour le solde
function initGrapheCourbeEvolution(idCanvas) {
    const canvas = document.getElementById(idCanvas);
    if (!canvas) return;

    if (grapheCourbeEvolution) {
        grapheCourbeEvolution.destroy();
    }

    const donnees = getDonneesGrapheCourbe();
    const c       = getCouleursDuTheme();
    const ctx     = canvas.getContext('2d');

    // Dégradé sous la courbe
    const degrade = ctx.createLinearGradient(0, 0, 0, 300);
    degrade.addColorStop(0, c.primaire + '40');
    degrade.addColorStop(1, c.primaire + '00');

    // Si aucune donnée — affiche une courbe plate à 0
    const aucuneDonnee = donnees.donneesSolde
        .every(v => v === 0);

    grapheCourbeEvolution = new Chart(ctx, {
        type: 'line',
        data: {
            labels:   donnees.labels,
            datasets: [{
                label:            'Solde (\u20AC)',
                data:             donnees.donneesSolde,
                borderColor:      c.primaire,
                backgroundColor:  aucuneDonnee
                    ? 'transparent' : degrade,
                borderWidth:          2.5,
                pointRadius:          5,
                pointHoverRadius:     8,
                pointBackgroundColor: c.primaire,
                pointBorderColor:     c.bgCarte,
                pointBorderWidth:     2,
                fill:                 true,
                tension:              0.4
            }]
        },
        options: {
            ...getOptionsCommunes(),
            plugins: {
                ...getOptionsCommunes().plugins,
                legend: { display: false }
            }
        }
    });
}


// ============================================================
// -- 4. Graphique Barres — Revenus vs Dépenses
// ============================================================

// Initialise le graphique en barres comparatif
function initGrapheBarresComparaison(idCanvas) {
    const canvas = document.getElementById(idCanvas);
    if (!canvas) return;

    if (grapheBarresComparaison) {
        grapheBarresComparaison.destroy();
    }

    const donnees = getDonneesGrapheBarres();
    const c       = getCouleursDuTheme();

    grapheBarresComparaison = new Chart(
        canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels:   donnees.labels,
                datasets: [
                    {
                        label:           'Revenus',
                        data:            donnees.donneesRevenus,
                        backgroundColor: c.succes + 'CC',
                        borderColor:     c.succes,
                        borderWidth:     1.5,
                        borderRadius:    6,
                        borderSkipped:   false
                    },
                    {
                        label:           'Dépenses',
                        data:            donnees.donneesDepenses,
                        backgroundColor: c.danger + 'CC',
                        borderColor:     c.danger,
                        borderWidth:     1.5,
                        borderRadius:    6,
                        borderSkipped:   false
                    }
                ]
            },
            options: {
                ...getOptionsCommunes(),
                barPercentage:      0.65,
                categoryPercentage: 0.8
            }
        }
    );
}


// ============================================================
// -- 5. Graphique Donut — Répartition par catégorie
// ============================================================

// Initialise le graphique camembert des dépenses
function initGrapheCamembertCategories(idCanvas) {
    const canvas = document.getElementById(idCanvas);
    if (!canvas) return;

    if (grapheCamembertCategories) {
        grapheCamembertCategories.destroy();
    }

    const donnees = getDonneesGrapheCamembert();
    const c       = getCouleursDuTheme();

    // Si aucune donnée — graphe vide avec message
    if (donnees.labels.length === 0) {
        grapheCamembertCategories = new Chart(
            canvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Aucune dépense ce mois'],
                    datasets: [{
                        data: [1],
                        backgroundColor: [c.bordure],
                        borderColor: c.bgCarte,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: {
                        legend:  { display: false },
                        tooltip: { enabled: false }
                    }
                }
            }
        );
        return;
    }

    grapheCamembertCategories = new Chart(
        canvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels:   donnees.labels,
                datasets: [{
                    data: donnees.donnees,
                    backgroundColor: donnees.couleurs
                        .map(col => col + 'DD'),
                    borderColor: c.bgCarte,
                    borderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                animation: {
                    duration: 700,
                    easing: 'easeInOutQuart'
                },
                cutout: '62%',
                plugins: {
                    legend: {
                        display:  true,
                        position: 'right',
                        labels: {
                            color: c.texteSecondaire,
                            padding: 12,
                            boxWidth: 12,
                            boxHeight: 12,
                            usePointStyle: true,
                            font: {
                                size: 11,
                                family:
                                    "'Work Sans', sans-serif"
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: c.bgCarte,
                        titleColor: c.textePrincipal,
                        bodyColor: c.texteSecondaire,
                        borderColor: c.bordure,
                        borderWidth: 1,
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: ctx => {
                                const total = ctx.dataset.data
                                    .reduce((a, b) => a + b, 0);
                                const pct = (
                                    (ctx.parsed / total) * 100
                                ).toFixed(1);
                                const mont = ctx.parsed
                                    .toLocaleString('fr-FR', {
                                        style: 'currency',
                                        currency: 'EUR'
                                    });
                                return ` ${mont} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        }
    );
}


// ============================================================
// -- 6. Filtre de période
// ============================================================

// Met à jour cartes et graphes selon la période sélectionnée
function filtrerParPeriode(periode) {
    let liste;

    switch (periode) {
        case 'jour':
            liste = getTracksDerniersJours(1);
            break;
        case 'semaine':
            liste = getTracksDerniersJours(7);
            break;
        case 'annee': {
            // Tous les tracks de l'année en cours
            const now2 = new Date();
            const yearStart = new Date(
                now2.getFullYear(), 0, 1
            );
            const yearEnd = new Date(
                now2.getFullYear(), 11, 31
            );
            liste = getTousTracks().filter(t => {
                const tDate = new Date(t.date);
                return tDate >= yearStart &&
                    tDate <= yearEnd;
            });
            break;
        }
        default: {
            const now = new Date();
            liste = getTracksParMois(
                now.getMonth(), now.getFullYear()
            );
        }
    }

    mettreAJourCartesStat(liste);
    mettreAJourCamembert(liste);

    // Mise à jour du label de période camembert
    const labels = {
        jour: "Aujourd'hui",
        semaine: 'Cette semaine',
        mois: 'Ce mois',
        annee: 'Cette année'
    };
    const lbl = document.getElementById(
        'label-periode-camembert'
    );
    if (lbl) lbl.textContent = labels[periode] || 'Ce mois';
}

// Met à jour les 4 cartes statistiques
function mettreAJourCartesStat(liste) {
    const stats = calculerSolde(liste);

    const elemR = document.getElementById('stat-revenus');
    const elemD = document.getElementById('stat-depenses');
    const elemS = document.getElementById('stat-solde');
    const elemN = document.getElementById(
        'stat-nb-transactions'
    );

    if (elemR) animerCompteur(elemR, stats.revenus, true);
    if (elemD) animerCompteur(elemD, stats.depenses, true);
    if (elemS) {
        animerCompteur(elemS, stats.solde, true);
        elemS.className = stats.solde >= 0
            ? 'stat-valeur texte-succes'
            : 'stat-valeur texte-danger';
    }
    if (elemN) animerCompteur(elemN, liste.length, false);
}

// Met à jour uniquement le camembert avec une nouvelle liste
function mettreAJourCamembert(liste) {
    if (!grapheCamembertCategories) return;
    const totaux  = calculerDepensesParCategorie(liste);
    const labels  = Object.keys(totaux);
    const donnees = Object.values(totaux);
    const palette = [
        '#6C63FF', '#00C896', '#FF4757', '#F5A623',
        '#0EA5E9', '#8B5CF6', '#EC4899', '#14B8A6',
        '#F97316', '#84CC16', '#06B6D4', '#EF4444',
        '#10B981', '#F59E0B', '#3B82F6', '#A855F7',
        '#22D3EE', '#FB923C', '#34D399', '#FBBF24'
    ];
    const c = getCouleursDuTheme();

    grapheCamembertCategories.data.labels = labels.length
        ? labels : ['Aucune dépense'];
    grapheCamembertCategories.data.datasets[0].data =
        labels.length ? donnees : [1];
    grapheCamembertCategories.data.datasets[0]
        .backgroundColor = labels.length
            ? palette.slice(0, labels.length)
                .map(x => x + 'DD')
            : [c.bordure];
    grapheCamembertCategories.update('active');
}


// ============================================================
// -- 7. Compteur animé (0 → valeur)
// ============================================================

// Anime un élément de 0 à la valeur cible
function animerCompteur(element, valeurCible, enEuros) {
    const duree  = 900;
    const debut  = performance.now();
    const depart = 0;

    function etape(maintenant) {
        const progress = Math.min(
            (maintenant - debut) / duree, 1
        );
        const ease = 1 - Math.pow(1 - progress, 3);
        const val  = depart + (valeurCible - depart) * ease;

        if (enEuros) {
            element.textContent = val.toLocaleString(
                'fr-FR', {
                    style: 'currency', currency: 'EUR'
                }
            );
        } else {
            element.textContent = Math.round(val);
        }

        if (progress < 1) requestAnimationFrame(etape);
    }

    requestAnimationFrame(etape);
}


// ============================================================
// -- 8. Mise à jour couleurs au changement de thème
// ============================================================

// Appelé par theme.js quand le thème change
function mettreAJourCouleursGraphes() {
    setTimeout(() => {
        if (grapheCourbeEvolution) {
            initGrapheCourbeEvolution(
                'graphe-courbe-evolution'
            );
        }
        if (grapheBarresComparaison) {
            initGrapheBarresComparaison(
                'graphe-barres-comparaison'
            );
        }
        if (grapheCamembertCategories) {
            initGrapheCamembertCategories(
                'graphe-camembert-categories'
            );
        }
    }, 80);
}


// ============================================================
// -- 9. Initialisation au chargement
// ============================================================

// Lance les graphiques au chargement de la page
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    // Configuration globale Chart.js
    Chart.defaults.font.family = "'Work Sans', sans-serif";
    Chart.defaults.font.size   = 12;

    // Lance les 3 graphiques
    initGrapheCourbeEvolution(
        'graphe-courbe-evolution'
    );
    initGrapheBarresComparaison(
        'graphe-barres-comparaison'
    );
    initGrapheCamembertCategories(
        'graphe-camembert-categories'
    );

    // Filtre "Mois" actif par défaut
    const btnMois = document.querySelector(
        '.filtre-btn[data-periode="mois"]'
    );
    if (btnMois) {
        btnMois.classList.add('actif');
        filtrerParPeriode('mois');
    }
});
