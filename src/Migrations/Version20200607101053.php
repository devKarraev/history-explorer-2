<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200607101053 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE event_person (event_id INT NOT NULL, person_id INT NOT NULL, INDEX IDX_645A62471F7E88B (event_id), INDEX IDX_645A624217BBB47 (person_id), PRIMARY KEY(event_id, person_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE event_person ADD CONSTRAINT FK_645A62471F7E88B FOREIGN KEY (event_id) REFERENCES event (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event_person ADD CONSTRAINT FK_645A624217BBB47 FOREIGN KEY (person_id) REFERENCES person (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE event ADD happened_before_id INT DEFAULT NULL, ADD happened_after_id INT DEFAULT NULL, ADD location_id INT DEFAULT NULL, ADD time INT DEFAULT NULL');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7B3C02335 FOREIGN KEY (happened_before_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7CCAB407B FOREIGN KEY (happened_after_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA764D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7B3C02335 ON event (happened_before_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA7CCAB407B ON event (happened_after_id)');
        $this->addSql('CREATE INDEX IDX_3BAE0AA764D218E ON event (location_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE event_person');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7B3C02335');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7CCAB407B');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA764D218E');
        $this->addSql('DROP INDEX IDX_3BAE0AA7B3C02335 ON event');
        $this->addSql('DROP INDEX IDX_3BAE0AA7CCAB407B ON event');
        $this->addSql('DROP INDEX IDX_3BAE0AA764D218E ON event');
        $this->addSql('ALTER TABLE event DROP happened_before_id, DROP happened_after_id, DROP location_id, DROP time');
    }
}
