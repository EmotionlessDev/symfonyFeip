<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250620125713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE booking (id SERIAL NOT NULL, house_id INT DEFAULT NULL, phone_number VARCHAR(255) NOT NULL, comment VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E00CEDDE6BB74515 ON booking (house_id)');
        $this->addSql('CREATE TABLE house (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, sleeping_capacity INT NOT NULL, bathrooms INT NOT NULL, location VARCHAR(255) NOT NULL, price INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE6BB74515 FOREIGN KEY (house_id) REFERENCES house (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE booking DROP CONSTRAINT FK_E00CEDDE6BB74515');
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE house');
    }
}
