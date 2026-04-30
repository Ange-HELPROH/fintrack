<?php
// ============================================================
// FICHIER      : php/traitement_formulaire.php
// AUTEUR       : Ghita — Développeure Backend PHP
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Traitement backend du formulaire de transaction.
//                Contient la fonction d'envoi d'email de confirmation
//                via PHPMailer après enregistrement d'une transaction.
//                Ce fichier est inclus dans formulaire.php via
//                require_once, il n'est pas appelé directement.
// DATE         : Mars 2026
// ============================================================


// -- 1. Chargement de la configuration PHPMailer --

// Charge la configuration SMTP et l'instance PHPMailer
require_once __DIR__ . '/../config/mail.php';


// ============================================================
// -- 2. Fonction principale d'envoi de l'email de confirmation --
// ============================================================

// Envoie un email de confirmation à l'utilisateur après saisie d'une transaction
// Paramètre : $valeurs — tableau associatif avec les champs du formulaire
//   ['date', 'type', 'categorie', 'description', 'montant', 'email']
// Retourne : true si l'email est envoyé avec succès, false sinon
function envoyerEmailConfirmationTransaction($valeurs) {

    // On essaie l'envoi et on capture les exceptions PHPMailer
    try {

        // -- 2.1 Création de l'instance PHPMailer configurée --
        $mailer = creerInstanceMailer();

        // -- 2.2 Destinataire : l'utilisateur qui a saisi la transaction --
        $mailer->addAddress($valeurs['email']);

        // -- 2.3 Sujet de l'email --
        $type_affiche   = $valeurs['type'] === 'revenu' ? 'Revenu' : 'Dépense';
        $mailer->Subject = '[FinTrack] Confirmation de transaction — ' . $type_affiche;

        // -- 2.4 Formatage des données pour l'email --

        // Formatage du montant en euros avec séparateurs français
        $montant_formate = number_format(floatval($valeurs['montant']), 2, ',', ' ') . ' €';

        // Formatage de la date en format lisible (JJ/MM/AAAA)
        $date_formatee = '';
        if (!empty($valeurs['date'])) {
            $date_obj      = DateTime::createFromFormat('Y-m-d', $valeurs['date']);
            $date_formatee = $date_obj ? $date_obj->format('d/m/Y') : $valeurs['date'];
        }

        // Couleur selon le type (pour le style de l'email HTML)
        $couleur_type = $valeurs['type'] === 'revenu' ? '#10b981' : '#ef4444';
        $signe_montant = $valeurs['type'] === 'revenu' ? '+' : '-';
        $categories_formatees = is_array($valeurs['categorie'])
            ? implode(', ', $valeurs['categorie'])
            : $valeurs['categorie'];

        // Date et heure de l'envoi de l'email
        $date_envoi = (new DateTime())->format('d/m/Y à H:i');

        // -- 2.5 Corps de l'email en HTML --
        $mailer->isHTML(true);
        $mailer->Body = construireCorpsEmailTransaction(
            $valeurs,
            $date_formatee,
            $montant_formate,
            $couleur_type,
            $signe_montant,
            $categories_formatees,
            $type_affiche,
            $date_envoi
        );

        // -- 2.6 Version texte brut (fallback pour les clients qui n'affichent pas le HTML) --
        $mailer->AltBody = construireCorpsTexteBrutTransaction(
            $valeurs,
            $date_formatee,
            $montant_formate,
            $categories_formatees,
            $type_affiche
        );

        // -- 2.7 Envoi de l'email --
        $mailer->send();
        return true;

    } catch (Exception $e) {
        // En production, on loggue l'erreur sans l'afficher à l'utilisateur
        // error_log('[FinTrack] Erreur envoi email transaction : ' . $e->getMessage());
        return false;
    }
}


// ============================================================
// -- 3. Construction du corps HTML de l'email de transaction --
// ============================================================

// Génère le template HTML complet de l'email de confirmation de transaction
// Paramètres : toutes les données nécessaires à l'affichage
// Retourne : string HTML de l'email
function construireCorpsEmailTransaction(
    $valeurs,
    $date_formatee,
    $montant_formate,
    $couleur_type,
    $signe_montant,
    $categories_formatees,
    $type_affiche,
    $date_envoi
) {
    // On utilise HEREDOC pour le template HTML multi-lignes
    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation de transaction — FinTrack</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif;">

    <!-- Conteneur principal -->
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding: 30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                       style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- En-tête colorée -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed); padding:32px 40px; text-align:center;">
                            <div style="font-size:28px; font-weight:800; color:#ffffff; margin-bottom:4px;">
                                FinTrack
                            </div>
                            <div style="font-size:14px; color:rgba(255,255,255,0.8);">
                                Confirmation de transaction
                            </div>
                        </td>
                    </tr>

                    <!-- Montant central -->
                    <tr>
                        <td style="padding:32px 40px 16px; text-align:center;">
                            <div style="font-size:42px; font-weight:800; color:{$couleur_type}; line-height:1;">
                                {$signe_montant}{$montant_formate}
                            </div>
                            <div style="display:inline-block; background:{$couleur_type}22; color:{$couleur_type};
                                        padding:4px 16px; border-radius:20px; font-size:12px;
                                        font-weight:700; margin-top:10px; text-transform:uppercase; letter-spacing:1px;">
                                {$type_affiche}
                            </div>
                        </td>
                    </tr>

                    <!-- Tableau des détails -->
                    <tr>
                        <td style="padding:8px 40px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                                <tr style="background:#f8fafc;">
                                    <td style="padding:12px 16px; font-size:12px; font-weight:700;
                                               color:#64748b; text-transform:uppercase; letter-spacing:0.5px;
                                               width:40%; border-bottom:1px solid #e2e8f0;">
                                        Date
                                    </td>
                                    <td style="padding:12px 16px; font-size:14px; color:#1e293b;
                                               font-weight:600; border-bottom:1px solid #e2e8f0;">
                                        {$date_formatee}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 16px; font-size:12px; font-weight:700;
                                               color:#64748b; text-transform:uppercase; letter-spacing:0.5px;
                                               background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                                        Catégorie
                                    </td>
                                    <td style="padding:12px 16px; font-size:14px; color:#1e293b;
                                               font-weight:600; border-bottom:1px solid #e2e8f0;">
                                        {$categories_formatees}
                                    </td>
                                </tr>
                                <tr style="background:#f8fafc;">
                                    <td style="padding:12px 16px; font-size:12px; font-weight:700;
                                               color:#64748b; text-transform:uppercase; letter-spacing:0.5px;">
                                        Description
                                    </td>
                                    <td style="padding:12px 16px; font-size:14px; color:#1e293b; font-weight:600;">
                                        {$valeurs['description']}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Bandeau de confirmation -->
                    <tr>
                        <td style="padding:0 40px 24px;">
                            <div style="background:#d1fae5; border:1px solid #10b981; border-radius:10px;
                                        padding:14px 18px; text-align:center;">
                                <span style="color:#065f46; font-weight:600; font-size:14px;">
                                    Transaction enregistree avec succes dans FinTrack
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Bouton dashboard -->
                    <tr>
                        <td style="padding:0 40px 32px; text-align:center;">
                            <a href="http://localhost/projet_info_fintrack_2026/dashboard.php"
                               style="display:inline-block; background:#4f46e5; color:#ffffff;
                                      padding:12px 28px; border-radius:8px; text-decoration:none;
                                      font-weight:700; font-size:14px;">
                                Voir mon tableau de bord
                            </a>
                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="background:#f8fafc; border-top:1px solid #e2e8f0;
                                   padding:20px 40px; text-align:center;">
                            <div style="font-size:12px; color:#94a3b8; line-height:1.6;">
                                Email généré automatiquement par <strong>FinTrack</strong> · ECAM-EPMI 2025-2026<br>
                                Envoyé le {$date_envoi}<br>
                                <span style="font-size:11px;">Ne pas répondre à cet email.</span>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;

    return $html;
}


// ============================================================
// -- 4. Version texte brut de l'email (fallback) --
// ============================================================

// Génère une version texte simple de l'email de confirmation
// Paramètres : données de la transaction formatées
// Retourne : string texte brut
function construireCorpsTexteBrutTransaction($valeurs, $date_formatee, $montant_formate, $categories_formatees, $type_affiche) {
    $signe = $valeurs['type'] === 'revenu' ? '+' : '-';

    return <<<TEXT
FinTrack — Confirmation de transaction
=======================================

Votre transaction a été enregistrée avec succès.

TYPE        : {$type_affiche}
MONTANT     : {$signe}{$montant_formate}
DATE        : {$date_formatee}
CATÉGORIE   : {$categories_formatees}
DESCRIPTION : {$valeurs['description']}

---
Email généré automatiquement par FinTrack — ECAM-EPMI 2025-2026.
Ne pas répondre à cet email.
TEXT;
}
