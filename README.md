# Guesthouse Management System
 
A full-stack web application built with PHP, MySQL and Bootstrap 5 that allows guesthouse owners to manage their property online. Guests can browse rooms, check real-time availability and make bookings. The owner manages everything through a secure admin panel.
 
This project was built as a portfolio piece and as a practical solution that can be pitched to real guesthouse businesses in South Africa.
 
---
 
## Features
 
**Public Website**
 
Guests can visit the homepage and use the booking bar to check availability for specific dates. The rooms page lists all available rooms with their amenities, capacity and nightly rates. The booking system checks the database in real time to prevent double bookings and generates a booking reference number on confirmation. There is also a photo gallery with a lightbox viewer, an about page, and a contact form that saves messages directly to the admin inbox.
 
**Admin Panel**
 
The owner logs in through a secure login page protected by bcrypt password hashing and IP based brute force lockout. The dashboard shows live stats including total bookings, pending bookings, confirmed bookings, total revenue, active rooms and unread messages. From the admin panel the owner can confirm or cancel bookings, add and edit rooms, upload and delete gallery photos, read and reply to guest messages, update site settings, and customise the entire website appearance through the theme page including primary colour, background colour, font style, dark mode and logo upload.
 
---
 
## Security
 
Passwords are stored using bcrypt hashing and are never saved as plain text. All database queries use prepared statements to prevent SQL injection. Every form includes a CSRF token to prevent cross site request forgery. User input is sanitised on every form field. File uploads are validated against a whitelist of allowed extensions and renamed with unique identifiers. After 5 failed login attempts the IP address is locked out for 15 minutes with a live countdown timer. All admin pages are protected by session authentication and redirect unauthenticated users to the login page immediately.
 
---
 
## Tech Stack
 
PHP 8.2 handles all server side logic, database interaction and session management. MySQL stores all relational data across 8 tables. Bootstrap 5.3 provides the responsive frontend framework. Custom CSS with variables powers the dynamic theming system. JavaScript handles form interactions, live previews, modals and countdown timers. Apache via XAMPP serves the application locally.
 
---
 
## Project Structure
 
```
guesthouse/
├── admin/                  
│   ├── includes/           
│   ├── index.php           
│   ├── bookings.php        
│   ├── rooms.php           
│   ├── gallery.php         
│   ├── messages.php        
│   ├── settings.php        
│   ├── theme.php           
│   ├── login.php           
│   └── logout.php          
├── config/
│   ├── config.example.php  
│   └── config.php          
├── includes/
│   ├── db.php              
│   ├── functions.php       
│   ├── header.php          
│   └── footer.php          
├── public/                 
│   ├── index.php           
│   ├── rooms.php           
│   ├── booking.php         
│   ├── gallery.php         
│   ├── about.php           
│   ├── contact.php         
│   └── assets/             
├── sql/
│   └── schema.sql          
└── index.php               
```
 
---
 
## Installation
 
You will need XAMPP with Apache and MySQL running on your machine.
 
Clone the repository into your XAMPP htdocs folder.
 
```
git clone https://github.com/YOUR_USERNAME/guesthouse-management-system.git
```
 
Copy the example config file and fill in your details.
 
```
cp config/config.example.php config/config.php
```
 
Open phpMyAdmin at http://localhost/phpmyadmin, click the SQL tab, paste the contents of sql/schema.sql and click Go.
 
Open the website at http://localhost/guesthouse and access the admin panel at http://localhost/guesthouse/admin/login.php using the default credentials username admin and password admin123. Change the password immediately after first login through the Settings page.
 
---
 
## Database
 
The system uses 8 tables. The admins table stores admin credentials with bcrypt hashed passwords. The rooms table stores room listings with pricing, capacity and amenities. The bookings table stores every reservation with status tracking. The blocked dates table allows the admin to manually block dates per room. The gallery table stores file paths of uploaded photos. The settings table is a key value store for all site configuration. The contact messages table stores contact form submissions. The failed logins table tracks failed login attempts by IP address for brute force protection.
 
---
 
## Author
 
Oryan : BSc Information Technology, 2026
 
Built as a portfolio project demonstrating full stack PHP development, relational database design, security implementation and systems thinking. Designed to be pitched to real guesthouse businesses as a practical management solution.