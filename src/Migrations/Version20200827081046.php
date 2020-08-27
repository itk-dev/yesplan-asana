<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200827081046 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE asana_event (id VARCHAR(255) NOT NULL, created_in_asana TINYINT(1) DEFAULT NULL, created_date DATETIME DEFAULT NULL, updated_date DATETIME DEFAULT NULL, created_in_new_events TINYINT(1) DEFAULT NULL, created_in_new_events_online TINYINT(1) DEFAULT NULL, created_in_few_tickets TINYINT(1) DEFAULT NULL, created_in_last_minute TINYINT(1) DEFAULT NULL, created_in_new_events_external TINYINT(1) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE yesplan_event (id VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', title VARCHAR(255) DEFAULT NULL, event_date DATETIME DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, genre VARCHAR(255) DEFAULT NULL, marketing_budget VARCHAR(255) DEFAULT NULL, publication_date DATETIME DEFAULT NULL, presale_date DATETIME DEFAULT NULL, in_sale_date DATETIME DEFAULT NULL, tickets_available INT DEFAULT NULL, tickets_reserved INT DEFAULT NULL, ticket_capacity INT DEFAULT NULL, tickets_blocked INT DEFAULT NULL, tickets_allocated INT DEFAULT NULL, production_online TINYINT(1) DEFAULT NULL, event_online TINYINT(1) DEFAULT NULL, capacity_percent NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(255) DEFAULT NULL, status_id VARCHAR(255) DEFAULT NULL, profile VARCHAR(255) DEFAULT NULL, profile_id VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE asana_event');
        $this->addSql('DROP TABLE yesplan_event');
    }
}
