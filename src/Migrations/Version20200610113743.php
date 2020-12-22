<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200610113743 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX book_id_chapter ON chapter_verses');
        $this->addSql('ALTER TABLE person_reference DROP FOREIGN KEY FK_168AAAB91645DEA9');
        $this->addSql('ALTER TABLE person_reference DROP FOREIGN KEY FK_168AAAB9217BBB47');
        $this->addSql('ALTER TABLE person_reference ADD id INT AUTO_INCREMENT NOT NULL, ADD type VARCHAR(255) NOT NULL, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE person_reference ADD CONSTRAINT FK_168AAAB91645DEA9 FOREIGN KEY (reference_id) REFERENCES reference (id)');
        $this->addSql('ALTER TABLE person_reference ADD CONSTRAINT FK_168AAAB9217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE reference DROP type');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE UNIQUE INDEX book_id_chapter ON chapter_verses (book_id, chapter)');
        $this->addSql('ALTER TABLE person_reference MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE person_reference DROP FOREIGN KEY FK_168AAAB9217BBB47');
        $this->addSql('ALTER TABLE person_reference DROP FOREIGN KEY FK_168AAAB91645DEA9');
        $this->addSql('ALTER TABLE person_reference DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE person_reference DROP id, DROP type');
        $this->addSql('ALTER TABLE person_reference ADD CONSTRAINT FK_168AAAB9217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_reference ADD CONSTRAINT FK_168AAAB91645DEA9 FOREIGN KEY (reference_id) REFERENCES reference (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE person_reference ADD PRIMARY KEY (person_id, reference_id)');
        $this->addSql('ALTER TABLE reference ADD type VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
