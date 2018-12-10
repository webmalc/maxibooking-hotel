<?php


namespace MBH\Bundle\BaseBundle\Lib\AliasChecker;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Dotenv\Exception\PathException;

final class AliasChecker
{

    public const GET_ALIAS_ACTION = 'get_alias';

    public const CHECKER_CLI_HOST = 'ALIAS_CHECKER_CLI_HOST';

    public const CHECKER_WEB_HOST = 'ALIAS_CHECKER_WEB_HOST';

    public const CHECKER_ENVS_ENABLED = 'ENABLED_IN_ENVS';


    /**
     * @param string $variableName
     * @param string $env
     * @throws AliasCheckerException
     */
    public static function checkAlias(string $variableName, string $env): void
    {

        $isCli = PHP_SAPI === 'cli';
        $clientName = $isCli ? getenv($variableName) : $_SERVER[$variableName];
        if (empty($clientName) && $isCli) {
            $clientName = \AppKernel::DEFAULT_CLIENT;
        }

        $handler = new RotatingFileHandler(__DIR__ . '/../../../../../../var/clients/maxibooking/logs/check_alias_error.log');
        $logger = new Logger('check_alias', [$handler]);


        try {
            self::loadConfig();
        } catch (\InvalidArgumentException|PathException $e) {
            $logger->critical($e->getMessage());
        }
        $enabledEnvs = explode(',', getenv(self::CHECKER_ENVS_ENABLED));

        if ($clientName !== \AppKernel::DEFAULT_CLIENT && \in_array($env, $enabledEnvs, true)) {
            try {
                $connectionString = 'http://%s?client=%s&action=%s';
                $clientName = strtolower($clientName);
                $request = $isCli
                    ? sprintf($connectionString, getenv(self::CHECKER_CLI_HOST), $clientName, self::GET_ALIAS_ACTION)
                    : sprintf($connectionString, getenv(self::CHECKER_WEB_HOST), $clientName, self::GET_ALIAS_ACTION);

                $client = new Client();
                $response = $client->get($request, ['timeout' => 2]);

                $realName = trim($response->getBody()->getContents());

                if ('None' === $realName || 'error' === $realName) {
                    throw new AliasCheckerException('No alias - name comparison found for client ' . $clientName);
                }

                if ($realName && ($clientName !== $realName)) {
                    if ($isCli) {
                        putenv(\AppKernel::CLIENT_VARIABLE . '=' . $realName);
                    } else {
                        $_SERVER[$variableName] = $realName;
                    }

                }
            } catch (ConnectException|RequestException $e) {

                $logger->critical($e->getMessage());
            }

        }
    }

    private static function loadConfig(): void
    {
        $dotEnv = new Dotenv();
        $dotEnv->load(__DIR__ . '/../../../../../../app/config/alias_checker.env');
    }

}