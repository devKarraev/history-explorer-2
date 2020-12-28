<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201228124945 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('update event set created_at=now() where created_at=0;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA77E3C61F9;');
        $this->addSql('alter table event add constraint FK_3BAE0AA77E3C61F9 foreign key (owner_id) references user (id) on delete set null;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA7B3C02335;');
        $this->addSql('alter table event add constraint FK_3BAE0AA7B3C02335 foreign key (happened_before_id) references event (id) on delete set null;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA7CCAB407B;');
        $this->addSql('alter table event add constraint FK_3BAE0AA7CCAB407B foreign key (happened_after_id) references event (id) on delete set null;');
        $this->addSql('alter table person_reference drop foreign key FK_168AAAB9217BBB47;');
        $this->addSql('alter table person_reference add constraint FK_168AAAB9217BBB47 foreign key (person_id) references person (id) on delete cascade;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD217BBB47;');
        $this->addSql('alter table entity_change add constraint FK_849A40AD217BBB47 foreign key (person_id) references person (id) on delete cascade;');
        $this->addSql('alter table person drop foreign key FK_34DCD1767E3C61F9;');
        $this->addSql('alter table person add constraint FK_34DCD1767E3C61F9 foreign key (owner_id) references user (id) on delete set null;');
        $this->addSql('alter table person_reference drop foreign key FK_168AAAB91645DEA9;');
        $this->addSql('alter table person_reference add constraint FK_168AAAB91645DEA9 foreign key (reference_id) references reference (id) on delete cascade;');
        $this->addSql('alter table location_reference drop foreign key FK_E0769F651645DEA9;');
        $this->addSql('alter table location_reference add constraint FK_E0769F651645DEA9 foreign key (reference_id) references reference (id) on delete cascade;');
        $this->addSql('alter table folk_reference drop foreign key FK_D23F02BE1645DEA9;');
        $this->addSql('alter table folk_reference add constraint FK_D23F02BE1645DEA9 foreign key (reference_id) references reference (id) on delete cascade;');
        $this->addSql('alter table event_reference drop foreign key FK_2836772E1645DEA9;');
        $this->addSql('alter table event_reference add constraint FK_2836772E1645DEA9 foreign key (reference_id) references reference (id) on delete cascade;');
        $this->addSql('alter table event_reference drop foreign key FK_2836772E71F7E88B;');
        $this->addSql('alter table event_reference add constraint FK_2836772E71F7E88B foreign key (event_id) references event (id) on delete cascade;');
        $this->addSql('alter table folk_reference drop foreign key FK_D23F02BEA54DED42;');
        $this->addSql('alter table folk_reference add constraint FK_D23F02BEA54DED42 foreign key (folk_id) references folk (id) on delete cascade;');
        $this->addSql('alter table folk drop foreign key FK_6FAA67A5E6268444;');
        $this->addSql('alter table folk add constraint FK_6FAA67A5E6268444 foreign key (father_folk_id) references folk (id) on delete set null;');
        $this->addSql('alter table person drop foreign key FK_34DCD176A54DED42;');
        $this->addSql('alter table person add constraint FK_34DCD176A54DED42 foreign key (folk_id) references folk (id) on delete set null;');
        $this->addSql('alter table location_reference drop foreign key FK_E0769F6564D218E;');
        $this->addSql('alter table location_reference add constraint FK_E0769F6564D218E foreign key (location_id) references location (id) on delete cascade;');
        $this->addSql('alter table reference drop foreign key FK_AEA3491316A2B381;');
        $this->addSql('alter table reference add constraint FK_AEA3491316A2B381 foreign key (book_id) references bible_books (id) on delete cascade;');
        $this->addSql('alter table chapter_verses drop foreign key FK_203251B316A2B381;');
        $this->addSql('alter table chapter_verses add constraint FK_203251B316A2B381 foreign key (book_id) references bible_books (id) on delete cascade;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA764D218E;');
        $this->addSql('alter table event add constraint FK_3BAE0AA764D218E foreign key (location_id) references location (id) on delete set null;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD64D218E;');
        $this->addSql('alter table entity_change add constraint FK_849A40AD64D218E foreign key (location_id) references location (id) on delete cascade;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD71F7E88B;');
        $this->addSql('alter table entity_change add constraint FK_849A40AD71F7E88B foreign key (event_id) references event (id) on delete cascade;');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

        $this->addSql('alter table event drop foreign key FK_3BAE0AA77E3C61F9;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA7B3C02335;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA7CCAB407B;');
        $this->addSql('alter table person_reference drop foreign key FK_168AAAB9217BBB47;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD217BBB47;');
        $this->addSql('alter table person drop foreign key FK_34DCD1767E3C61F9;');
        $this->addSql('alter table person_reference drop foreign key FK_168AAAB91645DEA9;');
        $this->addSql('alter table location_reference drop foreign key FK_E0769F651645DEA9;');
        $this->addSql('alter table folk_reference drop foreign key FK_D23F02BE1645DEA9;');
        $this->addSql('alter table event_reference drop foreign key FK_2836772E1645DEA9;');
        $this->addSql('alter table event_reference drop foreign key FK_2836772E71F7E88B;');
        $this->addSql('alter table folk_reference drop foreign key FK_D23F02BEA54DED42;');
        $this->addSql('alter table folk drop foreign key FK_6FAA67A5E6268444;');
        $this->addSql('alter table person drop foreign key FK_34DCD176A54DED42;');
        $this->addSql('alter table location_reference drop foreign key FK_E0769F6564D218E;');
        $this->addSql('alter table reference drop foreign key FK_AEA3491316A2B381;');
        $this->addSql('alter table chapter_verses drop foreign key FK_203251B316A2B381;');
        $this->addSql('alter table event drop foreign key FK_3BAE0AA764D218E;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD64D218E;');
        $this->addSql('alter table entity_change drop foreign key FK_849A40AD71F7E88B;');
    }
}
