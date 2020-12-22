<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200522203625 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity_change ADD changed_by_id INT NOT NULL, ADD object_id INT NOT NULL, ADD modification_type VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD232D562B FOREIGN KEY (object_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_849A40AD828AD0A0 ON entity_change (changed_by_id)');
        $this->addSql('CREATE INDEX IDX_849A40AD232D562B ON entity_change (object_id)');
        $this->addSql('ALTER TABLE person ADD owner_id INT DEFAULT NULL, DROP edit_user');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1767E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_34DCD1767E3C61F9 ON person (owner_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD828AD0A0');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD232D562B');
        $this->addSql('DROP INDEX IDX_849A40AD828AD0A0 ON entity_change');
        $this->addSql('DROP INDEX IDX_849A40AD232D562B ON entity_change');
        $this->addSql('ALTER TABLE entity_change DROP changed_by_id, DROP object_id, DROP modification_type');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1767E3C61F9');
        $this->addSql('DROP INDEX IDX_34DCD1767E3C61F9 ON person');
        $this->addSql('ALTER TABLE person ADD edit_user VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, DROP owner_id');
    }
}
