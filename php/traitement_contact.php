<?php
// ============================================================
// FICHIER      : php/traitement_contact.php
// AUTEUR       : Ghita — Développeure Backend PHP
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Traitement backend du formulaire de contact.
//                Envoie un email à l'équipe FinTrack avec le
//                message de l'utilisateur, et une confirmation
//                automatique à l'expéditeur via PHPMailer.
//                Inclus dans contact.php via require_once.
// DATE         : Mars 2026
// ============================================================


// -- 1. Chargement de la configuration PHPMailer --

require_once __DIR__ . '/../config/mail.php';


// ============================================================
// -- 2. Fonction principale d'envoi du message de contact --
// ============================================================

// Envoie deux emails lors d'une soumission du formulaire de contact :
//   1. L'email du visiteur → l'équipe FinTrack
//   2. Un email de confirmation automatique → le visiteur
// Paramètre : $valeurs — tableau ['nom', 'email', 'sujet', 'message']
// Retourne  : true si les deux envois réussissent, false sinon
function envoyerEmailContact($valeurs) {

    // On tente les deux envois
    $envoi_equipe   = envoyerEmailVersEquipe($valeurs);
    $envoi_confirm  = envoyerEmailConfirmationContact($valeurs);

    // On retourne true uniquement si l'envoi principal (à l'équipe) a réussi
    // L'email de confirmation est secondaire — une erreur ne bloque pas le processus
    return $envoi_equipe;
}


// ============================================================
// -- 3. Envoi du message à l'équipe FinTrack --
// ============================================================

// Envoie le message du visiteur à l'adresse email de l'équipe
// Paramètre : $valeurs — tableau avec les champs du formulaire
// Retourne  : true si succès, false sinon
function envoyerEmailVersEquipe($valeurs) {

    try {
        $mailer = creerInstanceMailer();

        // -- Destinataire : l'équipe FinTrack --
        $mailer->addAddress(MAIL_EQUIPE_EMAIL, MAIL_EQUIPE_NOM);

        // -- Reply-To : le visiteur (pour répondre directement) --
        $mailer->addReplyTo($valeurs['email'], $valeurs['nom']);

        // -- Sujet de l'email --
        $mailer->Subject = '[FinTrack Contact] ' . $valeurs['sujet'];

        // -- Corps HTML --
        $mailer->isHTML(true);
        $mailer->Body    = construireEmailVersEquipe($valeurs);

        // -- Corps texte brut --
        $mailer->AltBody = construireTexteEquipe($valeurs);

        $mailer->send();
        return true;

    } catch (Exception $e) {
        // error_log('[FinTrack] Erreur envoi email équipe contact : ' . $e->getMessage());
        return false;
    }
}


// ============================================================
// -- 4. Envoi de la confirmation automatique au visiteur --
// ============================================================

// Envoie un accusé de réception automatique au visiteur
// Paramètre : $valeurs — tableau avec les champs du formulaire
// Retourne  : true si succès, false sinon (non bloquant)
function envoyerEmailConfirmationContact($valeurs) {

    try {
        $mailer = creerInstanceMailer();

        // -- Destinataire : le visiteur qui a écrit --
        $mailer->addAddress($valeurs['email'], $valeurs['nom']);

        // -- Sujet de confirmation --
        $mailer->Subject = '[FinTrack] Nous avons reçu votre message — ' . $valeurs['sujet'];

        // -- Corps HTML --
        $mailer->isHTML(true);
        $mailer->Body    = construireEmailConfirmationContact($valeurs);

        // -- Corps texte brut --
        $mailer->AltBody = construireTexteConfirmationContact($valeurs);

        $mailer->send();
        return true;

    } catch (Exception $e) {
        // L'erreur de confirmation n'est pas bloquante, on la loggue silencieusement
        // error_log('[FinTrack] Erreur envoi confirmation contact : ' . $e->getMessage());
        return false;
    }
}


// ============================================================
// -- 5. Template HTML — Email vers l'équipe FinTrack --
// ============================================================

// Construit le template HTML de l'email reçu par l'équipe FinTrack
// Paramètre : $valeurs — données du formulaire de contact
// Retourne  : string HTML complet
function construireEmailVersEquipe($valeurs) {

    // Date et heure de réception
    $date_reception = (new DateTime())->format('d/m/Y à H:i:s');

    // Remplacement des sauts de ligne dans le message par des <br>
    $message_html = nl2br(htmlspecialchars($valeurs['message']));

    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Nouveau message de contact — FinTrack</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                       style="background:#fff; border-radius:16px; overflow:hidden;
                              box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- En-tête -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                   padding:28px 40px; text-align:center;">
                            <div style="font-size:24px; font-weight:800; color:#fff; margin-bottom:4px;">
                                FinTrack — Nouveau message
                            </div>
                            <div style="font-size:13px; color:rgba(255,255,255,0.8);">
                                Formulaire de contact
                            </div>
                        </td>
                    </tr>

                    <!-- Alerte nouveau message -->
                    <tr>
                        <td style="padding:24px 40px 8px;">
                            <div style="background:#eef2ff; border-left:4px solid #4f46e5;
                                        border-radius:6px; padding:12px 16px;
                                        font-size:14px; color:#3730a3; font-weight:600;">
                                📩 Vous avez reçu un nouveau message via le formulaire de contact FinTrack.
                            </div>
                        </td>
                    </tr>

                    <!-- Informations de l'expéditeur -->
                    <tr>
                        <td style="padding:16px 40px 8px;">
                            <div style="font-size:13px; font-weight:700; color:#64748b;
                                        text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                                Informations de l'expéditeur
                            </div>
                            <table width="100%" cellpadding="0" cellspacing="0"
                                   style="border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                                <tr style="background:#f8fafc;">
                                    <td style="padding:11px 16px; font-size:12px; font-weight:700;
                                               color:#64748b; width:35%; border-bottom:1px solid #e2e8f0;">
                                        Nom
                                    </td>
                                    <td style="padding:11px 16px; font-size:14px; color:#1e293b;
                                               font-weight:600; border-bottom:1px solid #e2e8f0;">
                                        {$valeurs['nom']}
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:11px 16px; font-size:12px; font-weight:700;
                                               color:#64748b; border-bottom:1px solid #e2e8f0;">
                                        Email
                                    </td>
                                    <td style="padding:11px 16px; font-size:14px; color:#4f46e5;
                                               font-weight:600; border-bottom:1px solid #e2e8f0;">
                                        <a href="mailto:{$valeurs['email']}" style="color:#4f46e5;">
                                            {$valeurs['email']}
                                        </a>
                                    </td>
                                </tr>
                                <tr style="background:#f8fafc;">
                                    <td style="padding:11px 16px; font-size:12px; font-weight:700; color:#64748b;">
                                        Sujet
                                    </td>
                                    <td style="padding:11px 16px; font-size:14px; color:#1e293b; font-weight:600;">
                                        {$valeurs['sujet']}
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Corps du message -->
                    <tr>
                        <td style="padding:16px 40px 24px;">
                            <div style="font-size:13px; font-weight:700; color:#64748b;
                                        text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                                Message
                            </div>
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;
                                        padding:16px 20px; font-size:14px; color:#1e293b;
                                        line-height:1.7; white-space:pre-wrap;">
                                {$message_html}
                            </div>
                        </td>
                    </tr>

                    <!-- Bouton répondre -->
                    <tr>
                        <td style="padding:0 40px 24px; text-align:center;">
                            <a href="mailto:{$valeurs['email']}?subject=Re: {$valeurs['sujet']}"
                               style="display:inline-block; background:#4f46e5; color:#fff;
                                      padding:12px 28px; border-radius:8px; text-decoration:none;
                                      font-weight:700; font-size:14px;">
                                ✉️ Répondre à {$valeurs['nom']}
                            </a>
                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="background:#f8fafc; border-top:1px solid #e2e8f0;
                                   padding:16px 40px; text-align:center;">
                            <div style="font-size:11px; color:#94a3b8;">
                                Message reçu le {$date_reception} via le formulaire de contact FinTrack<br>
                                ECAM-EPMI Cergy — Projet Web 2025-2026
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
// -- 6. Template HTML — Email de confirmation au visiteur --
// ============================================================

// Construit le template HTML de l'accusé de réception envoyé au visiteur
// Paramètre : $valeurs — données du formulaire de contact
// Retourne  : string HTML complet
function construireEmailConfirmationContact($valeurs) {

    $date_envoi = (new DateTime())->format('d/m/Y à H:i');

    $html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Confirmation de contact — FinTrack</title>
</head>
<body style="margin:0; padding:0; background:#f1f5f9; font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f1f5f9; padding:30px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                       style="background:#fff; border-radius:16px; overflow:hidden;
                              box-shadow:0 4px 12px rgba(0,0,0,0.08);">

                    <!-- En-tête -->
                    <tr>
                        <td style="background:linear-gradient(135deg,#4f46e5,#7c3aed);
                                   padding:32px 40px; text-align:center;">
                            <div style="font-size:26px; font-weight:800; color:#fff; margin-bottom:4px;">
                                FinTrack
                            </div>
                            <div style="font-size:14px; color:rgba(255,255,255,0.8);">
                                Nous avons bien reçu votre message
                            </div>
                        </td>
                    </tr>

                    <!-- Corps principal -->
                    <tr>
                        <td style="padding:32px 40px 16px;">
                            <div style="background:#d1fae5; border:1px solid #10b981; border-radius:10px;
                                        padding:16px 20px; text-align:center; margin-bottom:24px;">
                                <span style="font-size:28px;">&#10004;</span><br>
                                <span style="color:#065f46; font-weight:700; font-size:15px;">
                                    Votre message a bien été envoyé !
                                </span>
                            </div>

                            <p style="font-size:14px; color:#1e293b; line-height:1.7; margin:0 0 16px;">
                                Bonjour <strong>{$valeurs['nom']}</strong>,
                            </p>
                            <p style="font-size:14px; color:#64748b; line-height:1.7; margin:0 0 16px;">
                                Nous avons bien reçu votre message concernant :
                                <strong style="color:#1e293b;">« {$valeurs['sujet']} »</strong>.
                            </p>
                            <p style="font-size:14px; color:#64748b; line-height:1.7; margin:0 0 24px;">
                                Notre équipe vous répondra dans les plus brefs délais (généralement sous 24 à 48h).
                                En attendant, n'hésitez pas à explorer les fonctionnalités de FinTrack.
                            </p>

                            <!-- Récapitulatif du message -->
                            <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px;
                                        padding:16px 20px; margin-bottom:24px;">
                                <div style="font-size:12px; font-weight:700; color:#64748b;
                                            text-transform:uppercase; letter-spacing:0.5px; margin-bottom:10px;">
                                    Récapitulatif de votre message
                                </div>
                                <div style="font-size:13px; color:#1e293b; line-height:1.6;">
                                    <strong>Sujet :</strong> {$valeurs['sujet']}<br>
                                    <strong>Envoyé le :</strong> {$date_envoi}
                                </div>
                            </div>
                        </td>
                    </tr>

                    <!-- Bouton dashboard -->
                    <tr>
                        <td style="padding:0 40px 32px; text-align:center;">
                            <a href="http://localhost/projet_info_fintrack_2026/dashboard.php"
                               style="display:inline-block; background:#4f46e5; color:#fff;
                                      padding:12px 28px; border-radius:8px; text-decoration:none;
                                      font-weight:700; font-size:14px;">
                                Retour au tableau de bord
                            </a>
                        </td>
                    </tr>

                    <!-- Pied de page -->
                    <tr>
                        <td style="background:#f8fafc; border-top:1px solid #e2e8f0;
                                   padding:20px 40px; text-align:center;">
                            <div style="font-size:12px; color:#94a3b8; line-height:1.6;">
                                <strong>FinTrack</strong> — Projet Web ECAM-EPMI Cergy 2025-2026<br>
                                Ange · Ayoub · Ghita · Benoît · Maroua<br>
                                <span style="font-size:11px;">Cet email est envoyé automatiquement. Ne pas répondre.</span>
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
// -- 7. Versions texte brut (fallback) --
// ============================================================

// Version texte brut de l'email vers l'équipe
function construireTexteEquipe($valeurs) {
    return <<<TEXT
FinTrack — Nouveau message de contact
======================================

Nom     : {$valeurs['nom']}
Email   : {$valeurs['email']}
Sujet   : {$valeurs['sujet']}

Message :
---------
{$valeurs['message']}

---
Message reçu via le formulaire de contact FinTrack — ECAM-EPMI 2025-2026.
TEXT;
}

// Version texte brut de la confirmation au visiteur
function construireTexteConfirmationContact($valeurs) {
    return <<<TEXT
FinTrack — Confirmation de réception
======================================

Bonjour {$valeurs['nom']},

Nous avons bien reçu votre message concernant : "{$valeurs['sujet']}".

Notre équipe vous répondra dans les plus brefs délais (sous 24 à 48h).

---
FinTrack — Projet Web ECAM-EPMI Cergy 2025-2026.
Ange · Ayoub · Ghita · Benoît · Maroua
TEXT;
}