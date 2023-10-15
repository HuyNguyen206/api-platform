<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231015092444 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE api_token (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, expired_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', token VARCHAR(68) NOT NULL, scopes JSON DEFAULT NULL, INDEX IDX_7BA2F5EB7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dragon_treasure (id INT AUTO_INCREMENT NOT NULL, owner_id INT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, value INT NOT NULL, cool_factor INT NOT NULL, is_published TINYINT(1) NOT NULL, INDEX IDX_9E31BF5F7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE api_token ADD CONSTRAINT FK_7BA2F5EB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE dragon_treasure ADD CONSTRAINT FK_9E31BF5F7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE api_token DROP FOREIGN KEY FK_7BA2F5EB7E3C61F9');
        $this->addSql('ALTER TABLE dragon_treasure DROP FOREIGN KEY FK_9E31BF5F7E3C61F9');
        $this->addSql('DROP TABLE api_token');
        $this->addSql('DROP TABLE dragon_treasure');
        $this->addSql('DROP TABLE user');
    }
}
