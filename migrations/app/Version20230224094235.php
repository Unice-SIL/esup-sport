<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230224094235 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE style ADD navbar_background_color VARCHAR(7) NOT NULL, ADD navbar_foreground_color VARCHAR(7) NOT NULL');
        $this->addSql("UPDATE style SET navbar_background_color = '#1a1a1a', navbar_foreground_color = '#ffffff'");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE style DROP navbar_background_color, DROP navbar_foreground_color');
    }
}
