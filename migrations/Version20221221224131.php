<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221221224131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE duplicate_link_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE duplicate_link (id INT NOT NULL, clinic_a_id INT NOT NULL, clinic_b_id INT NOT NULL, similarity DOUBLE PRECISION NOT NULL, dismissed BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_247D75B16C5C80C1 ON duplicate_link (clinic_a_id)');
        $this->addSql('CREATE INDEX IDX_247D75B17EE92F2F ON duplicate_link (clinic_b_id)');
        $this->addSql('ALTER TABLE duplicate_link ADD CONSTRAINT FK_247D75B16C5C80C1 FOREIGN KEY (clinic_a_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE duplicate_link ADD CONSTRAINT FK_247D75B17EE92F2F FOREIGN KEY (clinic_b_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE duplicate_link_id_seq CASCADE');
        $this->addSql('ALTER TABLE duplicate_link DROP CONSTRAINT FK_247D75B16C5C80C1');
        $this->addSql('ALTER TABLE duplicate_link DROP CONSTRAINT FK_247D75B17EE92F2F');
        $this->addSql('DROP TABLE duplicate_link');
    }
}
