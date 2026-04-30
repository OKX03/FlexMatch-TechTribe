<?php
/**
 * IUserProfileService Interface
 * 
 * Defines the contract for user profile operations.
 * Implementations handle creation, retrieval, updating, and deletion of user profiles.
 */
interface IUserProfileService {
    
    /**
     * Create a new user profile
     * 
     * @param string $userID The ID of the user
     * @param string $userType Type of user ('employer' or 'jobseeker')
     * @param array $profileData Profile details (name, phone, address, etc.)
     * @return bool True if profile created successfully, false otherwise
     */
    public function createProfile(string $userID, string $userType, array $profileData): bool;
    
    /**
     * Update an existing user profile
     * 
     * @param string $userID The ID of the user
     * @param array $profileData Updated profile details
     * @return bool True if update succeeded, false otherwise
     */
    public function updateProfile(string $userID, array $profileData): bool;
    
    /**
     * Get user profile by user ID
     * 
     * @param string $userID The ID of the user
     * @return array|null Profile data or null if not found
     */
    public function getProfile(string $userID): ?array;
    
    /**
     * Get user profile with full details
     * 
     * @param string $userID The ID of the user
     * @return array|null Complete profile data or null
     */
    public function getProfileDetails(string $userID): ?array;
    
    /**
     * Delete a user profile
     * 
     * @param string $userID The ID of the user
     * @return bool True if deletion succeeded, false otherwise
     */
    public function deleteProfile(string $userID): bool;
    
    /**
     * Validate if a user profile exists
     * 
     * @param string $userID The ID of the user
     * @return bool True if profile exists, false otherwise
     */
    public function profileExists(string $userID): bool;
    
    /**
     * Get user type (employer or jobseeker)
     * 
     * @param string $userID The ID of the user
     * @return string|null The user type or null if not found
     */
    public function getUserType(string $userID): ?string;
    
    /**
     * Check if profile is complete
     * 
     * @param string $userID The ID of the user
     * @return bool True if all required fields are filled
     */
    public function isProfileComplete(string $userID): bool;
    
    /**
     * Search profiles by criteria
     * 
     * @param array $criteria Search criteria (name, location, skills, etc.)
     * @return array Array of matching profiles
     */
    public function searchProfiles(array $criteria): array;
    
    /**
     * Get user rating/reputation
     * 
     * @param string $userID The ID of the user
     * @return float Average rating from other users
     */
    public function getUserRating(string $userID): float;
    
    /**
     * Suspend/restrict a user account
     * 
     * @param string $userID The ID of the user
     * @param string $reason Reason for suspension
     * @param int $duration Duration in days (0 for permanent)
     * @return bool True if suspension succeeded
     */
    public function suspendAccount(string $userID, string $reason, int $duration = 0): bool;
    
    /**
     * Restore a suspended account
     * 
     * @param string $userID The ID of the user
     * @return bool True if restore succeeded
     */
    public function restoreAccount(string $userID): bool;
}
?>
