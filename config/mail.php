<?php
// ============================================================
// FICHIER      : config/mail.php
// AUTEUR       : Ghita — Développeure Backend PHP
// PROJET       : FinTrack — ECAM-EPMI 2025-2026
// DESCRIPTION  : Configuration centrale de PHPMailer pour le
//                projet FinTrack. Contient les paramètres SMTP
//                (serveur, port, authentification) et retourne
//                une instance PHPMailer prête à l'emploi.
//                Utilisé par traitement_formulaire.php et
//                traitement_contact.php.
// DATE         : Mars 2026
// ============================================================


// -- 1. Import de la librairie PHPMailer --

// Chargement des 3 fichiers nécessaires de PHPMailer (installés dans vendor/)
require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../vendor/phpmailer/Exception.php';

// Utilisation des classes PHPMailer dans l'espace de noms global
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


// -- 2. Constantes de configuration SMTP --
// ⚠️ À MODIFIER avec vos propres identifiants avant utilisation
// ⚠️ Ne jamais publier ce fichier avec de vraies données sur GitHub (.gitignore)

// Adresse email de l'expéditeur (compte SMTP utilisé pour envoyer)
define('MAIL_SMTP_HOST',       'smtp.gmail.com');       // Serveur SMTP (Gmail par défaut)
define('MAIL_SMTP_PORT',       587);                    // Port SMTP (587 = TLS, 465 = SSL)
define('MAIL_SMTP_SECURITE',   'tls');                  // Protocole de sécurité
define('MAIL_SMTP_USER',       'fintrack50@gmail.com');  // Email expéditeur SMTP
// ⚠️ IMPORTANT : Utiliser un "mot de passe d'application" Gmail, pas le vrai mot de passe du compte
// Générer un mot de passe app ici : https://myaccount.google.com/apppasswords
define('MAIL_SMTP_PASSWORD',   'VOTRE_MOT_DE_PASSE_ICI');   // Mot de passe d'application généré

define('MAIL_EXPEDITEUR_NOM',  'FinTrack ECAM-EPMI');   // Nom affiché à la réception
define('MAIL_EXPEDITEUR_EMAIL','fintrack50@gmail.com');  // Adresse affichée à la réception

// Adresse de l'équipe FinTrack (destinataire des messages de contact)
define('MAIL_EQUIPE_EMAIL',    'fintrack50@gmail.com');
define('MAIL_EQUIPE_NOM',      'Équipe FinTrack');


// -- 3. Fonction factory — crée et configure une instance PHPMailer --

// Crée une instance PHPMailer configurée avec les paramètres SMTP définis ci-dessus
// Retourne une instance PHPMailer prête à recevoir destinataire, sujet et corps
// En cas d'erreur de configuration, lance une Exception
function creerInstanceMailer() {

    // Création de l'instance avec gestion des exceptions activée
    $mailer = new PHPMailer(true);

    // -- 3.1 Paramètres SMTP --
    $mailer->isSMTP();                               // Mode SMTP
    $mailer->Host       = MAIL_SMTP_HOST;            // Serveur SMTP
    $mailer->SMTPAuth   = true;                      // Authentification SMTP requise
    $mailer->Username   = MAIL_SMTP_USER;            // Identifiant SMTP
    $mailer->Password   = MAIL_SMTP_PASSWORD;        // Mot de passe SMTP
    $mailer->SMTPSecure = MAIL_SMTP_SECURITE;        // Protocole TLS
    $mailer->Port       = MAIL_SMTP_PORT;            // Port SMTP

    // -- 3.2 Paramètres de l'expéditeur --
    $mailer->setFrom(MAIL_EXPEDITEUR_EMAIL, MAIL_EXPEDITEUR_NOM);
    $mailer->CharSet = 'UTF-8';                      // Encodage UTF-8 pour les accents

    // -- 3.3 Mode debug désactivé en production (messages techniques cachés) --
    // Redevient DEBUG_SERVER si vous devez déboguer à nouveau
    $mailer->SMTPDebug = SMTP::DEBUG_OFF;

    // -- 3.4 Configuration de vérification SSL (fix pour les problèmes de certificat) --
    // Désactiver la vérification SSL autorisée dans certains environnements locaux
    $mailer->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true
        )
    );

    return $mailer;
}