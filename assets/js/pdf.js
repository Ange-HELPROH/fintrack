// ============================================================
// FICHIER      : pdf.js
// AUTEUR       : Maroua — Développeure PDF & Documentation
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Génération PDF via jsPDF. CORRIGÉ : encodage
//                ASCII-safe (pas d'emoji ni accents dans jsPDF),
//                rapport avec graphiques, envoi PDF par email
//                via AJAX + PHPMailer.
// DATE         : Mars 2026
// ============================================================


// ============================================================
// -- 1. Configuration
// ============================================================

const PDF_ML   = 14;   // Marge gauche (mm)
const PDF_MR   = 14;   // Marge droite
const PDF_W    = 210;  // Largeur A4
const PDF_H    = 297;  // Hauteur A4
const PDF_UTIL = PDF_W - PDF_ML - PDF_MR;  // Largeur utile

// Couleurs RGB (pas d'emoji, pas d'accents dans jsPDF)
const COUL = {
    primaire: [108, 99,  255],
    succes:   [0,   200, 150],
    danger:   [255, 71,  87],
    or:       [245, 166, 35],
    texte:    [13,  17,  23],
    gris:     [74,  85,  104],
    clair:    [136, 150, 171],
    bordure:  [226, 232, 244],
    fond:     [248, 250, 255],
    blanc:    [255, 255, 255]
};

// ============================================================
// -- 2. Utilitaire — créer une instance jsPDF
// ============================================================

// Crée et retourne une instance jsPDF (portrait A4)
function creerDoc() {
    const Ctor = (window.jspdf && window.jspdf.jsPDF)
        ? window.jspdf.jsPDF : window.jsPDF;
    if (!Ctor) {
        alert('jsPDF non chargé. '
            + 'Vérifiez la connexion Internet.');
        return null;
    }
    return new Ctor({
        orientation: 'portrait', unit: 'mm', format: 'a4'
    });
}


// ============================================================
// -- 3. En-tête PDF (sans emoji — fix encodage)
// ============================================================

// Dessine l'en-tête colorée sur la page courante
// IMPORTANT : on utilise uniquement des caracteres ASCII
// pour eviter le bug de jsPDF avec les polices standard
function dessinerEntete(doc, titre, sousTitre) {
    // Fond violet
    doc.setFillColor(...COUL.primaire);
    doc.rect(0, 0, PDF_W, 36, 'F');

    // Logo texte — ASCII uniquement
    doc.setTextColor(...COUL.blanc);
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(18);
    doc.text('FinTrack', PDF_ML, 14);

    // Trait de separation sous le logo
    doc.setDrawColor(255, 255, 255);
    doc.setLineWidth(0.3);
    doc.line(PDF_ML, 17, PDF_ML + 40, 17);

    // Titre de la page
    doc.setFontSize(12);
    doc.text(titre, PDF_ML, 25);

    // Sous-titre
    if (sousTitre) {
        doc.setFontSize(8);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(200, 210, 255);
        doc.text(sousTitre, PDF_ML, 31);
    }

    // Mention droite
    doc.setFontSize(8);
    doc.setTextColor(200, 210, 255);
    doc.text(
        'ECAM-EPMI 2025-2026',
        PDF_W - PDF_MR, 14, { align: 'right' }
    );

    // Remet la couleur de texte par defaut
    doc.setTextColor(...COUL.texte);
}

// Dessine le pied de page
function dessinerPied(doc, page, total) {
    const y = PDF_H - 9;
    doc.setDrawColor(...COUL.bordure);
    doc.setLineWidth(0.3);
    doc.line(PDF_ML, y - 3, PDF_W - PDF_MR, y - 3);
    doc.setFontSize(7.5);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(...COUL.clair);
    doc.text(
        'FinTrack - Gestion financiere personnelle',
        PDF_ML, y
    );
    doc.text(
        `Page ${page} / ${total}`,
        PDF_W - PDF_MR, y, { align: 'right' }
    );
    const dateStr = new Date().toLocaleDateString('fr-FR');
    doc.text(
        'Genere le ' + dateStr,
        PDF_W / 2, y, { align: 'center' }
    );
}

// Dessine un titre de section
function dessinerSection(doc, texte, y) {
    doc.setFillColor(...COUL.fond);
    doc.rect(PDF_ML, y - 4.5, PDF_UTIL, 7.5, 'F');
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(9);
    doc.setTextColor(...COUL.primaire);
    doc.text(texte, PDF_ML + 3, y);
    doc.setTextColor(...COUL.texte);
    return y + 6;
}

// Dessine un séparateur horizontal
function dessinerSeparateur(doc, y) {
    doc.setDrawColor(...COUL.bordure);
    doc.setLineWidth(0.3);
    doc.line(PDF_ML, y, PDF_W - PDF_MR, y);
}


// ============================================================
// -- 4. Récapitulatif d'un track (depuis formulaire.php)
// ============================================================

// Génère un reçu PDF pour un track (retourne le base64)
// Paramètre : track {date, type, catégorie, description, montant}
// Retour : chaîne base64 du PDF (data:application/pdf;base64,...)
function genererTrackPDFBase64(track) {
    const doc = creerDoc();
    if (!doc) return null;

    const typeAff  = track.type === 'revenu'
        ? 'REVENU' : 'DEPENSE';
    const coulType = track.type === 'revenu'
        ? COUL.succes : COUL.danger;
    const signe    = track.type === 'revenu' ? '+' : '-';
    const montantStr = parseFloat(track.montant || 0)
        .toLocaleString('fr-FR', {
            style: 'currency', currency: 'EUR'
        });
    const dateStr = track.date
        ? new Date(track.date).toLocaleDateString('fr-FR')
        : new Date().toLocaleDateString('fr-FR');

    // Catégorie(s) en texte
    const cats = Array.isArray(track.categorie)
        ? track.categorie.join(', ')
        : (track.categorie || '\u2014');

    dessinerEntete(
        doc,
        'Recu de track',
        'Recapitulatif de votre operation financiere'
    );

    let y = 52;

    // Carte centrale
    doc.setFillColor(...COUL.blanc);
    doc.setDrawColor(...COUL.bordure);
    doc.setLineWidth(0.5);
    doc.roundedRect(PDF_ML, y, PDF_UTIL, 88, 4, 4, 'FD');

    y += 12;

    // Montant en grand
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(30);
    doc.setTextColor(...coulType);
    doc.text(
        signe + montantStr,
        PDF_W / 2, y, { align: 'center' }
    );

    // Badge type
    const bx = (PDF_W - 28) / 2;
    doc.setFillColor(...coulType);
    doc.roundedRect(bx, y + 4, 28, 6.5, 2, 2, 'F');
    doc.setFontSize(7);
    doc.setTextColor(...COUL.blanc);
    doc.text(
        typeAff,
        PDF_W / 2, y + 8.5, { align: 'center' }
    );

    y += 22;
    doc.setTextColor(...COUL.texte);

    // Tableau détails (ASCII dans doc.text)
    const details = [
        ['Date',        dateStr],
        ['Categorie',   cats],
        ['Description', track.description || '\u2014'],
        ['Statut',      'Confirme']
    ];

    details.forEach(([label, val], i) => {
        if (i % 2 === 0) {
            doc.setFillColor(...COUL.fond);
            doc.rect(
                PDF_ML + 2, y - 3.5,
                PDF_UTIL - 4, 7.5, 'F'
            );
        }
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8.5);
        doc.setTextColor(...COUL.gris);
        doc.text(label + ' :', PDF_ML + 6, y);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...COUL.texte);
        doc.text(String(val), PDF_ML + 52, y);
        y += 9;
    });

    y += 6;

    // Bandeau succès
    doc.setFillColor(0, 230, 168, 0.1);
    doc.setFillColor(224, 251, 244);
    doc.roundedRect(
        PDF_ML, y, PDF_UTIL, 16, 3, 3, 'F'
    );
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8.5);
    doc.setTextColor(...COUL.succes);
    doc.text(
        'Track enregistre avec succes dans FinTrack',
        PDF_W / 2, y + 7, { align: 'center' }
    );
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7.5);
    doc.setTextColor(...COUL.gris);
    doc.text(
        'Conservez ce document comme preuve'
        + ' de votre operation.',
        PDF_W / 2, y + 12.5, { align: 'center' }
    );

    dessinerPied(doc, 1, 1);

    // Retourne le base64 SANS télécharger
    return doc.output('datauristring');
}

// Génère un reçu PDF pour un track ET le télécharge
// Paramètre : track {date, type, catégorie, description, montant}
function exporterTrackPDF(track) {
    const doc = creerDoc();
    if (!doc) return;

    const typeAff  = track.type === 'revenu'
        ? 'REVENU' : 'DEPENSE';
    const coulType = track.type === 'revenu'
        ? COUL.succes : COUL.danger;
    const signe    = track.type === 'revenu' ? '+' : '-';
    const montantStr = parseFloat(track.montant || 0)
        .toLocaleString('fr-FR', {
            style: 'currency', currency: 'EUR'
        });
    const dateStr = track.date
        ? new Date(track.date).toLocaleDateString('fr-FR')
        : new Date().toLocaleDateString('fr-FR');

    // Catégorie(s) en texte
    const cats = Array.isArray(track.categorie)
        ? track.categorie.join(', ')
        : (track.categorie || '\u2014');

    dessinerEntete(
        doc,
        'Recu de track',
        'Recapitulatif de votre operation financiere'
    );

    let y = 52;

    // Carte centrale
    doc.setFillColor(...COUL.blanc);
    doc.setDrawColor(...COUL.bordure);
    doc.setLineWidth(0.5);
    doc.roundedRect(PDF_ML, y, PDF_UTIL, 88, 4, 4, 'FD');

    y += 12;

    // Montant en grand
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(30);
    doc.setTextColor(...coulType);
    doc.text(
        signe + montantStr,
        PDF_W / 2, y, { align: 'center' }
    );

    // Badge type
    const bx = (PDF_W - 28) / 2;
    doc.setFillColor(...coulType);
    doc.roundedRect(bx, y + 4, 28, 6.5, 2, 2, 'F');
    doc.setFontSize(7);
    doc.setTextColor(...COUL.blanc);
    doc.text(
        typeAff,
        PDF_W / 2, y + 8.5, { align: 'center' }
    );

    y += 22;
    doc.setTextColor(...COUL.texte);

    // Tableau détails (ASCII dans doc.text)
    const details = [
        ['Date',        dateStr],
        ['Categorie',   cats],
        ['Description', track.description || '\u2014'],
        ['Statut',      'Confirme']
    ];

    details.forEach(([label, val], i) => {
        if (i % 2 === 0) {
            doc.setFillColor(...COUL.fond);
            doc.rect(
                PDF_ML + 2, y - 3.5,
                PDF_UTIL - 4, 7.5, 'F'
            );
        }
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(8.5);
        doc.setTextColor(...COUL.gris);
        doc.text(label + ' :', PDF_ML + 6, y);
        doc.setFont('helvetica', 'normal');
        doc.setTextColor(...COUL.texte);
        doc.text(String(val), PDF_ML + 52, y);
        y += 9;
    });

    y += 6;

    // Bandeau succès
    doc.setFillColor(0, 230, 168, 0.1);
    doc.setFillColor(224, 251, 244);
    doc.roundedRect(
        PDF_ML, y, PDF_UTIL, 16, 3, 3, 'F'
    );
    doc.setFont('helvetica', 'bold');
    doc.setFontSize(8.5);
    doc.setTextColor(...COUL.succes);
    doc.text(
        'Track enregistre avec succes dans FinTrack',
        PDF_W / 2, y + 7, { align: 'center' }
    );
    doc.setFont('helvetica', 'normal');
    doc.setFontSize(7.5);
    doc.setTextColor(...COUL.gris);
    doc.text(
        'Conservez ce document comme preuve'
        + ' de votre operation.',
        PDF_W / 2, y + 12.5, { align: 'center' }
    );

    dessinerPied(doc, 1, 1);

    const nom = 'fintrack_track_'
        + (track.date || '').replace(/-/g, '') + '.pdf';
    doc.save(nom);  // Télécharge le fichier
}


// ============================================================
// -- 5. Rapport mensuel complet
// ============================================================

// Génère le rapport financier d'un mois
// Paramètres : mois (0-11), annee (ex: 2026)
// Retourne la chaîne base64 du PDF
function genererRapportMensuelPDF(mois, annee) {
    const doc = creerDoc();
    if (!doc) return null;

    const maintenant = new Date();
    const m = (mois !== undefined)
        ? mois : maintenant.getMonth();
    const a = annee || maintenant.getFullYear();

    // Noms de mois ASCII pour jsPDF
    const nomM = [
        'Janvier', 'Fevrier', 'Mars', 'Avril',
        'Mai', 'Juin', 'Juillet', 'Aout',
        'Septembre', 'Octobre', 'Novembre', 'Decembre'
    ][m];

    const tracks = getTracksParMois(m, a);
    const stats  = calculerSolde(tracks);
    const depCat = calculerDepensesParCategorie(tracks);
    const opts   = { style: 'currency', currency: 'EUR' };

    // -- Page 1 : Résumé --
    dessinerEntete(
        doc,
        'Rapport financier - ' + nomM + ' ' + a,
        'Bilan complet de vos finances pour '
        + nomM + ' ' + a
    );

    let y = 48;

    // -- 3 cartes indicateurs --
    y = dessinerSection(
        doc, 'Indicateurs cles du mois', y + 5
    );
    y += 4;

    const lc = (PDF_UTIL - 8) / 3;
    [
        {
            label: 'Revenus totaux',
            val: stats.revenus,
            coul: COUL.succes
        },
        {
            label: 'Depenses totales',
            val: stats.depenses,
            coul: COUL.danger
        },
        {
            label: 'Solde net',
            val: stats.solde,
            coul: stats.solde >= 0
                ? COUL.succes : COUL.danger
        }
    ].forEach((carte, i) => {
        const xc = PDF_ML + i * (lc + 4);
        doc.setFillColor(...COUL.blanc);
        doc.setDrawColor(...COUL.bordure);
        doc.setLineWidth(0.4);
        doc.roundedRect(xc, y, lc, 22, 3, 3, 'FD');
        doc.setFillColor(...carte.coul);
        doc.rect(xc, y, 2.5, 22, 'F');
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(7);
        doc.setTextColor(...COUL.gris);
        doc.text(carte.label, xc + 5, y + 7);
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(11);
        doc.setTextColor(...carte.coul);
        doc.text(
            carte.val.toLocaleString('fr-FR', opts),
            xc + 5, y + 16
        );
    });

    y += 30;

    // -- Dépenses par catégorie --
    if (Object.keys(depCat).length > 0) {
        y = dessinerSection(
            doc,
            'Repartition des depenses par categorie',
            y
        );
        y += 5;

        const totalDep = stats.depenses || 1;

        // En-tête tableau
        doc.setFillColor(...COUL.primaire);
        doc.rect(PDF_ML, y - 4, PDF_UTIL, 7, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7.5);
        doc.setTextColor(...COUL.blanc);
        doc.text('Categorie', PDF_ML + 3, y);
        doc.text('Montant',   PDF_ML + 100, y);
        doc.text('Part (%)',  PDF_ML + 135, y);
        doc.text('Barre',    PDF_ML + 155, y);

        y += 5;
        let idx = 0;

        for (const [cat, mont] of Object.entries(depCat)) {
            if (y > 265) {
                dessinerPied(
                    doc,
                    doc.internal.getNumberOfPages(), '?'
                );
                doc.addPage();
                dessinerEntete(
                    doc,
                    'Rapport - ' + nomM + ' ' + a,
                    '(suite)'
                );
                y = 50;
            }
            if (idx % 2 === 0) {
                doc.setFillColor(...COUL.fond);
                doc.rect(
                    PDF_ML, y - 3.5, PDF_UTIL, 7, 'F'
                );
            }
            const pct = (
                (mont / totalDep) * 100
            ).toFixed(1);
            doc.setFont('helvetica', 'normal');
            doc.setFontSize(8);
            doc.setTextColor(...COUL.texte);
            doc.text(
                cat.length > 26
                    ? cat.substring(0, 26) + '...'
                    : cat,
                PDF_ML + 3, y
            );
            doc.setFont('helvetica', 'bold');
            doc.setTextColor(...COUL.danger);
            doc.text(
                mont.toLocaleString('fr-FR', opts),
                PDF_ML + 100, y
            );
            doc.setFont('helvetica', 'normal');
            doc.setTextColor(...COUL.gris);
            doc.text(pct + '%', PDF_ML + 135, y);
            // Barre de progression
            const bmax = 35;
            const bval = (mont / totalDep) * bmax;
            doc.setFillColor(...COUL.bordure);
            doc.rect(
                PDF_ML + 155, y - 2, bmax, 3, 'F'
            );
            doc.setFillColor(...COUL.danger);
            doc.rect(
                PDF_ML + 155, y - 2, bval, 3, 'F'
            );

            y += 8;
            idx++;
        }
        y += 6;
    }

    // -- Liste des tracks --
    if (y > 220) {
        doc.addPage();
        dessinerEntete(
            doc, 'Tracks - ' + nomM + ' ' + a, ''
        );
        y = 48;
    }

    y = dessinerSection(
        doc,
        'Liste des tracks du mois (' + tracks.length + ')',
        y
    );
    y += 5;

    if (tracks.length === 0) {
        doc.setFont('helvetica', 'normal');
        doc.setFontSize(9);
        doc.setTextColor(...COUL.gris);
        doc.text(
            'Aucun track enregistre pour ce mois.',
            PDF_W / 2, y + 8, { align: 'center' }
        );
        y += 20;
    } else {
        // En-tête
        doc.setFillColor(...COUL.primaire);
        doc.rect(PDF_ML, y - 4, PDF_UTIL, 7, 'F');
        doc.setFont('helvetica', 'bold');
        doc.setFontSize(7.5);
        doc.setTextColor(...COUL.blanc);
        doc.text('Date',        PDF_ML + 3, y);
        doc.text('Type',        PDF_ML + 28, y);
        doc.text('Categorie',   PDF_ML + 55, y);
        doc.text('Description', PDF_ML + 100, y);
        doc.text('Montant',     PDF_ML + 160, y);
        y += 5;

        tracks.sort(
            (a, b) => new Date(b.date) - new Date(a.date)
        ).forEach((t, i) => {
            if (y > 265) {
                dessinerPied(
                    doc,
                    doc.internal.getNumberOfPages(), '?'
                );
                doc.addPage();
                dessinerEntete(
                    doc,
                    'Tracks - ' + nomM + ' ' + a,
                    '(suite)'
                );
                y = 50;
            }
            if (i % 2 === 0) {
                doc.setFillColor(...COUL.fond);
                doc.rect(
                    PDF_ML, y - 3.5, PDF_UTIL, 7, 'F'
                );
            }
            const estRev   = t.type === 'revenu';
            const coulType = estRev
                ? COUL.succes : COUL.danger;
            const signe    = estRev ? '+' : '-';
            const dateAff  = new Date(t.date)
                .toLocaleDateString('fr-FR');
            const cats     = Array.isArray(t.categorie)
                ? t.categorie[0] : t.categorie;
            const desc     = (t.description || '\u2014')
                .substring(0, 28);

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(...COUL.gris);
            doc.text(dateAff, PDF_ML + 3, y);

            // Badge type
            doc.setFillColor(...coulType);
            doc.roundedRect(
                PDF_ML + 28, y - 3, 22, 5.5, 1, 1, 'F'
            );
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(6.5);
            doc.setTextColor(...COUL.blanc);
            doc.text(
                estRev ? 'REVENU' : 'DEPENSE',
                PDF_ML + 39, y, { align: 'center' }
            );

            doc.setFont('helvetica', 'normal');
            doc.setFontSize(7.5);
            doc.setTextColor(...COUL.texte);
            doc.text(
                (cats || '').substring(0, 18),
                PDF_ML + 55, y
            );
            doc.text(desc, PDF_ML + 100, y);

            doc.setFont('helvetica', 'bold');
            doc.setTextColor(...coulType);
            doc.text(
                signe + parseFloat(t.montant)
                    .toLocaleString('fr-FR', opts),
                PDF_ML + 178, y, { align: 'right' }
            );
            y += 8;
        });

        // Totaux
        y += 4;
        dessinerSeparateur(doc, y);
        y += 6;
        [
            {
                label: 'TOTAL REVENUS :',
                val: stats.revenus,
                coul: COUL.succes
            },
            {
                label: 'TOTAL DEPENSES :',
                val: stats.depenses,
                coul: COUL.danger
            },
            {
                label: 'SOLDE NET :',
                val: stats.solde,
                coul: stats.solde >= 0
                    ? COUL.succes : COUL.danger
            }
        ].forEach(row => {
            doc.setFont('helvetica', 'bold');
            doc.setFontSize(8.5);
            doc.setTextColor(...COUL.texte);
            doc.text(row.label, PDF_ML + 100, y);
            doc.setTextColor(...row.coul);
            doc.text(
                row.val.toLocaleString('fr-FR', opts),
                PDF_ML + 178, y, { align: 'right' }
            );
            y += 7;
        });
    }

    // Pieds de page sur toutes les pages
    const total = doc.internal.getNumberOfPages();
    for (let p = 1; p <= total; p++) {
        doc.setPage(p);
        dessinerPied(doc, p, total);
    }

    const nom = 'fintrack_rapport_'
        + nomM.toLowerCase() + '_' + a + '.pdf';
    doc.save(nom);

    // Retourne base64 pour envoi email
    return doc.output('datauristring');
}


// ============================================================
// -- 6. Liaison des boutons PDF au DOM
// ============================================================

// Attache les gestionnaires de clic aux boutons PDF
document.addEventListener('DOMContentLoaded', () => {

    // Bouton reçu track (formulaire.php)
    const btnTrack = document.getElementById(
        'btn-export-transaction-pdf'
    );
    if (btnTrack) {
        btnTrack.addEventListener('click', () => {
            const donnees = {
                date: document.getElementById(
                    'date-transaction')?.value || '',
                type: document.getElementById(
                    'type-transaction')?.value || '',
                categorie:
                    obtenirCategoriesSelectionnees() || [],
                description: document.getElementById(
                    'description')?.value || '',
                montant: document.getElementById(
                    'montant')?.value || 0
            };
            if (!donnees.montant ||
                isNaN(donnees.montant)) {
                alert('Remplissez le formulaire avant '
                    + 'de générer le PDF.');
                return;
            }
            exporterTrackPDF(donnees);
        });
    }

    // Bouton rapport PDF (rapport.php)
    const btnRap = document.getElementById(
        'btn-export-rapport-pdf'
    );
    if (btnRap) {
        btnRap.addEventListener('click', () => {
            const m = parseInt(
                document.getElementById(
                    'select-mois-rapport')?.value
                ?? new Date().getMonth()
            );
            const a = parseInt(
                document.getElementById(
                    'select-annee-rapport')?.value
                ?? new Date().getFullYear()
            );
            genererRapportMensuelPDF(m, a);
        });
    }
});


// ============================================================
// -- 7. Envoi du PDF par email via AJAX + PHP
// ============================================================

// Envoie un PDF (base64) par email via php/envoyer_pdf.php
// Paramètres : base64PDF, email, nomFichier, callback
function envoyerPDFParEmail(base64PDF, email,
    nomFichier, callback) {
    if (!email || !base64PDF) {
        callback({
            succes: false,
            message: 'Email ou PDF manquant.'
        });
        return;
    }

    // Enlève le préfixe data:... pour garder le base64 pur
    const base64Pur = base64PDF.split(',')[1] || base64PDF;

    const formData = new FormData();
    formData.append('email', email);
    formData.append('pdf_b64', base64Pur);
    formData.append(
        'nom_fichier',
        nomFichier || 'fintrack_rapport.pdf'
    );

    fetch('php/envoyer_pdf.php', {
        method: 'POST',
        body:   formData
    })
    .then(r => r.json())
    .then(data => callback(data))
    .catch(() => callback({
        succes: false,
        message: 'Erreur réseau. Réessayez.'
    }));
}

// Récupère les catégories cochées dans le multi-select
function obtenirCategoriesSelectionnees() {
    const cases = document.querySelectorAll(
        '.multi-select-item input[type="checkbox"]:checked'
    );
    return Array.from(cases).map(c => c.value);
}
