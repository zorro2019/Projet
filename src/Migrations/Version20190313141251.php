<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190313141251 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abonnement DROP FOREIGN KEY FK_351268BB5258F8E6');
        $this->addSql('DROP INDEX IDX_351268BB5258F8E6 ON abonnement');
        $this->addSql('ALTER TABLE abonnement DROP id_vehicule_id, DROP create_at, DROP finish_at');
        $this->addSql('ALTER TABLE voyage CHANGE quantite_total quantite_total INT DEFAULT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE abonnement ADD id_vehicule_id INT DEFAULT NULL, ADD create_at DATETIME NOT NULL, ADD finish_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE abonnement ADD CONSTRAINT FK_351268BB5258F8E6 FOREIGN KEY (id_vehicule_id) REFERENCES vehicule (id)');
        $this->addSql('CREATE INDEX IDX_351268BB5258F8E6 ON abonnement (id_vehicule_id)');
        $this->addSql('ALTER TABLE voyage CHANGE quantite_total quantite_total INT DEFAULT 0');
    }
}
