<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200622081444 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE folk (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, approved TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE folk_reference (id INT AUTO_INCREMENT NOT NULL, folk_id INT NOT NULL, reference_id INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_D23F02BEA54DED42 (folk_id), INDEX IDX_D23F02BE1645DEA9 (reference_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE folk_reference ADD CONSTRAINT FK_D23F02BEA54DED42 FOREIGN KEY (folk_id) REFERENCES folk (id)');
        $this->addSql('ALTER TABLE folk_reference ADD CONSTRAINT FK_D23F02BE1645DEA9 FOREIGN KEY (reference_id) REFERENCES reference (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE folk_reference DROP FOREIGN KEY FK_D23F02BEA54DED42');
        $this->addSql('DROP TABLE folk');
        $this->addSql('DROP TABLE folk_reference');
    }
}
