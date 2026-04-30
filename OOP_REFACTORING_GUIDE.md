# OOP Refactoring Guide - Implementation Pattern

## Overview

This guide shows how to refactor your existing PHP files to follow proper Object-Oriented Programming (OOP) principles using the service layer pattern.

---

## Example 1: jobSeeker_posting_list.php (COMPLETED ✅)

### BEFORE (Old Code - Procedural)
```php
<?php
    include('../database/config.php');
    include('job_seeker_header.php');

    // Direct database queries mixed with business logic
    $sql = "SELECT * FROM jobPost WHERE endDate >= CURDATE()";
    $result = $con->query($sql);
    
    $jobs = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Nested query for ratings - tight coupling
            $ratingSQL = "SELECT r.rating, r.feedback, js.fullName
                          FROM jobRating r
                          JOIN jobHistory h ON r.historyID = h.historyID
                          JOIN jobSeeker js ON r.userID = js.userID
                          WHERE h.jobPostID = ?";
            $stmt = $con->prepare($ratingSQL);
            $stmt->bind_param("s", $row['jobPostID']);
            $stmt->execute();
            $ratingResult = $stmt->get_result();
            $rating = $ratingResult->fetch_all(MYSQLI_ASSOC);

            $row['rating'] = $rating;
            $jobs[] = $row;
        }
    }
    
    $con->close();
?>
```

### AFTER (New Code - OOP with Services)
```php
<?php
    // Import service classes
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    require_once '../services/RatingService.php';
    
    // Include header
    include('job_seeker_header.php');

    // Initialize services using dependency injection
    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    $ratingService = new RatingService($dbService);
    
    // Get all active jobs using the job posting service
    $jobs = [];
    $activeJobs = $jobPostingService->getActiveJobs();
    
    // Enrich job data with ratings
    foreach ($activeJobs as $job) {
        $jobPostID = $job['jobPostID'];
        
        // Get ratings for this job post
        $ratings = $ratingService->getRatingsByJobPost($jobPostID);
        
        // Add ratings to job data
        $job['rating'] = $ratings;
        $jobs[] = $job;
    }
    
    // Close the database connection
    $dbService->closeConnection();
?>
```

### Benefits
✅ **Separation of Concerns** - Business logic separated from presentation  
✅ **Reusability** - Services can be used in multiple files  
✅ **Testability** - Services can be mocked for unit testing  
✅ **Maintainability** - Changes to database queries only affect service layer  
✅ **Dependency Injection** - Services are injected, not hardcoded  

---

## Refactoring Pattern

### Step 1: Identify the Services Needed
Look at the SQL queries and determine which service(s) they belong to:

| Service | Handles |
|---------|---------|
| `JobPostingService` | Job post queries (SELECT, INSERT, UPDATE, DELETE) |
| `RatingService` | Rating and review queries |
| `UserProfileService` | User profile queries |
| `JobApplicationService` | Job application queries |
| `CommunicationService` | Chat/message queries |
| `NotificationService` | Notification queries |

### Step 2: Import Services
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    require_once '../services/RatingService.php';
    // Import other services as needed
```

### Step 3: Initialize Services
```php
    // Initialize services using dependency injection
    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    $ratingService = new RatingService($dbService);
```

### Step 4: Replace Direct Queries
```php
    // OLD: Direct query
    // $sql = "SELECT * FROM jobPost WHERE endDate >= CURDATE()";
    // $result = $con->query($sql);
    
    // NEW: Use service method
    $jobs = $jobPostingService->getActiveJobs();
```

### Step 5: Close Connection
```php
    $dbService->closeConnection();
```

---

## Example 2: Filtering Jobs (Using Filters)

### Refactored Code
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');

    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    
    // Build filter criteria
    $filters = [];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (!empty($_POST['location'])) {
            $filters['location'] = $_POST['location'];
        }
        
        if (isset($_POST['minSalary'])) {
            $filters['minSalary'] = floatval($_POST['minSalary']);
        }
        
        if (isset($_POST['maxSalary'])) {
            $filters['maxSalary'] = floatval($_POST['maxSalary']);
        }
        
        if (!empty($_POST['workingHour'])) {
            $filters['workingHour'] = $_POST['workingHour'];
        }
    }
    
    // Get filtered jobs using the service
    $jobs = $jobPostingService->filterJobs($filters);
    
    $dbService->closeConnection();
?>
```

---

## Example 3: Searching Jobs

### Refactored Code
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');

    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    
    $jobs = [];
    
    if ($_SERVER["REQUEST_METHOD"] == "GET" && !empty($_GET['search'])) {
        $searchQuery = $_GET['search'];
        $jobs = $jobPostingService->searchJobs($searchQuery);
    } else {
        $jobs = $jobPostingService->getActiveJobs();
    }
    
    $dbService->closeConnection();
?>
```

---

## Service Methods Available

### JobPostingService
```php
// Get all active jobs (end date not reached)
$jobs = $jobPostingService->getActiveJobs();

// Get jobs by specific employer
$employerJobs = $jobPostingService->getJobsByEmployer($employerID);

// Get details of a specific job
$jobDetails = $jobPostingService->getJobPostDetails($jobPostID);

// Filter jobs by criteria (location, salary, shift, etc.)
$filteredJobs = $jobPostingService->filterJobs([
    'location' => 'Selangor',
    'minSalary' => 10.00,
    'maxSalary' => 20.00,
    'workingHour' => 'Day Shift'
]);

// Search jobs by keyword
$searchResults = $jobPostingService->searchJobs('marketing');

// List all jobs with optional filters
$allJobs = $jobPostingService->listJobPosts($filters);

// Validate if job post exists and is active
$isValid = $jobPostingService->validateJobPost($jobPostID);

// Create new job post (admin/employer only)
$newJobID = $jobPostingService->createJobPost($employerID, [
    'jobTitle' => 'Marketing Manager',
    'location' => 'Kuala Lumpur',
    'salary' => 15.50,
    'startDate' => '2026-05-01',
    // ... other fields
]);

// Update job post
$success = $jobPostingService->updateJobPost($jobPostID, $updatedData);

// Delete job post
$deleted = $jobPostingService->deleteJobPost($jobPostID);
```

### RatingService
```php
// Get all ratings for a job post
$ratings = $ratingService->getRatingsByJobPost($jobPostID);

// Get average rating for a job post
$avgRating = $ratingService->getAverageRating($jobPostID);

// Get job history for a user
$history = $ratingService->getJobHistory($userID);

// Submit a rating for a completed job
$ratingID = $ratingService->submitRating($jobApplicationID, [
    'rating' => 5,
    'feedback' => 'Great experience!',
    'userID' => $userID
]);

// Get rating for specific application
$rating = $ratingService->getRating($jobApplicationID);

// Get completion status
$status = $ratingService->getCompletionStatus($jobApplicationID);

// Mark job as completed
$completed = $ratingService->completeJob($jobApplicationID);
```

---

## Files Ready for Refactoring

### High Priority (Frontend/User-Facing)
- [x] `Job Seeker/jobSeeker_posting_list.php` - **COMPLETED**
- [ ] `Job Seeker/filter_jobs.php` - Uses JobPostingService
- [ ] `Job Seeker/job_history.php` - Uses RatingService
- [ ] `Job Seeker/rate_job.php` - Uses RatingService
- [ ] `Employer/job_posting_list.php` - Uses JobPostingService
- [ ] `Employer/job_posting_form.php` - Uses JobPostingService

### Medium Priority (Job Management)
- [ ] `Job Seeker/my_application.php` - Needs JobApplicationService
- [ ] `Employer/processApplication.php` - Needs JobApplicationService
- [ ] `Job Seeker/manageJob.php` - Uses JobPostingService
- [ ] `database/applyForJob.php` - Needs JobApplicationService

### Lower Priority (Advanced Features)
- [ ] `Employer/employer_chat.php` - Needs CommunicationService
- [ ] `Job Seeker/jobSeeker_chat.php` - Needs CommunicationService
- [ ] `Employer/report_form.php` - Needs ReportingService
- [ ] `Admin/admin.php` - Needs multiple services

---

## Testing the Refactoring

### Test 1: Verify Jobs Load Correctly
```php
<?php
    require_once 'services/DatabaseService.php';
    require_once 'services/JobPostingService.php';
    
    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    
    $jobs = $jobPostingService->getActiveJobs();
    
    echo "Total active jobs: " . count($jobs) . "\n";
    
    if (count($jobs) > 0) {
        echo "First job: " . $jobs[0]['jobTitle'] . "\n";
    }
    
    $dbService->closeConnection();
?>
```

### Test 2: Verify Filtering Works
```php
<?php
    require_once 'services/DatabaseService.php';
    require_once 'services/JobPostingService.php';
    
    $dbService = new DatabaseService();
    $jobPostingService = new JobPostingService($dbService);
    
    $filtered = $jobPostingService->filterJobs([
        'location' => 'Selangor',
        'minSalary' => 10.00
    ]);
    
    echo "Filtered results: " . count($filtered) . "\n";
    
    $dbService->closeConnection();
?>
```

### Test 3: Verify Ratings Load
```php
<?php
    require_once 'services/DatabaseService.php';
    require_once 'services/RatingService.php';
    
    $dbService = new DatabaseService();
    $ratingService = new RatingService($dbService);
    
    $ratings = $ratingService->getRatingsByJobPost('JP001');
    
    echo "Ratings for job: " . count($ratings) . "\n";
    
    if (count($ratings) > 0) {
        echo "First rating: " . $ratings[0]['rating'] . " stars\n";
    }
    
    $dbService->closeConnection();
?>
```

---

## Common Patterns

### Pattern 1: Get Data and Enrich It
```php
$jobPostingService = new JobPostingService($dbService);
$ratingService = new RatingService($dbService);

$jobs = $jobPostingService->getActiveJobs();

foreach ($jobs as &$job) {
    $job['ratings'] = $ratingService->getRatingsByJobPost($job['jobPostID']);
    $job['averageRating'] = $ratingService->getAverageRating($job['jobPostID']);
}
```

### Pattern 2: Filter and Sort
```php
$jobs = $jobPostingService->filterJobs([
    'location' => $_POST['location'],
    'minSalary' => $_POST['minSalary']
]);

// Optional: Sort in PHP if needed
usort($jobs, function($a, $b) {
    return $b['salary'] - $a['salary'];
});
```

### Pattern 3: Error Handling
```php
$jobPostingService = new JobPostingService($dbService);

$job = $jobPostingService->getJobPostDetails($jobPostID);

if ($job === null) {
    echo "Job not found";
} else {
    echo "Job title: " . $job['jobTitle'];
}
```

---

## Migration Checklist

When refactoring a file:
- [ ] Identify required services
- [ ] Add `require_once` for services and interfaces
- [ ] Initialize services with dependency injection
- [ ] Replace direct database queries with service methods
- [ ] Update error handling to use service responses
- [ ] Test the page functionality
- [ ] Close database connection at end
- [ ] Remove old `include('../database/config.php')`
- [ ] Update any JavaScript that depends on the data format

---

## Next Steps

1. **Create remaining services** for Authentication, User Profile, Job Application, Communication, Notification, Reporting
2. **Refactor high-priority files** (job listing, filtering, applications)
3. **Create unit tests** for each service
4. **Create dependency injection container** for easier service management
5. **Update all remaining files** to use services
6. **Performance optimization** with caching if needed

