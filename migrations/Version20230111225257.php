<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230111225257 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE feedback_message_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE feedback_message (id INT NOT NULL, email VARCHAR(255) DEFAULT NULL, feedback_type INT NOT NULL, message_text TEXT NOT NULL, submitted_on TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN feedback_message.submitted_on IS \'(DC2Type:datetime)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP SEQUENCE feedback_message_id_seq CASCADE');
        $this->addSql('DROP TABLE feedback_message');
    }
}
