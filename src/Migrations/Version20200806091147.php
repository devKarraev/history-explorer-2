<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200806091147 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD232D562B');
        $this->addSql('DROP INDEX IDX_849A40AD232D562B ON entity_change');
        $this->addSql('ALTER TABLE entity_change ADD person_id INT DEFAULT NULL, ADD location_id INT DEFAULT NULL, ADD event_id INT DEFAULT NULL, DROP object_id');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_849A40AD217BBB47 ON entity_change (person_id)');
        $this->addSql('CREATE INDEX IDX_849A40AD64D218E ON entity_change (location_id)');
        $this->addSql('CREATE INDEX IDX_849A40AD71F7E88B ON entity_change (event_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD217BBB47');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD64D218E');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD71F7E88B');
        $this->addSql('DROP INDEX IDX_849A40AD217BBB47 ON entity_change');
        $this->addSql('DROP INDEX IDX_849A40AD64D218E ON entity_change');
        $this->addSql('DROP INDEX IDX_849A40AD71F7E88B ON entity_change');
        $this->addSql('ALTER TABLE entity_change ADD object_id INT NOT NULL, DROP person_id, DROP location_id, DROP event_id');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD232D562B FOREIGN KEY (object_id) REFERENCES person (id)');
        $this->addSql('CREATE INDEX IDX_849A40AD232D562B ON entity_change (object_id)');
    }
}
