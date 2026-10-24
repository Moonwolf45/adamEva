<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260922152706 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE cart (
              id BIGINT AUTO_INCREMENT NOT NULL,
              uuid VARCHAR(45) NOT NULL,
              user_id BIGINT DEFAULT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              synced_at DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_BA388B7D17F50A6 (uuid),
              INDEX IDX_BA388B743625D9F (updated_at),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE cart_items (
              id BIGINT AUTO_INCREMENT NOT NULL,
              product_id BIGINT DEFAULT NULL,
              quantity INT DEFAULT 1 NOT NULL,
              price BIGINT DEFAULT 0 NOT NULL,
              cart_id BIGINT NOT NULL,
              INDEX IDX_BEF484451AD5CDBF (cart_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE customers (
              id BIGINT AUTO_INCREMENT NOT NULL,
              first_name VARCHAR(255) NOT NULL,
              last_name VARCHAR(255) NOT NULL,
              second_name VARCHAR(255) NOT NULL,
              email VARCHAR(180) NOT NULL,
              password VARCHAR(255) NOT NULL,
              roles JSON NOT NULL,
              UNIQUE INDEX UNIQ_62534E21E7927C74 (email),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE deliveries (
              id BIGINT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              status TINYINT DEFAULT 1 NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_customer (
              id BIGINT AUTO_INCREMENT NOT NULL,
              order_id BIGINT NOT NULL,
              customer_id BIGINT DEFAULT NULL,
              customer_name VARCHAR(255) NOT NULL,
              customer_email VARCHAR(189) NOT NULL,
              customer_phone VARCHAR(32) NOT NULL,
              address LONGTEXT DEFAULT NULL,
              customer_comment VARCHAR(500) DEFAULT NULL,
              INDEX IDX_60C16CB88D9F6D38 (order_id),
              INDEX IDX_60C16CB89395C3F3 (customer_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE order_items (
              id BIGINT AUTO_INCREMENT NOT NULL,
              order_id BIGINT NOT NULL,
              product_id BIGINT DEFAULT NULL,
              product_title VARCHAR(255) NOT NULL,
              product_article VARCHAR(255) DEFAULT NULL,
              quantity INT NOT NULL,
              price BIGINT NOT NULL,
              total BIGINT NOT NULL,
              promotion JSON DEFAULT NULL,
              INDEX IDX_62809DB08D9F6D38 (order_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE orders (
              id BIGINT AUTO_INCREMENT NOT NULL,
              number VARCHAR(32) NOT NULL,
              cart_uuid VARCHAR(45) DEFAULT NULL,
              delivery_id BIGINT DEFAULT NULL,
              delivery_name VARCHAR(255) NOT NULL,
              delivery_price BIGINT NOT NULL,
              delivery_total BIGINT NOT NULL,
              payment_id BIGINT DEFAULT NULL,
              payment_name VARCHAR(255) NOT NULL,
              payment_status VARCHAR(32) DEFAULT 'pending' NOT NULL,
              payment_ref VARCHAR(255) DEFAULT NULL,
              paid_at DATETIME DEFAULT NULL,
              items_total BIGINT NOT NULL,
              discount_total BIGINT NOT NULL,
              total BIGINT NOT NULL,
              status VARCHAR(32) DEFAULT 'new' NOT NULL,
              admin_comment VARCHAR(1000) DEFAULT NULL,
              shipped_at DATETIME DEFAULT NULL,
              completed_at DATETIME DEFAULT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              UNIQUE INDEX UNIQ_E52FFDEE96901F54 (number),
              INDEX IDX_E52FFDEE3E9A509F (cart_uuid),
              INDEX IDX_E52FFDEE43625D9F (updated_at),
              INDEX IDX_E52FFDEE12136921 (delivery_id),
              INDEX IDX_E52FFDEE4C3A3BB (payment_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE payments (
              id BIGINT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              status TINYINT DEFAULT 1 NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotions (
              id BIGINT AUTO_INCREMENT NOT NULL,
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              type VARCHAR(16) NOT NULL,
              value INT DEFAULT 0 NOT NULL,
              starts_at DATETIME DEFAULT NULL,
              ends_at DATETIME DEFAULT NULL,
              status TINYINT DEFAULT 1 NOT NULL,
              created_at DATETIME NOT NULL,
              updated_at DATETIME NOT NULL,
              UNIQUE INDEX UNIQ_EA1B3034989D9B62 (slug),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotion_category (
              promotion_id BIGINT NOT NULL,
              category_id BIGINT NOT NULL,
              INDEX IDX_C018BD85139DF194 (promotion_id),
              INDEX IDX_C018BD8512469DE2 (category_id),
              PRIMARY KEY (promotion_id, category_id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE promotion_product (
              promotion_id BIGINT NOT NULL,
              product_id BIGINT NOT NULL,
              INDEX IDX_8B37F297139DF194 (promotion_id),
              INDEX IDX_8B37F2974584665A (product_id),
              PRIMARY KEY (promotion_id, product_id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reset_password_request (
              id BIGINT AUTO_INCREMENT NOT NULL,
              user_type VARCHAR(255) NOT NULL,
              user_id VARCHAR(255) NOT NULL,
              selector VARCHAR(20) NOT NULL,
              hashed_token VARCHAR(100) NOT NULL,
              requested_at DATETIME NOT NULL,
              expires_at DATETIME NOT NULL,
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cart_items
            ADD
              CONSTRAINT FK_BEF484451AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              order_customer
            ADD
              CONSTRAINT FK_60C16CB88D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              order_customer
            ADD
              CONSTRAINT FK_60C16CB89395C3F3 FOREIGN KEY (customer_id) REFERENCES customers (id) ON DELETE
            SET
              NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              order_items
            ADD
              CONSTRAINT FK_62809DB08D9F6D38 FOREIGN KEY (order_id) REFERENCES orders (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              orders
            ADD
              CONSTRAINT FK_E52FFDEE12136921 FOREIGN KEY (delivery_id) REFERENCES deliveries (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              orders
            ADD
              CONSTRAINT FK_E52FFDEE4C3A3BB FOREIGN KEY (payment_id) REFERENCES payments (id) ON DELETE RESTRICT
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              promotion_category
            ADD
              CONSTRAINT FK_C018BD85139DF194 FOREIGN KEY (promotion_id) REFERENCES promotions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              promotion_category
            ADD
              CONSTRAINT FK_C018BD8512469DE2 FOREIGN KEY (category_id) REFERENCES categories (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              promotion_product
            ADD
              CONSTRAINT FK_8B37F297139DF194 FOREIGN KEY (promotion_id) REFERENCES promotions (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              promotion_product
            ADD
              CONSTRAINT FK_8B37F2974584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
        SQL);
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_identifier_email TO UNIQ_1483A5E9E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cart_items DROP FOREIGN KEY FK_BEF484451AD5CDBF');
        $this->addSql('ALTER TABLE order_customer DROP FOREIGN KEY FK_60C16CB88D9F6D38');
        $this->addSql('ALTER TABLE order_customer DROP FOREIGN KEY FK_60C16CB89395C3F3');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE12136921');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE4C3A3BB');
        $this->addSql('ALTER TABLE promotion_category DROP FOREIGN KEY FK_C018BD85139DF194');
        $this->addSql('ALTER TABLE promotion_category DROP FOREIGN KEY FK_C018BD8512469DE2');
        $this->addSql('ALTER TABLE promotion_product DROP FOREIGN KEY FK_8B37F297139DF194');
        $this->addSql('ALTER TABLE promotion_product DROP FOREIGN KEY FK_8B37F2974584665A');
        $this->addSql('DROP TABLE cart');
        $this->addSql('DROP TABLE cart_items');
        $this->addSql('DROP TABLE customers');
        $this->addSql('DROP TABLE deliveries');
        $this->addSql('DROP TABLE order_customer');
        $this->addSql('DROP TABLE order_items');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE payments');
        $this->addSql('DROP TABLE promotions');
        $this->addSql('DROP TABLE promotion_category');
        $this->addSql('DROP TABLE promotion_product');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO UNIQ_IDENTIFIER_EMAIL');
    }
}
