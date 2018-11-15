<?php

namespace NewsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ArticleType extends AbstractType
{

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        foreach($options['languages'] as $language) {
            $lang[$language->getName()] = $language->getId();
        }

        $builder
            ->add('uuid', HiddenType::class, [
                'mapped' => false
            ])
            ->add('title', null, [
                'label' => 'admin.titles.title',
            ])
            ->add('author', null, [
                'label' => 'news.titles.author',
            ])
            ->add('position', null, [
                'label' => 'news.titles.position',
            ])
            ->add('photoFile', null, [
                'label' => 'admin.titles.photo',
//                'required' => true,
            ])
            ->add('description', null, [
                'label' => 'admin.titles.description',
            ])
            ->add('language', ChoiceType::class, [
                'label' =>  'admin.titles.lang',
                'choices' => $lang,
                'attr' => [
                    'class' => 'width-20 chosen-select'
                ],
                'constraints' => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add('active', null, [
                'label' =>  'admin.titles.active',
                'attr' => [
                    'class' => 'ace ace-switch ace-switch-5'
                ]
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'NewsBundle\Entity\Article',
            'languages' => []
        ]);
    }

}
