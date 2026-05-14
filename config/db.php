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
        $this->host     = getenv('DB_HOST')     ?: 'localhost';
        $this->dbName   = getenv('DB_NAME')     ?: 'chat_app';
        $this->user     = getenv('DB_USER')     ?: 'root';
        $this->password = getenv('DB_PASSWORD') ?: '';
        $this->port     = (int)(getenv('DB_PORT') ?: 3306);
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
