<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903113858 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AC6C55574');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D34584665A');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D312469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');

        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE ip_geolocation_cache (
              id BIGINT AUTO_INCREMENT NOT NULL,
              ip_prefix VARCHAR(45) NOT NULL,
              country VARCHAR(128) DEFAULT NULL,
              city VARCHAR(128) DEFAULT NULL,
              latitude DOUBLE PRECISION DEFAULT NULL,
              longitude DOUBLE PRECISION DEFAULT NULL,
              fetched_at DATETIME NOT NULL,
              UNIQUE INDEX UNIQ_228B05ED4D770CB2 (ip_prefix),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitors (
              id BIGINT AUTO_INCREMENT NOT NULL,
              ip VARCHAR(45) NOT NULL,
              country VARCHAR(128) DEFAULT NULL,
              city VARCHAR(128) DEFAULT NULL,
              latitude DOUBLE PRECISION DEFAULT NULL,
              longitude DOUBLE PRECISION DEFAULT NULL,
              created_at DATETIME NOT NULL,
              INDEX IDX_7B74A43FA5E3B32D (ip),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              categories
            CHANGE
              id id BIGINT AUTO_INCREMENT NOT NULL,
            CHANGE
              parent_id parent_id BIGINT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            CHANGE
              id id BIGINT AUTO_INCREMENT NOT NULL,
            CHANGE
              main_category_id main_category_id BIGINT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              category_product
            CHANGE
              product_id product_id BIGINT NOT NULL,
            CHANGE
              category_id category_id BIGINT NOT NULL
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE
              categories
            ADD
              CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE
            SET
              NULL
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
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            ADD
              CONSTRAINT FK_B3BA5A5AC6C55574 FOREIGN KEY (main_category_id) REFERENCES categories (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AC6C55574');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D34584665A');
        $this->addSql('ALTER TABLE category_product DROP FOREIGN KEY FK_149244D312469DE2');
        $this->addSql('ALTER TABLE categories DROP FOREIGN KEY FK_3AF34668727ACA70');

        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE ip_geolocation_cache');
        $this->addSql('DROP TABLE visitors');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              categories
            CHANGE
              id id INT AUTO_INCREMENT NOT NULL,
            CHANGE
              parent_id parent_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              category_product
            CHANGE
              product_id product_id INT NOT NULL,
            CHANGE
              category_id category_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            CHANGE
              id id INT AUTO_INCREMENT NOT NULL,
            CHANGE
              main_category_id main_category_id INT NOT NULL
        SQL);

        $this->addSql(<<<'SQL'
            ALTER TABLE
              categories
            ADD
              CONSTRAINT FK_3AF34668727ACA70 FOREIGN KEY (parent_id) REFERENCES categories (id) ON DELETE
            SET
              NULL
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
        $this->addSql(<<<'SQL'
            ALTER TABLE
              products
            ADD
              CONSTRAINT FK_B3BA5A5AC6C55574 FOREIGN KEY (main_category_id) REFERENCES categories (id)
        SQL);
    }
}
