# 🎯 Wheel of Names

A Laravel-based web application for spinning a wheel to randomly select names from a list of participants. Perfect for contests, giveaways, or any random selection needs.

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [Admin Credentials](#admin-credentials)
- [API Endpoints](#api-endpoints)
- [Troubleshooting](#troubleshooting)

## ✨ Features

- **🎯 Interactive Wheel Spinner**: Beautiful, animated wheel with customizable segments
- **👥 Participant Management**: Add, edit, delete, and manage participants
- **⚙️ Configurable Settings**: Adjustable spin duration and wheel appearance
- **🔐 Admin Authentication**: Secure login system for administrators
- **📊 Dashboard**: Admin dashboard with statistics and recent spins history
- **🎨 Background Customization**: Upload and manage custom wheel backgrounds
- **📱 Responsive Design**: Works on desktop, tablet, and mobile devices
- **🔄 Real-time Updates**: Live participant updates and wheel configuration

## 🔧 Requirements

### System Requirements
- **PHP**: 8.1 or higher
- **Composer**: Latest version
- **Web Server**: Apache/Nginx (or use Laravel's built-in server)
- **Database**: MySQL 5.7+ or MariaDB 10.3+

### PHP Extensions
- BCMath, Ctype, cURL, DOM, Fileinfo, JSON, Mbstring, OpenSSL, PCRE, PDO, Tokenizer, XML

## 🚀 Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd Wheel_Of_Names
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install
```

### 3. Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Create database (MySQL example)
mysql -u root -p
CREATE DATABASE wheel_of_names;
exit

# Update .env file with database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wheel_of_names
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations and Seeders
```bash
# Run database migrations
php artisan migrate

# Seed the database with default admin user
php artisan db:seed --class=AdminUserSeeder
```

### 6. Start the Application
```bash
# Start Laravel development server
php artisan serve

# Or start on specific host/port
php artisan serve --host=0.0.0.0 --port=8000
```

The application will be available at `http://localhost:8000`

## ⚙️ Configuration

### Environment Variables (.env)
```env
APP_NAME="Wheel of Names"
APP_ENV=local
APP_KEY=base64:your_generated_key
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wheel_of_names
DB_USERNAME=your_username
DB_PASSWORD=your_password

SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### File Permissions
```bash
# Set proper permissions for storage and cache
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 🎮 Usage

### Public Access
- **Wheel View**: Anyone can view and spin the wheel at the main page
- **No Registration**: Public users cannot register or modify settings
- **Login Required**: Only login access available for public users

### Admin Access
1. **Login**: Use admin credentials (see [Admin Credentials](#admin-credentials))
2. **Dashboard**: Access admin dashboard with statistics and controls
3. **Manage Participants**: Add, edit, or delete participants
4. **Configure Settings**: Adjust spin duration and wheel appearance
5. **Create Admins**: Create additional administrator accounts

### Basic Workflow
1. **Add Participants**: Go to "Manage Participants" and add names
2. **Configure Settings**: Set spin duration and upload custom backgrounds
3. **Spin the Wheel**: Use the main wheel page to spin and select winners
4. **View History**: Check dashboard for recent spins and statistics

## 🔐 Admin Credentials

### Default Admin Account
- **Email**: `admin@wheelofnames.com`
- **Password**: `password`

### Creating Additional Admins
1. Login with existing admin credentials
2. Go to Dashboard → "Create Admin" or "Create New Admin"
3. Fill out the registration form
4. New admin account will be created with full access

### Security Notes
- Change default password after first login
- Create additional admin accounts as needed
- Registration is restricted to authenticated admins only

## 🔌 API Endpoints

### Public Endpoints
- `GET /` - Main wheel page
- `GET /api/wheel` - Get wheel configuration
- `POST /api/spin` - Spin the wheel and get winner

### Admin Endpoints (Authentication Required)
- `GET /dashboard` - Admin dashboard
- `GET /participants` - Manage participants
- `POST /participants` - Add new participant
- `PUT /participants/{id}` - Update participant
- `DELETE /participants/{id}` - Delete participant
- `POST /participants/spin-duration` - Update spin duration
- `POST /participants/background` - Upload background image
- `POST /participants/background/{id}/activate` - Activate background
- `DELETE /participants/background/{id}` - Delete background
- `GET /register` - Create new admin (admin only)
- `POST /register` - Register new admin (admin only)

## 🛠️ Troubleshooting

### Common Issues

#### 1. "Vite manifest not found" Error
```bash
# Solution: The app uses custom views without Vite dependencies
# No action needed - this is expected behavior
```

#### 2. Database Connection Issues
```bash
# Check database credentials in .env
# Ensure database exists and user has proper permissions
php artisan config:clear
php artisan cache:clear
```

#### 3. Session Issues
```bash
# Clear session cache
php artisan session:table  # If using database sessions
php artisan migrate
```

#### 4. Permission Issues
```bash
# Set proper file permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

#### 5. Composer Issues
```bash
# Clear composer cache
composer clear-cache
composer install --no-cache
```

### Debug Mode
```bash
# Enable debug mode in .env
APP_DEBUG=true

# View detailed error logs
tail -f storage/logs/laravel.log
```

## 📁 Project Structure

```
Wheel_Of_Names/
├── app/
│   ├── Http/Controllers/
│   │   ├── WheelController.php
│   │   ├── ParticipantController.php
│   │   └── Auth/
│   ├── Models/
│   │   ├── Participant.php
│   │   ├── Spin.php
│   │   └── WheelSetting.php
│   └── ...
├── database/
│   ├── migrations/
│   └── seeders/
│       └── AdminUserSeeder.php
├── resources/
│   └── views/
│       ├── wheel.blade.php
│       ├── participants/
│       ├── auth/
│       └── dashboard.blade.php
├── routes/
│   ├── web.php
│   └── auth.php
├── storage/
│   └── app/public/backgrounds/
└── public/
    └── storage -> ../storage/app/public
```

## 🎨 Customization

### Adding Custom Backgrounds
1. Login as admin
2. Go to "Manage Participants"
3. Upload background images (JPG, PNG, GIF)
4. Activate desired background
5. Background will be applied to the wheel

### Modifying Spin Duration
1. Login as admin
2. Go to "Manage Participants"
3. Adjust "Spin Duration" setting (0.3 - 10 seconds)
4. Save changes
5. New duration applies to all future spins

### Styling Customizations
- Edit `resources/views/wheel.blade.php` for wheel appearance
- Modify CSS in the `<style>` sections
- Update colors, fonts, and animations as needed

---

**Happy Spinning! 🎯**
