namespace EasyLoc\Database;
use Doctrine\DBAL\DriverManager;
use Dotenv\Dotenv;

class SqlConnection {
    private $connection;

    public function __construct() {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
        $dotenv->load();

        $this->connection = DriverManager::getConnection([
            'dbname' => $_ENV['DB_NAME'],
            'user' => $_ENV['DB_USER'],
            'password' => $_ENV['DB_PASSWORD'],
            'host' => $_ENV['DB_HOST'],
            'driver' => 'pdo_sqlsrv',
        ]);
    }

    public function getConnection() {
        return $this->connection;
    }
}
// This class establishes a connection to a SQL database using environment variables.