<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160729164314 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE majoraotastore_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(500) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', enabled TINYINT(1) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE majoraotastore_application (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, support VARCHAR(255) NOT NULL, package_name VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX label_idx (`label`), UNIQUE INDEX slug_idx (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE majoraotastore_application_user (application_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_58FB6F1F3E030ACD (application_id), INDEX IDX_58FB6F1FA76ED395 (user_id), PRIMARY KEY(application_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE majoraotastore_build (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, version VARCHAR(255) NOT NULL, file_path VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_702E0EA83E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE majoraotastore_application_user ADD CONSTRAINT FK_58FB6F1F3E030ACD FOREIGN KEY (application_id) REFERENCES majoraotastore_application (id)');
        $this->addSql('ALTER TABLE majoraotastore_application_user ADD CONSTRAINT FK_58FB6F1FA76ED395 FOREIGN KEY (user_id) REFERENCES majoraotastore_user (id)');
        $this->addSql('ALTER TABLE majoraotastore_build ADD CONSTRAINT FK_702E0EA83E030ACD FOREIGN KEY (application_id) REFERENCES majoraotastore_application (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE majoraotastore_application_user DROP FOREIGN KEY FK_58FB6F1FA76ED395');
        $this->addSql('ALTER TABLE majoraotastore_application_user DROP FOREIGN KEY FK_58FB6F1F3E030ACD');
        $this->addSql('ALTER TABLE majoraotastore_build DROP FOREIGN KEY FK_702E0EA83E030ACD');
        $this->addSql('DROP TABLE majoraotastore_user');
        $this->addSql('DROP TABLE majoraotastore_application');
        $this->addSql('DROP TABLE majoraotastore_application_user');
        $this->addSql('DROP TABLE majoraotastore_build');
    }
}
