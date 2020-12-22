<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701072913 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE folk DROP FOREIGN KEY FK_6FAA67A593BAFCB0');
        $this->addSql('DROP INDEX UNIQ_6FAA67A593BAFCB0 ON folk');
        $this->addSql('ALTER TABLE folk DROP progenitor_id');
        $this->addSql('ALTER TABLE person ADD progenitor_of_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD17615EBF2BB FOREIGN KEY (progenitor_of_id) REFERENCES folk (id)');
        $this->addSql('CREATE INDEX IDX_34DCD17615EBF2BB ON person (progenitor_of_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE folk ADD progenitor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE folk ADD CONSTRAINT FK_6FAA67A593BAFCB0 FOREIGN KEY (progenitor_id) REFERENCES person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FAA67A593BAFCB0 ON folk (progenitor_id)');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD17615EBF2BB');
        $this->addSql('DROP INDEX IDX_34DCD17615EBF2BB ON person');
        $this->addSql('ALTER TABLE person DROP progenitor_of_id');
    }
}
