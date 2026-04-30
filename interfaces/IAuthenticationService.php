<?php
/**
 * IAuthenticationService Interface
 * 
 * Defines the contract for authentication and authorization operations.
 * Implementations handle user login, registration, session management, and role verification.
 */
interface IAuthenticationService {
    
    /**
     * Authenticate a user with username/email and password
     * 
     * @param string $username The username or email
     * @param string $password The password in plain text
     * @return string|null The user ID if authenticated, null otherwise
     */
    public function authenticate(string $username, string $password): ?string;
    
    /**
     * Register a new user account
     * 
     * @param array $userData Contains: username, password, email, userType (employer/jobseeker)
     * @return string|null The new user ID on success, null on failure
     */
    public function register(array $userData): ?string;
    
    /**
     * Logout a user (clear session/token)
     * 
     * @param string $userID The ID of the user to logout
     * @return bool True if logout succeeded, false otherwise
     */
    public function logout(string $userID): bool;
    
    /**
     * Validate if current session is valid
     * 
     * @param string $userID The user ID to validate
     * @return bool True if session is valid, false otherwise
     */
    public function validateSession(string $userID): bool;
    
    /**
     * Get user role (employer, jobseeker, admin)
     * 
     * @param string $userID The user's ID
     * @return string|null The user's role or null if not found
     */
    public function getRole(string $userID): ?string;
    
    /**
     * Check if user is authenticated
     * 
     * @return bool True if a user is currently logged in
     */
    public function isAuthenticated(): bool;
    
    /**
     * Get current authenticated user ID
     * 
     * @return string|null The current user's ID or null if not logged in
     */
    public function getCurrentUserID(): ?string;
    
    /**
     * Hash a password securely
     * 
     * @param string $password Plain text password
     * @return string Hashed password
     */
    public function hashPassword(string $password): string;
    
    /**
     * Verify password against hash
     * 
     * @param string $password Plain text password
     * @param string $hash Hashed password
     * @return bool True if password matches hash
     */
    public function verifyPassword(string $password, string $hash): bool;
}
?>
