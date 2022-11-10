<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614095115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE shnu_rubrique (id INT AUTO_INCREMENT NOT NULL, type_id INT DEFAULT NULL, ordre INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, lien VARCHAR(255) DEFAULT NULL, texte LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, updated_at datetime DEFAULT NULL, INDEX IDX_73BBE76EC54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type_rubrique (id INT AUTO_INCREMENT NOT NULL, libelle VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE UTF8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE shnu_rubrique ADD CONSTRAINT FK_73BBE76EC54C8C93 FOREIGN KEY (type_id) REFERENCES type_rubrique (id)');
        $this->addSql('ALTER TABLE groupe CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE montant_tarif_profil_utilisateur CHANGE montant montant NUMERIC(10, 2) DEFAULT \'-1\' NOT NULL');
        $this->addSql('ALTER TABLE tarif CHANGE pourcentage_tva pourcentage_tva NUMERIC(3, 1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE type_autorisation CHANGE tarif_libelle tarif_libelle LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('INSERT INTO type_rubrique (id, libelle) VALUES (1, \'Highlights\'), (2, \'Partenaires\'), (3, \'Lien vers l\\\'extérieur\'), (4, \'Page d\\\'informations\');');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shnu_rubrique DROP FOREIGN KEY FK_73BBE76EC54C8C93');
        $this->addSql('DROP TABLE shnu_rubrique');
        $this->addSql('DROP TABLE type_rubrique');
        $this->addSql('ALTER TABLE groupe CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE utf8mb4_bin');
        $this->addSql('ALTER TABLE montant_tarif_profil_utilisateur CHANGE montant montant NUMERIC(10, 2) DEFAULT \'-1.00\' NOT NULL');
        $this->addSql('ALTER TABLE tarif CHANGE pourcentage_tva pourcentage_tva NUMERIC(3, 1) DEFAULT \'0.0\' NOT NULL');
        $this->addSql('ALTER TABLE type_autorisation CHANGE tarif_libelle tarif_libelle LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE utilisateur CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE utf8mb4_bin');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
