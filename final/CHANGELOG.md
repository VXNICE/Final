# Changelog

## Added
- migrations/2025_09_create_bookings_and_payments.sql
- api/bookings_create.php
- api/bookings_list.php
- api/booking_payment_upload.php
- api/booking_verify.php
- admin_bookings.php
- uploads/payments/.htaccess
- Placeholder pages converted from `.txt` to `.php` in `bookings/`, `dashboard/`, and `rooms/`.

## Modified
- includes/db.php
- includes/auth.php (added `require_role`)
- dashboard.html (added payment modal)
- assets/js/dashboard.js (booking flow, payment upload, Book Now button)
- README.md
