<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230302091621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE style ADD success_color VARCHAR(7) NOT NULL, ADD success_hover DOUBLE PRECISION NOT NULL, ADD success_shadow DOUBLE PRECISION NOT NULL, ADD warning_color VARCHAR(7) NOT NULL, ADD warning_hover DOUBLE PRECISION NOT NULL, ADD warning_shadow DOUBLE PRECISION NOT NULL, ADD danger_color VARCHAR(7) NOT NULL, ADD danger_hover DOUBLE PRECISION NOT NULL, ADD danger_shadow DOUBLE PRECISION NOT NULL');
        $this->addSql('UPDATE style SET success_color = "#11C577", warning_color = "#FFC107", danger_color = "#AE1143"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE style DROP success_color, DROP success_hover, DROP success_shadow, DROP warning_color, DROP warning_hover, DROP warning_shadow, DROP danger_color, DROP danger_hover, DROP danger_shadow');
    }
}
