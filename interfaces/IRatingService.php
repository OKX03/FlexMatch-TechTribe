<?php
/**
 * IRatingService Interface
 * 
 * Defines the contract for job rating and review operations.
 * Implementations handle submission and retrieval of ratings and feedback.
 */
interface IRatingService {
    
    /**
     * Submit a rating for a completed job
     * 
     * @param string $jobApplicationID The ID of the job application being rated
     * @param array $ratingData Contains: rating (1-5), feedback (optional), userID
     * @return string The ID of the new rating record, or empty string on failure
     */
    public function submitRating(string $jobApplicationID, array $ratingData): string;
    
    /**
     * Get all ratings for a specific job posting
     * 
     * @param string $jobPostID The ID of the job post
     * @return array Array of rating records with user feedback
     */
    public function getRatingsByJobPost(string $jobPostID): array;
    
    /**
     * Get rating details for a specific application
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return array|null Rating details or null if no rating exists
     */
    public function getRating(string $jobApplicationID): ?array;
    
    /**
     * Get job history for a user (all completed jobs)
     * 
     * @param string $userID The user's ID
     * @return array Array of completed job records with ratings
     */
    public function getJobHistory(string $userID): array;
    
    /**
     * Get completion status of a job application
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return string Status ('pending', 'in_progress', 'completed', 'cancelled')
     */
    public function getCompletionStatus(string $jobApplicationID): string;
    
    /**
     * Mark a job application as completed
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return bool True if completion was recorded, false otherwise
     */
    public function completeJob(string $jobApplicationID): bool;
    
    /**
     * Get average rating for a job post
     * 
     * @param string $jobPostID The ID of the job post
     * @return float Average rating (0-5) or 0 if no ratings
     */
    public function getAverageRating(string $jobPostID): float;
}
?>
