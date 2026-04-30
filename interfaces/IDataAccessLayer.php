<?php
/**
 * IDataAccessLayer Interface
 * 
 * Defines the contract for all database operations.
 * Implementations of this interface handle database connectivity,
 * query execution, and statement preparation.
 */
interface IDataAccessLayer {
    
    /**
     * Establish database connection
     * 
     * @return mysqli|null Connection object or null on failure
     */
    public function connectDatabase();
    
    /**
     * Execute a raw SQL query
     * 
     * @param string $query The SQL query to execute
     * @return mysqli_result|bool Result object on success, false on failure
     */
    public function executeQuery(string $query);
    
    /**
     * Prepare a parameterized SQL statement
     * 
     * @param string $query The SQL query with placeholders (?)
     * @return mysqli_stmt|false Statement object or false on failure
     */
    public function prepareStatement(string $query);
    
    /**
     * Escape a string for safe SQL usage
     * 
     * @param string $string The string to escape
     * @return string The escaped string
     */
    public function escapeString(string $string): string;
    
    /**
     * Close the database connection
     * 
     * @return void
     */
    public function closeConnection(): void;
    
    /**
     * Get the last database error message
     * 
     * @return string The error message
     */
    public function getLastError(): string;
    
    /**
     * Get the mysqli connection object
     * 
     * @return mysqli|null The connection object
     */
    public function getConnection();
}
?>
