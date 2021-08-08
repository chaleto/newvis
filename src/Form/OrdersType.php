<?php

namespace App\Form;

use app\Entity\User;
use App\Entity\Orders;
use App\Entity\Offer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class OrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('usermail')
            ->add('userid')
            ->add('packid')
            ->add('offername')
            ->add('createdAt')
            ->add('price')
            ->add('Buy', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Orders::class,
        ]);
    }
}
