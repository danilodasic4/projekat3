<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219225149 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add admins table';
    }

    public function up(Schema $schema): void
{
    $this->addSql('CREATE TABLE `admins` (
        id INT AUTO_INCREMENT NOT NULL, 
        email VARCHAR(180) NOT NULL, 
        password VARCHAR(255) NOT NULL, 
        roles JSON NOT NULL, 
        UNIQUE INDEX UNIQ_A2E0150FE7927C74 (email), 
        PRIMARY KEY(id)
    ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
}


    public function down(Schema $schema): void
{
    $this->addSql('DROP TABLE `admins`');
}
}
