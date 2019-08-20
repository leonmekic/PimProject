<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Items;
use Symfony\Component\HttpFoundation\Request;

class ItemController extends FrontendController
{
    public function defaultAction(Request $request)
    {
    }

    public function itemAction(Request $request)
    {
        $item = DataObject::getById(12471);

        $this->view->image = $item->getImage()->getThumbnail('content');
        $this->view->description = $item->getDescription();
    }

    public function nailAction(Request $request)
    {
        $nail = DataObject::getById(12473);

        $this->view->image = $nail->getImage()->getThumbnail('content');
        $this->view->description = $nail->getDescription();

    }
}