<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221227150637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql("CREATE TABLE `logo_parametrable` (`id` INT(11) NOT NULL AUTO_INCREMENT, `image` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci', `updated_at` DATETIME NULL DEFAULT NULL, `description` VARCHAR(255) NULL DEFAULT NULL COLLATE 'utf8_unicode_ci', `emplacement` VARCHAR(255) NOT NULL COLLATE 'utf8_unicode_ci', PRIMARY KEY (`id`) USING BTREE ) COLLATE='utf8_unicode_ci' ENGINE=InnoDB");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (1, '', NULL, 'Logo dans l\'entête de l\'application (prévoir une image qui passe sur un fond noir)', 'Entete')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (2, '', NULL, 'Logo dans le pied de page de l\'application (prévoir une image qui passe sur un fond noir)', 'Pied de page')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (3, '', NULL, 'Logo utilisé dans les fichiers PDF générés (factures, avoirs, credits, ...), (prévoir une image qui passe sur un fond blanc)', 'PDF Générés')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (4, '', NULL, 'Logo utilisé sur les écrans de connexion (prévoir une image qui passe sur un fond blanc)', 'Ecran de connexion')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (5, '', NULL, 'Logo utilisé en signature des mails', 'Signature des mails')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (6, '', NULL, 'Logo utilisé dans les export excel', 'Exports Excel')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (7, '', NULL, 'Logo sur le carousel de la page d\'accueil', 'Caroussel Accueil')");
        $this->addSql("INSERT INTO `logo_parametrable` (`id`, `image`, `updated_at`, `description`, `emplacement`) VALUES (8, '', NULL, 'Icône des onglets du navigateur', 'favicon')");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql("DROP TABLE `logo_parametrable`");
    }
}
