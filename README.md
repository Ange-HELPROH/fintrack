FinTrack - Gestion de revenus et depenses personnelles

Projet Web dynamique realise dans le cadre du cours de Developpement Web,
2eme annee prepa ingenieur, ECAM-EPMI Cergy, annee scolaire 2025-2026.


Presentation

FinTrack est un site web qui permet de gerer ses finances personnelles.
On peut enregistrer ses revenus et depenses, visualiser ses donnees
sous forme de graphiques, recevoir des conseils financiers automatiques
et generer des rapports PDF.

Le site est construit avec HTML, CSS, JavaScript et PHP. Il utilise
Bootstrap pour le design responsive, Chart.js pour les graphiques,
jsPDF pour la generation de PDF et PHPMailer pour l'envoi d'emails.


Equipe

Ange TEUFACK : Chef de projet, design UI/UX, pages d'accueil et equipe,
               CSS global, theme clair/sombre.

Ayoub :         Developpeur Frontend JS, interactions dynamiques,
               filtres jQuery UI, animations, navigation.

Ghita :         Developpeuse Backend PHP, validation des formulaires,
               configuration SMTP, envoi d'emails via PHPMailer.

Benoit :        Developpeur Data et Visualisation, donnees localStorage,
               graphiques Chart.js, historique, conseils financiers.

Maroua :        Developpeuse PDF et Documentation, generation de PDF
               via jsPDF, redaction du README, preparation du rendu.


Installation

1. Installer XAMPP (Apache + PHP).
2. Copier le dossier projet_info_fintrack_2026 dans C:\xampp\htdocs\.
3. Demarrer Apache depuis le panneau XAMPP.
4. Ouvrir http://localhost/projet_info_fintrack_2026/ dans le navigateur.

Pour l'envoi d'emails, modifier config/mail.php avec vos identifiants
SMTP Gmail (mot de passe d'application).


Technologies utilisees

- HTML5, CSS3, JavaScript ES6+, PHP 8
- Bootstrap 5.3 (mise en page responsive)
- Bootstrap Icons 1.11 (icones)
- jQuery UI 1.13 (datepicker, autocomplete)
- Chart.js 4.4 (graphiques interactifs)
- jsPDF 2.5 (generation PDF)
- PHPMailer 6.x (envoi d'emails SMTP)


Structure du projet

- index.html : page d'accueil (landing page)
- dashboard.php : tableau de bord avec graphiques
- formulaire.php : saisie de transaction
- historique.php : liste des transactions avec filtres
- conseils.php : conseils financiers automatiques
- rapport.php : generation de rapports PDF
- equipe.php : presentation de l'equipe et formulaire de contact
- config/mail.php : configuration PHPMailer
- php/ : scripts de traitement (formulaire, contact, envoi PDF)
- assets/css/ : feuilles de style (style.css, theme.css)
- assets/js/ : scripts JS (main.js, data.js, charts.js, pdf.js, theme.js)
- vendor/phpmailer/ : librairie PHPMailer


Date de rendu : 5 avril 2026
Plateforme : Netypareo
Format : archive .zip
