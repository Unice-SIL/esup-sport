<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221004150409 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_detail DROP FOREIGN KEY FK_2C528446E7A306AC');
        $this->addSql('ALTER TABLE commande_detail ADD CONSTRAINT FK_2C528446E7A306AC FOREIGN KEY (reservabilite_id) REFERENCES reservabilite (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_detail DROP FOREIGN KEY FK_2C528446E7A306AC');
        $this->addSql('ALTER TABLE commande_detail ADD CONSTRAINT FK_2C528446E7A306AC FOREIGN KEY (reservabilite_id) REFERENCES reservabilite (id)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
