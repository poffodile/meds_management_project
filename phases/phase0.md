# Phase 0 — Existing Bugs & Cleanup

> Issues found in the current Care OS codebase that need fixing before or alongside Phase 1. These are pre-existing problems, not introduced by us.

---

## 1. Missing/Broken API Routes

### Schedule Shifts — AJAX endpoint missing
- **Page:** `/roster/schedule-shift`
- **JS file:** `public/frontEnd/staff/js/schedule-shift.js` (lines 421, 413)
- **Problem:** JS calls `GET /roster/carer/shifts` and `GET /roster/carer/shift-resources` but these routes don't exist in `routes/web.php` or `routes/api.php`
- **Result:** "Failed to load shifts" alert on page load
- **Fix:** Create the API routes and controller methods to return shift data as JSON

### Leave Tracker — Copy-paste stub, nothing works
- **Page:** Leave Tracker (Sales & Finance section)
- **Controller:** `app/Http/Controllers/frontEnd/salesFinance/leave_tracker/LeaveTrackerController.php`
- **View:** `resources/views/frontEnd/salesAndFinance/leave_tracker/leave_tracker.blade.php`
- **Problem:** View includes `council_tax.js` and sets URLs to `finance/save-council-tax`, `finance/edit-council-tax`, `finance/delete-council-tax`. Controller only has 2 stub methods that return views — no save/edit/delete logic. This is a copy-paste of council_tax page with no actual leave tracker backend.
- **Result:** Page renders but save/edit/delete do nothing
- **Fix:** Build actual leave tracker backend or remove the page if not needed

---

## 2. Hardcoded Production URLs (496 occurrences)

### Critical — Image upload path broken locally
- **File:** `resources/views/frontEnd/systemManagement/elements/add_staff.blade.php` (line 799)
- **Code:** `window.location.origin + '/socialcareitsolutions/public/images/userProfileImages/'`
- **Problem:** Hardcoded server path means profile image uploads resolve to wrong path on local dev
- **Fix:** Replace with `{{ asset('public/images/userProfileImages') }}` or use `ASSETS_URL` from `.env`

### Copyright footers (low priority)
- `resources/views/frontEnd/layouts/master.blade.php` (lines 357, 403, 450, 498)
- `resources/views/backEnd/layouts/master.blade.php` (line 222)
- `resources/views/frontEnd/common/dynamic_forms.blade.php` (line 1247)
- `resources/views/pdf/logbook.blade.php` (line 126)
- `resources/views/frontEnd/salesAndFinance/common/header_forms.blade.php` (line 1276)
- **Fix:** Replace `www.socialcareitsolutions.co.uk` with `{{ config('app.url') }}` or Omega Life branding

### Email templates with hardcoded URLs
- `resources/views/emails/policy/*.blade.php` — 3 templates with hardcoded social-share slugs and logo URLs pointing to `socialcareitsolutions.co.uk`
- **Fix:** Use `{{ config('app.url') }}` and `{{ asset() }}`

---

## 3. Files in Wrong Locations

### Controller code stored in views directory
| File | Problem |
|------|---------|
| `resources/views/backEnd/generalAdmin/department/server_code_daily_log_controller.php` | Full PHP controller class stored in views |
| `app/Http/Controllers/frontEnd/SystemManagement/living_skill.blade.php` | Blade view stored in Controllers directory |
| `resources/views/frontEnd/salesAndFinance/quote/autoload.php` | Composer's autoload.php dumped in views |

### Non-blade PHP files in views directory
| File | Problem |
|------|---------|
| `resources/views/backEnd/serviceUser/Untitled-2.php` | Duplicate layout HTML, not a blade template |
| `resources/views/rotaStaff/test_calendar.php` | Test/scratch file with mixed PHP + HTML |
| `resources/views/rotaStaff/pyrll_user_prfile_tmplte.php` | Payroll template fragment, not blade |
| `resources/views/frontEnd/serviceUserManagement/elements/notification_bar.php` | Raw JS snippet |
| `resources/views/frontEnd/serviceUserManagement/elements/qqa.php` | Raw HTML modal fragment |
| `resources/views/frontEnd/serviceUserManagement/elements/mood.blade(1).php` | Duplicate of mood.blade.php (download copy) |

---

## 4. Backup/Duplicate Files (safe to delete)

### Backup controllers (6 files)
- `app/Http/Controllers/frontEnd/ServiceUserManagement/DailyLogsController_backup.php`
- `app/Http/Controllers/frontEnd/ServiceUserManagement/DynamicFormController_backup.php`
- `app/Http/Controllers/frontEnd/ServiceUserManagement/LogBookController_backup_30_9_25.php`
- `app/Http/Controllers/frontEnd/ServiceUserManagement/MFCController_backup.php`
- `app/Http/Controllers/frontEnd/ServiceUserManagement/MFCController-old.php`
- `app/Http/Controllers/frontEnd/SystemManagement/CalendarController_bkup.php`

### Backup views (14 files)
- `resources/views/frontEnd/serviceUserManagement/daily_log.blade_backup.php`
- `resources/views/frontEnd/serviceUserManagement/daily_log.blade_backup_30_9_25.php`
- `resources/views/frontEnd/serviceUserManagement/elements/add_log.blade_backup_30_09_25.php`
- `resources/views/frontEnd/serviceUserManagement/profile.blade_backup.php`
- `resources/views/frontEnd/serviceUserManagement/placement_plan_backup_14_10_2025.blade.php`
- `resources/views/frontEnd/salesAndFinance/expenses/expense_Backup.blade.php`
- `resources/views/frontEnd/salesAndFinance/purchase_order/backup-finance_dasboard.blade.php`
- `resources/views/frontEnd/salesAndFinance/jobs/add_job.blade_BackupForStepwise.php`
- `resources/views/frontEnd/salesAndFinance/jobs/add_job.blade_NewbackupAgainChangeDesign.php`
- `resources/views/frontEnd/salesAndFinance/jobs/add_customer.blade_Backup.php`
- `resources/views/frontEnd/salesAndFinance/jobs/active_customer_BackupForChangeListingOfAllCRM.blade.php`
- `resources/views/frontEnd/common/dynamic_forms.blade_backup.php`
- `resources/views/backEnd/superAdmin/home/homes_backup_22_12_2025.blade.php`
- `resources/views/rotaStaff/calender.blade-old.php`

### Backup JS (2 items)
- `public/backEnd/js/bootstrap-datepicker/js/bootstrap-datepicker backup old.js`
- `public/frontEnd/js/gritter@backup1/` (entire directory)

---

## 5. Dead Route File

- **File:** `routes/user.php`
- **Problem:** Has `use App\http\RotaController;` (lowercase `h` — wrong namespace). All routes in it duplicate `routes/w2.php` but point to non-existent controller. File appears unused.
- **Fix:** Delete after confirming it's not loaded in `RouteServiceProvider`

---

## Priority Order

| Priority | Issue | Impact |
|----------|-------|--------|
| **P0** | Image upload path hardcoded (add_staff.blade.php) | Staff profile images won't upload locally |
| **P1** | Schedule shifts API routes missing | Schedule page broken |
| **P1** | Leave tracker is a stub | Page does nothing |
| **P2** | Hardcoded production URLs (496 occurrences) | Wrong branding, broken links locally |
| **P3** | Files in wrong locations | Confusion, PSR-4 autoload warnings |
| **P3** | Backup/duplicate files (22 files) | Clutter, autoload warnings |
| **P4** | Dead route file | Minor confusion |
