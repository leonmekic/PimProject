<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'add';
    }

    public function buildForm(FormBuilderInterface $builder, array $data)
    {
        $translator = $data['data']['translator'];

        $builder->add('name', TextType::class, ['label' => $translator->trans('object.name'),])
                ->add('description', TextAreaType::class, ['label' => $translator->trans('object.description'),])
                ->add('quantity', NumberType::class, ['label' => $translator->trans('object.quantity'),])
                ->add('save', SubmitType::class, ['label' => $translator->trans('decrease_amount_type.save'),]);
    }
}