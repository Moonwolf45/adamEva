<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260816174619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categories (
              id INT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              meta_keywords VARCHAR(255) DEFAULT NULL,
              meta_description VARCHAR(255) DEFAULT NULL,
              parent_id INT DEFAULT 0 NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              status TINYINT DEFAULT 1 NOT NULL,
              UNIQUE INDEX UNIQ_3AF34668989D9B62 (slug),
              INDEX IDX_3AF34668727ACA70 (parent_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE products (
              id INT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              meta_keywords VARCHAR(255) DEFAULT NULL,
              meta_description VARCHAR(255) DEFAULT NULL,
              model VARCHAR(255) DEFAULT NULL,
              article VARCHAR(255) NOT NULL,
              description VARCHAR(255) NOT NULL,
              image VARCHAR(255) DEFAULT NULL,
              price INT NOT NULL,
              status TINYINT DEFAULT 1 NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              deleted_at DATETIME DEFAULT NULL,
              main_category_id INT NOT NULL,
              UNIQUE INDEX UNIQ_B3BA5A5A989D9B62 (slug),
              INDEX IDX_B3BA5A5AC6C55574 (main_category_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE category_product (
              product_id INT NOT NULL,
              category_id INT NOT NULL,
              INDEX IDX_149244D34584665A (product_id),
              INDEX IDX_149244D312469DE2 (category_id),
              PRIMARY KEY (product_id, category_id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            ADD
              CONSTRAINT FK_B3BA5A5AC6C55574 FOREIGN KEY (main_category_id) REFERENCES categories (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              category_product
            ADD
              CONSTRAINT FK_149244D34584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              category_product
            ADD
              CONSTRAINT FK_149244D312469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AC6C55574');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D34584665A');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D312469DE2');
        $this->addSql('DROP TABLE categories');
        $this->addSql('DROP TABLE products');
        $this->addSql('DROP TABLE category_product');
    }
}
