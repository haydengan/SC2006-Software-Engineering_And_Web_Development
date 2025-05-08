# 👥 SC2006 Project – Let's Meet!

A web-based application that helps users **create, manage, and join group activities** by suggesting common meeting points based on participants' locations. Built as part of the SC2006 Software Engineering course at NTU.

---

## Key Features

- 📍 Location-based activity planning
- 👥 User registration and login
- 📝 Create, join, and view activities
- 📅 Real-time coordination and meetup tracking
- 🔒 Session management (login/logout)
- 🎨 Basic UI with navigation bar

---

## Tech Stack

- **Frontend**: HTML, CSS
- **Backend**: PHP
- **Database**: MySQL
- **Map/Route logic**: Custom routing via `get_route.php`

---

## Project Structure
```
├── stylesheets/ # CSS styling
├── 2006.sql # SQL script to create database schema
├── index.php # Home/landing page
├── login.php # Login functionality
├── register.php # User sign-up
├── logout.php # Log out and session cleanup
├── createActivity.php # Page to create new activity
├── viewActivity.php # Display activity details
├── dbFunctions.php # Database connection and helper functions
├── get_route.php # Logic for location-based routing
├── navbar.php # Reusable navigation bar
└── README.md # This file
```

##  My Role
I contributed to this project as a team member responsibility for:
- Designing the interactive and clean webpages
- Software documentation

##  How to Run
1. Import `2006.sql` into your local MySQL server
2. Host the PHP files using XAMPP/LAMP/etc.
3. Open `index.php` in your browser
4. Register a user and start planning meetups!

