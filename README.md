# TIGL - Tournament & Individual Golf League Points System

🏌️‍♂️ **TIGL Points** is a comprehensive, web-based golf league management system built for competitive golf events and tournaments. This robust platform handles everything from player registration and handicap management to detailed hole-by-hole scoring and advanced points calculation systems.

## 🌟 Core Features

### 🔐 **Authentication & Access Control**
- **PIN-based Authentication**: Secure 4-digit PIN login system
- **Role-based Access**: Separate Admin and Player access levels
- **Session Management**: Secure token-based sessions with 24-hour expiry
- **User Profiles**: Complete player management with handicap tracking

### 🏌️ **Tournament & Course Management**
- **Season Management**: Create and manage multiple tournament seasons with year-based organization
- **Course Administration**: 
  - Support for 9-hole and 18-hole courses
  - Detailed hole information (par, yardage, handicap ratings)
  - Course overview displays with total par calculations
- **Round Tracking**: Complete round lifecycle from start to completion

### 📊 **Advanced Scoring System**
- **Flexible Points Configuration**: Fully customizable scoring system with:
  - Albatross (-3): Default 3.0 points
  - Eagle (-2): Default 2.5 points  
  - Birdie (-1): Default 1.5 points
  - Par (0): Default 1.0 points
  - Bogey (+1): Default 0.5 points
  - Double Bogey (+2): Default 0.0 points
  - Triple Bogey & Worse: Negative points
- **Match Bonuses**: Win/Loss point modifiers
- **Round Performance**: Bonus points for under-par and even-par rounds
- **Penalty Tracking**: Out-of-bounds and penalty stroke points deduction

### 🎯 **Handicap & Net Scoring**
- **Individual Handicaps**: Player-specific handicap management
- **Round Handicap Override**: Ability to use different handicap for specific rounds
- **Net Score Calculations**: Automatic net scoring based on handicap and hole difficulty
- **Quota System**: Advanced quota calculations for fair competition

### 📱 **Mobile-Optimized Interface**
- **Responsive Design**: Mobile-first approach with touch-friendly controls
- **Live Scoring**: Real-time hole-by-hole score entry
- **Table Optimization**: Horizontal scrolling for mobile table viewing
- **Progressive Web App**: Optimized for mobile golf course usage

### 📈 **Statistics & Analytics**
- **Round History**: Complete player round tracking with detailed statistics
- **Performance Metrics**: Score analysis, handicap trends, and improvement tracking
- **Leaderboards**: Season-based point standings and rankings
- **Course Statistics**: Performance analysis by course and hole difficulty

## 🏗️ **Technical Architecture**

### **Frontend Technology Stack**
- **HTML5/CSS3**: Modern semantic markup with advanced CSS Grid and Flexbox
- **Vanilla JavaScript**: ES6+ features with async/await for API interactions
- **Responsive Framework**: Custom CSS framework optimized for mobile golf applications
- **Progressive Enhancement**: Graceful degradation for various device capabilities

### **Backend Infrastructure**
- **PHP 8.0+**: Modern PHP with strict typing and error handling
- **MySQL 8.0**: Relational database with optimized queries and indexing
- **RESTful API Design**: Clean separation between frontend and backend services
- **Session Management**: Secure token-based authentication with database session storage

### **Database Architecture**
The system uses a normalized relational database with the following key tables:

- **`users`** - Player and administrator account management with roles and handicaps
- **`user_sessions`** - Secure session token storage with expiration tracking
- **`seasons`** - Tournament season organization and management
- **`courses`** - Golf course information with hole count and metadata
- **`holes`** - Individual hole details including par, yardage, and handicap ratings
- **`rounds`** - Round tracking with player, course, season, and handicap associations
- **`hole_scores`** - Detailed hole-by-hole scoring with penalties and points calculation
- **`points_config`** - Configurable scoring system with customizable point values

### **Security Features**
- **Input Validation**: Comprehensive server-side validation and sanitization
- **SQL Injection Prevention**: Prepared statements for all database operations
- **Session Security**: Secure HTTP-only cookies with token-based authentication
- **Error Handling**: Structured error logging and user-friendly error messages

## 📁 **Project Structure & File Organization**

```
TIGL/
├── 📄 index.html                    # Main single-page application interface
├── ⚙️  config.php                   # Database connection configuration
├── 🔐 SessionManager.php            # User session and authentication handler
├── 🧮 PointsCalculator.php          # Golf scoring and points calculation engine
│
├── 🎨 styles/                       # Frontend styling and responsive design
│   ├── styles.css                   # Main application styles and layout
│   └── mobile.css                   # Mobile-specific responsive enhancements
│
├── 🔑 Authentication APIs/          
│   ├── login.php                    # PIN-based user authentication
│   ├── logout.php                   # Session termination and cleanup
│   └── check_session.php            # Session validation middleware
│
├── 👥 Player Management APIs/       
│   ├── addplayer.php               # New player registration
│   ├── editplayer.php              # Player profile updates
│   ├── getplayers.php              # Player listing and information
│   ├── getplayerdetails.php        # Individual player data retrieval
│   └── getplayerstats.php          # Player performance statistics
│
├── 🏌️ Course & Season APIs/        
│   ├── addcourse.php               # Course creation and setup
│   ├── editcourse.php              # Course information updates
│   ├── getcourses.php              # Course listing with metadata
│   ├── getcourseoverview.php       # Detailed course hole information
│   ├── saveholes.php               # Hole detail configuration
│   ├── addseason.php               # Tournament season creation
│   ├── activateseason.php          # Season management and activation
│   └── getseasons.php              # Season listing and status
│
├── 🏃 Round Management APIs/        
│   ├── startround.php              # Round initialization with handicap selection
│   ├── getmyrounds.php             # Player round history and status
│   ├── getrounddetails.php         # Detailed round scoring information
│   ├── getroundsbyseason.php       # Season-specific round listings
│   ├── getroundstatus.php          # Round completion status tracking
│   ├── getroundsummary.php         # Round statistical summaries
│   ├── completematch.php           # Round finalization processing
│   └── deleteround.php             # Round deletion and cleanup
│
├── 📊 Scoring APIs/                 
│   ├── saveholescore.php           # Real-time hole score recording
│   ├── savematchresult.php         # Match completion and final scoring
│   ├── getpoints.php               # Points configuration retrieval
│   ├── savepoints.php              # Points system configuration updates
│   ├── resetdefaultpoints.php      # Reset to default scoring values
│   └── getholeinfo.php             # Individual hole information
│
├── 🧪 Development & Testing/        
│   ├── test_db.php                 # Database connection and table verification
│   ├── test_login.php              # Authentication system testing
│   ├── submit.php                  # Form submission handler
│   └── session_info.php            # Session debugging and information
│
└── 📚 Documentation/                
    ├── README.md                   # Comprehensive system documentation
    └── error_log                   # Application error logging
```

### **API Endpoint Categories**

**🔐 Authentication Endpoints**
- Secure PIN-based login system with session management
- Token-based authentication with automatic expiration

**👥 Player Management Endpoints**  
- Complete CRUD operations for player accounts
- Handicap management and statistical tracking
- Role-based access control (Admin/Player)

**🏌️ Course & Tournament Endpoints**
- Golf course creation with detailed hole information
- Season management and tournament organization
- Multi-course tournament support

**📊 Scoring & Statistics Endpoints**
- Real-time score entry and calculation
- Advanced points system with configurable values
- Comprehensive performance analytics and reporting

## 🚀 **Installation & Setup**

### **System Requirements**
- **PHP**: Version 8.0 or higher with MySQLi extension
- **MySQL**: Version 8.0 or higher (MariaDB 10.4+ also supported)
- **Web Server**: Apache 2.4+ or Nginx 1.18+ with PHP support
- **Browser Support**: Modern browsers with ES6+ JavaScript support

### **Installation Process**

#### **1. Repository Setup**
```bash
# Clone the repository
git clone https://github.com/micheal0484/TIGL.git
cd TIGL

# Set appropriate permissions (Linux/macOS)
chmod 644 *.php
chmod 755 .
```

#### **2. Database Configuration**
```sql
-- Create database
CREATE DATABASE tigl_points CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create database user (optional but recommended)
CREATE USER 'tigl_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON tigl_points.* TO 'tigl_user'@'localhost';
FLUSH PRIVILEGES;
```

#### **3. Application Configuration**
Update the database connection settings in `config.php`:

```php
<?php
$servername = "localhost";           // Your database server
$username = "tigl_user";             // Database username
$password = "secure_password";       // Database password  
$dbname = "tigl_points";             // Database name

// Create connection with error handling
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

#### **4. Database Schema Creation**
**Important:** The application requires the following database tables to be created. You can create them manually or they will be created automatically when first accessed:

```sql
-- Users table for player and admin accounts
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    pin CHAR(4) NOT NULL,
    handicap DECIMAL(3,1) DEFAULT 0.0,
    isAdmin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table for golf course information
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    holes INT NOT NULL CHECK (holes IN (9, 18)),
    par_total INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Holes table for detailed course information  
CREATE TABLE holes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    hole_number INT NOT NULL,
    par INT NOT NULL CHECK (par BETWEEN 3 AND 5),
    yardage INT DEFAULT NULL,
    handicap INT DEFAULT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY unique_course_hole (course_id, hole_number)
);

-- Seasons table for tournament organization
CREATE TABLE seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    year INT NOT NULL UNIQUE,
    is_active TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rounds table for tracking individual rounds
CREATE TABLE rounds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    season_id INT NOT NULL,
    round_date DATE NOT NULL,
    handicap_used DECIMAL(3,1) NOT NULL,
    is_completed TINYINT(1) DEFAULT 0,
    total_score INT DEFAULT NULL,
    total_points DECIMAL(6,2) DEFAULT NULL,
    match_result ENUM('win', 'loss', 'tie') DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (season_id) REFERENCES seasons(id)
);

-- Hole scores table for detailed scoring
CREATE TABLE hole_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    round_id INT NOT NULL,
    hole_number INT NOT NULL,
    score INT NOT NULL,
    penalties INT DEFAULT 0,
    ob_strokes INT DEFAULT 0,
    points DECIMAL(4,2) DEFAULT 0.00,
    FOREIGN KEY (round_id) REFERENCES rounds(id),
    UNIQUE KEY unique_round_hole (round_id, hole_number)
);

-- Points configuration table
CREATE TABLE points_config (
    id INT PRIMARY KEY DEFAULT 1,
    points_albatross DECIMAL(4,2) DEFAULT 3.00,
    points_eagle DECIMAL(4,2) DEFAULT 2.50,
    points_birdie DECIMAL(4,2) DEFAULT 1.50,
    points_par DECIMAL(4,2) DEFAULT 1.00,
    points_bogey DECIMAL(4,2) DEFAULT 0.50,
    points_double_bogey DECIMAL(4,2) DEFAULT 0.00,
    points_triple_bogey DECIMAL(4,2) DEFAULT -1.00,
    points_worse DECIMAL(4,2) DEFAULT -2.00,
    points_penalty_stroke DECIMAL(4,2) DEFAULT -0.50,
    points_ob_stroke DECIMAL(4,2) DEFAULT -2.00,
    points_match_win DECIMAL(4,2) DEFAULT 2.00,
    points_match_loss DECIMAL(4,2) DEFAULT 0.00,
    points_under_par_round DECIMAL(4,2) DEFAULT 3.00,
    points_even_par_round DECIMAL(4,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User sessions table for secure authentication
CREATE TABLE user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    isAdmin TINYINT(1) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create indexes for better performance
CREATE INDEX idx_users_pin ON users(pin);
CREATE INDEX idx_rounds_user_season ON rounds(user_id, season_id);
CREATE INDEX idx_hole_scores_round ON hole_scores(round_id);
CREATE INDEX idx_sessions_token ON user_sessions(session_token);
CREATE INDEX idx_sessions_expires ON user_sessions(expires_at);
```

#### **5. Web Server Deployment**
- Upload all files to your web server's document root or subdirectory
- Ensure PHP has write permissions for session management
- Configure your web server to serve `.php` files
- Test the installation by accessing `test_db.php` to verify database connectivity

#### **6. First Run & Initial Setup**
1. **Access the Application**: Navigate to your TIGL installation URL
2. **Create Admin Account**: Use the "Add Player" function to create your first admin user
3. **Configure Points System**: Navigate to Admin Panel → Manage Points to set scoring values
4. **Add Courses**: Create your golf courses with detailed hole information
5. **Create Seasons**: Set up your tournament seasons
6. **Add Players**: Register additional players for your league

### **🔧 Configuration Options**

#### **Points System Customization**
The scoring system is fully customizable through the admin interface:

- **Score-based Points**: Configure points for each score relative to par
- **Match Bonuses**: Set win/loss bonus points for competitive play  
- **Round Performance**: Bonus points for exceptional round performance
- **Penalty System**: Deduct points for penalties and out-of-bounds shots

#### **Security Configuration**
- **Session Timeout**: Default 24-hour sessions (configurable in SessionManager.php)
- **PIN Requirements**: 4-digit numeric PINs with uniqueness validation
- **Database Security**: Use prepared statements throughout the application

## 🎮 **User Guide & Workflows**

### **👨‍💼 Administrator Workflow**

#### **Initial Setup & Configuration**
1. **System Setup**: Login with admin PIN and access the Admin Panel
2. **Points Configuration**: 
   - Navigate to "Manage Points" to customize scoring system
   - Set values for each score type (albatross through worse than triple bogey)
   - Configure match bonuses and round performance rewards
   - Use "Reset to Defaults" if needed
3. **Course Management**:
   - Add golf courses using "Add Course" (9 or 18 holes)
   - Configure detailed hole information (par, yardage, handicap)
   - Review course overviews to ensure accuracy
4. **Season Organization**:
   - Create new seasons with "Manage Seasons"
   - Set active season for current tournament play
   - Manage multiple concurrent seasons if needed

#### **Player & League Management**
1. **Player Registration**:
   - Add new players with unique 4-digit PINs
   - Set initial handicaps and admin privileges
   - Edit existing player information as needed
2. **Round Oversight**:
   - Monitor active rounds through "Manage Rounds"
   - Review completed rounds and resolve any scoring issues
   - Delete incomplete or problematic rounds when necessary
3. **Statistics & Reporting**:
   - Generate player performance reports
   - Monitor league standings and point distributions
   - Track handicap changes over time

### **⛳ Player Workflow**

#### **Getting Started**
1. **Account Access**: Login using your assigned 4-digit PIN
2. **Profile Review**: Check your current handicap and player statistics
3. **Round Preparation**: Review available courses and current season information

#### **Playing a Round**
1. **Round Initiation**:
   - Select "Start Round" from the player interface
   - Choose your course and confirm your handicap (or override if needed)
   - System creates round record and prepares scoring interface
2. **Live Scoring**:
   - Enter scores hole-by-hole as you play
   - Record penalties and out-of-bounds strokes when applicable
   - System automatically calculates net scores and points
3. **Round Completion**:
   - Complete all holes to finish the round
   - Review final score and point totals
   - Submit match result (win/loss/tie) if applicable

#### **Performance Tracking**
1. **Round History**: View all completed rounds with detailed statistics
2. **Points Analysis**: Track point accumulation and scoring trends  
3. **Handicap Monitoring**: Review handicap changes and performance metrics

### **📊 Scoring System Deep Dive**

#### **Point Calculation Logic**
The TIGL system uses a sophisticated points calculation that considers:

1. **Score Relative to Par**: Each hole score awards points based on performance
   - Better scores (birdies, eagles) award more points
   - Worse scores (bogeys, doubles) award fewer or negative points

2. **Handicap Integration**: Net scoring applies handicap strokes to appropriate holes
   - Handicap strokes reduce the effective par for scoring purposes
   - Creates fair competition across different skill levels

3. **Penalty Adjustments**: Additional penalties modify point calculations
   - Penalty strokes: Configurable point deduction per penalty
   - Out-of-bounds: Separate point deduction system

4. **Match & Round Bonuses**: Additional points for overall performance
   - Match win/loss bonuses encourage competitive play
   - Round performance bonuses reward exceptional rounds (under/even par)

#### **Handicap System**
- **Player Handicaps**: Each player has a profile handicap that can be updated by admins
- **Round Overrides**: Players can use different handicaps for specific rounds
- **Net Scoring**: Handicap strokes are applied to holes based on difficulty ratings
- **Quota Calculations**: Advanced quota system for alternative scoring formats

## ⚙️ **Advanced Configuration**

### **Points System Customization**
Access the points configuration through Admin Panel → Manage Points:

| Score Type | Default Points | Description |
|------------|---------------|-------------|
| Albatross (-3) | 3.0 | Rare exceptional performance |
| Eagle (-2) | 2.5 | Excellent hole performance |
| Birdie (-1) | 1.5 | Above-average performance |
| Par (0) | 1.0 | Standard expected performance |
| Bogey (+1) | 0.5 | Below-average performance |
| Double Bogey (+2) | 0.0 | Poor performance |
| Triple Bogey (+3) | -1.0 | Very poor performance |
| Worse than Triple | -2.0 | Severe performance penalty |

**Penalty & Bonus Points:**
- **Penalty Stroke**: -0.5 points (per penalty)
- **Out of Bounds**: -2.0 points (per OB stroke)
- **Match Win**: +2.0 points (round bonus)
- **Match Loss**: 0.0 points (no bonus)
- **Under Par Round**: +3.0 points (exceptional round)
- **Even Par Round**: +1.0 points (good round)

### **Handicap Management**
- **Profile Handicaps**: Set through player management interface
- **Round Overrides**: Allow different handicaps for specific conditions
- **Net Scoring**: Automatically calculated based on hole handicap ratings
- **Updates**: Only administrators can modify player handicaps

### **Database Optimization**
For large installations with many players and rounds:

```sql
-- Add additional indexes for performance
CREATE INDEX idx_rounds_date ON rounds(round_date);
CREATE INDEX idx_hole_scores_score ON hole_scores(score);
CREATE INDEX idx_users_handicap ON users(handicap);

-- Regular maintenance queries
DELETE FROM user_sessions WHERE expires_at < NOW();
OPTIMIZE TABLE rounds, hole_scores, user_sessions;
```

## 🐛 **Troubleshooting & Known Issues**

### **Common Issues & Solutions**

#### **Database Connection Problems**
```
Error: Connection failed: Access denied for user...
```
**Solution**: Verify database credentials in `config.php` and ensure MySQL user has appropriate permissions.

#### **Missing Database Tables**
```  
Error: Table 'courses' doesn't exist
```
**Solution**: Run the database schema creation scripts provided in the installation section.

#### **Session Issues**
```
Error: Access denied. Admin privileges required.
```
**Solution**: 
1. Clear browser cookies and retry login
2. Check user_sessions table for expired sessions
3. Verify user account has proper isAdmin flag set

#### **Points Calculation Errors**
```
Error: PointsCalculator.php file not found
```
**Solution**: Ensure all core PHP files are properly uploaded and accessible.

### **Performance Optimization**

#### **For High-Volume Installations**
1. **Database Indexing**: Implement additional indexes on frequently queried columns
2. **Session Cleanup**: Set up automated cleanup of expired sessions
3. **Cache Implementation**: Consider implementing query result caching for course/season data
4. **File Optimization**: Minimize and compress CSS/JS files for faster loading

#### **Mobile Performance**
1. **Image Optimization**: Optimize any course images for mobile display
2. **Network Efficiency**: Implement progressive loading for large datasets
3. **Offline Support**: Consider implementing service workers for offline scoring

### **Debug Mode**
Enable debugging by modifying the error reporting in PHP files:
```php
// Add to top of any PHP file for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
```

### **Backup & Recovery**

#### **Database Backup**
```sql
-- Create regular backups
mysqldump -u username -p tigl_points > tigl_backup_$(date +%Y%m%d).sql

-- Restore from backup  
mysql -u username -p tigl_points < tigl_backup_20260321.sql
```

#### **Application Backup**
- Backup all PHP files and configuration
- Include custom modifications to points system
- Store backups in version control system

## 🚀 **Version History & Roadmap**

### **v2.0.0** (Current - March 2026)
- ✅ **Enhanced Database Schema**: Improved table structure with proper relationships and constraints
- ✅ **Advanced Points System**: Configurable scoring with penalty tracking and match bonuses  
- ✅ **Mobile Optimization**: Responsive design with touch-friendly interface
- ✅ **Security Improvements**: Enhanced session management and input validation
- ✅ **Performance Upgrades**: Optimized database queries and indexing
- ✅ **Comprehensive Documentation**: Complete installation and user guides

### **v1.0.0** (August 2025) 
- ✅ Core golf management system
- ✅ Basic scoring and handicap functionality
- ✅ Player and course management
- ✅ Season organization capabilities

### **🚧 Future Roadmap (v3.0+)**

#### **Planned Features**
- **📱 Mobile App**: Native iOS/Android applications
- **🏆 Tournament Brackets**: Elimination tournament management
- **📊 Advanced Analytics**: Machine learning performance insights  
- **🌐 Multi-League Support**: Manage multiple golf leagues
- **🔄 Data Import/Export**: Integration with golf association systems
- **📈 Real-time Leaderboards**: Live tournament tracking
- **💾 Cloud Backup**: Automated backup and restore functionality

#### **Integration Opportunities**  
- **Golf Course APIs**: Weather and course condition integration
- **USGA Integration**: Official handicap system synchronization
- **Payment Processing**: Tournament entry fees and prize management
- **Social Features**: Player communication and group management

## 🤝 **Contributing & Development**

### **Development Environment Setup**
```bash
# Clone for development
git clone https://github.com/micheal0484/TIGL.git
cd TIGL

# Set up local development database
mysql -u root -p -e "CREATE DATABASE tigl_dev;"

# Copy and modify config for development
cp config.php config_dev.php
# Edit config_dev.php with local database settings
```

### **Code Standards**
- **PHP**: Follow PSR-12 coding standards
- **JavaScript**: Use ES6+ features with proper error handling
- **CSS**: Maintain mobile-first responsive design principles
- **SQL**: Use prepared statements for all database operations

### **Testing Guidelines**
- Test all new features on mobile devices
- Verify database operations with various data scenarios
- Ensure cross-browser compatibility
- Test with multiple user roles (admin/player)

### **Contribution Process**
1. Fork the repository and create a feature branch
2. Implement changes with proper error handling
3. Test thoroughly on mobile and desktop platforms  
4. Submit pull request with detailed description
5. Code review and integration by maintainers

### **Feature Requests**
Current development priorities focus on:
- **Enhanced Mobile Experience**: PWA capabilities and offline support
- **Advanced Analytics**: Statistical analysis and trend reporting  
- **Tournament Management**: Bracket systems and multi-round events
- **Integration APIs**: Third-party golf system connectivity

## 📄 **License & Legal**

### **MIT License**
```
MIT License

Copyright (c) 2024-2026 TIGL Development Team

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

### **Third-Party Acknowledgments**
- **MySQL**: Database management system
- **PHP**: Server-side scripting language
- **Modern Web Standards**: HTML5, CSS3, ES6+ JavaScript

## 🏌️‍♂️ **About TIGL**

### **Project Origin**
TIGL (Tournament & Individual Golf League) was developed to address the specific needs of competitive golf leagues requiring:
- Accurate handicap-based scoring systems
- Mobile-friendly round recording capabilities  
- Flexible points-based tournament formats
- Comprehensive player and season management

### **Development Team**
**Lead Developer**: Mike Morrison  
**Project Type**: Open Source Golf Management System  
**Development Period**: 2024-2026  
**Primary Language**: PHP with MySQL backend

### **Community & Support**
- **GitHub Issues**: Report bugs and feature requests
- **Documentation**: Comprehensive guides and API documentation
- **Community Forums**: Share configurations and best practices
- **Professional Support**: Available for enterprise deployments

---

## 🎯 **Quick Start Summary**

### **For Golf League Administrators**
1. **Install** TIGL on your web server with PHP and MySQL
2. **Configure** database connection and create required tables  
3. **Setup** your first admin account and configure points system
4. **Add** golf courses with detailed hole information
5. **Create** tournament seasons and register players
6. **Launch** your digital golf league management system

### **For Players**  
1. **Login** with your assigned 4-digit PIN
2. **Start** a new round by selecting course and confirming handicap
3. **Record** scores hole-by-hole during your round
4. **Complete** round and submit final results
5. **Track** your performance and points accumulation over time

---

**🏆 TIGL v2.0 - Professional Golf League Management Made Simple! ⛳**

*Transform your golf league with modern digital scoring, handicap management, and tournament organization.*
