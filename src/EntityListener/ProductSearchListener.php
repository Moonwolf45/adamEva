<?php

declare(strict_types=1);

namespace App\EntityListener;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\DBAL\Connection;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: 'postSoftDelete')]
class ProductSearchListener
{
    private Connection $manticoreConnection;

    /**
     * Флаг для массовых операций (импорт): построчная индексация отключается,
     * индекс пересобирается оптом командой app:manticore:reindex.
     */
    private bool $enabled = true;

    public function __construct(
        #[Target('manticoreConnection')]
        Connection $manticoreConnection
    )
    {
        $this->manticoreConnection = $manticoreConnection;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function enable(): void
    {
        $this->enabled = true;
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->indexProduct($entity);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->indexProduct($entity);
    }

    public function postRemove(LifecycleEventArgs $args): void
    {
        $this->removeFromIndex($args);
    }

    /**
     * Мягкое удаление (Gedmo SoftDeleteable) не срабатывает как postRemove/postUpdate,
     * поэтому удаляем товар из индекса по собственному событию Gedmo.
     */
    public function postSoftDelete(LifecycleEventArgs $args): void
    {
        $this->removeFromIndex($args);
    }

    private function removeFromIndex(LifecycleEventArgs $args): void
    {
        if (!$this->enabled) {
            return;
        }

        $entity = $args->getObject();

        if (!$entity instanceof Product) {
            return;
        }

        $this->manticoreConnection->executeStatement(
            'DELETE FROM products WHERE external_id = ?',
            [$entity->getId()]
        );
    }

    private function indexProduct(Product $product): void
    {
        // Manticore не принимает NULL в атрибутах — приводим к строке.
        $this->manticoreConnection->executeStatement(
            'REPLACE INTO products (
                id, 
                external_id, 
                title, 
                description, 
                model, 
                article, 
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?)',
            [
                $product->getId(),
                $product->getId(),
                (string) $product->getTitle(),
                (string) $product->getDescription(),
                (string) $product->getModel(),
                (string) $product->getArticle(),
                (int) $product->isStatus(),
            ]
        );
    }
}
