# Hotel Booking System
[CS5281 25Spring] Hotel Booking System - Group 2

## Project Overview
A comprehensive hotel booking system built with PHP that allows users to search for rooms, make reservations, and manage their bookings. This system features a clean, responsive interface inspired by Marriott's design principles while maintaining simplicity and functionality.

## Features
- **User Authentication System**
  - Register new accounts
  - Login with existing credentials
  - Session management
  - Profile management with editable personal information
  
- **Room Management**
  - View different room types (Standard, Deluxe, Executive Suite, Family Room)
  - Room availability checking with precise date-based calculation
  - Detailed room information and images
  
- **Booking System**
  - Date-based room search with automatic validation
  - Guest quantity selection
  - Booking confirmation process
  - Payment simulation
  - Maximum and minimum stay duration limits
  - Confirmation email notifications
  
- **User Dashboard**
  - View upcoming stays
  - View booking history
  - Cancel existing bookings (with time-based restrictions)
  - Real-time booking management with AJAX
  - Edit personal profile information
  
- **Admin Panel**
  - Manage all bookings with cancellation capabilities
  - View daily check-ins and check-outs
  - System overview statistics
  - Add and edit room information including type, price, and capacity
  - Manage room inventory
  - Real-time booking management with AJAX

## Enhanced Features
- **Improved Room Availability System**
  - Precise date-based availability checking
  - Prevents overbooking by tracking room inventory

- **Smart Booking Restrictions**
  - Users cannot cancel bookings within 24 hours of check-in
  - Administrators cannot cancel bookings on the check-in day
  - Maximum stay duration of 30 days

- **AJAX Integration**
  - Real-time room search without page refresh
  - Smooth booking cancellation with instant feedback
  - Enhanced user experience with dynamic content updates
  
- **Email Notification System**
  - Booking confirmation emails
  - Cancellation confirmation emails
  - HTML formatted emails with booking details
  - **Note**: The email functionality may not work on free hosting platforms like InfinityFree. This is a limitation of free hosting services that restrict mail functions. The code for email functionality is included in the project but will require a paid hosting service or a third-party email API service (like SendGrid, Mailgun) to function properly on a live site.
  
- **Robust Error Handling**
  - Comprehensive file operation error detection
  - User-friendly error messages
  - Automatic date correction with clear notifications

- **International Phone Number Support**
  - Support for China mainland phone numbers (11 digits, starting with 1)
  - Support for Hong Kong phone numbers (8 digits, starting with 5-9)
  - Consistent validation across registration, booking, and profile management

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Data Storage**: Text files (no database required)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome
- **Advanced Features**: AJAX for dynamic content loading

## Installation Instructions

### Prerequisites
- PHP 7.4 or higher
- Web server (Apache/XAMPP recommended)
- PHP mail function configured (for email notifications)

### Local Development Setup
1. Clone the repository:
```
git clone https://github.com/CS5281-Team2/hotel-booking-system
```

2. Move the project to your web server's document root (e.g., htdocs folder for XAMPP)

3. Ensure the `data` directory has write permissions:
```
chmod 755 data
```

4. If the data directory doesn't exist, the system will create it automatically on first run

5. For email functionality to work locally, ensure your PHP is configured with mail settings:
```
; in php.ini
[mail function]
SMTP = localhost
smtp_port = 25
sendmail_from = noreply@luxuryhotel.com
```

6. Access the site through your local web server:
```
http://localhost/hotel-booking-system/
```

### Online Deployment
1. Upload all files to your web hosting provider
2. Ensure the `htdocs` directory has proper write permissions
3. Access the live deployment at:
http://www.cs5281group2.free.nf/

### Email Functionality Note
The deployed version on InfinityFree does not support email functionality due to restrictions on free hosting plans. To enable email functionality:
- Use a paid hosting plan that supports PHP mail() function
- Or integrate a third-party email service API like SendGrid or Mailgun
- Or modify the code to use SMTP with services like Gmail

### Troubleshooting Installation
- If you encounter file permission errors, ensure the web server user has write access to the `data` directory
- The system requires file write permissions to store user accounts, room information, and bookings
- Check PHP error logs if you encounter any issues during setup
- For email issues, check PHP mail configuration and server settings

## Project Structure
```
hotel-booking/
├── api/           # API endpoints for AJAX
│ ├── search_rooms.php    # Room search API
│ └── cancel_booking.php  # Booking cancellation API
├── assets/        # Static resources
│ ├── css/         # Stylesheets
│ ├── js/          # JavaScript files
│ └── images/      # Images and room photos
│ └── rooms/       # Room type specific images
├── includes/      # Shared components
│ ├── header.php   # Site header
│ ├── footer.php   # Site footer
│ ├── db.php       # Data operations
│ ├── auth.php     # Authentication functions
│ └── mail.php     # Email functionality
├── admin/         # Admin section
│ ├── index.php    # Admin dashboard
│ ├── booking.php  # Booking management
│ └── room.php     # Room management
├── data/          # Data storage
│ ├── users.txt    # User information
│ ├── rooms.txt    # Room information
│ └── bookings.txt # Booking records
├── index.php      # Homepage
├── search.php     # Room search
├── room.php       # Room details
├── booking.php    # Booking process
├── login.php      # User login
├── register.php   # User registration
├── profile.php    # User profile
├── my-trips.php   # User bookings
└── confirmation.php # Booking confirmation
```

## Usage
### For Users
1. Browse available rooms on the homepage
2. Use the search functionality to find rooms for specific dates
3. Register an account or login to make a booking
4. Complete the booking process
5. Receive booking confirmation email
6. View and manage bookings from your account
7. Update personal information from your profile page
8. Cancel bookings if needed (receive cancellation email)
9. Note: Bookings cannot be cancelled within 24 hours of check-in

### For Administrators
1. Login with admin credentials
2. Access the admin panel
3. View system statistics and manage bookings
4. Add new rooms or edit existing room information
5. View daily check-ins and check-outs
6. Cancel bookings when necessary (except on check-in day)

## Admin Access
- **Email**: admin@luxuryhotel.com
- **Password**: admin123

## Credits
- Project developed for CS5281 Spring 2025
- Room images: Unsplash.com (free commercial use)
- Icons: Font Awesome 5

## License
This project is created for educational purposes. All rights reserved.