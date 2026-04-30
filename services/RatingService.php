<?php
require_once __DIR__ . '/../interfaces/IRatingService.php';
require_once __DIR__ . '/../interfaces/IDataAccessLayer.php';

/**
 * RatingService Class
 * 
 * Implements the IRatingService interface.
 * Handles job rating, review, and job history operations.
 * 
 * Usage:
 *   $db = new DatabaseService();
 *   $ratingService = new RatingService($db);
 *   $ratings = $ratingService->getRatingsByJobPost($jobPostID);
 */
class RatingService implements IRatingService {
    
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
     * Submit a rating for a completed job
     * 
     * @param string $jobApplicationID The ID of the job application being rated
     * @param array $ratingData Contains: rating (1-5), feedback (optional), userID
     * @return string The ID of the new rating record, empty string on failure
     */
    public function submitRating(string $jobApplicationID, array $ratingData): string {
        try {
            $con = $this->db->getConnection();
            
            // Get history ID from application
            $query = "SELECT historyID FROM jobApplication WHERE applicationID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobApplicationID);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            if (!$row) {
                return "";
            }
            
            $historyID = $row['historyID'];
            $rating = intval($ratingData['rating'] ?? 0);
            $feedback = isset($ratingData['feedback']) ? $ratingData['feedback'] : null;
            $userID = $ratingData['userID'] ?? '';
            
            // Generate rating ID
            $ratingID = 'R' . time() . rand(1000, 9999);
            
            $query = "INSERT INTO jobRating (ratingID, historyID, userID, rating, feedback) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("sssip", $ratingID, $historyID, $userID, $rating, $feedback);
            
            if ($stmt->execute()) {
                $stmt->close();
                return $ratingID;
            }
            
            $stmt->close();
            return "";
        } catch (Exception $e) {
            error_log("Error submitting rating: " . $e->getMessage());
            return "";
        }
    }
    
    /**
     * Get all ratings for a specific job posting
     * 
     * @param string $jobPostID The ID of the job post
     * @return array Array of rating records
     */
    public function getRatingsByJobPost(string $jobPostID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT r.rating, r.feedback, js.fullName
                     FROM jobRating r
                     JOIN jobHistory h ON r.historyID = h.historyID
                     JOIN jobSeeker js ON r.userID = js.userID
                     WHERE h.jobPostID = ?
                     ORDER BY r.ratingID DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $ratings = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $ratings[] = $row;
                }
            }
            
            $stmt->close();
            return $ratings;
        } catch (Exception $e) {
            error_log("Error getting ratings by job post: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get rating details for a specific application
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return array|null Rating details or null
     */
    public function getRating(string $jobApplicationID): ?array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT r.* FROM jobRating r
                     JOIN jobHistory h ON r.historyID = h.historyID
                     WHERE h.applicationID = ?
                     LIMIT 1";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobApplicationID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $rating = $result->fetch_assoc();
            $stmt->close();
            
            return $rating;
        } catch (Exception $e) {
            error_log("Error getting rating: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get job history for a user (all completed jobs)
     * 
     * @param string $userID The user's ID
     * @return array Array of completed job records
     */
    public function getJobHistory(string $userID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT h.*, jp.jobTitle, jp.location, jp.salary
                     FROM jobHistory h
                     JOIN jobPost jp ON h.jobPostID = jp.jobPostID
                     WHERE h.jobSeekerID = ?
                     ORDER BY h.endDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $userID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $history = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row;
                }
            }
            
            $stmt->close();
            return $history;
        } catch (Exception $e) {
            error_log("Error getting job history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get completion status of a job application
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return string Status string
     */
    public function getCompletionStatus(string $jobApplicationID): string {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT status FROM jobApplication WHERE applicationID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobApplicationID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? $row['status'] : 'unknown';
        } catch (Exception $e) {
            error_log("Error getting completion status: " . $e->getMessage());
            return 'unknown';
        }
    }
    
    /**
     * Mark a job application as completed
     * 
     * @param string $jobApplicationID The ID of the job application
     * @return bool True if successful, false otherwise
     */
    public function completeJob(string $jobApplicationID): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "UPDATE jobApplication SET status = 'completed' WHERE applicationID = ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobApplicationID);
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error completing job: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get average rating for a job post
     * 
     * @param string $jobPostID The ID of the job post
     * @return float Average rating (0-5)
     */
    public function getAverageRating(string $jobPostID): float {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT AVG(r.rating) as avgRating
                     FROM jobRating r
                     JOIN jobHistory h ON r.historyID = h.historyID
                     WHERE h.jobPostID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return floatval($row['avgRating'] ?? 0);
        } catch (Exception $e) {
            error_log("Error getting average rating: " . $e->getMessage());
            return 0.0;
        }
    }
}
?>
