<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200417073203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yesplan_event ADD title VARCHAR(255) DEFAULT NULL, ADD event_date DATETIME DEFAULT NULL, ADD location VARCHAR(255) DEFAULT NULL, ADD genre VARCHAR(255) DEFAULT NULL, ADD marketing_budget VARCHAR(255) DEFAULT NULL, ADD publication_date DATETIME DEFAULT NULL, ADD presale_date DATETIME DEFAULT NULL, ADD in_sale_date DATETIME DEFAULT NULL, ADD tickets_available INT DEFAULT NULL, ADD tickets_reserved INT DEFAULT NULL, ADD ticket_capacity INT DEFAULT NULL, ADD tickets_blocked INT DEFAULT NULL, ADD tickets_allocated INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf('mysql' !== $this->connection->getDatabasePlatform()->getName(), 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE yesplan_event DROP title, DROP event_date, DROP location, DROP genre, DROP marketing_budget, DROP publication_date, DROP presale_date, DROP in_sale_date, DROP tickets_available, DROP tickets_reserved, DROP ticket_capacity, DROP tickets_blocked, DROP tickets_allocated');
    }
}
