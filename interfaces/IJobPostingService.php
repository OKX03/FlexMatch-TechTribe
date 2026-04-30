<?php
/**
 * IJobPostingService Interface
 * 
 * Defines the contract for job posting operations.
 * Implementations handle creation, retrieval, filtering, and management of job posts.
 */
interface IJobPostingService {
    
    /**
     * Create a new job posting
     * 
     * @param string $employerID The ID of the employer posting the job
     * @param array $jobData Associative array containing job details
     *              Keys: jobTitle, location, salary, description, requirements, 
     *                    workingHour, startDate, endDate, venue, language, race,
     *                    workingTimeStart, workingTimeEnd
     * @return string The ID of the newly created job post, or empty string on failure
     */
    public function createJobPost(string $employerID, array $jobData): string;
    
    /**
     * Update an existing job posting
     * 
     * @param string $jobPostID The ID of the job post to update
     * @param array $jobData Associative array containing updated job details
     * @return bool True if update succeeded, false otherwise
     */
    public function updateJobPost(string $jobPostID, array $jobData): bool;
    
    /**
     * Delete a job posting
     * 
     * @param string $jobPostID The ID of the job post to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function deleteJobPost(string $jobPostID): bool;
    
    /**
     * Retrieve all job postings with optional filtering
     * 
     * @param array $filters Optional filters (location, salary range, working hour, etc.)
     * @return array Array of job posting records
     */
    public function listJobPosts(array $filters = []): array;
    
    /**
     * Get details of a specific job posting
     * 
     * @param string $jobPostID The ID of the job post
     * @return array|null Job posting details or null if not found
     */
    public function getJobPostDetails(string $jobPostID): ?array;
    
    /**
     * Validate if a job post exists and is active
     * 
     * @param string $jobPostID The ID of the job post
     * @return bool True if job post is valid and active, false otherwise
     */
    public function validateJobPost(string $jobPostID): bool;
    
    /**
     * Filter job postings by various criteria
     * 
     * @param array $criteria Filtering criteria (location, salary, shift type, etc.)
     * @return array Array of filtered job postings
     */
    public function filterJobs(array $criteria): array;
    
    /**
     * Search job postings by title or keywords
     * 
     * @param string $query The search query/keywords
     * @return array Array of matching job postings
     */
    public function searchJobs(string $query): array;
    
    /**
     * Get active job postings (not yet ended)
     * 
     * @return array Array of active job postings
     */
    public function getActiveJobs(): array;
    
    /**
     * Get job postings by employer ID
     * 
     * @param string $employerID The employer's user ID
     * @return array Array of job postings from that employer
     */
    public function getJobsByEmployer(string $employerID): array;
}
?>
