<?php


namespace MBH\Bundle\BaseBundle\Lib\AliasChecker;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AliasChecker
{

    public const CHECK_ALIAS_SCRIPT = __DIR__.'/../../../../../../scripts/checkalias/checkalias.py';


    public static function checkAlias(string $variableName): void
    {

        $fileSystem = new  Filesystem();
        $isCli = PHP_SAPI === 'cli';
        $clientName = $isCli ? getenv($variableName) : $_SERVER[$variableName];
        if (empty($clientName) && $isCli) {
            $clientName = \AppKernel::DEFAULT_CLIENT;
        }

        if ($clientName !== \AppKernel::DEFAULT_CLIENT && $fileSystem->exists(realpath(static::CHECK_ALIAS_SCRIPT))) {

            $commandline = 'python ' . static::CHECK_ALIAS_SCRIPT . ' --client ' . $clientName;
            $process = new Process($commandline);
            $process->mustRun();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $realName = trim($process->getOutput());

            if ('None' === $realName) {
                throw new AliasCheckerException('No alias - name comparison for client ' . $clientName);
            }

            if ($realName && ($clientName !== $realName)) {
                if ($isCli) {
                    putenv(\AppKernel::CLIENT_VARIABLE. '=' .$realName);
                } else {
                    $_SERVER[$variableName] = $realName;
                }

            }
        }
    }
}