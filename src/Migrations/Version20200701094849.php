<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701094849 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE folk ADD father_folk_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE folk ADD CONSTRAINT FK_6FAA67A5E6268444 FOREIGN KEY (father_folk_id) REFERENCES folk (id)');
        $this->addSql('CREATE INDEX IDX_6FAA67A5E6268444 ON folk (father_folk_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE folk DROP FOREIGN KEY FK_6FAA67A5E6268444');
        $this->addSql('DROP INDEX IDX_6FAA67A5E6268444 ON folk');
        $this->addSql('ALTER TABLE folk DROP father_folk_id');
    }
}
