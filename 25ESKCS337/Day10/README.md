# Day 10: Student Management Dashboard

This is my Day 10 project. I built a student management dashboard using PHP and MySQL. It lets me add students, edit their details directly in the table, filter by branch or search, and check average stats.

## What it does:
- Shows average CGPA and total student count at the top.
- Let's you add a new student with a profile picture.
- Inline editing: You can edit student names, emails, branches, CGPA, and status directly in the row and hit Update.
- You can filter by Branch, minimum/maximum CGPA, or search by name.
- It also has a toggle to only see Active students or view all of them.

## Setup:
1. Make a database in phpMyAdmin named `student_dashboard` and run the queries in `schema.sql`.
2. Run your PHP local server or host it in XAMPP.
3. Check `config.php` database connection strings.
4. Go to `http://localhost:8000/index.php`.

## What I want to improve:
- The inline row editor is kind of messy, it would be much nicer to use a popup modal for editing.
- Adding some visual styling/CSS would make the tables look cleaner.
