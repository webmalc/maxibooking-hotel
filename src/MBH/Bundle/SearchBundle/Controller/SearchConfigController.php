<?php


namespace MBH\Bundle\SearchBundle\Controller;


use MBH\Bundle\BaseBundle\Controller\BaseController;
use MBH\Bundle\SearchBundle\Document\SearchConfig;
use MBH\Bundle\SearchBundle\Form\SearchConfigType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchConfigController
 * @package MBH\Bundle\SearchBundle\Controller
 * @Route("/config")
 */
class SearchConfigController extends BaseController
{
    /**
     * @Route("/", name="search_config")
     * @Security("is_granted('ROLE_CLIENT_CONFIG_VIEW')")
     * @Template("MBHSearchBundle:SearchConfig:index.html.twig")
     */
    public function indexAction(Request $request)
    {
        $searchConfig = $this->dm->getRepository(SearchConfig::class)->findOneBy([]);
        if (!$searchConfig) {
            $searchConfig = new SearchConfig();
        }
        $form = $this->createForm(SearchConfigType::class, $searchConfig);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dm->persist($searchConfig);
            $this->dm->flush($searchConfig);

            $request->getSession()->getFlashBag()
                ->set('success', 'Конфигурация поиска была обновлена');
        }

        return [
            'entity' => $searchConfig,
            'form' => $form->createView(),
            'logs' => $this->logs($searchConfig)
        ];
    }

}