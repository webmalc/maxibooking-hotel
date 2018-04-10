<?php


namespace MBH\Bundle\BillingBundle\Service;


use MongoDB\Client;
use MongoDB\Driver\Command;
use MongoDB\Driver\Cursor;
use MongoDB\Model\DatabaseInfo;

class BillingMongoClient
{

    /** @var \MongoDB\Client */
    protected $client;
    /** @var \MongoDB\Driver\Manager */
    protected $manager;
    /** @var string */
    protected $adminDatabase;
    /**
     * @var string
     */
    private $adminLogin;
    /**
     * @var string
     */
    private $adminPassword;
    /**
     * @var string
     */
    private $host;
    /**
     * @var string
     */
    private $options;


    /**
     * BillingMongoClient constructor.
     * @param string $adminLogin
     * @param string $adminPassword
     * @param string $host
     * @param string $adminDatabase
     * @param string $options
     * @throws \MongoDB\Exception\InvalidArgumentException
     * @throws \MongoDB\Driver\Exception\RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function __construct(
        string $adminLogin,
        string $adminPassword,
        string $host,
        string $adminDatabase,
        string $options
    ) {
        $this->adminDatabase = $adminDatabase;
        $this->client = new Client("mongodb://${adminLogin}:${adminPassword}@${host}/${adminDatabase}${options}");
        $this->manager = $this->client->getManager();
        $this->adminLogin = $adminLogin;
        $this->adminPassword = $adminPassword;
        $this->host = $host;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getAdminLogin(): string
    {
        return $this->adminLogin;
    }

    /**
     * @return string
     */
    public function getAdminPassword(): string
    {
        return $this->adminPassword;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getOptions(): string
    {
        return $this->options;
    }


    /**
     * @param string $templateDBName
     * @param string $newDbName
     * @return bool
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function copyDatabase(string $templateDBName, string $newDbName): bool
    {

        $command = new Command(['copydb' => 1, 'fromdb' => $templateDBName, 'todb' => $newDbName]);
        $result = $this->executeMongoCommand($command);

        return (bool)$result->toArray()[0]->ok;
    }

    public function checkIfDbExists(string $dbName): bool
    {
        $databases = $this->client->listDatabases();
        $result = array_filter(
            iterator_to_array($databases),
            function ($database) use ($dbName) {
                /** @var DatabaseInfo $database */
                return $database->getName() === $dbName;
            }
        );

        return (bool) $result;
    }

    /**
     * @param string $dbName
     * @return bool
     */
    public function dropDatabase(string $dbName): bool
    {
        $result = $this->client->dropDatabase($dbName);

        return (bool)$result->getIterator()->ok;
    }

    /**
     * @param string $dbName
     * @param string $userName
     * @param string $password
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function createDbUser(string $dbName, string $userName, string $password): void
    {
        $this->dropDbUser($dbName, $userName);
        $command = new Command(
            [
                'createUser' => $userName,
                'pwd' => $password,
                'roles' => ['dbAdmin', 'readWrite'],
            ]

        );

        $this->executeMongoCommand($command, $dbName);
    }

    /**
     * @param string $dbName
     * @param string $userName
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function dropDbUser(string $dbName, string $userName): void
    {
        if ($this->checkUserExists($dbName, $userName)) {
            $command = new Command(
                [
                    'dropUser' => $userName,
                ]
            );
            $this->executeMongoCommand($command, $dbName);
        }

    }

    /**
     * @param $dbName
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public function purgeAllDbUsers($dbName): void
    {
        $command = new Command(
            [
                'dropAllUsersFromDatabase' => 1,
            ]
        );
        $this->executeMongoCommand($command, $dbName);
    }

    /**
     * @param string $dbName
     * @param string $userName
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    private function checkUserExists(string $dbName, string $userName): bool
    {
        $command = new Command(
            [
                'usersInfo' => [
                    'user' => $userName,
                    'db' => $dbName,
                ],
            ]
        );
        $result = $this->executeMongoCommand($command, $dbName);

        return (bool) $result->toArray()[0]->users;
    }


    /**
     * @param Command $command
     * @param string|null $dbName
     * @return Cursor
     * @throws \MongoDB\Driver\Exception\Exception
     */
    private function executeMongoCommand(Command $command, string $dbName = null): Cursor
    {
        return $this->manager->executeCommand($dbName ?? $this->adminDatabase, $command);
    }

    /**
     * @param string $adminLogin
     * @param string $adminPassword
     * @param string $host
     * @param string $adminDatabase
     * @param string $options
     * @return static
     * @throws \MongoDB\Exception\InvalidArgumentException
     * @throws \MongoDB\Driver\Exception\RuntimeException
     * @throws \MongoDB\Driver\Exception\InvalidArgumentException
     */
    public static function createMongoClient(
        string $adminLogin,
        string $adminPassword,
        string $host,
        string $adminDatabase,
        string $options
    ) {
        return new static($adminLogin, $adminPassword, $host, $adminDatabase, $options);
    }


}