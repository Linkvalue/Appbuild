<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171019161456 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appbuild_application ADD display_image_file_path VARCHAR(510) DEFAULT NULL, ADD full_size_image_file_path VARCHAR(510) DEFAULT NULL');
        $this->addSql('ALTER TABLE appbuild_build CHANGE file_path file_path VARCHAR(510) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appbuild_application DROP display_image_file_path, DROP full_size_image_file_path');
        $this->addSql('ALTER TABLE appbuild_build CHANGE file_path file_path VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
