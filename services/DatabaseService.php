<?php
require_once __DIR__ . '/../interfaces/IDataAccessLayer.php';

/**
 * DatabaseService Class
 * 
 * Implements the IDataAccessLayer interface.
 * Handles all database connectivity and query execution operations.
 * 
 * Usage:
 *   $db = new DatabaseService();
 *   $connection = $db->connectDatabase();
 *   $result = $db->executeQuery("SELECT * FROM users");
 */
class DatabaseService implements IDataAccessLayer {
    
    private $connection;
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $lastError;
    
    /**
     * Constructor - Initialize database credentials
     */
    public function __construct() {
        $this->servername = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "flexmatch";
        $this->lastError = "";
        $this->connection = null;
    }
    
    /**
     * Establish database connection
     * 
     * @return mysqli|null Connection object or null on failure
     */
    public function connectDatabase() {
        try {
            $this->connection = new mysqli(
                $this->servername, 
                $this->username, 
                $this->password, 
                $this->dbname
            );
            
            if ($this->connection->connect_error) {
                $this->lastError = "Connection failed: " . $this->connection->connect_error;
                return null;
            }
            
            // Set charset to utf8
            $this->connection->set_charset("utf8");
            
            return $this->connection;
        } catch (Exception $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
    }
    
    /**
     * Execute a raw SQL query
     * 
     * @param string $query The SQL query to execute
     * @return mysqli_result|bool Result object on success, false on failure
     */
    public function executeQuery(string $query) {
        if (!$this->connection) {
            $this->connectDatabase();
        }
        
        $result = $this->connection->query($query);
        
        if (!$result) {
            $this->lastError = $this->connection->error;
        }
        
        return $result;
    }
    
    /**
     * Prepare a parameterized SQL statement
     * 
     * @param string $query The SQL query with placeholders (?)
     * @return mysqli_stmt|false Statement object or false on failure
     */
    public function prepareStatement(string $query) {
        if (!$this->connection) {
            $this->connectDatabase();
        }
        
        $stmt = $this->connection->prepare($query);
        
        if (!$stmt) {
            $this->lastError = $this->connection->error;
        }
        
        return $stmt;
    }
    
    /**
     * Escape a string for safe SQL usage
     * 
     * @param string $string The string to escape
     * @return string The escaped string
     */
    public function escapeString(string $string): string {
        if (!$this->connection) {
            $this->connectDatabase();
        }
        
        return $this->connection->real_escape_string($string);
    }
    
    /**
     * Close the database connection
     * 
     * @return void
     */
    public function closeConnection(): void {
        if ($this->connection) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    /**
     * Get the last database error message
     * 
     * @return string The error message
     */
    public function getLastError(): string {
        return $this->lastError;
    }
    
    /**
     * Get the mysqli connection object
     * 
     * @return mysqli|null The connection object
     */
    public function getConnection() {
        if (!$this->connection) {
            $this->connectDatabase();
        }
        return $this->connection;
    }
    
    /**
     * Destructor - Ensure connection is closed
     */
    public function __destruct() {
        $this->closeConnection();
    }
}
?>
