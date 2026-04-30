# 🚀 Quick Start Guide - Using OOP Services

## What You Have Now

Your FlexMatch application now has a **professional OOP architecture** with:

✅ **6 Interface Definitions** (Service contracts)  
✅ **4 Working Service Implementations** (Ready to use)  
✅ **1 Example Refactored File** (jobSeeker_posting_list.php)  
✅ **Complete Documentation** (Guides & checklists)  

---

## Get Started in 5 Minutes

### Step 1: Open a File You Want to Refactor
Example: `Job Seeker/filter_jobs.php`

### Step 2: Add Service Imports
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');
```

### Step 3: Initialize Services
```php
    $dbService = new DatabaseService();
    $jobService = new JobPostingService($dbService);
```

### Step 4: Replace Database Queries
```php
    // OLD: $sql = "SELECT * FROM jobPost WHERE ..."; 
    // NEW:
    $jobs = $jobService->filterJobs([
        'location' => $_POST['location'],
        'minSalary' => $_POST['minSalary'],
        'maxSalary' => $_POST['maxSalary']
    ]);
```

### Step 5: Close Connection
```php
    $dbService->closeConnection();
?>
```

**Done!** Your file now uses OOP services.

---

## Service Methods Cheat Sheet

### JobPostingService
```php
$jobs = $jobService->getActiveJobs();
$jobs = $jobService->getJobsByEmployer($employerID);
$job = $jobService->getJobPostDetails($jobPostID);
$jobs = $jobService->filterJobs(['location' => 'KL', 'minSalary' => 10]);
$jobs = $jobService->searchJobs('marketing');
```

### RatingService
```php
$ratings = $ratingService->getRatingsByJobPost($jobPostID);
$avg = $ratingService->getAverageRating($jobPostID);
$history = $ratingService->getJobHistory($userID);
$id = $ratingService->submitRating($appID, ['rating' => 5, 'feedback' => '...']);
```

### JobApplicationService
```php
$appID = $appService->applyForJob($jobSeekerID, $jobPostID);
$apps = $appService->listApplications($jobPostID);
$apps = $appService->getApplicationsByJobSeeker($jobSeekerID);
$apps = $appService->getApplicationsByEmployer($employerID);
$appService->respondToApplication($appID, 'accepted', 'Welcome!');
$appService->cancelApplication($appID);
```

### DatabaseService
```php
$db = new DatabaseService();
$connection = $db->connectDatabase();
$result = $db->executeQuery("SELECT * FROM ...");
$stmt = $db->prepareStatement("SELECT * FROM ? WHERE ?");
$db->closeConnection();
```

---

## Real Example: Refactoring Filter Jobs

### BEFORE (Procedural)
```php
<?php
    include('../database/config.php');
    include('job_seeker_header.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $location = isset($_POST['location']) ? 
                    mysqli_real_escape_string($con, $_POST['location']) : '';
        $minSalary = isset($_POST['minSalary']) ? 
                     floatval($_POST['minSalary']) : 0;
        
        $sql = "SELECT * FROM jobPost WHERE endDate >= CURDATE()";
        
        if (!empty($location)) {
            $sql .= " AND location = '$location'";
        }
        if ($minSalary > 0) {
            $sql .= " AND salary >= $minSalary";
        }
        
        $result = $con->query($sql);
        $jobs = [];
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $jobs[] = $row;
            }
        }
    } else {
        $jobs = [];
    }
    
    $con->close();
?>
```

### AFTER (OOP with Services)
```php
<?php
    require_once '../services/DatabaseService.php';
    require_once '../services/JobPostingService.php';
    
    include('job_seeker_header.php');

    $dbService = new DatabaseService();
    $jobService = new JobPostingService($dbService);
    
    $jobs = [];
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $filters = [];
        
        if (!empty($_POST['location'])) {
            $filters['location'] = $_POST['location'];
        }
        
        if (isset($_POST['minSalary'])) {
            $filters['minSalary'] = floatval($_POST['minSalary']);
        }
        
        $jobs = $jobService->filterJobs($filters);
    }
    
    $dbService->closeConnection();
?>
```

**Much cleaner! No SQL queries in the view file.**

---

## Understanding the Architecture

### The Layers

```
┌─────────────────────────────────┐
│  YOUR PHP FILE (View Layer)     │
│  jobSeeker_posting_list.php     │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│  SERVICES (Business Logic)      │
│  JobPostingService              │
│  RatingService                  │
│  JobApplicationService          │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│  DATABASE SERVICE (Data Access) │
│  DatabaseService                │
└────────────┬────────────────────┘
             │
             ▼
┌─────────────────────────────────┐
│  DATABASE (MySQL)               │
│  flexmatch database             │
└─────────────────────────────────┘
```

### Key Benefits

| Before | After |
|--------|-------|
| SQL mixed with HTML | SQL in services only |
| Hard to test | Easy to mock services |
| Code duplication | DRY (Don't Repeat Yourself) |
| Tight coupling | Loose coupling |
| Hard to maintain | Easy to maintain |
| No error consistency | Consistent handling |

---

## Common Patterns

### Pattern 1: Get Data
```php
$jobs = $jobService->getActiveJobs();
foreach ($jobs as $job) {
    echo $job['jobTitle'];
}
```

### Pattern 2: Filter Data
```php
$jobs = $jobService->filterJobs([
    'location' => 'Selangor',
    'minSalary' => 12.00,
    'workingHour' => 'Day Shift'
]);
```

### Pattern 3: Enrich Data
```php
$jobs = $jobService->getActiveJobs();
foreach ($jobs as &$job) {
    // Add average rating to each job
    $job['avgRating'] = $ratingService->getAverageRating($job['jobPostID']);
}
```

### Pattern 4: Handle Not Found
```php
$job = $jobService->getJobPostDetails($jobPostID);
if ($job === null) {
    http_response_code(404);
    echo "Job not found";
    exit;
}
```

---

## When to Use Each Service

| Service | Use For |
|---------|---------|
| JobPostingService | Creating, retrieving, filtering job posts |
| RatingService | Ratings, reviews, job completion, history |
| JobApplicationService | Applications, responses, tracking |
| DatabaseService | Direct DB access (rarely needed) |
| AuthenticationService | Login, registration, auth (coming soon) |
| UserProfileService | User info, profiles (coming soon) |

---

## Troubleshooting

### "Class not found" Error
Make sure file paths are correct:
```php
require_once '../services/JobPostingService.php';  // ✅ Correct
require_once './services/JobPostingService.php';   // ❌ Wrong
```

### Empty Results
Check if the service returned an array:
```php
$jobs = $jobService->getActiveJobs();
echo count($jobs);  // Use count(), not if($jobs)
```

### Database Connection Error
Verify credentials in `DatabaseService.php`:
```php
$this->servername = "localhost";
$this->username = "root";
$this->password = "";
$this->dbname = "flexmatch";
```

---

## Files You Need to Know

### Read These First
1. **OOP_IMPLEMENTATION_SUMMARY.md** - Complete overview
2. **QUICK_REFERENCE_GUIDE.md** - Architecture diagrams
3. **UML_RESTRUCTURING_SUMMARY.md** - Component mapping

### For Refactoring
1. **OOP_REFACTORING_GUIDE.md** - Step-by-step patterns
2. **MIGRATION_CHECKLIST.md** - Verification checklist
3. **jobSeeker_posting_list.php** - Real example (already refactored)

### For Reference
1. `/interfaces/` folder - All interface definitions
2. `/services/` folder - All service implementations
3. `database/config.php` - Old way (deprecated)

---

## Next Steps

### Immediate (Today)
- [ ] Review `OOP_IMPLEMENTATION_SUMMARY.md`
- [ ] Look at the refactored example file
- [ ] Test that jobSeeker_posting_list.php works

### Short-term (This Week)
- [ ] Refactor 3-4 Priority 1 files
- [ ] Test each refactored file
- [ ] Document any issues

### Medium-term (Next 2 weeks)
- [ ] Refactor all Priority 1 files
- [ ] Start Priority 2 files
- [ ] Create missing services (Auth, UserProfile)

### Long-term (Month+)
- [ ] Refactor all files
- [ ] Add unit tests
- [ ] Implement service factory
- [ ] Create dependency injection container

---

## Pro Tips

### Tip 1: Use VSCode IntelliSense
PHPDoc comments help IntelliSense show you available methods:
```php
$jobService = new JobPostingService($db);
// Start typing $jobService-> and see all available methods!
```

### Tip 2: Debug with error_log()
When something's wrong:
```php
$jobs = $jobService->getActiveJobs();
error_log("Jobs count: " . count($jobs));  // Check in PHP error log
```

### Tip 3: Keep Services Focused
Each service does ONE thing. Don't mix concerns:
```php
// ✅ Good - Clear separation
$jobs = $jobService->getActiveJobs();
$ratings = $ratingService->getRatingsByJobPost($jobID);

// ❌ Bad - Mixing concerns
$jobsWithEverything = $jobService->getJobsWithRatingsAndApplications();
```

### Tip 4: Always Close Connection
Services auto-close, but be explicit:
```php
$dbService->closeConnection();  // Clean up
```

---

## Success Metrics

You'll know the refactoring is working when:

✅ No `mysqli_real_escape_string()` calls in view files  
✅ No direct `$con->query()` in view files  
✅ No SQL strings in view files  
✅ All database logic in services/  
✅ Services handle all error cases  
✅ Pages load without database errors  
✅ Code is more readable  
✅ Less code duplication  

---

## Quick Links

| Document | Purpose |
|----------|---------|
| OOP_IMPLEMENTATION_SUMMARY.md | Complete reference |
| OOP_REFACTORING_GUIDE.md | How to refactor |
| MIGRATION_CHECKLIST.md | Step-by-step checklist |
| QUICK_REFERENCE_GUIDE.md | Architecture diagrams |
| jobSeeker_posting_list.php | Real example |

---

## Questions?

- **How do I use a service?** → See "Service Methods Cheat Sheet"
- **How do I refactor a file?** → Follow "Real Example: Refactoring Filter Jobs"
- **What's the architecture?** → See "Understanding the Architecture"
- **Is my refactoring done?** → Check "MIGRATION_CHECKLIST.md"

---

## Summary

You now have:
- ✅ **Professional OOP architecture**
- ✅ **Ready-to-use services**
- ✅ **Clear examples**
- ✅ **Complete documentation**
- ✅ **Step-by-step guides**

**Start refactoring your files using the patterns above!** 🚀

