<?php

namespace AppBundle\EventListener;

use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Articles;
use Pimcore\Model\DataObject\Quantity;

class ArticleListener {

    public function onPreUpdate (ElementEventInterface $e) {
        $object = $e->getObject();

        if ($object instanceof Quantity) {
            if ($object->getQuantity() <= 0) {
                $object->setQuantity(0);
                $object->setPublished(false);
            }
        }
    }

    public function onPreAdd (ElementEventInterface $e) {
        $object = $e->getObject();

        if ($object instanceof Articles) {
                $object->setPublished(true);
        }

        if ($object instanceof Quantity) {
            if ($object->getQuantity() > 0) {
                $object->setPublished(true);
            }
        }
    }
}