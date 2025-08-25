# TIGL - Golf Management System v1.0

🏌️‍♂️ **TIGL Points** is a comprehensive, mobile-first golf management system designed for golf leagues and events. Built with PHP, MySQL, and responsive web technologies, it provides complete administration and player management capabilities.

## 🌟 Features

### 🔐 **User Management**
- **Dual Access Levels**: Admin and Player accounts
- **Secure Authentication**: PIN-based login system with session management
- **Player Profiles**: Individual handicap tracking and statistics

### 🏌️ **Event Management**
- **Season Management**: Create and manage multiple seasons
- **Course Management**: Add courses with detailed hole information (par, yardage, handicaps)
- **Round Management**: Complete round tracking with hole-by-hole scoring

### 📊 **Scoring System**
- **Configurable Points**: Customizable point values for different score types
  - Par, Birdie, Eagle, Bogey, Double Bogey scoring
  - Match result bonuses (Win/Loss)
  - Round performance bonuses (Under par, Even par)
- **Handicap System**: Individual player handicaps with round-specific overrides
- **Net Scoring**: Automatic net score calculations

### 📱 **Mobile-First Design**
- **Responsive Interface**: Optimized for mobile devices and tablets
- **Touch-Friendly**: Large buttons and intuitive navigation
- **Horizontal Scrolling Tables**: Mobile-optimized table viewing

### 📈 **Statistics & Reporting**
- **Player Statistics**: Individual performance tracking and averages
- **Season Leaderboards**: Points-based rankings with detailed breakdowns
- **Round Summaries**: Comprehensive hole-by-hole analysis
- **Historical Data**: Complete tournament history tracking

## 🏗️ **System Architecture**

### **Frontend**
- **HTML5/CSS3**: Modern web standards with responsive design
- **JavaScript**: Dynamic content loading and form handling
- **Mobile CSS Framework**: Custom responsive grid and component system

### **Backend**
- **PHP 7.4+**: Server-side logic and API endpoints
- **MySQL**: Relational database for all tournament data
- **Session Management**: Secure user authentication and authorization
- **RESTful APIs**: Clean separation between frontend and backend

### **Database Schema**
- `users` - Player and admin account management
- `seasons` - Tournament season tracking
- `courses` - Golf course information and hole details
- `rounds` - Individual round tracking
- `hole_scores` - Detailed hole-by-hole scoring
- `points_config` - Configurable scoring system
- `user_sessions` - Secure session management

## 📁 **Project Structure**

```
TIGL/
├── index.html              # Main application interface
├── config.php              # Database configuration
├── SessionManager.php      # User session handling
├── PointsCalculator.php    # Scoring logic engine
├── styles/
│   ├── styles.css          # Main application styles
│   └── mobile.css          # Mobile-specific enhancements
├── API Endpoints/
│   ├── login.php           # User authentication
│   ├── addplayer.php       # Player management
│   ├── addcourse.php       # Course creation
│   ├── addseason.php       # Season management
│   ├── startround.php      # Round initialization
│   ├── saveholescore.php   # Hole score recording
│   ├── savematchresult.php # Match result recording
│   └── [additional APIs]   # Complete API suite
└── README.md               # This documentation
```

## 🚀 **Installation**

### **Prerequisites**
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)

### **Setup Steps**

1. **Clone the repository**
   ```bash
   git clone https://github.com/micheal0484/TIGL.git
   cd TIGL
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the database schema (see config.php for table structures)
   - Update database credentials in `config.php`

3. **Configuration**
   ```php
   // config.php
   $host = 'your-db-host';
   $username = 'your-db-username';
   $password = 'your-db-password';
   $database = 'your-database-name';
   ```

4. **Web Server**
   - Deploy files to your web server document root
   - Ensure PHP has MySQL extension enabled
   - Set appropriate file permissions

5. **First Run**
   - Access the application via web browser
   - Create your first admin account
   - Configure point values and create your first season

## 🎮 **Usage**

### **For Administrators**
1. **Login** with admin credentials
2. **Manage Players** - Add, edit, and track player handicaps
3. **Setup Courses** - Add golf courses with hole details
4. **Create Seasons** - Start new tournament seasons
5. **Configure Points** - Set scoring values and bonuses
6. **Monitor Progress** - View rounds and statistics

### **For Players**
1. **Login** with player PIN
2. **Play Rounds** - Record scores hole-by-hole
3. **View Statistics** - Track personal performance
4. **Review History** - See past rounds and improvement

## 🔧 **Configuration**

### **Points System**
- Navigate to Admin → Manage Points
- Configure values for: Par, Birdie, Eagle, Bogey, Double Bogey
- Set match result bonuses and round performance bonuses

### **Handicap System**
- Players have profile handicaps
- Round-specific handicap overrides available
- Automatic net score calculations

## 🐛 **Known Issues**
- None reported for core functionality

## 🚀 **Version History**

### **v1.0.0** (August 2025)
- ✅ Complete management system
- ✅ Mobile-responsive design
- ✅ Full scoring and handicap system
- ✅ Season and course management
- ✅ Player statistics and leaderboards
- ✅ Secure authentication system

## 🤝 **Contributing**

This project is feature-complete for v1.0. Future enhancements may include:
- Tournament bracket management
- Advanced reporting features
- Mobile app development
- Integration with golf APIs

## 📄 **License**

This project is licensed under the MIT License. See the LICENSE file for more details.

## 👨‍💻 **Author**

**Mike** - Golf tournament management system developer

---

**TIGL v1.0** - Ready for Golf season! 🏆⛳
