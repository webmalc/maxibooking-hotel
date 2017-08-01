<?php


namespace MBH\Bundle\BillingBundle\Request\ParamConverter;


use MBH\Bundle\BillingBundle\Lib\Model\Client;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class JsonToClientConverter implements ParamConverterInterface
{

    public function apply(Request $request, ParamConverter $configuration)
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        try {
            $client = $serializer->deserialize($request->getContent(), $configuration->getClass(), 'json');
        } catch (UnexpectedValueException $exception) {
            $client = null;
        }
        $request->attributes->set($configuration->getName(), $client);
    }

    public function supports(ParamConverter $configuration)
    {
        return $configuration->getClass() == Client::class;
    }

}