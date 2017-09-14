<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170626205956 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX label_idx ON appbuild_application');
        $this->addSql('DROP INDEX slug_idx ON appbuild_application');
        $this->addSql('ALTER TABLE appbuild_application DROP slug');
        $this->addSql('CREATE UNIQUE INDEX label_support_idx ON appbuild_application (`label`, support)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX label_support_idx ON appbuild_application');
        $this->addSql('ALTER TABLE appbuild_application ADD slug VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('CREATE UNIQUE INDEX label_idx ON appbuild_application (`label`)');
        $this->addSql('CREATE UNIQUE INDEX slug_idx ON appbuild_application (slug)');
    }
}
