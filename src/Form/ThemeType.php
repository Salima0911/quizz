<?php

namespace App\Form;

use App\Entity\Score;
use App\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;

class ThemeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme')
            ->add('img', FileType::class, [
                'data_class' => null,
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([

                        'maxSize' => '1M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],

                    ]),
                ],
            ])
            ->add('idScore', EntityType::class, [
                'class' => Score::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Theme::class,
        ]);
    }
}
