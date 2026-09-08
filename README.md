# BloodBridge

BloodBridge is a PHP, HTML, CSS, and JavaScript Blood Donation Management System based on the supplied project proposal. It uses JSON files in `data/` instead of a database, making it easy to run for a class project or demo.

## Run locally

1. Install PHP 8 or newer and make sure `php` is available in your terminal.
2. Open a terminal in this folder.
3. Run `php -S localhost:8000`.
4. Visit http://localhost:8000.

## Demo accounts

- Admin: `admin@bloodbridge.local` / `password`
- Donor: `donor@bloodbridge.local` / `password`
- Recipient: `patient@bloodbridge.local` / `password`

The app includes role-based dashboards, registration and login, profile editing, donor availability, donor search, blood requests, request status management, and blood stock management.

## MVC structure

- `index.php` is the front controller and route dispatcher.
- `app/Controllers/` contains authentication and portal workflows.
- `app/Models/` contains user, request, and stock domain operations.
- `app/Core/JsonStore.php` provides locked JSON persistence.
- `app/bootstrap.php` wires dependencies, sessions, validation, CSRF, and shared view helpers.
- `views/` contains the presentation templates.

All state-changing forms use session-backed CSRF tokens, and admin, donor, and recipient actions are checked by role before data changes are made.