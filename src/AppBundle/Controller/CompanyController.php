<?php

namespace AppBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Items;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class CompanyController extends FrontendController
{

    /**
     * @Route("/{_locale}/companies")
     *
     * @Template("/Items/all.html.twig")
     */
    public function allAction(Request $request)
    {
        $items = new DataObject\Company\Listing();

        $this->view->items = $items;
    }

    /**
     * @Route("/{_locale}/companies/{id}", name="show_company")
     *
     * @Template("/Items/show.html.twig")
     */
    public function showAction(Request $request, $id)
    {
        if ($request->query->has('category')) {
            $articles = new DataObject\Articles\Listing();

            $articles->setCondition("Category like '%,".$request->query->get('category').",%'");

            $articles->load();
        }

        $company = DataObject::getById($id);

        $item = $company;

        $categories = new DataObject\Categories\Listing();

        $categories->setCondition("Company like '%,".$company->getId().",%'");

        $categories->load();

        $this->view->articles = $articles;
        $this->view->item = $item;
        $this->view->categories = $categories;
    }
}