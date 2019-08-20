<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Pimcore\Model\DataObject;

class DecreaseAmountType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'decrease';
    }

    public function buildForm(FormBuilderInterface $builder, array $data)
    {
        $articles = [];
        $products = [];

        $categories = new DataObject\Categories\Listing();

        $categories->setCondition("Company like '%,".$data['data']['companyId'].",%'");

        $translator = $data['data']['translator'];

        $categories->load();

        foreach ($categories as $category) {
            $allArticles = new DataObject\Articles\Listing();

            $allArticles->setCondition("Category like '%,".$category->getId().",%'");

            $articles[] = $allArticles->load();
        }

        $list = new DataObject\Quantity\Listing();

        foreach ($articles as $categoryArticles){
            foreach ($categoryArticles as $categoryArticle) {
                $list->setCondition('Article__id = ' . $categoryArticle->getId() . ' AND o_parentId = ' . $data['data']['companyId']);
                $list->load();

                $existingArticleQuantity = $list->getObjects()[0];

                $products[$categoryArticle->getName() . ' - quantity = ' . $existingArticleQuantity->getQuantity()] = $categoryArticle->getId();
            }
        }

        $builder->add('article',ChoiceType::class, ['choices' => [$products], 'label' => $translator->trans('decrease_amount_type.article'),])
                ->add('quantity', NumberType::class, [
                    'label' => $translator->trans('decrease_amount_type.decrease_by'),
                    'constraints' => array(
                    new \Symfony\Component\Validator\Constraints\PositiveOrZero(['message' => 'Value cannot be bellow zero']),)
                ])
                ->add('save', SubmitType::class, ['label' => $translator->trans('decrease_amount_type.save'),]);
    }
}