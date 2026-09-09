<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260909214518 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables chantier et equipement, et de leur table de jointure.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chantier (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, statut VARCHAR(20) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE chantier_equipement (chantier_id INT NOT NULL, equipement_id INT NOT NULL, INDEX IDX_CAB66A20D0C0049D (chantier_id), INDEX IDX_CAB66A20806F0F5C (equipement_id), PRIMARY KEY (chantier_id, equipement_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE equipement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, quantite INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chantier_equipement ADD CONSTRAINT FK_CAB66A20D0C0049D FOREIGN KEY (chantier_id) REFERENCES chantier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE chantier_equipement ADD CONSTRAINT FK_CAB66A20806F0F5C FOREIGN KEY (equipement_id) REFERENCES equipement (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chantier_equipement DROP FOREIGN KEY FK_CAB66A20D0C0049D');
        $this->addSql('ALTER TABLE chantier_equipement DROP FOREIGN KEY FK_CAB66A20806F0F5C');
        $this->addSql('DROP TABLE chantier');
        $this->addSql('DROP TABLE chantier_equipement');
        $this->addSql('DROP TABLE equipement');
    }
}
