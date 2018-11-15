<?php

namespace NewsBundle\Entity\Repository\Article;

use AdminBundle\Classes\DataGrid as AdminDataGrid;

class Front extends AdminDataGrid
{
    protected $em;

    function __construct($em)
    {
        $this->em = $em;
    }

    /**
     * Get all news
     *
     * @param $paginator
     * @param $page
     * @param $locale
     * @return mixed
     */
    public function getAllNews($paginator, $page, $locale)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a
        FROM NewsBundle:Article a
        WHERE a.language = :language ";

        $dql .= " GROUP BY a.id ORDER BY a.id ";
        $query = $em->createQuery($dql);
        $query->setParameter('language', $locale);

        $pagination = $paginator->paginate(
            $query,
            $page,
            10
        );
        return $pagination;
    }

    /**
     * Get article details
     *
     * @param $id
     * @return mixed
     */
    public function getArticleDetails($id)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT a
        FROM NewsBundle:Article a
        WHERE a.id = :id ";

        $query = $em->createQuery($dql);
        $query->setParameter('id', $id);
        $result = $query->getResult();

        return $result;
    }


}
