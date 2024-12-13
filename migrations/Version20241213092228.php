<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213092228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user_id to appointment and fix foreign keys';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844C3C6F69F');
        
        $this->addSql('ALTER TABLE appointment ADD user_id INT NOT NULL, CHANGE car_id car_id INT NOT NULL, CHANGE appointment_type appointment_type ENUM("maintenance", "registration", "polishing", "painting") NOT NULL');
        
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844A76ED395 FOREIGN KEY (user_id) REFERENCES `users` (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844C3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id)');
        
        $this->addSql('CREATE INDEX IDX_FE38F844A76ED395 ON appointment (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844A76ED395');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844C3C6F69F');
        $this->addSql('DROP INDEX IDX_FE38F844A76ED395 ON appointment');
        
        $this->addSql('ALTER TABLE appointment DROP user_id, CHANGE car_id car_id INT NOT NULL, CHANGE appointment_type appointment_type VARCHAR(50) NOT NULL');
        
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844C3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
