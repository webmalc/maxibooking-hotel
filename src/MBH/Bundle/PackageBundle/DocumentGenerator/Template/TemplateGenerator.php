<?php


namespace MBH\Bundle\PackageBundle\DocumentGenerator\Template;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultDocumentTemplateGenerator
 * @author Aleksandr Arofikin <sasaharo@gmail.com>
 */
abstract class TemplateGenerator implements TemplateGeneratorInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    abstract protected function getType();

    /**
     * @param array $formData
     * @return array
     */
    abstract protected function prepareParams(array $formData);

    /**
     * @param array $formData
     * @return string
     */
    public function getTemplate(array $formData)
    {
        $params = $this->prepareParams($formData);
        $html = $this->container->get('templating')->render($this->getTemplateName(), $params);

        return $html;
    }

    /**
     * @return Response
     */
    public function generateResponse(array $formData)
    {
        //return new Response($this->getTemplate($formData), 200);
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $content = $this->container->get('knp_snappy.pdf')->getOutputFromHtml($this->getTemplate($formData), [
            'cookie' => [$request->getSession()->getName() => $request->getSession()->getId()],
            //'disable-smart-shrinking' => true,
        ]);
        return new Response($content, 200, ['Content-Type' => 'application/pdf']);
    }


    /**
     * @return string
     * @throws \Exception
     */
    protected function getTemplateName()
    {
        $templateName = 'MBHPackageBundle:Documents/pdfTemplates:'.$this->getType().'.html.twig';
        if(!$this->container->get('templating')->exists($templateName)) {
            throw new \Exception();
        };

        return $templateName;
    }
}