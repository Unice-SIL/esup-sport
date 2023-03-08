<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230210095439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE style (id INT AUTO_INCREMENT NOT NULL, primary_color VARCHAR(7) NOT NULL, preview TINYINT(1) NOT NULL, primary_hover DOUBLE PRECISION NOT NULL, primary_shadow DOUBLE PRECISION NOT NULL, secondary_color VARCHAR(7) NOT NULL, secondary_hover DOUBLE PRECISION NOT NULL, secondary_shadow DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql("INSERT INTO `style` (`id`, `primary_color`, `preview`, `primary_hover`, `primary_shadow`, `secondary_color`, `secondary_hover`, `secondary_shadow`) VALUES (1, '#46aed8', 0, -0.08, -0.15, '#293132', 0, -0.10)");
        $this->addSql("INSERT INTO `style` (`id`, `primary_color`, `preview`, `primary_hover`, `primary_shadow`, `secondary_color`, `secondary_hover`, `secondary_shadow`) VALUES (2, '#46aed8', 1, -0.08, -0.15, '#293132', 0, -0.10)");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE style');
    }
}
