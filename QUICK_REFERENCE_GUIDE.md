# FlexMatch Component Diagram - Quick Reference Guide

## 📊 System Architecture at a Glance

```
┌─────────────────────────────────────────────────────────────────────────┐
│                       FLEXMATCH ARCHITECTURE                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ╔═══════════════════════════════════════════════════════════════════╗  │
│  ║                    PRESENTATION LAYER                            ║  │
│  ║   (HTML/PHP Pages - User Interface)                             ║  │
│  ╚═══════════════════════════════════════════════════════════════════╝  │
│                              ▼                                           │
│  ╔═══════════════════════════════════════════════════════════════════╗  │
│  ║               COMPONENT/SERVICE LAYER                            ║  │
│  ║                                                                  ║  │
│  ║  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          ║  │
│  ║  │ IAuthService │  │ IUserProfile │  │ IJobPosting  │  ...     ║  │
│  ║  └──────────────┘  └──────────────┘  └──────────────┘          ║  │
│  ║                                                                  ║  │
│  ║  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          ║  │
│  ║  │ IJobApplic   │  │ IComm        │  │ INotification│  ...     ║  │
│  ║  └──────────────┘  └──────────────┘  └──────────────┘          ║  │
│  ║                                                                  ║  │
│  ║  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          ║  │
│  ║  │ IReporting   │  │ IRating      │  │ IWallPost    │  ...     ║  │
│  ║  └──────────────┘  └──────────────┘  └──────────────┘          ║  │
│  ║                                                                  ║  │
│  ╚═══════════════════════════════════════════════════════════════════╝  │
│                              ▼                                           │
│  ╔═══════════════════════════════════════════════════════════════════╗  │
│  ║               DATA ACCESS LAYER                                  ║  │
│  ║          (IDataAccessLayer - Database Operations)               ║  │
│  ╚═══════════════════════════════════════════════════════════════════╝  │
│                              ▼                                           │
│  ╔═══════════════════════════════════════════════════════════════════╗  │
│  ║                  DATABASE LAYER                                  ║  │
│  ║            (MySQL - flexmatch database)                         ║  │
│  ╚═══════════════════════════════════════════════════════════════════╝  │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## 🔑 10 Core Service Interfaces

### Layer 1: Authentication & Security
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ IAuthenticationService             │
├────────────────────────────────────┤
│ + authenticate()                   │
│ + register()                       │
│ + logout()                         │
│ + validateSession()                │
│ + getRole()                        │
└────────────────────────────────────┘
        △
        │
        ├─implements──┬──login.php
        │             ├──register.php
        │             └──logout.php
        │
        └─requires──► IDataAccessLayer
```

### Layer 2: User Management
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ IUserProfileService                │
├────────────────────────────────────┤
│ + createProfile()                  │
│ + updateProfile()                  │
│ + viewProfile()                    │
│ + deleteProfile()                  │
│ + validateProfile()                │
└────────────────────────────────────┘
        △
        ├─implements──┬──Admin/*
        │             ├──Employer/*
        │             └──Job Seeker/*
        │
        └─requires──► IDataAccessLayer
```

### Layer 3: Job Management
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ IJobPostingService                 │
├────────────────────────────────────┤
│ + createJobPost()                  │
│ + updateJobPost()                  │
│ + deleteJobPost()                  │
│ + listJobPosts()                   │
│ + filterJobs()                     │
└────────────────────────────────────┘
        △
        ├─implements──┬──job_posting_form.php
        │             ├──job_posting_list.php
        │             └──edit_job_posting.php
        │
        └─requires──► IDataAccessLayer
                   → IUserProfileService

┌────────────────────────────────────┐
│ <<interface>>                      │
│ IJobApplicationService             │
├────────────────────────────────────┤
│ + applyForJob()                    │
│ + listApplications()               │
│ + respondToApplication()           │
│ + cancelApplication()              │
└────────────────────────────────────┘
        △
        ├─implements──┬──applyForJob.php
        │             ├──processApplication.php
        │             └──response_application.php
        │
        └─requires──► IDataAccessLayer
                   → IJobPostingService
                   → INotificationService
```

### Layer 4: Communication
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ ICommunicationService              │
├────────────────────────────────────┤
│ + sendMessage()                    │
│ + getConversation()                │
│ + listChats()                      │
│ + filterChats()                    │
│ + markAsRead()                     │
└────────────────────────────────────┘
        △
        ├─implements──┬──employer_chat.php
        │             ├──jobSeeker_chat.php
        │             └──chat.php
        │
        └─requires──► IDataAccessLayer
                   → IUserProfileService
                   → INotificationService

┌────────────────────────────────────┐
│ <<interface>>                      │
│ IWallPostService                   │
├────────────────────────────────────┤
│ + createWallPost()                 │
│ + updateWallPost()                 │
│ + deleteWallPost()                 │
│ + displayWallPosts()               │
└────────────────────────────────────┘
        △
        ├─implements──┬──create_wall_post.php
        │             ├──display_wall_post.php
        │             └──edit_wall_post.php
        │
        └─requires──► IDataAccessLayer
                   → IUserProfileService
```

### Layer 5: Support Services
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ INotificationService               │
├────────────────────────────────────┤
│ + sendNotification()               │
│ + fetchNotifications()             │
│ + markAsRead()                     │
│ + getUnreadCount()                 │
└────────────────────────────────────┘
        △
        └─implements──notification.php
        └─requires──► IDataAccessLayer

┌────────────────────────────────────┐
│ <<interface>>                      │
│ IReportingService                  │
├────────────────────────────────────┤
│ + submitReport()                   │
│ + reviewReport()                   │
│ + issueWarning()                   │
│ + suspendAccount()                 │
└────────────────────────────────────┘
        △
        ├─implements──┬──report_form.php
        │             ├──reviewReport.php
        │             └──updateWarning.php
        │
        └─requires──► IDataAccessLayer
                   → IUserProfileService
                   → INotificationService

┌────────────────────────────────────┐
│ <<interface>>                      │
│ IRatingService                     │
├────────────────────────────────────┤
│ + submitRating()                   │
│ + getRating()                      │
│ + getJobHistory()                  │
│ + getCompletionStatus()            │
└────────────────────────────────────┘
        △
        ├─implements──┬──rate_job.php
        │             ├──rate_complete.php
        │             └──job_history.php
        │
        └─requires──► IDataAccessLayer
                   → IJobApplicationService
```

### Layer 0: Data Access
```
┌────────────────────────────────────┐
│ <<interface>>                      │
│ IDataAccessLayer                   │
├────────────────────────────────────┤
│ + connectDatabase()                │
│ + executeQuery()                   │
│ + prepareStatement()               │
│ + closeConnection()                │
└────────────────────────────────────┘
        △
        └─implements──config.php
                      └──MySQL connection
```

---

## 📋 Component-to-File Mapping

```
┌─────────────────────────────────────────────────────────────────┐
│ AUTHENTICATION COMPONENT                                        │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IAuthenticationService                              │
│ Files:                                                          │
│  ├─ login.php           (User login)                           │
│  ├─ register.php        (User registration)                    │
│  └─ logout.php          (User logout)                          │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ USER PROFILE COMPONENT                                          │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IUserProfileService                                 │
│ Files (Admin):                                                  │
│  ├─ Admin/view_employer_profile.php                            │
│  ├─ Admin/view_jobseeker_profile.php                           │
│  └─ Admin/viewProfile.php                                      │
│ Files (Employer):                                               │
│  ├─ Employer/create_employer_profile.php                       │
│  ├─ Employer/update_employer_profile.php                       │
│  └─ Employer/view_employer_profile.php                         │
│ Files (Job Seeker):                                             │
│  ├─ Job Seeker/create_jobseeker_profile.php                    │
│  ├─ Job Seeker/delete_jobseeker_profile.php                    │
│  └─ Job Seeker/view_selfprofile.php                            │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ JOB POSTING COMPONENT                                           │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IJobPostingService                                  │
│ Files:                                                          │
│  ├─ Employer/job_posting_form.php      (Create job post)      │
│  ├─ Employer/job_posting_list.php      (List job posts)       │
│  ├─ Employer/edit_job_posting.php      (Edit job post)        │
│  ├─ Job Seeker/jobSeeker_posting_list.php  (Browse jobs)      │
│  ├─ Job Seeker/filter_jobs.php         (Filter jobs)          │
│  └─ Admin/viewJobPost.php              (Admin view)           │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ JOB APPLICATION COMPONENT                                       │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IJobApplicationService                              │
│ Files:                                                          │
│  ├─ database/applyForJob.php           (Apply for job)        │
│  ├─ Employer/processApplication.php    (Process applications) │
│  ├─ Employer/response_application.php  (Respond to app)       │
│  ├─ Job Seeker/my_application.php      (View applications)    │
│  ├─ Job Seeker/processResponse.php     (Process response)     │
│  └─ Job Seeker/cancelJob.php           (Cancel application)   │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ CHAT/COMMUNICATION COMPONENT                                    │
├─────────────────────────────────────────────────────────────────┤
│ Implements: ICommunicationService                               │
│ Files:                                                          │
│  ├─ Employer/employer_chat.php          (Employer chat)        │
│  ├─ Job Seeker/jobSeeker_chat.php       (Job seeker chat)      │
│  ├─ database/chat.php                   (Chat data layer)      │
│  ├─ database/employer_filter_chat.php   (Employer filtering)   │
│  ├─ database/jobSeeker_filter_chat.php  (Job seeker filtering) │
│  └─ database/jobSeeker_search_chat.php  (Search chats)        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ WALL POST COMPONENT                                             │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IWallPostService                                    │
│ Files:                                                          │
│  ├─ Job Seeker/create_wall_post.php    (Create post)          │
│  ├─ Job Seeker/display_wall_post.php   (Display posts)        │
│  ├─ Job Seeker/edit_wall_post.php      (Edit post)            │
│  ├─ Job Seeker/delete_wall_post.php    (Delete post)          │
│  ├─ Job Seeker/my_posts.php            (View own posts)       │
│  └─ Employer/display_wall_post.php     (Employer view)        │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ NOTIFICATION COMPONENT                                          │
├─────────────────────────────────────────────────────────────────┤
│ Implements: INotificationService                                │
│ Files:                                                          │
│  └─ notification/notification.php       (Notification manager) │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ REPORTING/MODERATION COMPONENT                                  │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IReportingService                                   │
│ Files:                                                          │
│  ├─ Employer/report_form.php            (Submit report)        │
│  ├─ Admin/reviewReport.php              (Review reports)       │
│  ├─ Admin/getReportDetails.php          (Report details)       │
│  ├─ Admin/accountIssueList.php          (Issue list)           │
│  ├─ Admin/updateWarning.php             (Warn users)           │
│  └─ Admin/suspendAccount.php            (Suspend accounts)     │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ RATING/HISTORY COMPONENT                                        │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IRatingService                                      │
│ Files:                                                          │
│  ├─ Job Seeker/rate_job.php             (Rate job)            │
│  ├─ Job Seeker/rate_complete.php        (Complete & rate)     │
│  ├─ Job Seeker/job_history.php          (View history)        │
│  └─ Employer/viewReportStatus.php       (View status)         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ DATABASE SERVICE COMPONENT                                      │
├─────────────────────────────────────────────────────────────────┤
│ Implements: IDataAccessLayer                                    │
│ Files:                                                          │
│  └─ database/config.php                 (DB connection)        │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🔗 Data Flow Examples

### User Registration → Profile Creation
```
┌─────────┐
│ Register│
│ Page    │
└────┬────┘
     │
     ▼
┌──────────────────────────┐
│ IAuthenticationService   │
│ .register()              │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ IUserProfileService      │
│ .createProfile()         │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ IDataAccessLayer         │
│ .executeQuery()          │
└────────┬─────────────────┘
         │
         ▼
   ╔═════════╗
   ║ DATABASE║
   ╚═════════╝
```

### Job Application → Notification
```
┌────────────────┐
│ Apply for Job  │
└────────┬───────┘
         │
         ▼
┌──────────────────────────┐
│ IJobApplicationService   │
│ .applyForJob()           │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ INotificationService     │
│ .sendNotification()      │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ IDataAccessLayer         │
│ .executeQuery()          │
└────────┬─────────────────┘
         │
         ▼
   ╔═════════╗
   ║ DATABASE║
   ╚═════════╝
```

### Message Sending → Chat Storage
```
┌─────────────┐
│ Send Message│
└────┬────────┘
     │
     ▼
┌──────────────────────────┐
│ ICommunicationService    │
│ .sendMessage()           │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ INotificationService     │
│ .sendNotification()      │
└────────┬─────────────────┘
         │
         ▼
┌──────────────────────────┐
│ IDataAccessLayer         │
│ .executeQuery()          │
└────────┬─────────────────┘
         │
         ▼
   ╔═════════╗
   ║ DATABASE║
   ╚═════════╝
```

---

## 📌 Key Takeaways

✅ **10 Interface Classes Identified**
- Each representing a distinct business domain
- Clear contracts and operations defined
- Mapped to existing code files

✅ **Layered Architecture**
- Core Data Access Layer at bottom
- Service Interfaces in middle
- Presentation Layer at top

✅ **UML 2.0 Compliant**
- Proper component notation
- Interface stereotypes
- Clear dependency relationships
- Subsystem grouping

✅ **Ready for Evolution**
- Can add new implementations without changing interfaces
- Easy to test with mock implementations
- Prepared for microservices architecture
- Cloud-deployment ready

---

## 📚 Related Documentation

1. **COMPONENT_DIAGRAM_ANALYSIS.md** - Detailed interface specifications
2. **UML_RESTRUCTURING_SUMMARY.md** - Implementation roadmap
3. **UML_2.0_STANDARDS_APPLIED.md** - Standards compliance details
4. **Mermaid Diagram** - Visual component diagram in editor

