<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241113020844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Create the cars table
        $this->addSql('CREATE TABLE cars (
            id INT AUTO_INCREMENT NOT NULL,
            user_id INT NOT NULL,
            brand VARCHAR(255) NOT NULL,
            model VARCHAR(255) NOT NULL,
            year INT NOT NULL,
            engine_capacity INT NOT NULL,
            horse_power INT NOT NULL,
            color VARCHAR(50) DEFAULT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME DEFAULT NULL,
            deleted_at DATETIME DEFAULT NULL,
            registration_date DATE NOT NULL,
            INDEX IDX_773DE69D9D86650F (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraint
        $this->addSql('ALTER TABLE cars ADD CONSTRAINT FK_773DE69D9D86650F FOREIGN KEY (user_id) REFERENCES users(id)');
    }

    public function down(Schema $schema): void
    {
        // Remove the foreign key and drop the cars table
        $this->addSql('ALTER TABLE cars DROP FOREIGN KEY FK_773DE69D9D86650F');
        $this->addSql('DROP TABLE cars');
    }
}
