<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
    final class Version20200518150826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, father_id INT DEFAULT NULL, mother_id INT DEFAULT NULL, folk_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, born_year INT DEFAULT NULL, died_year INT DEFAULT NULL, gender VARCHAR(1) DEFAULT NULL, father_name VARCHAR(50) DEFAULT NULL, url VARCHAR(255) DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, is_abstract TINYINT(1) NOT NULL, leaf_start INT DEFAULT NULL, leaf_out INT DEFAULT NULL, leaf_level INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_34DCD1762055B9A2 (father_id), INDEX IDX_34DCD176B78A354D (mother_id), INDEX IDX_34DCD176A54DED42 (folk_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1762055B9A2 FOREIGN KEY (father_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B78A354D FOREIGN KEY (mother_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176A54DED42 FOREIGN KEY (folk_id) REFERENCES person (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1762055B9A2');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B78A354D');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176A54DED42');
        $this->addSql('DROP TABLE person');
    }
}
