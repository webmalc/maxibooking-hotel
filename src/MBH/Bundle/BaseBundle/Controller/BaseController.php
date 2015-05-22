<?php

namespace MBH\Bundle\BaseBundle\Controller;

use MBH\Bundle\HotelBundle\Document\Hotel;
use MBH\Bundle\PackageBundle\Lib\DeleteException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    public function setContainer(ContainerInterface $container = null)
    {
        parent::setContainer($container);

        $this->dm = $this->get('doctrine_mongodb')->getManager();
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
     * @return \Gedmo\Loggable\Entity\LogEntr[]|null
     */
    public function logs($entity)
    {
        if (empty($entity)) {
            return null;
        }

        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $logs = $dm->getRepository('Gedmo\Loggable\Document\LogEntry')->getLogEntries($entity);
        
        if (empty($logs)) {
            return null;
        }
        
        return array_slice($logs, 0, $this->container->getParameter('mbh.logs.max'));
    }
    
    /**
     * Redirect after entity save
     * @param string $route
     * @param string $id
     * @param [] $params
     * @param string $suffix
     * @return Response
     */
    public function afterSaveRedirect($route, $id, array $params = [], $suffix = '_edit')
    {
        if ($this->getRequest()->get('save') !== null) {
            
            return $this->redirect($this->generateUrl($route . $suffix, array_merge(['id' => $id], $params)));
        }
        
        return $this->redirect($this->generateUrl($route, $params));
    }
    
    /**
     * Delete entity
     * @param int $id
     * @param string $repo repository name
     * @param string $route route name for redirect
     * @param array $params
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function deleteEntity($id, $repo, $route, array $params = [])
    {
        try {
            /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
            $dm = $this->get('doctrine_mongodb')->getManager();

            $entity = $dm->getRepository($repo)->find($id);

            if (!$entity) {
                throw $this->createNotFoundException();
            }

            if (method_exists($entity, 'getHotel')) {
                if(!$this->container->get('mbh.hotel.selector')->checkPermissions($entity->getHotel())) {
                    throw $this->createNotFoundException();
                }
            }

            if ($entity instanceof Hotel) {
                if(!$this->container->get('mbh.hotel.selector')->checkPermissions($entity)) {
                    throw $this->createNotFoundException();
                }
            }

            $dm->remove($entity);
            $dm->flush($entity);

            $this->getRequest()
                ->getSession()
                ->getFlashBag()
                ->set('success', $this->get('translator')->trans('controller.baseController.delete_record_success'));

        } catch (DeleteException $e) {
            $this->getRequest()
                ->getSession()
                ->getFlashBag()
                ->set('danger', $e->getMessage());
        }


        return $this->redirect($this->generateUrl($route, $params));
    }

}
