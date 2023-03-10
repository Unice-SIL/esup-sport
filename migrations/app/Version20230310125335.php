<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230310125335 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lexik_trans_unit (id INT AUTO_INCREMENT NOT NULL, key_name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX key_domain_idx (key_name, domain), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lexik_trans_unit_translations (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, trans_unit_id INT DEFAULT NULL, locale VARCHAR(10) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, modified_manually TINYINT(1) NOT NULL, INDEX IDX_B0AA394493CB796C (file_id), INDEX IDX_B0AA3944C3C583C9 (trans_unit_id), UNIQUE INDEX trans_unit_locale_idx (trans_unit_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE lexik_translation_file (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) NOT NULL, locale VARCHAR(10) NOT NULL, extention VARCHAR(10) NOT NULL, path VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA394493CB796C FOREIGN KEY (file_id) REFERENCES lexik_translation_file (id)');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA3944C3C583C9 FOREIGN KEY (trans_unit_id) REFERENCES lexik_trans_unit (id)');
        $this->addSql('ALTER TABLE email ADD nom VARCHAR(255) NOT NULL');
        $this->addSql('DELETE FROM `email` WHERE `id` = 1');
        $this->addSql('DELETE FROM `email` WHERE `id` = 2');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (3, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Votre commande n&deg;[[numeroCommande]]a &eacute;t&eacute; annul&eacute;e.</p>\r\n</div>\', \'Annulation de la commande\', \'AnulationCommande\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (4, \'<div>Bonjour,\r\n<p>Linscription portant l&#39;id&nbsp;[[id_inscription]] n&#39;a pas pu &ecirc;tre annul&eacute;e par le timeout.<br />\r\nIl semblerait que la commande &agrave; laquelle elle est associ&eacute;e soit valide et termin&eacute;e.</p>\r\n</div>\', \'TIMEOUT : erreur annulation inscription\', \'ErreurAnnulationInscription\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (5, \'<div>Bonjour,\r\n<p>La commande n&deg;[[numeroCommande]] n&#39;a pas pu &ecirc;tre termin&eacute;e.<br />\r\nLe montant PAYBOX pay&eacute; par l&#39;utilisateur est de&nbsp;[[montantPaybox]] &euro; alors que le montant totale de la commande est de [[montantTotal]]&euro;.</p>\r\n</div>\', \'Erreur retour paiement PAYBOX\', \'ErreurMontantPaybox\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (6, \'<div>Bonjour,\r\n<p>Le paiement de votre commande n&deg;[[numeroCommande]]a bien &eacute;t&eacute; enregistr&eacute;e. Vous pouvez acc&eacute;der &agrave; la facture de votre commande avec le lien suivant :&nbsp;[[lienFacture]]</p>\r\n</div>\', \'Validation de la commande\', \'ValidationCommande\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (7, \'<p>Votre compte est bloqu&eacute;</p>\', \'Votre compte est bloqué\', \'UtilisateurBloquerEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (8, \'<p>Votre compte est activ&eacute;</p>\r\n\r\n<p>&nbsp;</p>\', \'Votre compte est activé\', \'UtilisateurDebloquerEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (9, \'<p>Bonjour,<br />\r\n&nbsp;</p>\r\n\r\n<p>Vous avez une demande de contact de la part de :&nbsp;[[contact_from]]</p>\r\n\r\n<p>Sujet :&nbsp;[[objet]]<br />\r\nCorps du message :<br />\r\n[[message]]</p>\', \'[[objet]]\', \'ContactEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (10, \'<p>Bonjour,<br />\r\n&nbsp;</p>\r\n\r\n<p>Le message suivant vous a &eacute;t&eacute; envoy&eacute; par l&#39;encadrant de l&#39;activit&eacute;:</p>\r\n\r\n<p>[[message]]</p>\r\n\r\n<p>&nbsp;</p>\', \'[[objet]]\', \'ContactEmailing\')');
        $this->addSql('DELETE FROM `email` WHERE `id`= 11');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (12, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Vous avez &eacute;t&eacute; d&eacute;sinscrit &agrave; l&#39;activit&eacute; suivante:&nbsp;[[inscription]]<br />\r\nEn cas de probl&egrave;me, merci de contacter le bureau des sports</p>\r\n</div>\', \'Désinscription\', \'Desinscription\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (13, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Votre partenaire qui a initi&eacute; l&#39;inscription s&#39;est d&eacute;sinscrit de l&#39;activit&eacute; suivante : [[inscription]]<br />\r\nVotre commande a donc &eacute;t&eacute; annul&eacute;e.<br />\r\nEn cas de probl&egrave;me, merci de contacter le bureau des sports.</p>\r\n</div>\', \'Désinscription partenaire\', \'DesinscriptionPartenaire\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (14, \'<p>Bonjour,<br />\r\n&nbsp;</p>\r\n\r\n<p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />\r\nCette inscription n&eacute;cessite la validation d&#39;un encadrant :&nbsp;[[listeEncadrants]]</p>\', \'Inscription\', \'InscriptionAvecValidation\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (15, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Le [[date]],&nbsp;[[prenom]]&nbsp;[[nom]]&nbsp;([[mail]]) a souhait&eacute; s&#39;inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />\r\nCette inscription n&eacute;cessite votre validation en tant [[statut]]</p>\r\n\r\n<p>Pour valider cette inscription, vous pouvez vous rendre &agrave; l&#39;adresse suivante : [[lienInscription]]</p>\r\n</div>\', \'Demande d\\\'inscription\', \'InscriptionDemandeValidation\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (16, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>L&#39;utilisateur [[prenom]]&nbsp;[[nom]]vous &agrave; ajout&eacute; en tant que partenaire pour un entra&icirc;nement !<br />\r\nVous devez confirmer votre pr&eacute;sence au cr&eacute;neau de&nbsp;[[formatActivite]] de[[dateDebut]]&agrave;&nbsp;[[dateFin]] au [[etablissement]]-[[ressource]] le [[evenement]]<br />\r\n<br />\r\nConnectez-vous &agrave; votre compte UCA Sport, et cliquez sur le lien suivant : [[lienInscription]] pour vous permettre d&rsquo;ajouter l&rsquo;invitation &agrave; jouer &agrave; votre panier.</p>\r\n\r\n<p>Si vous n&rsquo;avez pas encore de compte UCA Sport.<br />\r\nCr&eacute;ez d&egrave;s &agrave; pr&eacute;sent votre compte <a href="{{ app.request != null ? app.request.schemeAndHttpHost }}{{ path(\\\'UcaWeb_preInscription\\\') }}">ici</a>.<br />\r\nUne fois votre compte cr&eacute;&eacute;, connectez-vous, et cliquez sur le lien suivant : [[lienInscription]]&nbsp;pour vous permettre d&rsquo;ajouter l&rsquo;invitation &agrave; jouer &agrave; votre panier.<br />\r\nValidez votre panier pour confirmer votre inscription en tant que partenaire.<br />\r\n<br />\r\nEn cas de probl&egrave;me, merci de contacter le bureau des sports.</p>\r\n</div>\', \'Inscription avec partenaire\', \'InscriptionPartenaire\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (17, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante: [[inscription]]<br />\r\nCette inscription a &eacute;t&eacute; refus&eacute;e pour le motif [[motifAnnulation]]. La pr&eacute;cision suivant a &eacute;t&eacute; indiqu&eacute;e: [[commentaireAnnulation]]</p>\r\n</div>\', \'Demande d\\\'inscription refusée\', \'InscriptionRefusee\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (18, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Le [[date]], vous avez souhait&eacute; vous inscrire &agrave; l&#39;activit&eacute; suivante:[[inscription]]<strong> </strong><br />\r\nVotre inscription a &eacute;t&eacute; autoris&eacute;e. Vous devez maintenant acc&eacute;der &agrave; la page [[lienInscription]]&nbsp;afin de pouvoir ajouter votre inscription au panier et finaliser votre commande.</p>\r\n\r\n<p>Attention, vous avez&nbsp;[[timerPanierApresValidation]] heure(s) pour ajouter votre inscription au panier. Apr&egrave;s l&#39;ajout au panier vous disposerez de&nbsp;[[timerPanier]] minutes pour soit payer votre commande en ligne, soit confirmer que vous paierez au bureau des sports. Vous finaliserez ainsi votre inscription. <strong>Si vous d&eacute;passez ces d&eacute;lais, votre inscription sera automatiquement annul&eacute;e.</strong></p>\r\n<strong><strong> </strong></strong></div>\', \'Demande d\\\'inscription validée\', \'InscriptionValidee\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (19, \'<p>Bonjour&nbsp;[[user]] !</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Pour valider votre compte utilisateur, merci de vous rendre sur [[lienPreInscription]]</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Ce lien ne peut &ecirc;tre utilis&eacute; qu&#39;une seule fois pour valider votre compte.</p>\', \'Inscription confirmée\', \'ConfirmationEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (20, \'<div>Bonjour,\r\n<p>Une demande de pr&eacute;-inscription a &eacute;t&eacute; faite par l&#39;utilisateur suivant : [[prenom]]&nbsp;[[nom]]</p>\r\n\r\n<p>Pour aller consulter sa fiche, merci de vous rendre sur le lien suivant:&nbsp;[[lienUtilisateur]]</p>\r\n</div>\', \'Demande de validation\', \'DemandeValidationEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (21, \'<p>Bonjour,</p>\r\n\r\n<p>Votre demande de pr&eacute;-inscription a bien &eacute;t&eacute; prise en compte.</p>\', \'Confirmation demande d\\\'inscription\', \'PreInscriptionEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (22, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Votre demande de pr&eacute;-inscription a &eacute;t&eacute; refus&eacute;e.</p>\r\n</div>\', \'Inscription refusée\', \'RefusEmail\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (23, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>Le message suivant vous a &eacute;t&eacute; envoy&eacute; par l&#39;encadrant de l&#39;activit&eacute;:</p>\r\n\r\n<p>[[message]]</p>\r\n\r\n<p>&nbsp;</p>\r\n</div>\', \'[[formatActivite]] : [[dateDebut]] - [[dateFin]] [[objet]]\', \'MailPourTousLesInscripts\')');
        $this->addSql('DELETE FROM `email` WHERE `id` = 24');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (25, \'<div>Bonjour,<br />\r\n&nbsp;\r\n<p>La commande n&deg;[[numeroCommande]]&nbsp;a &eacute;t&eacute; enregistr&eacute;e. Vous avez [[timerBds]]&nbsp;heures pour payer cette commande aupr&egrave;s du bureau des sports. Pass&eacute; ce d&eacute;lai, la commande sera annul&eacute;e.</p>\r\n</div>\', \'Commande à régler au bureau des sports\', \'CommandeARegler\')');
        $this->addSql('REPLACE INTO `email` (`id`, `corps`, `subject`, `nom`) VALUES (26, \'<p>Bonjour,<br />\r\n&nbsp;</p>\r\n\r\n<p>Vous avez une demande de contact de la part de :&nbsp;[[contact_from]]</p>\r\n\r\n<p>Sujet :&nbsp;&nbsp;[[format_activite]]&nbsp;:&nbsp;[[event_date]]&nbsp;[[event_start_hour]]&nbsp;-&nbsp;[[event_date]]&nbsp;[[event_end_hour]]<br />\r\nCorps du message :<br />\r\n[[message]]</p>\', \'[[format_activite]] : [[event_date]] [[event_start_hour]] - [[event_date]] [[event_end_hour]]\', \'ContactEncadrantEmail\')');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lexik_trans_unit_translations DROP FOREIGN KEY FK_B0AA394493CB796C');
        $this->addSql('ALTER TABLE lexik_trans_unit_translations DROP FOREIGN KEY FK_B0AA3944C3C583C9');
        $this->addSql('DROP TABLE lexik_trans_unit');
        $this->addSql('DROP TABLE lexik_trans_unit_translations');
        $this->addSql('DROP TABLE lexik_translation_file');
        $this->addSql('ALTER TABLE email DROP nom');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
