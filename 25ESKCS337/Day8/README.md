# Day 8: Registration Confirmation System (PHP)

This is my project for Day 8. I built a student registration form and confirmation system using HTML, Bootstrap for styles, some Javascript for image previews, and PHP for processing the form on the server. The data gets saved in a flat JSON file database called `data.json`.

## What I did:
1. **HTML & Bootstrap form**: Created a nice-looking registration form card with input fields like name, email, phone, gender, course, and address.
2. **Instant image preview**: Used Javascript and `FileReader` to show the profile photo preview as soon as it's selected in the browser.
3. **PHP backend processing**: Sanitized the input using `trim` and `htmlspecialchars`. Validated email and file uploads (checking for JPEG/PNG and a 2MB size limit).
4. **JSON database**: Saved all the successfully registered profiles inside `data.json`.
5. **Confirmation receipt**: Shows a confirmation screen with the uploaded photo and all details formatted nicely.

## How to run it:
1. Open this folder in your terminal.
2. Run the PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
3. Open `http://localhost:8000/index.php` in your browser.
4. Try registering a student. The photo will be saved in the `uploads/` folder and data in `data.json`.

## Things to improve:
- The JSON file data database isn't ideal for a huge number of students, SQL would be much better (I'll do this in Day 9).
- Could add more custom error messages next to the input boxes rather than standard alerts.
