<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213090325 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create appointment table with car relationship';
    }

    public function up(Schema $schema): void
    {
        // Create appointment table
        $this->addSql('CREATE TABLE appointment (
            id INT AUTO_INCREMENT NOT NULL, 
            car_id INT NOT NULL, 
            scheduled_at DATETIME NOT NULL, 
            appointment_type VARCHAR(50) NOT NULL, 
            created_at DATETIME NOT NULL, 
            INDEX IDX_FE38F844C3C6F69F (car_id), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE appointment 
            ADD CONSTRAINT FK_FE38F844C3C6F69F 
            FOREIGN KEY (car_id) REFERENCES cars (id) 
            ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraint and table
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844C3C6F69F');
        $this->addSql('DROP TABLE appointment');
    }
}
