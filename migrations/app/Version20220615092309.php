<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615092309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO shnu_rubrique (id, type_id, ordre, titre, lien, texte, image, updated_at) VALUES (1, 3, 1, \'ÉTUDIANT / FUTUR ÉTUDIANT, CANDIDATEZ POUR LE STATUT DE SHN\', \'https://shnu.univ-cotedazur.fr/\', NULL, \'\', \'2022-11-11 09:00:00\'), (2, 1, 2, \'NOS AMBASSADEURS\', NULL, NULL, \'\', \'2022-11-11 09:00:00\'), (3, 4, 3, \'ACCOMPAGNEMENT\', NULL, \'\', \'\', \'2022-11-11 09:00:00\'), (4, 2, 4, \'PARTENAIRES\', NULL, NULL, \'\', \'2022-11-11 09:00:00\');');
        $this->addSql('UPDATE shnu_rubrique set image = (select image from image_fond where id = 15) where id = 1;');
        $this->addSql('UPDATE shnu_rubrique set image = (select image from image_fond where id = 16) where id = 2;');
        $this->addSql('UPDATE shnu_rubrique set image = (select image from image_fond where id = 17) where id = 3;');
        $this->addSql('UPDATE shnu_rubrique set image = (select image from image_fond where id = 18) where id = 4;');
        $this->addSql('UPDATE shnu_rubrique set texte = (select texte from texte where id = 25) where id = 3;');

        $this->addSql('DELETE FROM image_fond WHERE image_fond.id = 14;');
        $this->addSql('DELETE FROM texte WHERE texte.id = 24;');
        $this->addSql('DELETE FROM image_fond WHERE image_fond.id = 17;');
        $this->addSql('DELETE FROM texte WHERE texte.id = 25;');
        $this->addSql('DELETE FROM image_fond WHERE image_fond.id = 15;');
        $this->addSql('DELETE FROM image_fond WHERE image_fond.id = 16;');
        $this->addSql('DELETE FROM image_fond WHERE image_fond.id = 18;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('INSERT INTO image_fond (id, emplacement, titre, image, updated_at) VALUES(14, \'SHN - Accompagnement \', \'Vous souhaitez intégrer UCA\', \'\', \'2020-09-29 09:18:04\')');
        $this->addSql('INSERT INTO texte (id, emplacement, titre, texte, mobile, texte_mobile) VALUES (24, \'SHN - Accompagnement\', \'Vous souhaitez intégrer UCA en tant que Sportif de Haut Niveau\', \'<h3><strong>Sport de Haut Niveau Universitaire</strong></h3>\r\n\r\n<p>D&eacute;poser une candidature sportif de haut niveau</p>\', 0, NULL)');
        $this->addSql('INSERT INTO image_fond (id, emplacement, titre, image, updated_at) VALUES(17, \'SHN - Representer\', \'Accompagnement\', \'\', \'2020-09-29 09:06:40\')');
        $this->addSql('INSERT INTO texte (id, emplacement, titre, texte, mobile, texte_mobile) VALUES (25, \'SHN - Representer\', \'Accompagnement\', \'<p><strong>Universit&eacute; C&ocirc;te d&rsquo;Azur</strong> s&rsquo;engage dans une politique volontariste d&rsquo;aide aux sportif.ve.s de haut niveau. L&rsquo;obtention du statut facilite la r&eacute;ussite du double objectif&nbsp;: concilier ses &eacute;tudes sup&eacute;rieures avec une carri&egrave;re sportive professionnelle ou semi professionnelle.&nbsp;</p>\r\n\r\n<p>Le <strong>statut de SHNU</strong> est accord&eacute; pour une ann&eacute;e universitaire sur avis de la commission SHNU et est renouvelable sans limite d&egrave;s lors que l&rsquo;on est &eacute;tudiant.e r&eacute;guli&egrave;rement inscrit.e &agrave; Universit&eacute; C&ocirc;te d&rsquo;Azur.</p>\r\n\r\n<p>Chaque &eacute;tudiant SHNU est suivi par un <strong>r&eacute;f&eacute;rent p&eacute;dagogique</strong>, qui l&rsquo;accompagne dans la r&eacute;ussite de son projet universitaire, au regard de ses contraintes sportives.</p>\r\n\r\n<p>Qu&rsquo;offre le statut SHNU&nbsp;&agrave; Universit&eacute; C&ocirc;te d&rsquo;Azur ?</p>\r\n\r\n<ul>\r\n	<li>Un <strong>am&eacute;nagement des &eacute;tudes et des examens</strong> en lien avec les responsables de formation</li>\r\n	<li>Un <strong>accompagnement personnalis&eacute;</strong> par le r&eacute;f&eacute;rent p&eacute;dagogique d&rsquo;Universit&eacute; C&ocirc;te d&rsquo;Azur</li>\r\n	<li>Une <strong>alimentation diff&eacute;renci&eacute;e</strong> dans les points de restauration CROUS</li>\r\n	<li>Un <strong>acc&egrave;s facilit&eacute; aux logements &eacute;tudiants</strong> CROUS</li>\r\n	<li>Une <strong>valorisation</strong> par les services de communication d&rsquo;Universit&eacute; C&ocirc;te d&rsquo;Azur</li>\r\n	<li>L&rsquo;&eacute;ligibilit&eacute; aux <strong>bourses r&eacute;serv&eacute;es aux sportifs de haut niveau</strong> par la <a href=\"https://fondation-uca.org\">Fondation UCA</a></li>\r\n</ul>\r\n\r\n<p>Comment obtenir le statut ?&nbsp;</p>\r\n\r\n<ul>\r\n	<li>Rendez-vous sur le bouton &quot;candidater&quot; de la page Sport de Haut Niveau</li>\r\n</ul>\', 0, NULL)');
        $this->addSql('INSERT INTO image_fond (id, emplacement, titre, image, updated_at) VALUES(15, \'SHN - Candidater\', \'Candidater\', \'\', \'2020-07-21 16:10:59\')');
        $this->addSql('INSERT INTO image_fond (id, emplacement, titre, image, updated_at) VALUES(16, \'SHN - Highlights\', \'Highlights\', \'\', \'2020-07-21 16:15:30\')');
        $this->addSql('INSERT INTO image_fond (id, emplacement, titre, image, updated_at) VALUES(18, \'SHN - Partenaires\', \'Partenaires\', \'\', \'2020-08-24 15:39:01\')');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
