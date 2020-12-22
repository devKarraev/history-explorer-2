<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200528112736 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person ADD lived_at_time_of_person_id INT DEFAULT NULL, ADD born DATE DEFAULT NULL, ADD died DATE DEFAULT NULL, ADD born_estimated DATE DEFAULT NULL, ADD died_estimated DATE DEFAULT NULL, DROP born_year, DROP died_year');
        $this->addSql('ALTER TABLE person ADD CONSTRAINT FK_34DCD176C08DED9F FOREIGN KEY (lived_at_time_of_person_id) REFERENCES person (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_34DCD176C08DED9F ON person (lived_at_time_of_person_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE person DROP FOREIGN KEY FK_34DCD176C08DED9F');
        $this->addSql('DROP INDEX IDX_34DCD176C08DED9F ON person');
        $this->addSql('ALTER TABLE person ADD died_year INT DEFAULT NULL, DROP born, DROP died, DROP born_estimated, DROP died_estimated, CHANGE lived_at_time_of_person_id born_year INT DEFAULT NULL');
    }
}
