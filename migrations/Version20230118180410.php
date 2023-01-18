<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230118180410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE feedback_message ADD clinic_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE feedback_message ADD CONSTRAINT FK_27E410DCCC22AD4 FOREIGN KEY (clinic_id) REFERENCES clinic (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_27E410DCCC22AD4 ON feedback_message (clinic_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_27E410DCCC22AD4');
        $this->addSql('ALTER TABLE feedback_message DROP CONSTRAINT FK_27E410DCCC22AD4');
        $this->addSql('ALTER TABLE feedback_message DROP clinic_id');
    }
}
