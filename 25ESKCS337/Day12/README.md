# Day 12: Student Management System (CRUD)

This is my Day 12 project. I built a full student management application with login/register authentication, full CRUD operations (add, view, edit, delete student records), image uploading, and search/filter.

## Features:
- Secure login, registration, and logout using sessions.
- CRUD: I can add new students, view them in a directory, edit their information, and delete records.
- Photo uploads: Users can upload student photos which are saved in the `uploads/` folder. It has a fallback avatar if they don't upload a photo.
- Dashboard stats: Shows the total number of students, average CGPA of all students, and students counted per branch.
- Search and filtering: You can search by name, email, or branch, and filter by course or status (Active/Inactive).

## How to run it:
1. Create a database in MySQL and run the code in `schema.sql` to setup tables.
2. Verify database settings in `config.php`.
3. Make sure the `uploads/` folder is writeable.
4. Run the local development server:
   ```bash
   php -S localhost:8000
   ```
5. Go to `http://localhost:8000/register.php` to sign up, then log in and manage students from the dashboard.

## Ideas for future upgrades:
- Add pagination for student list so it doesn't get too long.
- Let users update their own details from a profile settings page.
