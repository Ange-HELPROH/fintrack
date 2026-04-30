<?php
// ============================================================
// FICHIER      : php/envoyer_pdf.php
// AUTEUR       : Ghita — Développeure Backend PHP
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Endpoint AJAX. Reçoit un PDF en base64 +
//                une adresse email, et envoie le PDF en pièce
//                jointe via PHPMailer. Appelé par pdf.js via
//                la fonction envoyerPDFParEmail().
// DATE         : Mars 2026
// ============================================================

// -- 1. Headers JSON obligatoires (réponse JSON) --
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// -- 2. Vérifie que la requête est bien en POST --
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['succes' => false, 'message' => 'Methode non autorisee.']);
    exit;
}

// -- 3. Récupération et validation des données --

// Email destinataire
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
if (!$email) {
    echo json_encode(['succes' => false, 'message' => 'Adresse email invalide.']);
    exit;
}

// Données PDF en base64 pur (sans le préfixe data:...)
$pdfBase64 = trim($_POST['pdf_b64'] ?? '');
if (empty($pdfBase64)) {
    echo json_encode(['succes' => false, 'message' => 'Donnees PDF manquantes.']);
    exit;
}

// Nom du fichier PDF
$nomFichier = preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $_POST['nom_fichier'] ?? 'fintrack_rapport.pdf');
if (empty($nomFichier) || !str_ends_with($nomFichier, '.pdf')) {
    $nomFichier = 'fintrack_rapport.pdf';
}

// -- 4. Décode le base64 en binaire PDF --
$contenuPDF = base64_decode($pdfBase64, true);
if ($contenuPDF === false || strlen($contenuPDF) < 100) {
    echo json_encode(['succes' => false, 'message' => 'PDF corrompu ou trop petit.']);
    exit;
}

// -- 5. Chargement PHPMailer et envoi --
require_once __DIR__ . '/../config/mail.php';

try {
    $mailer = creerInstanceMailer();

    // -- 5.1 Destinataire --
    $mailer->addAddress($email);

    // -- 5.2 Sujet --
    $mailer->Subject = '[FinTrack] Votre rapport financier';

    // -- 5.3 Piece jointe PDF (depuis le binaire en memoire) --
    $mailer->addStringAttachment($contenuPDF, $nomFichier, 'base64', 'application/pdf');

    // -- 5.4 Corps HTML de l'email -- 
    $mailer->isHTML(true);
    $dateEnvoi = (new DateTime())->format('d/m/Y a H:i');

    $mailer->Body = '<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>FinTrack - Rapport</title></head>
<body style="margin:0;padding:0;background:#F8FAFF;font-family:Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#F8FAFF;padding:30px 0;">
<tr><td align="center">
<table width="580" cellpadding="0" cellspacing="0"
       style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(108,99,255,0.10);">
    <tr>
        <td style="background:linear-gradient(135deg,#6C63FF,#4F46E5);padding:28px 36px;text-align:center;">
            <div style="font-size:22px;font-weight:900;color:#fff;letter-spacing:3px;">FINTRACK</div>
            <div style="font-size:12px;color:rgba(255,255,255,0.8);margin-top:4px;">Votre rapport financier vous est envoye en piece jointe</div>
        </td>
    </tr>
    <tr>
        <td style="padding:28px 36px;">
            <div style="background:#E0FBF4;border:1px solid #00C896;border-radius:10px;padding:14px 18px;text-align:center;margin-bottom:24px;">
                <span style="font-size:24px;">&#128196;</span><br>
                <span style="color:#00A87D;font-weight:700;font-size:14px;">Votre PDF est en piece jointe !</span>
            </div>
            <p style="color:#4A5568;font-size:14px;line-height:1.7;margin:0 0 16px;">
                Bonjour,<br><br>
                Veuillez trouver en piece jointe votre rapport financier FinTrack au format PDF.<br>
                Vous pouvez l\'ouvrir, le telecharger ou l\'imprimer directement.
            </p>
            <p style="color:#8896AB;font-size:12px;margin:0;">
                Ce rapport a ete genere le ' . $dateEnvoi . '.
            </p>
        </td>
    </tr>
    <tr>
        <td style="text-align:center;padding:0 36px 28px;">
            <a href="http://localhost/projet_info_fintrack_2026/dashboard.php"
               style="display:inline-block;background:linear-gradient(135deg,#6C63FF,#4F46E5);color:#fff;
                      padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:700;font-size:14px;">
                Retour au Dashboard
            </a>
        </td>
    </tr>
    <tr>
        <td style="background:#F8FAFF;border-top:1px solid #E2E8F4;padding:16px 36px;text-align:center;">
            <div style="font-size:11px;color:#8896AB;">
                FinTrack - Projet Web ECAM-EPMI Cergy 2025-2026<br>
                Ange · Ayoub · Ghita · Benoit · Maroua
            </div>
        </td>
    </tr>
</table>
</td></tr></table>
</body></html>';

    // -- 5.5 Version texte brut --
    $mailer->AltBody = "FinTrack - Rapport financier\n\nVeuillez trouver en piece jointe votre rapport PDF FinTrack.\n\nGenere le " . $dateEnvoi . "\n\nECAM-EPMI 2025-2026";

    // -- 5.6 Envoi --
    $mailer->send();

    echo json_encode(['succes' => true, 'message' => 'Email envoye avec succes !']);

} catch (Exception $e) {
    // On loggue l'erreur silencieusement et on retourne un message generique
    // error_log('[FinTrack] Erreur envoi PDF : ' . $e->getMessage());
    echo json_encode(['succes' => false, 'message' => 'Erreur d\'envoi. Verifiez la configuration SMTP dans config/mail.php']);
}