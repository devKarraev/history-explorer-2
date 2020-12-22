<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200531113941 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reference ADD book_id INT DEFAULT NULL, ADD type VARCHAR(10) NOT NULL, DROP book');
        $this->addSql('ALTER TABLE reference ADD CONSTRAINT FK_AEA3491316A2B381 FOREIGN KEY (book_id) REFERENCES bible_books (id)');
        $this->addSql('CREATE INDEX IDX_AEA3491316A2B381 ON reference (book_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE reference DROP FOREIGN KEY FK_AEA3491316A2B381');
        $this->addSql('DROP INDEX IDX_AEA3491316A2B381 ON reference');
        $this->addSql('ALTER TABLE reference ADD book VARCHAR(50) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP book_id, DROP type');
    }
}
