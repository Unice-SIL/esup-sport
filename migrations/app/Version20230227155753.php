<?php

declare(strict_types=1);

namespace App\Migrations\Base;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230227155753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parametrage ADD signature_mail LONGTEXT NOT NULL');
        $this->addSql('UPDATE parametrage SET signature_mail = "<p style=\"font-style: 12px; font-style: oblique;\"><span>Ceci est une notification automatique d\'UCA, toute réponse sera ignorée.</span><span></br></span><span>Page d\'accueil d\'UCA Sport :<a href=\"{{ app.request != null ? app.request.schemeAndHttpHost }}{{ path(\'UcaWeb_Accueil\') }}\" class=\"c-pink underline\" style=\"font-family: Open Sans\">{{ app.request != null ? app.request.schemeAndHttpHost }}{{ path(\'UcaWeb_Accueil\') }}</a> </span><span></br></span></p>"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE parametrage DROP signature_mail');
    }
}
