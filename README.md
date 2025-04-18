# Hotel Booking System
[CS5281 25Spring] Hotel Booking System - Group 2

## Project Overview
A comprehensive hotel booking system built with PHP that allows users to search for rooms, make reservations, and manage their bookings. This system features a clean, responsive interface with a focus on simplicity and functionality.

## Features

### User Authentication System
- User Registration with Name, Email, and Mobile Number
- Secure Login System with Password Hashing
- User Profile Management
- Role-based Access Control (User and Admin)

### Room Management
- View Available Rooms
- Room Details and Images
- Room Status Management (Available, Maintenance, Reserved)
- Room Quantity Tracking
- Image Upload Support for Room Photos

### Booking System
- Real-time Room Availability Check
- Date Range Selection
- Guest Count Selection
- Booking Confirmation
- Mobile Phone Verification
- Special Requests Option

### User Dashboard
- View Upcoming Stays
- View Past Stays
- View Cancelled Bookings
- Cancel Bookings (with 24-hour restriction)
- Booking Status Tracking

### Admin Dashboard
- Comprehensive Room Management
  - Add/Edit/Delete Rooms
  - Room Status Management (Available/Maintenance/Reserved)
  - Room Image Management
  - Room Quantity Control
- Booking Management
  - View All Bookings
  - Cancel Bookings
  - View Booking Details
- Dashboard Statistics
  - Today's Check-ins
  - Today's Check-outs
  - Total Bookings
  - Total Revenue

### Mobile Number Support
- Mainland China Mobile Numbers (11 digits, starting with 1)
- Hong Kong Mobile Numbers (8 digits, starting with 5, 6, or 9)
- Macau Mobile Numbers (8 digits, starting with 6)
- Consistent validation across registration, booking, and profile management

## Enhanced Features
- **Room Availability System**
  - Date-based availability checking
  - Prevents overbooking by tracking room inventory
  - Room status management (Available/Maintenance/Reserved)

- **Booking Restrictions**
  - Users cannot cancel bookings within 24 hours of check-in
  - Administrators can cancel bookings at any time
  - Maximum stay duration of 30 days

- **AJAX Integration**
  - Real-time room search without page refresh
  - Smooth booking cancellation with instant feedback
  - AJAX-powered room deletion in admin panel
  - Improved user experience with dynamic content updates
  
- **Robust Error Handling**
  - Comprehensive file operation error checks
  - User-friendly error messages
  - Automatic date correction with clear notifications
  - File upload validation and size limits

## Technologies Used
- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP
- **Data Storage**: Flat File System (Text Files)
- **Styling**: Custom CSS with responsive design
- **Icons**: Font Awesome
- **Advanced Features**: AJAX for dynamic content loading

## Installation Instructions

### Prerequisites
- PHP 7.0 or higher
- Web server (Apache/XAMPP recommended)
- PHP mail function configured (for email notifications)
- Write permissions for data directory

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
- For image upload issues, check file permissions and PHP upload settings

## Project Structure
```
hotel-booking-system/
├── admin/         # Admin section
│   ├── index.php  # Admin dashboard
│   ├── booking.php # Booking management
│   └── room.php   # Room management
├── api/           # API endpoints for AJAX
│   ├── search_rooms.php    # Room search API
│   ├── cancel_booking.php  # Booking cancellation API
│   └── manage_room.php     # Room management API
├── assets/        # Static resources
│   ├── css/       # Stylesheets
│   ├── js/        # JavaScript files
│   └── images/    # Images and room photos
│       └── rooms/ # Room type specific images
├── data/          # Data storage
│   ├── users.txt  # User information
│   ├── rooms.txt  # Room information
│   └── bookings.txt # Booking records
├── includes/      # Shared components
│   ├── header.php # Site header
│   ├── footer.php # Site footer
│   ├── db.php     # Data operations
│   └── auth.php   # Authentication functions
├── index.php      # Homepage
├── search.php     # Room search
├── room.php       # Room details
├── booking-form.php # Booking process
├── confirmation.php # Booking confirmation
├── login.php      # User login
├── logout.php     # User logout
├── register.php   # User registration
├── profile.php    # User profile
└── my-trips.php   # User bookings
```

## Data Storage Specifications

### File Structure and Format
All data is stored in text files within the `data/` directory using a structured format:

#### users.txt
- One user record per line
- Fields separated by '|' character
- Format: `id|name|email|mobile|password_hash|role|created_at`
- Example: `2|John Doe|john@example.com|13912345678|$2y$10$hash...|user|2025-04-10 09:15:20`

#### rooms.txt
- One room record per line
- Fields separated by '|' character
- Format: `id|name|price|has_wifi|capacity|description|image_name|quantity|status`
- Example: `1|Deluxe Room|199.99|Yes|5|Spacious room with king-size bed|deluxe.jpg|5|available`

#### bookings.txt
- One booking record per line
- Fields separated by '|' character
- Format: `booking_id|user_id|room_id|check_in|check_out|guests|total_price|status|created_at|contact_mobile|special_requests`
- Example: `67fe8dbf65fd4|2|1|2025-04-10|2025-04-13|2|599.97|completed|2025-03-15 10:30:45|13912345678|Extra bed needed`

### Data Operations
- All file operations use flock() for concurrency control
- Atomic write operations using temporary files
- Regular backups recommended (every 24 hours)
- File permissions: 644 for data files, 755 for data directory

### Data Validation
- All input data is sanitized and validated before storage
- Date format: YYYY-MM-DD
- Time format: YYYY-MM-DD HH:MM:SS
- Mobile number validation by region:
  - Mainland China: 11 digits, starting with 1
  - Hong Kong: 8 digits, starting with 5, 6, or 9
  - Macau: 8 digits, starting with 6
- Email addresses validated using filter_var()

### Error Handling
- File operation errors logged to error.log
- Automatic file creation if not exists
- Backup creation before critical operations
- Data integrity checks on read/write operations

## User Workflow

1. **Registration Process**
   - Enter personal information (name, email, mobile)
   - Create password
   - Complete registration

2. **Booking Process**
   - Search for available rooms by date and guest count
   - Select room type
   - Enter contact information and special requests
   - Confirm booking
   - View booking confirmation

3. **Profile Management**
   - View booking history
   - Update personal information
   - Manage upcoming and past bookings
   - Cancel eligible bookings

4. **Admin Operations**
   - Manage room inventory
   - Monitor bookings
   - Update room status
   - Cancel bookings (at any time, without time restrictions)
   - Add new room types
   - Manage room quantities

## Admin Access
- **Email**: admin@luxuryhotel.com
- **Password**: admin123

## Default Room Types
The system comes pre-configured with four room types:
1. **Standard Room** - $149.99 per night, capacity: 3 persons
2. **Deluxe Room** - $199.99 per night, capacity: 5 persons
3. **Executive Suite** - $299.99 per night, capacity: 2 persons
4. **Family Room** - $249.99 per night, capacity: 4 persons

## Data Storage
- All data is stored in text files within the `/data` directory
- Each entry is pipe-delimited (|) for efficient parsing
- Data structure ensures consistency and integrity

## License
This project is created for educational purposes. All rights reserved.