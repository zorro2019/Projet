<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190313141657 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE abonnement_vehicule (abonnement_id INT NOT NULL, vehicule_id INT NOT NULL, INDEX IDX_6A2644CAF1D74413 (abonnement_id), INDEX IDX_6A2644CA4A4A3511 (vehicule_id), PRIMARY KEY(abonnement_id, vehicule_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abonnement_vehicule ADD CONSTRAINT FK_6A2644CAF1D74413 FOREIGN KEY (abonnement_id) REFERENCES abonnement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE abonnement_vehicule ADD CONSTRAINT FK_6A2644CA4A4A3511 FOREIGN KEY (vehicule_id) REFERENCES vehicule (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE abonnement_vehicule');
    }
}
