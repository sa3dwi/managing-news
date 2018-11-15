<?php

namespace NewsBundle\Subscriber;

use AdminBundle\Classes\AdminEvent;
use AdminBundle\Entity\User;
use CoreBundle\Entity\ProductCluster;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\Event\PreUpdateEventArgs;
use CoreBundle\Entity\Product;
use NewsBundle\Entity\Article;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\EventDispatcher\GenericEvent;


class EntitySubscriber implements EventSubscriber, ContainerAwareInterface
{

    use ContainerAwareTrait;

    private static $preservedId;

    public function getSubscribedEvents()
    {
        return [
            'preRemove',
            'postRemove',
        ];
    }

    public function getContainer() 
    {
        return $this->container;
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        self::$preservedId = $entity->getId();
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Article) {
            $this->articlePostRemove($entity);
        }
    }


    private function articlePostRemove(Article $article)
    {
        $id = self::$preservedId;
        $eventDispatcher = $this->container->get('event_dispatcher');
        $event = new GenericEvent();
        $event->setArgument('id', $id);
        $eventDispatcher->dispatch('news.remove_article', $event);
    }



}