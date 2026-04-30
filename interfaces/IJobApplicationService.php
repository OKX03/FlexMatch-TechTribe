<?php
/**
 * IJobApplicationService Interface
 * 
 * Defines the contract for job application operations.
 * Implementations handle application submission, retrieval, and response management.
 */
interface IJobApplicationService {
    
    /**
     * Submit a new job application
     * 
     * @param string $jobSeekerID The ID of the job seeker applying
     * @param string $jobPostID The ID of the job post being applied to
     * @return string|null The application ID on success, null on failure
     */
    public function applyForJob(string $jobSeekerID, string $jobPostID): ?string;
    
    /**
     * Retrieve all applications for a specific job post
     * 
     * @param string $jobPostID The ID of the job post
     * @return array Array of application records
     */
    public function listApplications(string $jobPostID): array;
    
    /**
     * Get details of a specific application
     * 
     * @param string $applicationID The ID of the application
     * @return array|null Application details or null if not found
     */
    public function getApplicationDetails(string $applicationID): ?array;
    
    /**
     * Respond to a job application (accept/reject)
     * 
     * @param string $applicationID The ID of the application
     * @param string $status The response status ('accepted' or 'rejected')
     * @param string $comments Optional comments from employer
     * @return bool True if response was recorded, false otherwise
     */
    public function respondToApplication(string $applicationID, string $status, string $comments = ''): bool;
    
    /**
     * Cancel/withdraw a job application
     * 
     * @param string $applicationID The ID of the application
     * @return bool True if cancellation succeeded, false otherwise
     */
    public function cancelApplication(string $applicationID): bool;
    
    /**
     * Get all applications from a specific job seeker
     * 
     * @param string $jobSeekerID The job seeker's ID
     * @return array Array of their applications
     */
    public function getApplicationsByJobSeeker(string $jobSeekerID): array;
    
    /**
     * Get all applications received by an employer
     * 
     * @param string $employerID The employer's ID
     * @return array Array of applications to their job posts
     */
    public function getApplicationsByEmployer(string $employerID): array;
    
    /**
     * Check if a job seeker has already applied for a job
     * 
     * @param string $jobSeekerID The job seeker's ID
     * @param string $jobPostID The job post ID
     * @return bool True if already applied, false otherwise
     */
    public function hasApplied(string $jobSeekerID, string $jobPostID): bool;
    
    /**
     * Get application status
     * 
     * @param string $applicationID The application ID
     * @return string Status string ('pending', 'accepted', 'rejected', 'cancelled')
     */
    public function getApplicationStatus(string $applicationID): string;
    
    /**
     * Get count of applications for a job post
     * 
     * @param string $jobPostID The job post ID
     * @return int Number of applications
     */
    public function getApplicationCount(string $jobPostID): int;
}
?>
