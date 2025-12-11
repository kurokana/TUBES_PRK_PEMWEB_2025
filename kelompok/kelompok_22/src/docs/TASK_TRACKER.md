# Issue Tracker - SiPaMaLi Development

## 🎯 MODUL 1: User Management & Authentication
**Assignee:** @kurokana (Muhammad Faisal)

### Tasks Checklist:

#### Database Setup
- [ ] Create `users` table with role column
- [ ] Add `user_id` foreign key to `reports` table
- [ ] Insert sample users (admin, petugas, warga)
- [ ] Test database connections

#### Registration System
- [ ] Create `register.php` form
- [ ] Implement input validation (username, email, password)
- [ ] Check duplicate email/username
- [ ] Hash password with `password_hash()`
- [ ] Insert new user to database
- [ ] Create `register-validation.js` for client-side validation
- [ ] Test registration flow

#### Multi-Role Login
- [ ] Update `login.php` to support 3 roles
- [ ] Modify `includes/auth.php` for role-based session
- [ ] Implement "Remember Me" functionality
- [ ] Redirect based on role (admin→admin.php, petugas→petugas.php, warga→index.html)
- [ ] Test login for all roles

#### Profile Management
- [ ] Create `profile.php` page
- [ ] Display user info (username, email, full_name, phone, avatar)
- [ ] Show user's report statistics
- [ ] Create `edit-profile.php` form
- [ ] Upload avatar image with validation
- [ ] Update profile (name, phone, email)
- [ ] Change password feature with old password verification
- [ ] Test all profile features

#### Security
- [ ] XSS protection on all inputs
- [ ] SQL injection prevention (prepared statements)
- [ ] CSRF token implementation
- [ ] Session timeout (30 minutes)
- [ ] Secure password requirements (min 8 chars)

---

## 🎯 MODUL 2: Report Tracking & Assignment
**Assignee:** @deeloon22 (Delon)

### Tasks Checklist:

#### Database Setup
- [ ] Create `report_assignments` table
- [ ] Create `report_progress` table
- [ ] Add `assigned_to` column to `reports` table
- [ ] Add `priority` enum to `reports` table
- [ ] Create indexes for performance

#### Dashboard Petugas
- [ ] Create `petugas.php` page
- [ ] Show statistics (total assigned, in progress, completed)
- [ ] List assigned reports with filtering
- [ ] Sort by priority & date
- [ ] Responsive design for mobile
- [ ] Test petugas dashboard

#### Assignment System
- [ ] Create `assign-report.php` (admin only)
- [ ] Dropdown list of available petugas
- [ ] Assign report to petugas with notes
- [ ] Save to `report_assignments` table
- [ ] Update `reports.assigned_to`
- [ ] Create notification for assigned petugas
- [ ] Test assignment flow

#### Progress Tracking
- [ ] Create `update-progress.php` (petugas only)
- [ ] Form: status update, notes, upload foto before/after
- [ ] Save to `report_progress` table
- [ ] Update main `reports` status
- [ ] Display progress timeline
- [ ] Show before/after photos side by side
- [ ] Test progress update

#### API Endpoints
- [ ] `POST /api.php?action=assignReport`
- [ ] `POST /api.php?action=updateProgress`
- [ ] `GET /api.php?action=getAssignedReports`
- [ ] `GET /api.php?action=getProgressHistory`
- [ ] Test all API endpoints

---

## 🎯 MODUL 3: Comment & Notification System
**Assignee:** @najwaaprisda (Nadjwa Aprisda)

### Tasks Checklist:

#### Database Setup
- [ ] Create `report_comments` table
- [ ] Create `notifications` table
- [ ] Create indexes for query optimization
- [ ] Insert sample comments & notifications

#### Comment System
- [ ] Create `api-comments.php` for CRUD operations
- [ ] `POST /api-comments.php?action=addComment`
- [ ] `GET /api-comments.php?action=getComments&report_id=X`
- [ ] `DELETE /api-comments.php?action=deleteComment` (own comment only)
- [ ] Display comments on report detail page
- [ ] Real-time comment loading (AJAX)
- [ ] Create `js/realtime-comments.js`
- [ ] Comment form with character limit (max 500)
- [ ] Test comment system

#### Notification System
- [ ] Create `api-notifications.php`
- [ ] `GET /api-notifications.php?action=getNotifications`
- [ ] `POST /api-notifications.php?action=markAsRead`
- [ ] Create `includes/notification-helper.php`
- [ ] Function: `createNotification($user_id, $type, $title, $message)`
- [ ] Create `notifications.php` page
- [ ] Display notification list with unread badge
- [ ] Notification dropdown in navbar
- [ ] Auto-create notification on:
  - [ ] Report status changed
  - [ ] New comment added
  - [ ] Report assigned to petugas
- [ ] Test notification creation & display

#### Email Notification (Optional)
- [ ] Create `includes/email.php`
- [ ] Setup SMTP configuration
- [ ] Create HTML email template
- [ ] Send email on report completion
- [ ] Test email sending

---

## 🎯 MODUL 4: Analytics & Reporting Dashboard
**Assignee:** @geraldilyas (Gerald)

### Tasks Checklist:

#### Database Setup
- [ ] Create `analytics_summary` VIEW
- [ ] Create `petugas_leaderboard` VIEW
- [ ] Optimize queries with indexes

#### Analytics Dashboard
- [ ] Create `dashboard-analytics.php` (admin only)
- [ ] Include Chart.js library (CDN)
- [ ] Create `api-stats.php` for chart data
- [ ] Bar chart: Reports by category
- [ ] Pie chart: Reports by status
- [ ] Line chart: Monthly report trends
- [ ] Statistic cards (total, pending, completed)
- [ ] Create `js/charts.js`
- [ ] Responsive chart layout
- [ ] Test all charts with real data

#### Public Statistics
- [ ] Create `statistics.php` (public, no login)
- [ ] Display total reports & completion rate
- [ ] Show category breakdown
- [ ] Timeline of recent completed reports
- [ ] Petugas leaderboard (most resolved)
- [ ] Responsive design
- [ ] Test public stats page

#### Export Features
- [ ] Install TCPDF library (via composer or manual)
- [ ] Create `export-pdf.php`
- [ ] PDF template with header/footer
- [ ] Generate report list with filtering
- [ ] Include charts in PDF (screenshot)
- [ ] Create `export-excel.php`
- [ ] Generate CSV format
- [ ] Include all report data & statistics
- [ ] Add download button in admin panel
- [ ] Test PDF & Excel export

#### Print Stylesheet
- [ ] Create `css/print.css`
- [ ] Print-friendly layout
- [ ] Hide navigation & unnecessary elements
- [ ] Test print preview

---

## 📋 Integration Tasks (All Members)

### Final Integration
- [ ] Test user registration → login → create report
- [ ] Test admin assign → petugas update → notification
- [ ] Test comment → notification flow
- [ ] Test analytics data accuracy
- [ ] Cross-browser testing (Chrome, Firefox, Edge)
- [ ] Mobile responsive testing
- [ ] Fix all UI/UX inconsistencies

### Documentation
- [ ] Update main README.md
- [ ] Create API documentation
- [ ] Create user manual (PDF)
- [ ] Update database schema diagram
- [ ] Record demo video (optional)

### Code Quality
- [ ] Code review for all modules
- [ ] Remove debug code & console.log
- [ ] Consistent code formatting
- [ ] Add comments for complex logic
- [ ] Security audit

---

## 🐛 Known Issues / Bugs

*(Catat bug yang ditemukan di sini)*

| ID | Module | Description | Status | Assignee |
|----|--------|-------------|--------|----------|
| - | - | - | - | - |

---

## 📅 Milestone Schedule

- **Week 1 (Dec 4-10):** Database setup & Module 1-2 development
- **Week 2 (Dec 11-17):** Module 3-4 development
- **Week 3 (Dec 18-24):** Integration & testing
- **Week 4 (Dec 25-31):** Bug fixing & documentation
- **Jan 1:** Final submission

---

**Last Updated:** December 4, 2025
