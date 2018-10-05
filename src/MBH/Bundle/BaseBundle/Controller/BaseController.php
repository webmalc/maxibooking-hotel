<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use MBH\Bundle\ClientBundle\Document\ClientConfig;
use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use MBH\Bundle\SearchBundle\Lib\Exceptions\InvalidateException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Gedmo\Tool\Wrapper\MongoDocumentWrapper;
use Doctrine\ODM\MongoDB\Cursor;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * @var DocumentManager $dm
     */
    protected $dm;

    /**
     * Current selected hotel
     * @var \MBH\Bundle\HotelBundle\Document\Hotel|null
     */
    protected $hotel;

    /**
     * @var \MBH\Bundle\BaseBundle\Service\Helper
     */
    protected $helper;

    /**
     * @var ClientConfig
     */
    protected $clientConfig;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->dm = $this->get('doctrine_mongodb')->getManager();
        $this->hotel = $this->get('mbh.hotel.selector')->getSelected();
        $this->helper = $this->get('mbh.helper');
        $this->clientConfig = $this->dm->getRepository('MBHClientBundle:ClientConfig')->fetchConfig();
    }

    /**
     * Add Access-Control-Allow-Origin header to response
     * @param array $sites
     */
    public function addAccessControlAllowOriginHeaders(array $sites)
    {
        $origin = $this->getRequest()->headers->get('origin');
        foreach ($sites as $site) {
            if ($origin == $site) {
                header('Access-Control-Allow-Origin: ' . $site);
            }
        }
    }
    
    /**
     * Get entity logs
     * @param object $entity
     * @return \Gedmo\Loggable\Document\LogEntry[]|null
     */
    public function logs($entity)
    {
        if (empty($entity)) {
            return null;
        }

        $repo = $this->dm->getRepository('Gedmo\Loggable\Document\LogEntry');

        $wrapped = new MongoDocumentWrapper($entity, $this->dm);
        $objectId = $wrapped->getIdentifier();
        $qb = $repo->createQueryBuilder();
        $qb->field('objectId')->equals($objectId);
        $qb->field('objectClass')->equals($wrapped->getMetadata()->name);
        $qb->limit($this->container->getParameter('mbh.logs.max'));
        $qb->sort('version', 'DESC');
        $q = $qb->getQuery();
        $logs = $q->execute();
        if ($logs instanceof Cursor) {
            $logs = $logs->toArray();
        }

        if (empty($logs)) {
            return null;
        }

        return $logs;
    }

    /**
     * Redirect after entity save
     * @param string $route
     * @param string $id
     * @param [] $params
     * @param string $suffix
     * @return RedirectResponse
     */
    public function afterSaveRedirect($route, $id, array $params = [], $suffix = '_edit')
    {
        return $this->isSavedRequest() ?
            $this->redirectToRoute($route . $suffix, array_merge(['id' => $id], $params)) :
            $this->redirectToRoute($route, $params);
    }

    /**
     * Is saved request and whether need to stay on current page
     * @return bool
     */
    protected function isSavedRequest()
    {
        return $this->getRequest()->get('save') !== null;
    }

    /**
     * Delete entity
     * @param int $id
     * @param string $repo repository name
     * @param string $route route name for redirect
     * @param array $params
     * @return RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function deleteEntity($id, $repo, $route, array $params = [])
    {
        try {
            $entity = $this->dm->getRepository($repo)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException();
            }

            if (method_exists($entity, 'getHotel')) {
                if ($entity->getHotel() && !$this->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                    throw $this->createNotFoundException();
                }
            }

            if ($entity instanceof Hotel) {
                if (!$this->get('mbh.hotel.selector')->checkPermissions($entity)) {
                    throw $this->createNotFoundException();
                }
            }

            try {
                $this->get('mbh_search.invalidate_queue_creator')->addToQueue($entity);
            } catch (InvalidateException $e) {

            }

            $this->dm->remove($entity);
            $this->dm->flush($entity);

            $this->getRequest()->getSession()->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.baseController.delete_record_success'));

        } catch (DeleteException $e) {
            $this->getRequest()->getSession()->getFlashBag()
                ->set('danger', $this->get('translator')->trans($e->getMessage(), ['%total%' => $e->total]));
        }

        return $this->redirectToRoute($route, $params);
    }

    protected function setLocaleByRequest()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $locale = $request->get('locale');
        if ($locale) {
            $this->setLocale($locale);
        }
    }

    protected function setLocale($locale)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $request->setLocale($locale);
        $this->get('translator')->setLocale($request->getLocale());
    }

    /**
     * @return null|\Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->get('request_stack')->getCurrentRequest();
    }
}
