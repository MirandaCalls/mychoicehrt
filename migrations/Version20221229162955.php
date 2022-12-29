<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221229162955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE EXTENSION pg_trgm;');
        $this->addSql('CREATE INDEX cities_alternate_names_idx ON geo_city USING GIN (to_tsvector(\'simple\', geo_city.alternate_names));');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX cities_alternate_names_idx');
        $this->addSql('DROP EXTENSION pg_trgm');
    }
}
