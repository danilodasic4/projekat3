<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241117100420 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change birthday column type from DATETIME to DATE in users table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users CHANGE birthday birthday DATE DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE newsletter newsletter TINYINT(1) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users CHANGE birthday birthday DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE users CHANGE newsletter newsletter TINYINT(1) DEFAULT 0 NOT NULL');
    }
}
