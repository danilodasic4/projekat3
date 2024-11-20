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
        return 'Add additional fields to the user table (birthday, gender, newsletter, profile_picture) and modify the birthday column type to DATE using raw SQL';
    }

    public function up(Schema $schema): void
    {
        // SQL upit za dodavanje novih kolona
        $this->addSql('ALTER TABLE users ADD birthday DATE NULL');
        $this->addSql('ALTER TABLE users ADD gender VARCHAR(10) NULL');
        $this->addSql('ALTER TABLE users ADD newsletter TINYINT(1) DEFAULT 0');
        $this->addSql('ALTER TABLE users ADD profile_picture VARCHAR(255) NULL');
    }

    public function down(Schema $schema): void
    {
        // SQL upit za brisanje kolona
        $this->addSql('ALTER TABLE users DROP COLUMN birthday');
        $this->addSql('ALTER TABLE users DROP COLUMN gender');
        $this->addSql('ALTER TABLE users DROP COLUMN newsletter');
        $this->addSql('ALTER TABLE users DROP COLUMN profile_picture');
    }
}
