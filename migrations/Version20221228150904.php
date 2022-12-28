<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221228150904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE geo_city_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE geo_postal_code_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE geo_city (id INT NOT NULL, geoname_id INT NOT NULL, name VARCHAR(200) NOT NULL, ascii_name VARCHAR(200) NOT NULL, alternate_names VARCHAR(10000) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, country_code VARCHAR(2) NOT NULL, location geography(GEOMETRY, 4326) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE geo_postal_code (id INT NOT NULL, country_code VARCHAR(2) NOT NULL, postal_code VARCHAR(20) NOT NULL, place_name VARCHAR(180) NOT NULL, state VARCHAR(100) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, location geography(GEOMETRY, 4326) NOT NULL, PRIMARY KEY(id))');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE geo_city_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE geo_postal_code_id_seq CASCADE');
        $this->addSql('DROP TABLE geo_city');
        $this->addSql('DROP TABLE geo_postal_code');
    }
}
