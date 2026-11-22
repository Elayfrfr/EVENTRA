# Eventra
Campus event management & ticketing (students and faculty). PHP 8 + MySQL, no framework.
See **GUIDE.md** for setup, Git and InfinityFree deployment.

Quick start: import `database/schema.sql` then `database/seed.sql`, copy `includes/config.sample.php` to `includes/config.local.php`, open the site. Demo password: `Eventra@Demo2026`.

## Folder map
```
index.php, landing.php     entry + public landing page
auth/        login, signup, forgot-password, reset-password, logout, coming-soon
dashboard/   home (student & faculty dashboard), notifications
events/      events (browse), register-event, directions, calendar, ics
tickets/     tickets, payment, receipt, ticket-download
profile/     profile, edit-profile, change-password, help, terms
faculty/     event-add, event-edit, events-created, report-download
includes/    config*, db, functions, bootstrap   (core, never opened directly)
  layout/    header, footer, auth_header, auth_footer
  partials/  event_form, terms_modal, terms_content
assets/      css/style.css, js/app.js
database/    schema.sql, seed.sql
uploads/     avatars (profile photos)
```
Every page starts with `require __DIR__ . '/../includes/bootstrap.php';` and links with `url('folder/page.php')`.
