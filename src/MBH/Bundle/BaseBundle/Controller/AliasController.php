<?php


namespace MBH\Bundle\BaseBundle\Controller;


use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasChecker;
use MBH\Bundle\BaseBundle\Lib\AliasChecker\AliasCheckerException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AliasController
 * @package MBH\Bundle\BaseBundle\Controller
 * @Route("/alias")
 */
class AliasController extends Controller
{
    /**
     * @param null|string $alias
     * @return JsonResponse
     * @Route("/invalidate/{alias}", defaults={"alias":null})
     *
     */
    public function invalidateAction(?string $alias): JsonResponse
    {

        try {
            if (null === $alias) {
                throw new AliasCheckerException('Alias MUST be specified');
            }

            $commandline = sprintf('python3 %s --client %s --mode %s', AliasChecker::CHECK_ALIAS_SCRIPT, $alias, 'invalidate');
            $process = new Process($commandline);
            $process->mustRun();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $result = trim($process->getOutput());
            if ('error' === $result) {
                throw new AliasCheckerException('Error when alias update');
            }
            $status = 'ok';
            $message = 'Alias ' . $alias . ' was updated successful.';
        } catch (ProcessFailedException|AliasCheckerException $e) {
            $status = 'error';
            $message = $e->getMessage();
        }


        $data = [
            'status' => $status,
            'message' => $message
        ];

        return new JsonResponse($data);
    }
}