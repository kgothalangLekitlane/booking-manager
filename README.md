# Booking Manager

My first PHP project: a simple booking management system built with **PHP, HTML, CSS, and MySQL**. I am developing it step by step as I learn backend development and database programming.

## Project goals

The Booking Manager will gradually support:

- Create new bookings with customer details and a booking date.
- View a list of existing bookings.
- Search for a booking by its ID.
- Update a booking's status.
- Delete bookings when needed.
- Validate user input and show clear success or error messages.

## Technology

- **PHP** for application logic
- **PDO** for database access and prepared SQL statements
- **MySQL** for storing booking information
- **HTML and CSS** for the user interface

## Current progress

- [x] MySQL database schema added in `database/bookings.sql`.
- [x] PDO database connection added in `config/database.php`.
- [x] Booking list page added in `public/index.php`.
- [x] Search for a booking by ID using a prepared statement.
- [x] Add-booking form and PDO INSERT added in `public/add-booking.php`.
- [x] Basic form validation and CSRF token protection added.
- [x] Success message and shared page styling added.
- [x] Local configuration files excluded through `.gitignore`.
- [ ] Test the application with a local PHP/MySQL environment.
- [ ] Add update and delete booking features.

## Database setup

1. Make sure MySQL is running in your local development environment.
2. Open the `database/bookings.sql` file in MySQL Workbench or phpMyAdmin and run it.
3. Update the local MySQL username and password in `config/database.php` if necessary.
4. From the project directory, start PHP's development server with `php -S localhost:8000 -t public`.
5. Visit `http://localhost:8000` in your browser.
6. Select **Add booking** to create a record, or search for a booking using its ID.

## Learning approach

This project is being built incrementally. Each stage adds a practical feature while reinforcing PHP, PDO, SQL, input validation, and secure database queries.
