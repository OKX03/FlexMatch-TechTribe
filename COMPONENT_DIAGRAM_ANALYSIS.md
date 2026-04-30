# FlexMatch UML 2.0 Component Diagram Analysis

## Architecture Overview

This document provides a restructured component diagram following UML 2.0 standards with identified interface classes.

---

## Identified Interface Classes (Contracts)

### 1. **IAuthenticationService** (Authentication Subsystem)
**Files:** `login.php`, `register.php`, `logout.php`

**Provided Interface Operations:**
- `authenticate(username, password): boolean`
- `register(userDetails): userID`
- `logout(userID): void`
- `validateSession(userID): boolean`

**Dependent Components:** All subsystems depend on this for user verification

---

### 2. **IUserProfileService** (User Management Subsystem)
**Files:** 
- Admin: `Admin/create_employer_profile.php`, `Admin/view_employer_profile.php`, `Admin/view_jobseeker_profile.php`
- Employer: `Employer/create_employer_profile.php`, `Employer/update_employer_profile.php`, `Employer/view_employer_profile.php`
- JobSeeker: `Job Seeker/create_jobseeker_profile.php`, `Job Seeker/delete_jobseeker_profile.php`

**Provided Interface Operations:**
- `createProfile(userID, profileData): profileID`
- `updateProfile(profileID, profileData): boolean`
- `viewProfile(profileID): profileData`
- `deleteProfile(profileID): boolean`
- `validateProfile(profileID): boolean`
- `getRoleFromUserID(userID): role`

---

### 3. **IJobPostingService** (Job Management Subsystem)
**Files:** 
- `Employer/job_posting_form.php`
- `Employer/job_posting_list.php`
- `Employer/edit_job_posting.php`
- `Job Seeker/jobSeeker_posting_list.php`
- `Admin/viewJobPost.php`

**Provided Interface Operations:**
- `createJobPost(jobData): jobPostID`
- `updateJobPost(jobPostID, jobData): boolean`
- `deleteJobPost(jobPostID): boolean`
- `listJobPosts(filters): jobPosts[]`
- `getJobPostDetails(jobPostID): jobPostData`
- `validateJobPost(jobPostID): boolean`
- `filterJobs(criteria): jobPosts[]`

---

### 4. **IJobApplicationService** (Job Application Subsystem)
**Files:**
- `database/applyForJob.php`
- `Employer/processApplication.php`
- `Employer/response_application.php`
- `Job Seeker/my_application.php`
- `Job Seeker/processResponse.php`

**Provided Interface Operations:**
- `applyForJob(jobSeekerID, jobPostID): applicationID`
- `listApplications(jobPostID): applications[]`
- `getApplicationDetails(applicationID): applicationData`
- `respondToApplication(applicationID, status): boolean`
- `cancelApplication(applicationID): boolean`

---

### 5. **ICommunicationService** (Communication Subsystem)
**Files:**
- `Employer/employer_chat.php`
- `Job Seeker/jobSeeker_chat.php`
- `database/chat.php`
- `database/employer_filter_chat.php`
- `database/employer_search_chat.php`
- `database/jobSeeker_filter_chat.php`
- `database/jobSeeker_search_chat.php`
- `database/jobSeekerChat.php`

**Provided Interface Operations:**
- `sendMessage(senderID, receiverID, messageData): messageID`
- `getConversation(userID1, userID2): messages[]`
- `listChats(userID): conversations[]`
- `filterChats(userID, criteria): conversations[]`
- `searchChats(userID, query): messages[]`
- `markAsRead(messageID): boolean`

---

### 6. **INotificationService** (Notification Subsystem)
**Files:**
- `notification/notification.php`

**Provided Interface Operations:**
- `sendNotification(userID, notificationData): notificationID`
- `fetchNotifications(userID): notifications[]`
- `markAsRead(notificationID): boolean`
- `getUnreadCount(userID): integer`

---
a
### 7. **IReportingService** (Moderation & Reporting Subsystem)
**Files:**
- `Employer/report_form.php`
- `Admin/reviewReport.php`
- `Admin/getReportDetails.php`
- `Admin/accountIssueList.php`
- `Admin/updateWarning.php`
- `Admin/suspendAccount.php`

**Provided Interface Operations:**
- `submitReport(reporterID, reportedUserID, reportData): reportID`
- `listReports(): reports[]`
- `getReportDetails(reportID): reportData`
- `updateReportStatus(reportID, status): boolean`
- `issueWarning(userID, warningData): warningID`
- `suspendAccount(userID, duration): boolean`

---

### 8. **IRatingService** (Job Completion & Rating Subsystem)
**Files:**
- `Job Seeker/rate_job.php`
- `Job Seeker/rate_complete.php`
- `Job Seeker/job_history.php`
- `Employer/viewReportStatus.php`

**Provided Interface Operations:**
- `submitRating(jobApplicationID, ratingData): ratingID`
- `getRating(jobApplicationID): ratingData`
- `getJobHistory(userID): jobHistory[]`
- `getCompletionStatus(jobApplicationID): status`

---

### 9. **IWallPostService** (Social/Wall Subsystem)
**Files:**
- `Employer/display_wall_post.php`
- `Job Seeker/create_wall_post.php`
- `Job Seeker/display_wall_post.php`
- `Job Seeker/edit_wall_post.php`
- `Job Seeker/delete_wall_post.php`
- `Job Seeker/my_posts.php`
- `Job Seeker/display_my_post.php`

**Provided Interface Operations:**
- `createWallPost(userID, postData): postID`
- `updateWallPost(postID, postData): boolean`
- `deleteWallPost(postID): boolean`
- `getWallPosts(userID): posts[]`
- `displayWallPosts(filters): posts[]`

---

### 10. **IDataAccessLayer** (Database Access)
**Files:**
- `database/config.php` - Main database configuration and connection interface

**Provided Interface Operations:**
- `connectDatabase(): connection`
- `executeQuery(query): result`
- `prepareStatement(query): statement`
- `closeConnection(): void`

---

## Component Dependencies Map

```
┌─────────────────────────────────────────────────────────────────┐
│                     CORE LAYER                                  │
│                 (Database & Configuration)                      │
│              [IDataAccessLayer Component]                       │
└─────────────────────────────────────────────────────────────────┘
                            ▲
         ┌──────────────────┼──────────────────┐
         │                  │                  │
┌────────┴────────┐  ┌──────┴──────┐  ┌───────┴────────┐
│  AUTHENTICATION │  │ USER MGMT   │  │  MODERATION    │
│   [IAuth Svc]   │  │[IUserProf]  │  │[IReport Svc]   │
└─────────────────┘  └─────────────┘  └────────────────┘
         ▲                  ▲                  ▲
         │                  │                  │
         └──────────────────┼──────────────────┘
                            │
    ┌───────────────────────┼───────────────────────┐
    │                       │                       │
┌───┴────────┐    ┌─────────┴──────┐     ┌────────┴───────┐
│   JOB MGT  │    │  COMMUNICATION │     │   NOTIFICATION │
│[IJobPost]  │    │[IComm Service] │     │[INotif Service]│
│[IJobApp]   │    │[IWallPost]     │     └────────────────┘
└────────────┘    └────────────────┘
    │
┌───┴────────────┐
│  RATING SYSTEM │
│[IRating Svc]   │
└────────────────┘
```

---

## UML 2.0 Component Diagram Notation Rules Applied

1. **Component Symbols**: Represented with component stereotype `<<component>>`
2. **Interface Notation**: 
   - Lollipop notation (circle) for provided interfaces
   - Socket notation (semicircle) for required interfaces
3. **Dependencies**: Dashed arrows showing "depends on"
4. **Ports**: Components can have multiple ports for different interfaces
5. **Subsystems**: Grouped related components within larger containers

---

## Data Flow & Component Interactions

### User Login Flow:
```
Login/Register Page → IAuthenticationService → IUserProfileService → Session Management
```

### Job Posting Flow:
```
Employer Dashboard → IJobPostingService → Job Database → List Display
```

### Job Application Flow:
```
JobSeeker → IJobApplicationService → INotificationService → Employer Notification
```

### Communication Flow:
```
User A → ICommunicationService → INotificationService → User B
```

### Moderation Flow:
```
Reporter → IReportingService → Admin Dashboard → IUserProfileService (Suspend)
```

---

## Key Architecture Principles

1. **Separation of Concerns**: Each interface handles a specific business domain
2. **Dependency Inversion**: Components depend on abstractions (interfaces), not concrete implementations
3. **Single Responsibility**: Each service has one primary reason to change
4. **Scalability**: Adding new features doesn't require modifying existing interfaces
5. **Reusability**: Interfaces can be consumed by multiple components

---

## Recommendations for Implementation

1. **Create PHP Interface Classes**:
   ```php
   interface IAuthenticationService {
       public function authenticate($username, $password);
       public function register($userData);
       public function logout($userID);
   }
   ```

2. **Use Dependency Injection**: Pass services through constructors
3. **Implement Service Layer Pattern**: Create service classes that implement these interfaces
4. **Add Configuration Management**: Centralize configuration in the database layer
5. **Error Handling**: Implement proper exception handling for each service

