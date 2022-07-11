<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615095742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highlight CHANGE video video LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE montant_tarif_profil_utilisateur CHANGE montant montant NUMERIC(10, 2) DEFAULT \'-1\' NOT NULL');
        $this->addSql('ALTER TABLE tarif CHANGE pourcentage_tva pourcentage_tva NUMERIC(3, 1) DEFAULT \'0\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE highlight CHANGE video video VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE montant_tarif_profil_utilisateur CHANGE montant montant NUMERIC(10, 2) DEFAULT \'-1.00\' NOT NULL');
        $this->addSql('ALTER TABLE tarif CHANGE pourcentage_tva pourcentage_tva NUMERIC(3, 1) DEFAULT \'0.0\' NOT NULL');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
