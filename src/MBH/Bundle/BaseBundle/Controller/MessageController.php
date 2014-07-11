<?php

namespace MBH\Bundle\BaseBundle\Controller;

use MBH\Bundle\BaseBundle\Controller\BaseController as Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @Route("/message")
 */
class MessageController extends Controller
{
    /**
     * rooms json list.
     *
     * @Route("/", name="message", options={"expose"=true})
     * @Method("GET")
     * @Security("is_granted('ROLE_USER')")
     * @Template()
     */
    public function getAction()
    {
        /* @var $dm  \Doctrine\Bundle\MongoDBBundle\ManagerRegistry */
        $dm = $this->get('doctrine_mongodb')->getManager();
        $repo = $dm->getRepository('MBHBaseBundle:Message');
        
        $repo->createQueryBuilder('q')
             ->remove()
             ->field('end')->lt(new \DateTime())
             ->getQuery()
             ->execute()
        ;
        
        return new JsonResponse($repo->findAll());
    }
}
