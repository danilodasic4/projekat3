<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241115084638 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add additional fields to the user table (birthday, gender, newsletter, profile_picture)';
    }

    public function up(Schema $schema): void
    {
        // Add new fields to the users table
        $table = $schema->getTable('users');
        
        // Add new columns
        $table->addColumn('birthday', 'datetime', ['nullable' => true]);
        $table->addColumn('gender', 'string', ['length' => 10, 'nullable' => true]);
        $table->addColumn('newsletter', 'boolean', ['default' => false]);
        $table->addColumn('profile_picture', 'string', ['length' => 255, 'nullable' => true]);
    }

    public function down(Schema $schema): void
    {
        // Remove the added columns
        $table = $schema->getTable('users');
        
        $table->dropColumn('birthday');
        $table->dropColumn('gender');
        $table->dropColumn('newsletter');
        $table->dropColumn('profile_picture');
    }
}