# Day 11: Secure Login & Account System

This is my Day 11 project. I built a user login and registration system with profile management and password changing capabilities. I used Bootstrap 5 for clean pages and PDO for secure database connection with MySQL.

## Features I built:
- **Registration**: Allows signing up with username, email, and password. Hashes the password using `password_hash()` for safety.
- **Login**: Verifies credentials with `password_verify()` and starts a session.
- **Profile Picture Upload**: You can upload a photo. I wrote checks to make sure the file is under 2MB and is a valid image (JPEG/PNG/GIF/WEBP). It renames files to random strings to avoid naming clashes.
- **Password Reset (Mock)**: A mockup form that tells you a link was sent to your email (doesn't actually send mail, but acts like it).
- **Change Password**: A form where you can update your current password after verifying it.

## How to run it:
1. Create a MySQL database and run the code in `schema.sql` to set up the tables.
2. Put the credentials in `config.php`.
3. Run the PHP local server:
   ```bash
   php -S localhost:8000
   ```
4. Go to `http://localhost:8000/login.php` in your browser.

## Things I want to work on next:
- Make an actual email reset system instead of just a mockup.
- Design the dashboard page to look cooler.
