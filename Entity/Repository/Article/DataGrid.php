<?php

namespace NewsBundle\Entity\Repository\Article;

use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use AdminBundle\Classes\DataGrid as AdminDataGrid;

class DataGrid extends AdminDataGrid
{

    /**
     * @param $paginator
     * @param $page
     * @param $locale
     * @return mixed
     */
    public function getGrid($paginator, $page, $locale)
    {
        $em = $this->getEntityManager();
        $parameters = [];

        $dql = "SELECT a as data
        FROM NewsBundle:Article a
        INNER JOIN LocaleBundle:Language l with a.language = l
        WHERE 1=1 ";

        $elementValue = $this->getFormDataElement('id');
        if($elementValue) {
            $dql .= "AND a.id IN(:ids) ";
            $parameters['ids'] = $this->commaDelimitedToArray($elementValue);
        }

        $elementValue = $this->getFormDataElement('language');
        if($elementValue) {
            $dql .= "AND a.language IN(:languages) ";
            $parameters['languages'] = $elementValue;
        }

        $elementValue = $this->getFormDataElement('dateFrom');
        if($elementValue) {
            $dateTime = $this->parseDateTime($elementValue);
            if($dateTime) {
                $dql .= "AND a.createdAt >= :dateFrom ";
                $parameters['dateFrom'] = $dateTime;
            }
        }

        $elementValue = $this->getFormDataElement('dateTo');
        if($elementValue) {
            $dateTime = $this->parseDateTime($elementValue);
            if($dateTime) {
                $dql .= "AND a.createdAt <= :dateTo ";
                $parameters['dateTo'] = $dateTime;
            }
        }

        $dql .= " GROUP BY a.id ORDER BY a.id ";
        $query = $em->createQuery($dql);
        if(sizeof($parameters)) {
            $query->setParameters($parameters);
        }

        $pagination = $paginator->paginate(
            $query,
            $page,
            50,
            ['wrap-queries' => true]
        );
        return $pagination;
    }

    /**
     * Form for filtering
     *
     * @param $formFactory
     * @param $formActionUrl
     * @param null $data
     * @param array $options
     * @return mixed
     */
    public function getFilterForm($formFactory, $formActionUrl, $data = null, $options = [])
    {
        $form = $formFactory->createBuilder(FormType::class, $data, $options);
        $form
            ->setMethod('POST')
            ->setAction($formActionUrl)
            ->add('id', TextType::class, [
                'label' => 'admin.titles.id'
            ])
            ->add('dateFrom', TextType::class, [
                'label' => 'admin.titles.date_from',
                'attr' => ['class' => 'date'],
            ])
            ->add('dateTo', TextType::class, [
                'label' => 'admin.titles.date_to',
                'attr' => ['class' => 'date'],
            ])
            ->add('language', EntityType::class, [
                'label' => 'admin.titles.lang',
                'class' => 'LocaleBundle:Language',
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('l')
                        ->orderBy('l.name');
                },
                'multiple' => true,
            ])
            ;
        return $form->getForm();
    }
}