<?php

declare(strict_types=1);

namespace Application\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191209102517 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE acts_committee_member (id INT AUTO_INCREMENT NOT NULL, soc_id INT NOT NULL, pid INT NOT NULL, term_start DATE NOT NULL, term_end DATE NOT NULL, role VARCHAR(255) NOT NULL, comment VARCHAR(255) DEFAULT NULL, `order` INT NOT NULL, INDEX IDX_E9A6B9AB97857A9F (soc_id), INDEX IDX_E9A6B9AB5550C4ED (pid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE acts_committee_member ADD CONSTRAINT FK_E9A6B9AB97857A9F FOREIGN KEY (soc_id) REFERENCES acts_societies (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE acts_committee_member ADD CONSTRAINT FK_E9A6B9AB5550C4ED FOREIGN KEY (pid) REFERENCES acts_people_data (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE acts_committee_member');
    }
}
