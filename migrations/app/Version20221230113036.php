<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221230113036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE format_activite_niveau_sportif DROP FOREIGN KEY FK_BF69240D8C5FABB');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif DROP FOREIGN KEY FK_BF69240DAB3B8EF6');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD id INT AUTO_INCREMENT NOT NULL, ADD detail LONGTEXT NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD CONSTRAINT FK_BF69240D8C5FABB FOREIGN KEY (format_activite_id) REFERENCES format_activite (id)');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD CONSTRAINT FK_BF69240DAB3B8EF6 FOREIGN KEY (niveau_sportif_id) REFERENCES niveau_sportif (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE format_activite_niveau_sportif MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif DROP FOREIGN KEY FK_BF69240D8C5FABB');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif DROP FOREIGN KEY FK_BF69240DAB3B8EF6');
        $this->addSql('DROP INDEX `primary` ON format_activite_niveau_sportif');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif DROP id, DROP detail');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD CONSTRAINT FK_BF69240D8C5FABB FOREIGN KEY (format_activite_id) REFERENCES format_activite (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD CONSTRAINT FK_BF69240DAB3B8EF6 FOREIGN KEY (niveau_sportif_id) REFERENCES niveau_sportif (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE format_activite_niveau_sportif ADD PRIMARY KEY (format_activite_id, niveau_sportif_id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
