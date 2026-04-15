# Task 11 Plan

- [completed] Implement teacher forced password change flow.
- [completed] Register `EnsurePasswordChanged` middleware alias and apply it to protected teacher routes.
- [completed] Add teacher password change controller, request, and view.
- [completed] Update login redirect behavior to use the actual teacher dashboard route.
- [completed] Add Pest feature tests for forced password change behavior and validation.
- [completed] Run targeted tests and Pint.

# Review

- Added `EnsurePasswordChanged` middleware and registered it as `password.changed` in `bootstrap/app.php`.
- Added `/teacher/change-password` GET and POST routes and applied forced-password protection to the rest of the teacher route group in `routes/web.php`.
- Implemented `Teacher\PasswordController` and `Teacher\ChangePasswordRequest`.
- Added `resources/views/teacher/change-password.blade.php` using the existing Tailwind CDN page style.
- Updated `LoginController` to send teachers with `must_change_password = true` to the password form and corrected the normal teacher redirect to `route('teacher.dashboard')`.
- Added focused coverage in `tests/Feature/WebAuthTest.php`, `tests/Feature/RoleMiddlewareTest.php`, and new `tests/Feature/TeacherPasswordChangeTest.php`.
- Verification: `php artisan test --compact tests/Feature/WebAuthTest.php tests/Feature/RoleMiddlewareTest.php tests/Feature/TeacherPasswordChangeTest.php` passed with 42 tests, and `vendor/bin/pint --dirty --format agent` passed.
