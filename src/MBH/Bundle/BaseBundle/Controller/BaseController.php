<?php

namespace MBH\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Base Controller
 */
class BaseController extends Controller
{
    /**
     * Get entity logs
     * @param object $entity
     * @return Gedmo\Loggable\Entity\LogEntr[]|null
     */
    public function logs($entity)
    {
        $dm = $this->get('doctrine_mongodb')->getManager();
        
        $logs = $dm->getRepository('Gedmo\Loggable\Document\LogEntry')->getLogEntries($entity);
        
        if (empty($logs)) {
            return null;
        }
        
        return array_slice($logs, 0, $this->container->getParameter('maxibooking.logs.max'));
    }
    
    /**
     * Redirect after entity save
     * @param string $route
     * @param int $id
     * @return Response
     */
    public function afterSaveRedirect($route, $id)
    {
        if ($this->getRequest()->get('save') !== null) {
            
            return $this->redirect($this->generateUrl($route . '_edit', array('id' => $id)));
        }
        
        return $this->redirect($this->generateUrl($route));
    }
    
    /**
     * Delete entity
     * @param int $id
     * @param string $repo repository name
     * @param string $route route name for redirect
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function deleteEntity($id, $repo, $route)
    {
        /* @var $dm  Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();

        $entity = $dm->getRepository($repo)->find($id);

        if (!$entity) {
            throw $this->createNotFoundException();
        }

        $dm->remove($entity);
        $dm->flush($entity);

        $this->getRequest()
             ->getSession()
             ->getFlashBag()
             ->set('success', 'Запись успешно удалена.');

        return $this->redirect($this->generateUrl($route));
    }

}
