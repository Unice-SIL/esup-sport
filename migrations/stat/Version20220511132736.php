<?php

declare(strict_types=1);

namespace App\Migrations\Stat;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220511132736 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création du schéma de base de données statistique';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE data_utilisateur (id INT AUTO_INCREMENT NOT NULL, codEtu VARCHAR(255) NOT NULL, codEtp VARCHAR(255) DEFAULT NULL, niveau VARCHAR(255) DEFAULT NULL, categorie VARCHAR(255) DEFAULT NULL, libCmp VARCHAR(255) DEFAULT NULL, codCmp VARCHAR(255) DEFAULT NULL, shnu VARCHAR(1) DEFAULT NULL, boursier VARCHAR(1) DEFAULT NULL, sexe VARCHAR(1) NOT NULL, est_membre_personnel TINYINT(1) NOT NULL, dateNaissance VARCHAR(255) NOT NULL, annee_universitaire INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kpi_generaux_etudiants (id INT AUTO_INCREMENT NOT NULL, annee_universitaire INT NOT NULL, nb_etudiants INT DEFAULT NULL, nb_inscrits INT DEFAULT NULL, nb_boursier INT DEFAULT NULL, nb_shnu INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE kpi_generaux_personnels (id INT AUTO_INCREMENT NOT NULL, annee_universitaire INT NOT NULL, nb_personnels INT DEFAULT NULL, nb_inscrits INT DEFAULT NULL, nb_cat_a INT DEFAULT NULL, nb_cat_b INT DEFAULT NULL, nb_cat_c INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nb_user_by_element (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, libelle VARCHAR(255) NOT NULL, nombre_user INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nb_user_by_genre_and_age (id INT AUTO_INCREMENT NOT NULL, age VARCHAR(255) DEFAULT NULL, genre VARCHAR(255) NOT NULL, nombre_user INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE nb_user_by_horaire_and_element (id INT AUTO_INCREMENT NOT NULL, type INT NOT NULL, horaire VARCHAR(255) DEFAULT NULL, libelle VARCHAR(255) NOT NULL, nombre_user INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE data_utilisateur');
        $this->addSql('DROP TABLE kpi_generaux_etudiants');
        $this->addSql('DROP TABLE kpi_generaux_personnels');
        $this->addSql('DROP TABLE nb_user_by_element');
        $this->addSql('DROP TABLE nb_user_by_genre_and_age');
        $this->addSql('DROP TABLE nb_user_by_horaire_and_element');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}