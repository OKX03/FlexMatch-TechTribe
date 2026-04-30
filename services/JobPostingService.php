<?php
require_once __DIR__ . '/../interfaces/IJobPostingService.php';
require_once __DIR__ . '/../interfaces/IDataAccessLayer.php';

/**
 * JobPostingService Class
 * 
 * Implements the IJobPostingService interface.
 * Handles all job posting operations: creation, retrieval, filtering, and management.
 * 
 * Usage:
 *   $db = new DatabaseService();
 *   $jobService = new JobPostingService($db);
 *   $activeJobs = $jobService->getActiveJobs();
 */
class JobPostingService implements IJobPostingService {
    
    private $db;
    
    /**
     * Constructor - Initialize with database service
     * 
     * @param IDataAccessLayer $db Database service instance
     */
    public function __construct(IDataAccessLayer $db) {
        $this->db = $db;
    }
    
    /**
     * Create a new job posting
     * 
     * @param string $employerID The ID of the employer posting the job
     * @param array $jobData Job details
     * @return string Job post ID on success, empty string on failure
     */
    public function createJobPost(string $employerID, array $jobData): string {
        try {
            // Generate job post ID
            $jobPostID = $this->generateJobPostID();
            
            if (empty($jobPostID)) {
                return "";
            }
            
            $con = $this->db->getConnection();
            
            $query = "INSERT INTO jobPost (
                jobPostID, jobTitle, location, salary, startDate, endDate, 
                workingHour, jobDescription, jobRequirement, venue, language, 
                race, workingTimeStart, workingTimeEnd, userID
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($query);
            
            $stmt->bind_param(
                "sssdsssssssiss",
                $jobPostID,
                $jobData['jobTitle'],
                $jobData['location'],
                $jobData['salary'],
                $jobData['startDate'],
                $jobData['endDate'],
                $jobData['workingHour'],
                $jobData['jobDescription'],
                $jobData['jobRequirement'],
                $jobData['venue'],
                $jobData['language'],
                $jobData['race'],
                $jobData['workingTimeStart'],
                $jobData['workingTimeEnd'],
                $employerID
            );
            
            if ($stmt->execute()) {
                $stmt->close();
                return $jobPostID;
            }
            
            $stmt->close();
            return "";
        } catch (Exception $e) {
            error_log("Error creating job post: " . $e->getMessage());
            return "";
        }
    }
    
    /**
     * Update an existing job posting
     * 
     * @param string $jobPostID The ID of the job post to update
     * @param array $jobData Updated job details
     * @return bool True if update succeeded, false otherwise
     */
    public function updateJobPost(string $jobPostID, array $jobData): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "UPDATE jobPost SET 
                jobTitle = ?, location = ?, salary = ?, startDate = ?, 
                endDate = ?, workingHour = ?, jobDescription = ?, 
                jobRequirement = ?, venue = ?, language = ?, race = ?, 
                workingTimeStart = ?, workingTimeEnd = ?
                WHERE jobPostID = ?";
            
            $stmt = $con->prepare($query);
            
            $stmt->bind_param(
                "ssdsssssssisss",
                $jobData['jobTitle'],
                $jobData['location'],
                $jobData['salary'],
                $jobData['startDate'],
                $jobData['endDate'],
                $jobData['workingHour'],
                $jobData['jobDescription'],
                $jobData['jobRequirement'],
                $jobData['venue'],
                $jobData['language'],
                $jobData['race'],
                $jobData['workingTimeStart'],
                $jobData['workingTimeEnd'],
                $jobPostID
            );
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error updating job post: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete a job posting
     * 
     * @param string $jobPostID The ID of the job post to delete
     * @return bool True if deletion succeeded, false otherwise
     */
    public function deleteJobPost(string $jobPostID): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "DELETE FROM jobPost WHERE jobPostID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error deleting job post: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Retrieve all job postings with optional filtering
     * 
     * @param array $filters Optional filters
     * @return array Array of job postings
     */
    public function listJobPosts(array $filters = []): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT * FROM jobPost WHERE endDate >= CURDATE()";
            
            if (!empty($filters)) {
                if (!empty($filters['location'])) {
                    $location = $con->real_escape_string($filters['location']);
                    $query .= " AND location = '$location'";
                }
                
                if (isset($filters['minSalary'])) {
                    $query .= " AND salary >= " . floatval($filters['minSalary']);
                }
                
                if (isset($filters['maxSalary'])) {
                    $query .= " AND salary <= " . floatval($filters['maxSalary']);
                }
                
                if (!empty($filters['workingHour'])) {
                    $workingHour = $con->real_escape_string($filters['workingHour']);
                    $query .= " AND workingHour = '$workingHour'";
                }
            }
            
            $result = $con->query($query);
            $jobs = [];
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $jobs[] = $row;
                }
            }
            
            return $jobs;
        } catch (Exception $e) {
            error_log("Error listing job posts: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get details of a specific job posting
     * 
     * @param string $jobPostID The ID of the job post
     * @return array|null Job posting details or null
     */
    public function getJobPostDetails(string $jobPostID): ?array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT * FROM jobPost WHERE jobPostID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $job = $result->fetch_assoc();
            $stmt->close();
            
            return $job;
        } catch (Exception $e) {
            error_log("Error getting job post details: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Validate if a job post exists and is active
     * 
     * @param string $jobPostID The ID of the job post
     * @return bool True if valid, false otherwise
     */
    public function validateJobPost(string $jobPostID): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT COUNT(*) as count FROM jobPost 
                     WHERE jobPostID = ? AND endDate >= CURDATE()";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error validating job post: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Filter job postings by various criteria
     * 
     * @param array $criteria Filtering criteria
     * @return array Array of filtered job postings
     */
    public function filterJobs(array $criteria): array {
        return $this->listJobPosts($criteria);
    }
    
    /**
     * Search job postings by title or keywords
     * 
     * @param string $query The search query
     * @return array Array of matching job postings
     */
    public function searchJobs(string $query): array {
        try {
            $con = $this->db->getConnection();
            
            $searchTerm = "%" . $con->real_escape_string($query) . "%";
            
            $sql = "SELECT * FROM jobPost 
                   WHERE (jobTitle LIKE ? OR jobDescription LIKE ?)
                   AND endDate >= CURDATE()";
            
            $stmt = $con->prepare($sql);
            $stmt->bind_param("ss", $searchTerm, $searchTerm);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $jobs = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $jobs[] = $row;
                }
            }
            
            $stmt->close();
            return $jobs;
        } catch (Exception $e) {
            error_log("Error searching jobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get active job postings (not yet ended)
     * 
     * @return array Array of active job postings
     */
    public function getActiveJobs(): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT * FROM jobPost WHERE endDate >= CURDATE() ORDER BY startDate DESC";
            $result = $con->query($query);
            
            $jobs = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $jobs[] = $row;
                }
            }
            
            return $jobs;
        } catch (Exception $e) {
            error_log("Error getting active jobs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get job postings by employer ID
     * 
     * @param string $employerID The employer's user ID
     * @return array Array of job postings from that employer
     */
    public function getJobsByEmployer(string $employerID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT * FROM jobPost WHERE userID = ? ORDER BY startDate DESC";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $employerID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $jobs = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $jobs[] = $row;
                }
            }
            
            $stmt->close();
            return $jobs;
        } catch (Exception $e) {
            error_log("Error getting jobs by employer: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Generate a unique job post ID
     * 
     * @return string Generated job post ID
     */
    private function generateJobPostID(): string {
        try {
            $con = $this->db->getConnection();
            
            $sql_find_id = "
            SELECT MIN(t1.id + 1) AS missingID
            FROM (
                SELECT CAST(SUBSTRING(jobPostID, 3) AS UNSIGNED) AS id
                FROM jobPost
            ) t1
            WHERE NOT EXISTS (
                SELECT 1
                FROM (
                    SELECT CAST(SUBSTRING(jobPostID, 3) AS UNSIGNED) AS id
                    FROM jobPost
                ) t2
                WHERE t2.id = t1.id + 1
            )
            ";
            
            $result = $con->query($sql_find_id);
            $row = $result->fetch_assoc();
            $missingID = $row['missingID'] ?? 1;
            
            $sql_max_id = "SELECT MAX(CAST(SUBSTRING(jobPostID, 3) AS UNSIGNED)) AS maxID FROM jobPost";
            $result_max = $con->query($sql_max_id);
            $row_max = $result_max->fetch_assoc();
            $maxID = $row_max['maxID'] ?? 0;
            
            if ($missingID > $maxID) {
                $missingID = $maxID + 1;
            }
            
            return 'JP' . str_pad($missingID, 3, '0', STR_PAD_LEFT);
        } catch (Exception $e) {
            error_log("Error generating job post ID: " . $e->getMessage());
            return "";
        }
    }
}
?>
