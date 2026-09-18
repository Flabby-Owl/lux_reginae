<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/layout.php';

forum_header('Confidentialite');
?>

<section class="forum-form">
  <h2>Politique de confidentialite</h2>
  <p>Cette page fournit une base de conformite RGPD pour le forum Lux Reginae. Elle doit etre completee avec l identite et les coordonnees exactes du responsable du site avant publication.</p>
  <h3>Donnees collectees</h3>
  <p>Le forum collecte uniquement les donnees necessaires au service : pseudo, mot de passe hache, role, dates de creation et de derniere connexion, messages et sujets publies.</p>
  <h3>Finalites</h3>
  <p>Ces donnees servent a creer un compte, connecter les membres, afficher les publications, moderer le forum et securiser les formulaires.</p>
  <h3>Base legale</h3>
  <p>La creation de compte repose sur le consentement explicite de l utilisateur et sur l execution du service demande.</p>
  <h3>Conservation</h3>
  <p>Les comptes sont conserves tant que l utilisateur les maintient. Une suppression de compte est disponible depuis la page Mon compte.</p>
  <h3>Droits RGPD</h3>
  <p>Chaque utilisateur peut exporter ses donnees et supprimer son compte. Pour toute demande complementaire : rectification, opposition ou limitation, contactez le responsable du site.</p>
  <h3>Securite</h3>
  <p>Les mots de passe ne sont jamais stockes en clair. Ils sont haches avec l API native de PHP. Les sessions utilisent des cookies HTTPOnly et SameSite=Lax.</p>
  <h3>Cookies</h3>
  <p>Le forum utilise uniquement un cookie de session necessaire a la connexion. Aucun cookie publicitaire ou de suivi n est ajoute par ce module.</p>
</section>

<?php forum_footer(); ?>
