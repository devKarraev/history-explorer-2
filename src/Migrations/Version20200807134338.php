<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200807134338 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD217BBB47 FOREIGN KEY (person_id) REFERENCES person (id)');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD64D218E FOREIGN KEY (location_id) REFERENCES location (id)');
        $this->addSql('ALTER TABLE entity_change ADD CONSTRAINT FK_849A40AD71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1762055B9A2 FOREIGN KEY (father_id) REFERENCES person (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176B78A354D FOREIGN KEY (mother_id) REFERENCES person (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176A54DED42 FOREIGN KEY (folk_id) REFERENCES folk (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1767E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176C08DED9F FOREIGN KEY (lived_at_time_of_person_id) REFERENCES person (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD1763A6941FE FOREIGN KEY (update_of_id) REFERENCES entity_change (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD828AD0A0');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD217BBB47');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD64D218E');
        $this->addSql('ALTER TABLE entity_change DROP FOREIGN KEY FK_849A40AD71F7E88B');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1762055B9A2');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176B78A354D');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176A54DED42');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1767E3C61F9');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176C08DED9F');
        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD1763A6941FE');
    }
}
