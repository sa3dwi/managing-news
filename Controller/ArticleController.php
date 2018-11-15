<?php

namespace NewsBundle\Controller;

use CommonBundle\Classes\UUID;
use NewsBundle\Entity\ArticleImage;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use NewsBundle\Entity\Article;
use NewsBundle\Form\ArticleType;

/**
 * @Route("/article")
 */
class ArticleController extends Controller
{

    /**
     * Get list of news articles
     *
     * @param Request $request
     * @Route("/", name="news_article")
     * @Security("has_role('ROLE_NEWS_ARTICLE_LIST')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userRepository = $em->getRepository('NewsBundle:Article');
        $dataGrid = $userRepository->getDataGrid();
        $form = $dataGrid->getFilterForm(
            $this->container->get('form.factory'),
            $this->generateUrl('news_article')
        );

        if ('POST' == $request->getMethod()) {
            $form->handleRequest($request);
            return $this->redirectToRoute('news_article', [
                'filter' => $dataGrid->getEncodedFilterArray($form)
            ]);
        }

        $filter = $request->query->get('filter');
        if ($filter) {
            $formData = $dataGrid->decodeFilterArray($filter);
            $form = $dataGrid->setFormFilterData($form, $formData);
        }

        $entities = $dataGrid->getGrid(
            $this->get('knp_paginator'),
            $request->query->getInt('page', 1),
            $request->getLocale()
        );
        return $this->render('NewsBundle:Article:index.html.twig', [
            'entities' => $entities,
            'formFilter' => $form->createView(),
        ]);
    }


    /**
     * Create new article
     *
     * @param Request $request
     * @Route("/new", name="news_article_new")
     * @Security("has_role('ROLE_NEWS_ARTICLE_NEW')")
     * @Method({"GET", "POST"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function newAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = new Article();
        $entity->setFileService($this->get('file.service'));
        $articleRepository = $em->getRepository('NewsBundle:Article');
        $languages = $this->get('locale.service')->getFrontEndSwitchLanguages();
        $form = $this->createForm(ArticleType::class, $entity, ['languages'=> $languages]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uuid = $form->get('uuid')->getData();
            $entity->setLanguage($form->get('language')->getData());
            $entity->setUuid($uuid);
            $em->persist($entity);
            $em->flush();

            $articleRepository->moveTempImagesToNewsItem($entity, $uuid);
            $articleRepository->deleteExpiredTempFiles();

            $this->get('session')->getFlashBag()->add(
                'success', $this->get('translator')->trans('admin.messages.the_entry_has_been_added')
            );
            $this->get('common.service')->log($this->get('news.service')->getName(), 'news.log.add_article', ['%entity_id%'=>$entity->getId()], $this->getUser()->getId());
            return $this->redirectToRoute('news_article_show', ['id'=>$entity->getId()]);
        }elseif(!$form->isSubmitted()) {
            $uuid = UUID::v4();
            $form->get('uuid')->setData($uuid);
        }

        return $this->render('NewsBundle:Article:new.html.twig', [
            'entity'    => $entity,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * Show article details
     *
     * @param Article $entity
     * @ParamConverter("entity", class="NewsBundle:Article")
     * @Route("/show/{id}", name="news_article_show")
     * @Security("has_role('ROLE_NEWS_ARTICLE_SHOW')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showAction(Article $entity)
    {
        return $this->render('NewsBundle:Article:show.html.twig', [
            'entity' => $entity
        ]);
    }

    /**
     * Edit article
     *
     * @param Request $request
     * @param Article $entity
     * @ParamConverter("entity", class="NewsBundle:Article")
     * @Route("/edit/{id}", name="news_article_edit")
     * @Security("has_role('ROLE_NEWS_ARTICLE_EDIT')")
     * @Method({"GET", "POST"})
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, Article $entity)
    {
        $em = $this->getDoctrine()->getManager();
        $languages = $this->get('locale.service')->getFrontEndSwitchLanguages();
        $editForm = $this->createForm(ArticleType::class, $entity, ['languages'=>$languages] );
        $entity->setFileService($this->get('file.service'));
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted()) {
            if($editForm->isValid()) {
                $entity->setLanguage($editForm->get('language')->getData());
                $em->flush();
                $this->get('session')->getFlashBag()->add(
                    'success', $this->get('translator')->trans('admin.messages.the_entry_has_been_updated')
                );
                $this->get('common.service')->log($this->get('news.service')->getName(), 'news.log.edit_article', ['%entity_id%'=>$entity->getId()], $this->getUser()->getId());
                return $this->redirectToRoute('news_article_show', ['id' => $entity->getId()]);
            }
        }elseif(!$editForm->isSubmitted()) {
            $editForm->get('uuid')->setData($entity->getUuid());
        }

        return $this->render('NewsBundle:Article:edit.html.twig', [
            'entity'        => $entity,
            'edit_form'     => $editForm->createView(),
        ]);

    }


    /**
     * Delete article
     *
     * @param Request $request
     * @Route("/delete", name="news_article_delete")
     * @Security("has_role('ROLE_NEWS_ARTICLE_DELETE')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(Request $request)
    {
        $em = $this->get('doctrine')->getManager();
        $single = $request->query->get('single', false);
        $id = $request->query->get('id');
        $encodedRedirect = $request->query->get('redirect');
        $redirect = base64_decode( $encodedRedirect );
        if ($single) {
            return $this->redirectToRoute('news_article_delete', [
                'id' => base64_encode(serialize([$id])),
                'redirect' => $encodedRedirect
            ]);
        } else {
            $repository = $em->getRepository('NewsBundle:Article');
            $bundleService = $this->get('news.service');
            $ids = unserialize(base64_decode($id));
            if (!is_array($ids) || empty($ids)) {
                throw $this->createNotFoundException( $this->get('translator')->trans('admin.titles.error_happened') );
            }
            if ('POST' == $request->getMethod()) {
                foreach($ids as $id) {
                    try {
                        $article = $repository->find($id);
                        $article->setFileService($this->get('file.service'));
                        if($article) {
                            $em->remove($article);
                            $em->flush();
                            $this->get('session')->getFlashBag()->add(
                                'success', $this->get('translator')->trans('admin.messages.the_entry_has_been_deleted').' '.$article
                            );
                            $this->get('common.service')->log($this->get('news.service')->getName(), 'news.log.delete_article', ['%entity_id%'=>$id], $this->getUser()->getId());
                        }
                    }catch(\Exception $e) {
                        $this->get('session')->getFlashBag()->add(
                            'danger', $e->getMessage()
                        );
                    }
                }
                return $this->redirectToRoute('news_article');
            }else{
                $report = $repository->getDeleteRestrectionsByIds(
                    $bundleService,
                    $ids
                );
                return $this->render('NewsBundle:Article:delete.html.twig', [
                    'report' => $report,
                    'redirect' => $redirect,
                ]);
            }
        }
    }


    /**
     * Delete multiple articles
     *
     * @param Request $request
     * @Route("/batch", name="news_article_batch")
     * @Security("has_role('ROLE_NEWS_ARTICLE_DELETE')")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchAction(Request $request)
    {
        $ids = $request->query->get('ids');
        $idx = explode(',', $ids);
        $action = $request->query->get('action');
        if ('delete' == $action) {
            return $this->redirectToRoute('news_article_delete', [
                'id' => base64_encode(serialize($idx))
            ]);
        }
    }


    /**
     * Upload image for article
     *
     * @param Request $request
     * @param $uniqid
     * @param bool $temp
     * @Route("/upload-image/{uniqid}/{temp}", name="news_article_upload_image")
     * @Security("has_role('ROLE_ADMIN')")
     * @Method({"POST"})
     * @return JsonResponse
     * @throws \Exception
     */
    public function uploadImageAction(Request $request, $uniqid, $temp = false)
    {
        $em = $this->getDoctrine()->getManager();
        $response = new JsonResponse();
        $news = null;
        if(!$temp) {
            $news = $em->getRepository('NewsBundle:Article')->findOneByUuid($uniqid);
            if(!$news) {
                $response->setData([
                    'success' => false,
                    'msg' => 'news item not found'
                ]);
                return $response;
            }
        }

        $storeFolder = ($this->container->getParameter('kernel.root_dir') . '/../web/upload/news');
        $uploader = new \AdminBundle\Classes\FileUpload('uploadfile');
        $ext = pathinfo($uploader->getFileName(), PATHINFO_EXTENSION);
        $newFileName = sha1(uniqid()).'.'.$ext;
        $uploader->newFileName = $newFileName;

        $result = $uploader->handleUpload($storeFolder, ['jpg']);

        if (!$result) {
            $response->setData([
                'success' => false,
                'msg' => $uploader->getErrorMsg()
            ]);
        }else{
            if($temp) {
                $image = new ArticleImage();
                $image->setFileName($newFileName);
                $image->setUuid($uniqid);
                $image->setFileSize($uploader->getFileSize());
                $image->setFileExtension($ext);
            }else{
                $image = new ArticleImage();
                $image->setFileName($newFileName);
                $image->setArticle($news);
                $image->setFileSize($uploader->getFileSize());
                $image->setFileExtension($ext);
            }

            $em->persist($image);
            $em->flush();

            $response->setData([
                'success' => true
            ]);
        }
        return $response;
    }

    /**
     * Delete image of article
     *
     * @param Request $request
     * @Route("/delete-image", name="news_article_delete_image")
     * @Security("has_role('ROLE_ADMIN')")
     * @Method({"POST"})
     * @return JsonResponse
     */
    public function deleteImageAction(Request $request)
    {
        $id = $request->request->get('id');
        $temp = $request->request->get('temp') == 'true' ? true : false ;
        $em = $this->getDoctrine()->getManager();
        $success = true;
        $response = new JsonResponse();
        $image = $em->getRepository('NewsBundle:ArticleImage')->find($id);
        if($image) {
            $em->remove($image);
            $em->flush();
        }else{
            $success = false;
        }
        $response->setData([
            'success' => $success
        ]);
        return $response;
    }

    /**
     * Load images for article
     *
     * @param Request $request
     * @param $uniqid
     * @param bool $temp
     * @Route("/list-images/{uniqid}/{temp}", name="news_article_load_images")
     * @Security("has_role('ROLE_ADMIN')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loadImagesAction(Request $request, $uniqid, $temp = false)
    {
        $em = $this->getDoctrine()->getManager();

        $images = [];
        if($temp) {
            $images = $em->getRepository('NewsBundle:Article')->getImages($uniqid);
        }else{
            $newsItem = $em->getRepository('NewsBundle:Article')->findOneByUuid($uniqid);
            if($newsItem) {
                $images = $newsItem->getImages();
            }
        }

        return $this->render('NewsBundle:Article:_loadImages.html.twig', [
            'images'  => $images,
        ]);
    }


}
