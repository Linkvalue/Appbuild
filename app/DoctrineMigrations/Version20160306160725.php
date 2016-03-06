<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160306160725 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE appbuild_application_user (application_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_85DB94FA3E030ACD (application_id), INDEX IDX_85DB94FAA76ED395 (user_id), PRIMARY KEY(application_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appbuild_application_user ADD CONSTRAINT FK_85DB94FA3E030ACD FOREIGN KEY (application_id) REFERENCES appbuild_application (id)');
        $this->addSql('ALTER TABLE appbuild_application_user ADD CONSTRAINT FK_85DB94FAA76ED395 FOREIGN KEY (user_id) REFERENCES appbuild_user (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE appbuild_application_user');
    }
}
