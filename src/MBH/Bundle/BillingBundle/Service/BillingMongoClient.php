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
    }

    public function copyDatabase(string $templateDBName, string $newDbName): bool
    {
        $command = new Command(['copydb' => 1, 'fromdb' => $templateDBName, 'todb' => $newDbName]);
        $result = $this->executeMongoCommand($command);

        return boolval($result->toArray()[0]->ok);
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

        return boolval($result);
    }

    public function dropDatabase(string $dbName): bool
    {
        $result = $this->client->dropDatabase($dbName);

        return boolval($result->getIterator()->ok);
    }

    public function createDbUser(string $dbName, string $userName, string $password)
    {
        $this->dropDbUser($dbName, $userName);
        $command = new Command(
            [
                "createUser" => $userName,
                "pwd" => $password,
                "roles" => ["dbAdmin", "readWrite"]
            ]

        );

        $this->executeMongoCommand($command, $dbName);
    }

    public function dropDbUser(string $dbName, string $userName)
    {
        if ($this->checkUserExists($dbName, $userName)) {
            $command = new Command(
                [
                    "dropUser" => $userName
                ]
            );
            $this->executeMongoCommand($command, $dbName);
        }

    }

    public function purgeAllDbUsers($dbName) {
        $command = new Command(
            [
                "dropAllUsersFromDatabase" => 1,
            ]
        );
        $this->executeMongoCommand($command, $dbName);
    }

    private function checkUserExists(string $dbName, string $userName): bool
    {
        $command = new Command(
            ["usersInfo" => [
                "user" => $userName,
                "db" => $dbName
            ]]
        );
        $result = $this->executeMongoCommand($command, $dbName);

        return boolval($result->toArray()[0]->users);
    }


    private function executeMongoCommand(Command $command, string $dbName = null): Cursor
    {
        return $this->manager->executeCommand($dbName??$this->adminDatabase, $command);
    }


}