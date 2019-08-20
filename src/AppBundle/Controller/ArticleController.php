<?php

namespace AppBundle\Controller;

use AppBundle\Form\AddType;
use AppBundle\Form\DecreaseAmountType;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Items;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ArticleController extends FrontendController
{
    /**
     * @Route("/{_locale}/companies/{id}/article/add")
     *
     * @Template("/Items/add.html.twig")
     */
    public function addArticleAction(Request $request)
    {
        $company = DataObject::getById($request->get("_route_params")["id"]);

        $category = DataObject::getById($request->query->get('category'));

        $formData['translator'] = $this->get('translator');

        $form = $this->createForm(AddType::class, $formData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formValue = $form->getData();
            $newArticle = new DataObject\Articles();

            $newArticle->setKey($formValue['name']);
            $newArticle->setParentId(12474);

            $newQuan = new DataObject\Quantity();

            $newQuan->setKey($formValue['name'] . '.quantity');
            $newQuan->setParentId($company->getId());

            $existingArticle = DataObject\Articles::getByLocalizedfields("Name", $formValue['name'], null, ['limit' => 1,'unpublished' => true]);

            if ($existingArticle) {
                $list = new DataObject\Quantity\Listing();
                $list->setCondition('Article__id = ' . $existingArticle->getId() . ' AND o_parentId = ' . $company->getId());
                $list->load();

                $existingArticleQuantity = $list->getObjects()[0];
            }

            if ($existingArticleQuantity) {
                $newArticle = $existingArticle;
                $newQuan = $existingArticleQuantity;
                $quantity = $existingArticleQuantity->getQuantity();
                $formValue['quantity'] = $formValue['quantity'] + $quantity;

                $this->addFlash(
                    'notice',
                    'Article already exists, changes have been saved'
                );

                if (!$existingArticle->getPublished()){
                    $newArticle->setPublished(true);
                    $this->addFlash(
                        'notice',
                        'Article is published'
                    );
                }
            }


            $newArticle->setName($formValue['name']);
            $newArticle->setDescription($formValue['description']);
            $newArticle->setCategory([$category]);

            $newArticle->save();

            $newQuan->setQuantity($formValue['quantity']);
            $newQuan->setArticle($newArticle);

            $newQuan->save();

            return $this->redirectToRoute('show_company', ["id" => $company->getId()]);
        }

        return $this->render('/Items/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{_locale}/companies/{id}/article/decrease")
     *
     * @Template("/Items/add.html.twig")
     */
    public function decreaseArticleAction(Request $request)
    {
        $company = DataObject::getById($request->get("_route_params")["id"]);

        $formData['companyId'] = $company->getId();
        $formData['translator'] = $this->get('translator');

        $form = $this->createForm(DecreaseAmountType::class, $formData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $formValue = $form->getData();

//            $article = DataObject\Articles::getById($formValue['article']);

            $list = new DataObject\Quantity\Listing();
            $list->setCondition('Article__id = ' . $formValue['article'] . ' AND o_parentId = ' . $company->getId());
            $list->load();

            $existingArticleQuantity = $list->getObjects()[0];

            $quantity = $existingArticleQuantity->getQuantity();
            $formValue['quantity'] = $quantity - $formValue['quantity'];

            if ($formValue['quantity'] < 0) {
                $formValue['quantity'] = 0;
            }

            $existingArticleQuantity->setQuantity($formValue['quantity']);

            $existingArticleQuantity->save();

            $this->addFlash(
                'notice',
                'Article quantity decreased'
            );

            return $this->redirectToRoute('show_company', ["id" => $request->get("_route_params")["id"]]);
        }

        return $this->render('/Items/addForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{_locale}/companies/{id}/articles")
     *
     * @Template("/Items/articles.html.twig")
     */
    public function showArticlesAction(Request $request, $id)
    {
        if ($request->query->has('category')) {
            $articles = new DataObject\Articles\Listing();

            $articles->setCondition("Category like '%,".$request->query->get('category').",%'");

            $articles->load();
        }

        $list = new DataObject\Quantity\Listing();

        $products = [];

        foreach ($articles as $key => $article) {
            $list->setCondition('Article__id = ' . $article->getId() . ' AND o_parentId = ' . $id);
            $list->load();

            $existingArticleQuantity = $list->getObjects()[0];

            $article->quan = '';
            if ($existingArticleQuantity) {
                $article->quan = $existingArticleQuantity->getQuantity();
            }
            $products[$key] = $article;
        }

        foreach ($products as $key => $article) {
            if ($article->quan == "") {
                unset($products[$key]);
            }
        }

        $this->view->companyId = $id;
        $this->view->articles = $products;
        $this->view->categoryId = $request->query->get('category');
    }

    /**
     * @Route("/change_quantity", name="change")
     */
    public function changeQuantityAction(Request $request)
    {
        $notEmptyArticles = 0;

        $attributes = [];

        foreach ($request->request as $name => $quantity) {
            $attributes[$name] = $quantity;
        }

        $companyId = array_pop($attributes);
        $category = DataObject\Categories::getById(array_pop($attributes));

        $list = new DataObject\Quantity\Listing();

        foreach ($attributes as $name => $quantity) {
            $attribute = explode('-', $name);

            $product = DataObject\Articles::getById($attribute[0]);

            if ($product) {

                $list->setCondition('Article__id = ' . $product->getId() . ' AND o_parentId = ' . $companyId);
                $list->load();

                $existingArticleQuantity = $list->getObjects()[0];

                if ($existingArticleQuantity) {
                    $existingArticleQuantity->setQuantity($quantity);
                    $existingArticleQuantity->save();
                }

                if ($quantity != 0) {
                    $notEmptyArticles += 1;
                }
            }
        }

        $this->addFlash(
            'notice',
            'Changes have been saved'
        );

        if ($notEmptyArticles == 0) {
            $category->setPublished(false);
            $category->save();

            $this->addFlash(
                'notice',
                'Category is unpublished'
            );
        }

        return new RedirectResponse($request->server->get('HTTP_REFERER'));
    }
}