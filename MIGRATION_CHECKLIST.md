# Migration Checklist - Procedural to OOP

This document provides a step-by-step checklist for migrating your PHP files from procedural code to OOP using the service layer pattern.

---

## Pre-Migration Checklist

- [ ] Review the `OOP_IMPLEMENTATION_SUMMARY.md` document
- [ ] Review the `OOP_REFACTORING_GUIDE.md` document
- [ ] Understand the service classes in `/services/` directory
- [ ] Understand the interfaces in `/interfaces/` directory
- [ ] Test the example: `Job Seeker/jobSeeker_posting_list.php`

---

## Migration Process for Each File

### Step 1: Analyze the File
```
[ ] List all SQL queries in the file
[ ] Identify which service each query belongs to
[ ] Check for repeated queries (code duplication)
[ ] Document any custom business logic
```

### Step 2: Identify Required Services
```
Chart out the queries and map them:

SQL Query                          → Service
SELECT * FROM jobPost              → JobPostingService
SELECT * FROM jobRating            → RatingService
SELECT * FROM jobApplication       → JobApplicationService
SELECT * FROM login                → AuthenticationService
SELECT * FROM employer              → UserProfileService
```

### Step 3: Update Includes
```
OLD:
<?php
    include('../database/config.php');
    include('../footer/footer.php');
?>

NEW:
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    require_once '../services/RatingService.php';
    include('../footer/footer.php');
?>
```

### Step 4: Initialize Services
```php
// Initialize database service first
$dbService = new DatabaseService();

// Initialize dependent services
$jobPostingService = new JobPostingService($dbService);
$ratingService = new RatingService($dbService);
$jobApplicationService = new JobApplicationService($dbService);
```

### Step 5: Replace Queries with Service Calls
```
BEFORE:
$sql = "SELECT * FROM jobPost WHERE endDate >= CURDATE()";
$result = $con->query($sql);
$jobs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $jobs[] = $row;
    }
}

AFTER:
$jobs = $jobPostingService->getActiveJobs();
```

### Step 6: Test the File
```
[ ] Load the page in browser
[ ] Verify all data displays correctly
[ ] Check for PHP errors/warnings
[ ] Test filtering/searching if applicable
[ ] Test form submissions if applicable
```

### Step 7: Code Review
```
[ ] Remove all direct database calls
[ ] Remove $con->close() (service closes connection)
[ ] Verify all variables are properly initialized
[ ] Check for unused includes
[ ] Add error handling where needed
```

### Step 8: Commit Changes
```bash
git add <filename>
git commit -m "Refactor: Migrate <filename> to OOP service layer"
```

---

## File Priority List

### Priority 1: CRITICAL (Used on many pages)
```
Status  File                                Service
[ ] ✅  Job Seeker/jobSeeker_posting_list.php     JobPostingService, RatingService
[ ]    Employer/job_posting_list.php             JobPostingService
[ ]    Job Seeker/filter_jobs.php                JobPostingService
[ ]    Employer/job_posting_form.php             JobPostingService
[ ]    Employer/edit_job_posting.php             JobPostingService
```

### Priority 2: HIGH (Important functionality)
```
Status  File                                Service
[ ]    Job Seeker/my_application.php              JobApplicationService
[ ]    Employer/processApplication.php           JobApplicationService
[ ]    Employer/response_application.php         JobApplicationService
[ ]    Job Seeker/rate_job.php                   RatingService
[ ]    Job Seeker/rate_complete.php              RatingService
[ ]    Job Seeker/job_history.php                RatingService
```

### Priority 3: MEDIUM (Feature support)
```
Status  File                                Service
[ ]    Employer/employer_chat.php                CommunicationService
[ ]    Job Seeker/jobSeeker_chat.php            CommunicationService
[ ]    Employer/report_form.php                 ReportingService
[ ]    Job Seeker/create_wall_post.php          WallPostService
[ ]    Job Seeker/display_wall_post.php         WallPostService
```

### Priority 4: LOW (Admin/Optional)
```
Status  File                                Service
[ ]    Admin/admin_dashboard.php                  Multiple
[ ]    Admin/reviewReport.php                    ReportingService
[ ]    Admin/suspendAccount.php                  UserProfileService
[ ]    login.php                                 AuthenticationService
[ ]    register.php                              AuthenticationService
```

---

## Detailed Refactoring Examples

### Example 1: Job Posting List Page

**File:** `Employer/job_posting_list.php`

**Checklist:**
```
[ ] Find all queries related to jobPost
[ ] Find all queries related to jobApplication
[ ] Map to JobPostingService and JobApplicationService
[ ] Replace queries with service calls
[ ] Test all filters work
[ ] Verify counts display correctly
```

**Migration Code:**
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    require_once '../services/JobApplicationService.php';
    
    include('employer_header.php');
    
    $dbService = new DatabaseService();
    $jobService = new JobPostingService($dbService);
    $appService = new JobApplicationService($dbService);
    
    $employerID = $_SESSION['userID'];
    
    // Get employer's job postings
    $jobs = $jobService->getJobsByEmployer($employerID);
    
    // Enrich with application counts
    foreach ($jobs as &$job) {
        $job['applicationCount'] = $appService->getApplicationCount($job['jobPostID']);
    }
    
    $dbService->closeConnection();
?>
```

### Example 2: Filter Jobs Page

**File:** `Job Seeker/filter_jobs.php`

**Migration Code:**
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');
    
    $dbService = new DatabaseService();
    $jobService = new JobPostingService($dbService);
    
    $jobs = [];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $filters = [
            'location' => $_POST['location'] ?? '',
            'minSalary' => isset($_POST['minSalary']) ? floatval($_POST['minSalary']) : null,
            'maxSalary' => isset($_POST['maxSalary']) ? floatval($_POST['maxSalary']) : null,
            'workingHour' => $_POST['workingHour'] ?? ''
        ];
        
        // Remove null/empty values
        $filters = array_filter($filters);
        
        $jobs = $jobService->filterJobs($filters);
    } else {
        $jobs = $jobService->getActiveJobs();
    }
    
    $dbService->closeConnection();
?>
```

### Example 3: My Applications Page

**File:** `Job Seeker/my_application.php`

**Migration Code:**
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobApplicationService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');
    
    $dbService = new DatabaseService();
    $appService = new JobApplicationService($dbService);
    $jobService = new JobPostingService($dbService);
    
    $jobSeekerID = $_SESSION['userID'];
    
    // Get all applications from this job seeker
    $applications = $appService->getApplicationsByJobSeeker($jobSeekerID);
    
    // Enrich with job details
    foreach ($applications as &$app) {
        $app['jobDetails'] = $jobService->getJobPostDetails($app['jobPostID']);
    }
    
    $dbService->closeConnection();
?>
```

---

## Common Patterns

### Pattern: Filter + Enrich
```php
// Get filtered data
$jobs = $jobService->filterJobs($criteria);

// Enrich with additional data
foreach ($jobs as &$job) {
    $job['rating'] = $ratingService->getAverageRating($job['jobPostID']);
    $job['applicants'] = $appService->getApplicationCount($job['jobPostID']);
}
```

### Pattern: Validate + Create
```php
// Validate input
if (empty($data['jobTitle']) || $data['salary'] <= 0) {
    $error = "Invalid job data";
} else {
    // Create using service
    $jobID = $jobService->createJobPost($employerID, $data);
    if ($jobID) {
        $success = "Job posted successfully";
    } else {
        $error = "Failed to post job";
    }
}
```

### Pattern: Handle Not Found
```php
$job = $jobService->getJobPostDetails($jobPostID);

if ($job === null) {
    http_response_code(404);
    echo "Job not found";
    exit;
}

// Process job...
```

---

## Troubleshooting Migration Issues

### Issue: "Call to undefined method"
```
ERROR: Call to undefined method JobPostingService::someMethod()

FIX: Check the interface in /interfaces/IJobPostingService.php
     Make sure the method you're calling exists
     Check for typos in method names
```

### Issue: Empty results after migration
```
CAUSE: Service returns empty array instead of NULL
FIX: Check if count($results) > 0 instead of checking if ($results)
     Verify the database query in the service
     Add error logging to debug
```

### Issue: Database connection fails
```
CAUSE: DatabaseService credentials don't match
FIX: Verify in DatabaseService.php:
     - servername = "localhost"
     - username = "root"
     - password = ""
     - dbname = "flexmatch"
     
     These should match your MySQL setup
```

### Issue: Variable undefined in HTML
```
CAUSE: Variable set in old procedural code, not set in new service code
FIX: Make sure service call populates all needed variables
     Example: $jobs = $jobService->getActiveJobs();
```

---

## Testing Checklist for Each File

After migrating a file, verify:

```
FUNCTIONALITY TESTS
[ ] Page loads without errors
[ ] All data displays correctly
[ ] Data matches what was shown before
[ ] Links/buttons work properly
[ ] Forms submit correctly
[ ] Error messages display
[ ] Success messages display

FILTER/SEARCH TESTS
[ ] Filters work correctly
[ ] Search returns relevant results
[ ] Filters can be cleared
[ ] Results update when filters change

DATABASE TESTS
[ ] No SQL errors in PHP error log
[ ] Database connection closes properly
[ ] No queries run after close
[ ] Transaction handling works (if applicable)

PERFORMANCE TESTS
[ ] Page loads in < 1 second
[ ] No N+1 query problems
[ ] Memory usage reasonable
```

---

## Validation Checklist

Before considering a file "done":

```
CODE QUALITY
[ ] No direct $con->query() calls remain
[ ] No direct include('../database/config.php')
[ ] All SQL in service layer only
[ ] No $con->close() in file
[ ] Proper error handling
[ ] No deprecated functions

SECURITY
[ ] No hardcoded credentials
[ ] All user input validated
[ ] Prepared statements used (in service)
[ ] No SQL injection vulnerabilities
[ ] Session validation present

DOCUMENTATION
[ ] Comments explain complex logic
[ ] Function purposes clear
[ ] Error handling documented
[ ] Service dependencies clear

TESTING
[ ] All functionality tested
[ ] Edge cases handled
[ ] Error paths tested
[ ] No PHP warnings/notices
```

---

## Files Status Tracker

Copy this table and update as you migrate:

```
File                                Priority  Status    Date Done  Notes
Job Seeker/jobSeeker_posting_list    1         DONE      2026-04-30 ✅ Example
Employer/job_posting_list            1         TODO
Job Seeker/filter_jobs               1         TODO
Employer/job_posting_form            1         TODO
Job Seeker/my_application            2         TODO
Employer/processApplication          2         TODO
Job Seeker/rate_job                  2         TODO
Job Seeker/job_history               2         TODO
Employer/employer_chat               3         TODO
Job Seeker/jobSeeker_chat           3         TODO
Admin/admin_dashboard                4         TODO
login.php                            4         TODO
register.php                         4         TODO
```

---

## Quick Reference: Service Methods

### JobPostingService
```
getActiveJobs()                    - Get active jobs
getJobsByEmployer(id)              - Get employer's jobs
getJobPostDetails(id)              - Get job details
filterJobs(criteria)               - Filter by criteria
searchJobs(query)                  - Search jobs
validateJobPost(id)                - Check if valid
listJobPosts(filters)              - List all jobs
createJobPost(empID, data)         - Create job
updateJobPost(id, data)            - Update job
deleteJobPost(id)                  - Delete job
```

### JobApplicationService
```
applyForJob(seekerID, jobID)       - Apply for job
listApplications(jobID)            - Get applications for job
getApplicationsByJobSeeker(id)     - Get my applications
getApplicationsByEmployer(id)      - Get received applications
getApplicationDetails(id)          - Get application details
respondToApplication(id, status)   - Respond to application
cancelApplication(id)              - Cancel application
hasApplied(seekerID, jobID)       - Check if applied
getApplicationStatus(id)           - Get status
getApplicationCount(jobID)         - Count applications
```

### RatingService
```
getRatingsByJobPost(jobID)         - Get ratings for job
getAverageRating(jobID)            - Get average rating
getRating(appID)                   - Get specific rating
getJobHistory(userID)              - Get job history
submitRating(appID, data)          - Submit rating
getCompletionStatus(appID)         - Get status
completeJob(appID)                 - Mark complete
```

### DatabaseService
```
connectDatabase()                  - Connect to DB
getConnection()                    - Get mysqli object
executeQuery(query)                - Execute SQL
prepareStatement(query)            - Prepare statement
escapeString(string)               - Escape string
closeConnection()                  - Close connection
getLastError()                     - Get last error
```

---

## Success Criteria

Your migration is complete when:

✅ All critical files refactored (Priority 1-2)  
✅ All unit tests passing  
✅ No direct database calls remain  
✅ All services implemented  
✅ Performance improved or maintained  
✅ Code coverage > 80%  
✅ Team trained on OOP pattern  
✅ Documentation updated  

---

## Getting Help

If stuck on a migration:

1. **Review the example:** `Job Seeker/jobSeeker_posting_list.php`
2. **Check the guide:** `OOP_REFACTORING_GUIDE.md`
3. **Review the service:** Look at the service implementation
4. **Check tests:** Look at how service is tested
5. **Debug:** Add `var_dump()` or `error_log()` to see service results

