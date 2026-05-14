<?php
class Database {
    private string $host;
    private string $dbName;
    private string $user;
    private string $password;
    private int $port;
    private ?PDO $conn = null;
    private static ?self $instance = null;

    private function __construct()
    {
        $envHost     = getenv('DB_HOST');
        $envDbName   = getenv('DB_NAME');
        $envUser     = getenv('DB_USER');
        $envPassword = getenv('DB_PASSWORD');
        $envPort     = getenv('DB_PORT');

        if ($envHost && $envDbName && $envUser && $envPassword) {
            $this->host     = $envHost;
            $this->dbName   = $envDbName;
            $this->user     = $envUser;
            $this->password = $envPassword;
            $this->port     = (int)($envPort ?: 4000);
        } else {
            $config = require __DIR__ . '/env.php';
            $this->host     = $config['db_host'];
            $this->dbName   = $config['db_name'];
            $this->user     = $config['db_user'];
            $this->password = $config['db_password'];
            $this->port     = $config['db_port'] ?? 4000;
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        if ($this->conn === null) {
            try {
                $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->dbName};charset=utf8mb4";
                $this->conn = new PDO($dsn, $this->user, $this->password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                error_log("Database Connection Failed: " . $e->getMessage());
                die("Database Connection Failed");
            }
        }
        return $this->conn;
    }
}
