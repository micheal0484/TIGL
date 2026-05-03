# TIGL - Tournament & Individual Golf League Points System

**TIGL Points** is a web-based golf league management system built as a single-page application (SPA) using vanilla JavaScript, PHP, and MySQL. It handles player management, course configuration, season tracking, hole-by-hole scoring, and a configurable points system with role-based access for admins and players.

## Core Features

### Authentication & Access Control
- **PIN-based Authentication**: 4-digit PIN login system
- **Role-based Access**: Separate Admin and Player interfaces
- **Session Management**: Server-side token-based sessions with automatic expiration
- **Continuous Validation**: Session verified on every API call

### Admin Features
- **Player Management**: Add, edit, and view all players including handicap and PIN updates
- **Course Management**: Create 9-hole or 18-hole courses; configure par, yardage, and handicap per hole
- **Season Management**: Create seasons by year, set active season, manage multiple concurrent seasons
- **Points Configuration**: Customize all scoring point values; reset to defaults at any time
- **Round Oversight**: View all rounds by season, access full scoring details, delete rounds

### Player Features
- **Start a Round**: Select course, season, and handicap (use profile default or override per round)
- **Hole-by-Hole Scoring**: Progressive scoring with per-hole par display, score entry, penalties, and OB strokes
- **Resume Incomplete Rounds**: Continue from the last scored hole
- **Edit Completed Rounds**: Reopen, modify scores, and re-submit match results
- **Round History**: View all rounds by season with full scoring breakdowns
- **Season Statistics**: Review performance metrics across seasons

### Scoring System
- Points awarded per hole based on score relative to net par (handicap applied)
- Penalty strokes and OB strokes each carry configurable point deductions
- Match result bonus (win/loss) applied at round completion
- Round performance bonuses for under-par and even-par net rounds
- All point values are configurable by admin; factory defaults can be restored

## Technical Architecture

### Stack
- **Frontend**: Single-page HTML application, vanilla JavaScript (ES6+), CSS Grid/Flexbox
- **Backend**: PHP 8.0+ API endpoints, MySQLi with prepared statements
- **Database**: MySQL 8.0 (MariaDB 10.4+ also supported)
- **Authentication**: Token-based sessions stored in the database with expiration tracking

### Database Tables
| Table | Purpose |
|-------|---------|
| `users` | Player accounts with username, PIN, handicap, admin flag |
| `user_sessions` | Session tokens with expiry |
| `seasons` | Tournament seasons by year with active status |
| `courses` | Golf course metadata (name, hole count) |
| `holes` | Per-hole details: par, yardage, men's/women's handicap |
| `rounds` | Round records: player, course, season, date, handicap used, completion status |
| `hole_scores` | Per-hole scoring: strokes, penalties, OB, calculated points |
| `points_config` | All configurable point values (single row, id=1) |

### Security
- All database operations use prepared statements
- Input validated server-side on every endpoint
- Role-based authorization checks on all admin endpoints
- Sessions expire automatically; tokens are validated per request

## Project Structure

```
TIGL/
├── index.html                   # Single-page application entry point
├── config.php                   # Database connection configuration
├── SessionManager.php           # Session token creation and validation
├── PointsCalculator.php         # Points calculation engine
│
├── styles/
│   ├── styles.css               # Main application styles
│   ├── modern-golf.css          # Golf-themed design system
│   └── mobile.css               # Mobile-specific responsive styles
│
├── login.php                    # PIN authentication
├── logout.php                   # Session termination
├── check_session.php            # Session validation
├── session_info.php             # Session debug info
│
├── addplayer.php                # Create player account
├── editplayer.php               # Update player details
├── getplayers.php               # List all players
├── getplayerdetails.php         # Single player data
├── getplayerstats.php           # Player performance statistics
│
├── addcourse.php                # Create course
├── editcourse.php               # Update course
├── getcourses.php               # List courses
├── getcompletecourses.php       # Courses with full hole data
├── getcourseoverview.php        # Course hole layout summary
├── geteditableholes.php         # Hole data for editing
├── saveholes.php                # Save hole configuration
│
├── addseason.php                # Create season
├── activateseason.php           # Set active season
├── getseasons.php               # List seasons
│
├── startround.php               # Initialize a new round
├── getmyrounds.php              # Player's round history
├── getrounddetails.php          # Full round scoring detail
├── getroundsbyseason.php        # Rounds filtered by season
├── getroundstatus.php           # Round completion status
├── getroundsummary.php          # Round summary statistics
├── completematch.php            # Finalize round with match result
├── reopenround.php              # Reopen completed round for editing
├── deleteround.php              # Delete a round
│
├── saveholescore.php            # Save individual hole score
├── savematchresult.php          # Save final match result
├── getholeinfo.php              # Hole info for scoring display
├── getuserhandicap.php          # Retrieve user handicap options
│
├── getpoints.php                # Retrieve points configuration
├── savepoints.php               # Update points configuration
├── resetdefaultpoints.php       # Reset points to defaults
│
├── test_db.php                  # Database connectivity check
├── test_login.php               # Login system test
├── submit.php                   # Form submission handler
└── session_info.php             # Session debugging
```

## Installation & Setup

### Requirements
- PHP 8.0+ with MySQLi extension
- MySQL 8.0+ or MariaDB 10.4+
- Apache 2.4+ or Nginx 1.18+ with PHP support
- Modern browser with ES6+ support

### 1. Clone the Repository
```bash
git clone https://github.com/micheal0484/TIGL.git
cd TIGL
```

### 2. Create the Database
```sql
CREATE DATABASE tigl_points CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'tigl_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON tigl_points.* TO 'tigl_user'@'localhost';
FLUSH PRIVILEGES;
```

### 3. Configure the Application
Edit `config.php` with your database credentials:

```php
<?php
$servername = "localhost";
$username   = "tigl_user";
$password   = "secure_password";
$dbname     = "tigl_points";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
```

### 4. Create the Database Schema
Run the following SQL to create all required tables:

```sql
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50) UNIQUE NOT NULL,
    pin        CHAR(4) NOT NULL,
    handicap   DECIMAL(3,1) DEFAULT 0.0,
    isAdmin    TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100) NOT NULL,
    holes      INT NOT NULL CHECK (holes IN (9, 18)),
    par_total  INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE holes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    course_id   INT NOT NULL,
    hole_number INT NOT NULL,
    par         INT NOT NULL CHECK (par BETWEEN 3 AND 5),
    yardage     INT DEFAULT NULL,
    handicap    INT DEFAULT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id),
    UNIQUE KEY unique_course_hole (course_id, hole_number)
);

CREATE TABLE seasons (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    year       INT NOT NULL UNIQUE,
    is_active  TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE rounds (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    user_id        INT NOT NULL,
    course_id      INT NOT NULL,
    season_id      INT NOT NULL,
    round_date     DATE NOT NULL,
    handicap_used  DECIMAL(3,1) NOT NULL,
    is_completed   TINYINT(1) DEFAULT 0,
    total_score    INT DEFAULT NULL,
    total_points   DECIMAL(6,2) DEFAULT NULL,
    match_result   ENUM('win', 'loss', 'tie') DEFAULT NULL,
    FOREIGN KEY (user_id)   REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES courses(id),
    FOREIGN KEY (season_id) REFERENCES seasons(id)
);

CREATE TABLE hole_scores (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    round_id    INT NOT NULL,
    hole_number INT NOT NULL,
    score       INT NOT NULL,
    penalties   INT DEFAULT 0,
    ob_strokes  INT DEFAULT 0,
    points      DECIMAL(4,2) DEFAULT 0.00,
    FOREIGN KEY (round_id) REFERENCES rounds(id),
    UNIQUE KEY unique_round_hole (round_id, hole_number)
);

CREATE TABLE points_config (
    id                   INT PRIMARY KEY DEFAULT 1,
    points_albatross     DECIMAL(4,2) DEFAULT  3.00,
    points_eagle         DECIMAL(4,2) DEFAULT  2.50,
    points_birdie        DECIMAL(4,2) DEFAULT  1.50,
    points_par           DECIMAL(4,2) DEFAULT  1.00,
    points_bogey         DECIMAL(4,2) DEFAULT  0.50,
    points_double_bogey  DECIMAL(4,2) DEFAULT  0.00,
    points_triple_bogey  DECIMAL(4,2) DEFAULT -1.00,
    points_worse         DECIMAL(4,2) DEFAULT -2.00,
    points_penalty_stroke DECIMAL(4,2) DEFAULT -0.50,
    points_ob_stroke     DECIMAL(4,2) DEFAULT -2.00,
    points_match_win     DECIMAL(4,2) DEFAULT  2.00,
    points_match_loss    DECIMAL(4,2) DEFAULT  0.00,
    points_under_par_round DECIMAL(4,2) DEFAULT 3.00,
    points_even_par_round  DECIMAL(4,2) DEFAULT 1.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default points configuration row
INSERT INTO points_config (id) VALUES (1);

CREATE TABLE user_sessions (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    session_token VARCHAR(64) UNIQUE NOT NULL,
    user_id       INT NOT NULL,
    username      VARCHAR(50) NOT NULL,
    isAdmin       TINYINT(1) NOT NULL,
    expires_at    DATETIME NOT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE INDEX idx_users_pin          ON users(pin);
CREATE INDEX idx_rounds_user_season ON rounds(user_id, season_id);
CREATE INDEX idx_hole_scores_round  ON hole_scores(round_id);
CREATE INDEX idx_sessions_token     ON user_sessions(session_token);
CREATE INDEX idx_sessions_expires   ON user_sessions(expires_at);
```

### 5. Deploy and Verify
- Upload all files to your web server document root
- Confirm PHP has write access for session handling
- Open `test_db.php` in a browser to verify database connectivity
- Remove or restrict access to `test_db.php` and `test_login.php` before going to production

### 6. First-Time Setup
1. Navigate to your TIGL URL and log in with an admin account
2. Go to **Admin Panel → Manage Points** to review or adjust point values
3. Add your golf courses under **Course Management** and configure each hole
4. Create your first season under **Season Management** and set it as active
5. Register players under **Player Management**

## User Guide

### Administrator Workflows

#### Player Management
1. **Login** → Admin Panel → Player Management
2. **Add Player**: Enter username, PIN, handicap, and admin flag → Submit
3. **Edit Player**: Select from player list → Modify fields → Save
4. **View Players**: Browse the full player list with details

#### Course Setup
1. **Add Course**: Enter name and hole count (9 or 18) → Submit
2. **Configure Holes**: Enter par, yardage, and handicap rating for each hole → Save
3. **Review**: Use Course Overview to verify the complete layout

#### Season Management
1. **Create Season**: Enter year → Submit
2. **Activate**: Select a season → Activate to set it as the default for new rounds
3. **View All**: Review season list with active/inactive status

#### Round Administration
1. Filter rounds by season to review activity
2. Open any round for a full hole-by-hole scoring breakdown
3. Delete rounds with the confirmation prompt when needed

### Player Workflows

#### Playing a Round
1. **Start Round**: Select course → Select season (active season pre-selected) → Confirm handicap → Begin
2. **Score Each Hole**: View hole info (par, yardage, handicap) → Enter strokes, penalties, OB → Next hole
3. **Complete Round**: Review summary → Select match result (won/lost) → Submit

#### Managing Rounds
- **Resume**: Incomplete rounds appear in your round list; select to continue from last scored hole
- **Edit**: Reopen a completed round, adjust scores, and re-submit match result
- **History**: Filter rounds by season and open any round for full details

#### Statistics
- Select a season to load your performance statistics for that period
- Review scoring averages, points totals, and round counts

## Points Reference

| Score | Default Points |
|-------|---------------|
| Albatross (-3) | 3.0 |
| Eagle (-2) | 2.5 |
| Birdie (-1) | 1.5 |
| Par (0) | 1.0 |
| Bogey (+1) | 0.5 |
| Double Bogey (+2) | 0.0 |
| Triple Bogey (+3) | -1.0 |
| Worse than Triple | -2.0 |

| Modifier | Default Points |
|----------|---------------|
| Penalty stroke | -0.5 per stroke |
| Out of bounds | -2.0 per stroke |
| Match win bonus | +2.0 |
| Match loss | 0.0 |
| Under-par round bonus | +3.0 |
| Even-par round bonus | +1.0 |

All values are configurable in Admin Panel → Manage Points. Use "Reset to Defaults" to restore the values above.

## Troubleshooting

**Database connection fails**
Verify credentials in `config.php` and confirm the MySQL user has full privileges on the `tigl_points` database.

**Class "mysqli" not found**
PHP is running without the MySQL extension. Enable `mysqli` in `php.ini` (`extension=mysqli`), restart your terminal/server, and verify with `php -m`.

**Remote DB access denied from local machine**
If using a hosted database, set `servername` in `config.php` to the remote MySQL host (not `localhost`) and whitelist your public IP in the provider's Remote MySQL/host access settings.

**"Table does not exist" errors**
Run the full schema creation SQL from the Installation section. Tables are not created automatically.

**Session / access denied errors**
Clear browser cookies, log out and log back in. Check that the `user_sessions` table exists and that the user account has the correct `isAdmin` value.

**Points not calculating**
Confirm the `points_config` table has exactly one row with `id = 1`. Run the `INSERT INTO points_config (id) VALUES (1);` statement if missing.

**Debug mode**
Add the following to any PHP file to enable verbose error output:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
```

## Development

### Local Setup (Windows + PHP Built-in Server)

#### 1. Install PHP with MySQL support
Make sure PHP 8+ is installed and available in PATH.

Check installation:

```powershell
php --version
php -m
```

Required module list must include:
- `mysqli`
- `pdo_mysql` (recommended)

If `mysqli` is missing:
- Create or edit `php.ini`
- Enable `extension=mysqli`
- Restart terminal/session and verify with `php -m`

#### 2. Clone and open the project

```powershell
git clone https://github.com/micheal0484/TIGL.git
cd TIGL
```

#### 3. Configure database connection

Edit `config.php` for one of these test modes:

- Local database mode:
    - `servername = localhost`
    - local MySQL user/password/database
- Remote existing database mode:
    - `servername = <remote mysql hostname>` (not localhost)
    - remote MySQL user/password/database

Important for remote DB mode:
- Add your local public IP to the host allowlist in your hosting panel (often "Remote MySQL")
- Ensure port `3306` is reachable from your machine

#### 4. Start the local web server

From the project root:

```powershell
php -S localhost:8080
```

Open:
- `http://localhost:8080`

#### 5. Verify API/database connectivity before login tests

Open:
- `http://localhost:8080/test_db.php`

If this works, continue to normal login and round testing.

### Local Setup (Apache/Nginx alternative)

If you prefer Apache/Nginx (XAMPP, Laragon, WAMP), place the repo in your web root, point `config.php` to your target DB, then browse to the project URL. Functional behavior is the same as the PHP built-in server mode.

### Code Standards
- **PHP**: PSR-12, prepared statements for all queries, validate all inputs server-side
- **JavaScript**: ES6+ with async/await, no external dependencies
- **CSS**: Mobile-first, use existing CSS custom properties for theming
- **SQL**: Prepared statements only; no dynamic query string building

### Contribution Process
1. Fork the repository and create a feature branch
2. Test with both admin and player roles on mobile and desktop
3. Submit a pull request with a clear description of the change

## Database Maintenance

```sql
-- Remove expired sessions
DELETE FROM user_sessions WHERE expires_at < NOW();

-- Optimize high-traffic tables
OPTIMIZE TABLE rounds, hole_scores, user_sessions;

-- Backup
mysqldump -u username -p tigl_points > tigl_backup_$(date +%Y%m%d).sql

-- Restore
mysql -u username -p tigl_points < tigl_backup_20260426.sql
```

## License

MIT License — Copyright (c) 2024-2026 TIGL Development Team

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions: The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.

---

**Lead Developer**: Mike Morrison | **Language**: PHP + MySQL + Vanilla JS | **License**: MIT
