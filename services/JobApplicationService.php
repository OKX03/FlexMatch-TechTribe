<?php
require_once __DIR__ . '/../interfaces/IJobApplicationService.php';
require_once __DIR__ . '/../interfaces/IDataAccessLayer.php';

/**
 * JobApplicationService Class
 * 
 * Implements the IJobApplicationService interface.
 * Handles job application operations: submission, retrieval, and response management.
 */
class JobApplicationService implements IJobApplicationService {
    
    private $db;
    
    /**
     * Constructor
     * 
     * @param IDataAccessLayer $db Database service instance
     */
    public function __construct(IDataAccessLayer $db) {
        $this->db = $db;
    }
    
    /**
     * Submit a new job application
     * 
     * @param string $jobSeekerID The ID of the job seeker
     * @param string $jobPostID The ID of the job post
     * @return string|null Application ID or null
     */
    public function applyForJob(string $jobSeekerID, string $jobPostID): ?string {
        try {
            // Check if already applied
            if ($this->hasApplied($jobSeekerID, $jobPostID)) {
                return null;
            }
            
            $con = $this->db->getConnection();
            
            $applicationID = 'APP' . time() . rand(1000, 9999);
            $applicationDate = date('Y-m-d H:i:s');
            $status = 'pending';
            
            $query = "INSERT INTO jobApplication 
                     (applicationID, jobSeekerID, jobPostID, applicationDate, status) 
                     VALUES (?, ?, ?, ?, ?)";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("sssss", $applicationID, $jobSeekerID, $jobPostID, $applicationDate, $status);
            
            if ($stmt->execute()) {
                $stmt->close();
                return $applicationID;
            }
            
            $stmt->close();
            return null;
        } catch (Exception $e) {
            error_log("Error applying for job: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Retrieve all applications for a job post
     * 
     * @param string $jobPostID The job post ID
     * @return array Array of applications
     */
    public function listApplications(string $jobPostID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT ja.*, js.fullName, js.phone, js.email 
                     FROM jobApplication ja
                     JOIN jobSeeker js ON ja.jobSeekerID = js.userID
                     WHERE ja.jobPostID = ?
                     ORDER BY ja.applicationDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $applications = [];
            
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
            
            $stmt->close();
            return $applications;
        } catch (Exception $e) {
            error_log("Error listing applications: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get application details
     * 
     * @param string $applicationID The application ID
     * @return array|null Application data or null
     */
    public function getApplicationDetails(string $applicationID): ?array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT ja.*, js.fullName, jp.jobTitle 
                     FROM jobApplication ja
                     JOIN jobSeeker js ON ja.jobSeekerID = js.userID
                     JOIN jobPost jp ON ja.jobPostID = jp.jobPostID
                     WHERE ja.applicationID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $applicationID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $application = $result->fetch_assoc();
            $stmt->close();
            
            return $application;
        } catch (Exception $e) {
            error_log("Error getting application details: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Respond to application
     * 
     * @param string $applicationID Application ID
     * @param string $status Response status
     * @param string $comments Optional comments
     * @return bool True if successful
     */
    public function respondToApplication(string $applicationID, string $status, string $comments = ''): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "UPDATE jobApplication SET status = ?, employerComments = ? WHERE applicationID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("sss", $status, $comments, $applicationID);
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error responding to application: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cancel application
     * 
     * @param string $applicationID Application ID
     * @return bool True if successful
     */
    public function cancelApplication(string $applicationID): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "UPDATE jobApplication SET status = 'cancelled' WHERE applicationID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $applicationID);
            
            $result = $stmt->execute();
            $stmt->close();
            
            return $result;
        } catch (Exception $e) {
            error_log("Error cancelling application: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get applications by job seeker
     * 
     * @param string $jobSeekerID Job seeker ID
     * @return array Array of applications
     */
    public function getApplicationsByJobSeeker(string $jobSeekerID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT ja.*, jp.jobTitle, jp.location, jp.salary 
                     FROM jobApplication ja
                     JOIN jobPost jp ON ja.jobPostID = jp.jobPostID
                     WHERE ja.jobSeekerID = ?
                     ORDER BY ja.applicationDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobSeekerID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $applications = [];
            
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
            
            $stmt->close();
            return $applications;
        } catch (Exception $e) {
            error_log("Error getting applications by job seeker: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get applications by employer
     * 
     * @param string $employerID Employer ID
     * @return array Array of applications
     */
    public function getApplicationsByEmployer(string $employerID): array {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT ja.*, jp.jobTitle, js.fullName 
                     FROM jobApplication ja
                     JOIN jobPost jp ON ja.jobPostID = jp.jobPostID
                     JOIN jobSeeker js ON ja.jobSeekerID = js.userID
                     WHERE jp.userID = ?
                     ORDER BY ja.applicationDate DESC";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $employerID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $applications = [];
            
            while ($row = $result->fetch_assoc()) {
                $applications[] = $row;
            }
            
            $stmt->close();
            return $applications;
        } catch (Exception $e) {
            error_log("Error getting applications by employer: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if already applied
     * 
     * @param string $jobSeekerID Job seeker ID
     * @param string $jobPostID Job post ID
     * @return bool True if already applied
     */
    public function hasApplied(string $jobSeekerID, string $jobPostID): bool {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT COUNT(*) as count FROM jobApplication 
                     WHERE jobSeekerID = ? AND jobPostID = ? AND status != 'cancelled'";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $jobSeekerID, $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking if applied: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get application status
     * 
     * @param string $applicationID Application ID
     * @return string Status string
     */
    public function getApplicationStatus(string $applicationID): string {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT status FROM jobApplication WHERE applicationID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $applicationID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row ? $row['status'] : 'unknown';
        } catch (Exception $e) {
            error_log("Error getting application status: " . $e->getMessage());
            return 'unknown';
        }
    }
    
    /**
     * Get application count
     * 
     * @param string $jobPostID Job post ID
     * @return int Number of applications
     */
    public function getApplicationCount(string $jobPostID): int {
        try {
            $con = $this->db->getConnection();
            
            $query = "SELECT COUNT(*) as count FROM jobApplication WHERE jobPostID = ?";
            
            $stmt = $con->prepare($query);
            $stmt->bind_param("s", $jobPostID);
            $stmt->execute();
            
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return intval($row['count']);
        } catch (Exception $e) {
            error_log("Error getting application count: " . $e->getMessage());
            return 0;
        }
    }
}
?>
