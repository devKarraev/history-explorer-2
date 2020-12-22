<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200701072015 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE person2');
        $this->addSql('ALTER TABLE folk ADD progenitor_id INT DEFAULT NULL, ADD died INT DEFAULT NULL');
        $this->addSql('ALTER TABLE folk ADD CONSTRAINT FK_6FAA67A593BAFCB0 FOREIGN KEY (progenitor_id) REFERENCES person (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_6FAA67A593BAFCB0 ON folk (progenitor_id)');
        $this->addSql('ALTER TABLE user DROP cas');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE person2 (id INT AUTO_INCREMENT NOT NULL, father_id INT DEFAULT NULL, mother_id INT DEFAULT NULL, folk_id INT DEFAULT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, gender VARCHAR(1) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, alternate_names VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, is_abstract TINYINT(1) NOT NULL, leaf_start INT DEFAULT NULL, leaf_out INT DEFAULT NULL, leaf_level INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, owner_id INT DEFAULT NULL, lived_at_time_of_person_id INT DEFAULT NULL, born INT DEFAULT NULL, died INT DEFAULT NULL, born_estimated INT DEFAULT NULL, died_estimated INT DEFAULT NULL, approved TINYINT(1) NOT NULL, born_calculated INT DEFAULT NULL, died_calculated INT DEFAULT NULL, description LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_34DCD1767E3C61F9 (owner_id), INDEX IDX_34DCD176B78A354D (mother_id), INDEX IDX_34DCD176C08DED9F (lived_at_time_of_person_id), INDEX IDX_34DCD176A54DED42 (folk_id), INDEX IDX_34DCD1762055B9A2 (father_id), UNIQUE INDEX id_father_id (id, father_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE folk DROP FOREIGN KEY FK_6FAA67A593BAFCB0');
        $this->addSql('DROP INDEX UNIQ_6FAA67A593BAFCB0 ON folk');
        $this->addSql('ALTER TABLE folk DROP progenitor_id, DROP died');
        $this->addSql('ALTER TABLE user ADD cas LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
