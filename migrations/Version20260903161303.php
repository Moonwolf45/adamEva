<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903161303 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE product_images (
              id BIGINT AUTO_INCREMENT NOT NULL,
              image VARCHAR(255) NOT NULL,
              product_id BIGINT NOT NULL,
              INDEX IDX_8263FFCE4584665A (product_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (
              id BIGINT AUTO_INCREMENT NOT NULL,
              username VARCHAR(255) NOT NULL,
              email VARCHAR(180) NOT NULL,
              roles JSON NOT NULL,
              password VARCHAR(255) NOT NULL,
              UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              product_images
            ADD
              CONSTRAINT FK_8263FFCE4584665A FOREIGN KEY (product_id) REFERENCES products (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            CHANGE
              description description LONGTEXT NOT NULL,
            CHANGE
              price price BIGINT DEFAULT 0 NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_images DROP FOREIGN KEY FK_8263FFCE4584665A');
        $this->addSql('DROP TABLE product_images');
        $this->addSql('DROP TABLE users');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            CHANGE
              description description VARCHAR(255) NOT NULL,
            CHANGE
              price price BIGINT NOT NULL
        SQL);
    }
}
