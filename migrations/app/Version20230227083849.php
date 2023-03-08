<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227083849 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logo_parametrable ADD actif TINYINT(1) NOT NULL');
        $this->addSql('UPDATE logo_parametrable SET actif = 1');
        $this->addSql('UPDATE logo_parametrable SET description = "Logo dans l\'entête de l\'application" WHERE id = 1');
        $this->addSql('UPDATE logo_parametrable SET description = "Logo dans le pied de page de l\'application" WHERE id = 2');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logo_parametrable DROP actif');
    }
}
