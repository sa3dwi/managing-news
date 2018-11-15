<?php

namespace NewsBundle\Entity\Repository\Article;

use Doctrine\ORM\EntityRepository;
use NewsBundle\Entity\Article;
use NewsBundle\Entity\ArticleImage;
use NewsBundle\Service\NewsService\NewsService;

class Repository extends EntityRepository
{

    private static $dataGrid;
    private $front;

    public function getDataGrid()
    {
        if (!self::$dataGrid) {
            self::$dataGrid = new DataGrid($this->getEntityManager());
        }
        return self::$dataGrid;
    }

    /**
     * Get front
     *
     * @return Front
     */
    public function getFront()
    {
        if (!$this->front) {
            $this->front = new Front($this->getEntityManager());
        }
        return $this->front;
    }

    /**
     * Get images
     *
     * @param $uuid
     * @return mixed
     */
    public function getImages($uuid)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT i
        FROM NewsBundle:ArticleImage i
        WHERE i.uuid = :uuid ";

        $query = $em->createQuery($dql);
        $query->setParameter('uuid', $uuid);
        return $query->getResult();
    }

    /**
     * Move temporary images to news articles
     *
     * @param Article $article
     * @param $uuid
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function moveTempImagesToNewsItem(Article $article, $uuid)
    {
        $em = $this->getEntityManager();
        $tempImages = $this->getImages($uuid);
        /** @var ArticleImage $tempImage */
        foreach ($tempImages as $tempImage) {
            $tempImage->setArticle($article);
            $em->flush();
        }
    }

    /**
     * Delete expired temporary files
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteExpiredTempFiles()
    {
        $em = $this->getEntityManager();
        $dql = "SELECT v
        FROM NewsBundle:ArticleImage v
        WHERE v.article IS NULL 
        AND DATE_DIFF(CURRENT_DATE(), v.createdAt) >= 3 ";
        $query = $em->createQuery($dql);
        $result = $query->getResult();

        foreach ($result as $row) {
            $em->remove($row);
            $em->flush();
        }
    }

    /**
     * @param NewsService $service
     * @param $ids
     * @return array
     */
    public function getDeleteRestrectionsByIds(NewsService $service, $ids)
    {
        $restrictions = [];
        $delets = [];
        if (is_array($ids)) {
            foreach ($ids as $id) {
                $entity = $this->find($id);
                if ($entity) {
                    $count = 0;
                    foreach ($service->getRelatedServices() as $relatedService) {
                        $count += $relatedService->getArticleRestrictions($entity);
                    }
                    if ($count) {
                        $restrictions[] = [
                            'entity' => $entity,
                            'serviceName' => $relatedService->getName(),
                            'count' => $count
                        ];
                    } else {
                        $delets[] = $entity;
                    }
                }
            }
        }
        return [
            'restrictions' => $restrictions,
            'delets' => $delets,
        ];
    }

    /**
     * @param $paginator
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getNewsList($paginator, $page, $limit, $lastUpdatedDate)
    {
        $em = $this->getEntityManager();
        $dql = "SELECT a
        FROM NewsBundle:Article a ";
        $conditions = ' WHERE a.active = 1 ';

        if (null != $lastUpdatedDate) {
            $conditions .= " AND a.updatedAt > :lastUpdatedDate ";
            $parameters['lastUpdatedDate'] = date('Y-m-d H:i:s', $lastUpdatedDate);
        }

        $dql .= $conditions . " ORDER BY a.updatedAt DESC";
        $query = $em->createQuery($dql);

        if (!empty($parameters) && sizeof($parameters)) {
            $query->setParameters($parameters);
        }

        return $paginator->paginate(
            $query,
            $page,
            $limit
        );
    }
}
