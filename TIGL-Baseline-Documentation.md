# TIGL Points System - Baseline Functionality Documentation

## System Overview

The TIGL (Tournament & Individual Golf League) Points System is a comprehensive golf league management web application built as a single-page application (SPA) using vanilla JavaScript, modern CSS, and PHP backend APIs. The system supports tournament management, scoring, statistics tracking, and player management with role-based access control.

### Core Architecture
- **Frontend**: Single-page HTML application with vanilla JavaScript
- **Styling**: Modern responsive CSS with golf-themed design
- **Backend**: PHP API endpoints with MySQL database
- **Authentication**: PIN-based login with server-side session management
- **Data Storage**: MySQL database with comprehensive relational schema

## User Roles and Authentication

### Authentication System
- **Login Method**: 4-digit PIN-based authentication
- **Session Management**: Server-side sessions with token-based validation  
- **Session Persistence**: Client-side storage with server verification
- **Security Features**: Automatic session expiration, logout functionality

### User Roles

#### Admin Users
- **Full System Access**: Complete control over all system functionality
- **Player Management**: Add, edit, view all players and their details
- **Course Management**: Create, edit, view golf courses and hole configurations
- **Season Management**: Create seasons, activate/deactivate, manage tournaments
- **Points System**: Configure scoring rules and point values for all game scenarios
- **Round Management**: View all rounds across all players and seasons
- **Administrative Interface**: Dedicated admin panel with comprehensive controls

#### Regular Players
- **Round Play**: Start and complete golf rounds with hole-by-hole scoring
- **Score Tracking**: Enter scores, penalties, and OB strokes for each hole
- **Statistics**: View personal performance statistics across seasons
- **Round History**: Access complete history of played rounds with details
- **Round Editing**: Edit and complete previously started but unfinished rounds
- **Personal Dashboard**: Player-focused interface for golf activities

## Core Features by Role

### Admin Panel Features

#### Player Management
- **Add New Players**: Create player accounts with username, PIN, handicap, admin privileges
- **Edit Existing Players**: Modify player information, change PINs, update handicaps
- **View All Players**: Comprehensive player list with details and statistics
- **Player Selection Interface**: Easy player selection for editing with visual buttons

#### Course Management  
- **Add New Courses**: Create golf courses with 9 or 18 holes
- **Hole Configuration**: Define par, yardage, men's/women's handicap for each hole
- **Course Overview**: View complete course layouts and statistics
- **Edit Courses**: Modify existing course information and hole details
- **Course Listing**: Browse all available courses with key information

#### Season Management
- **Create Seasons**: Start new tournament seasons by year
- **Activate Seasons**: Set active season for default round assignments
- **Season Status**: Track active/inactive status across all seasons
- **Multi-Season Support**: Maintain historical data across multiple seasons

#### Points System Configuration
- **Score-Based Points**: Configure points for par, birdie, eagle, albatross, bogeys, etc.
- **Penalty Points**: Set point deductions for penalty strokes and OB shots
- **Match Result Points**: Define points awarded for match wins/losses
- **Bonus Points**: Configure bonuses for under-par and even-par rounds
- **Reset to Defaults**: Restore factory default point values
- **Real-Time Updates**: Changes apply immediately to new rounds

#### Round Management
- **Season-Based Viewing**: Filter rounds by specific seasons
- **Round Details**: View complete round summaries with scoring breakdown
- **Round Deletion**: Remove rounds with confirmation safeguards
- **Administrative Oversight**: Monitor all player activities across the system

### Player Panel Features

#### Round Play System
- **Course Selection**: Choose from available configured courses
- **Season Assignment**: Select or auto-select active season for rounds
- **Handicap Selection**: Use default or custom handicap for individual rounds
- **Hole-by-Hole Scoring**: Progressive scoring through each hole of the course
- **Score Entry**: Intuitive score selection with par pre-selected
- **Penalty Tracking**: Record penalty strokes and out-of-bounds shots
- **Round Completion**: Submit match results (won/lost) upon completion
- **Points Calculator**: Real-time point calculation based on performance

#### Round Management
- **Resume Incomplete Rounds**: Continue previously started but unfinished rounds
- **Edit Completed Rounds**: Reopen and modify completed rounds with re-submission
- **Round History**: View chronological list of all played rounds by season
- **Round Details**: Access complete scoring breakdowns and statistics
- **Navigation Controls**: Easy movement between sections and return to menu

#### Statistics and Analytics
- **Season Statistics**: Comprehensive performance metrics by season
- **Historical Data**: Track improvement and trends over time
- **Personal Dashboard**: Centralized view of golf activities and achievements

## Technical Implementation Details

### Frontend Architecture

#### JavaScript Structure
- **Event-Driven Architecture**: Comprehensive event listeners for all user interactions
- **Asynchronous API Calls**: Fetch-based communication with PHP backend
- **State Management**: Client-side state tracking for current rounds and user sessions
- **Error Handling**: Robust error catching and user feedback systems
- **Session Validation**: Continuous server-side session verification

#### Key JavaScript Components
- **Authentication System**: Login/logout handling with session management
- **Round Playing Engine**: Multi-hole progressive scoring system
- **Dynamic Content Loading**: AJAX-based content updates without page refresh
- **Form Validation**: Client-side validation with server-side verification
- **Real-Time Updates**: Immediate feedback for user actions and data changes

#### UI/UX Features
- **Mobile-First Design**: Optimized for mobile devices and touch interfaces
- **Responsive Layout**: Adaptive design across all screen sizes
- **Progressive Enhancement**: Core functionality works without JavaScript
- **Accessible Design**: Semantic HTML and keyboard navigation support
- **Visual Feedback**: Loading states, success/error messages, confirmation dialogs

### Backend Integration

#### API Endpoints (Referenced in JavaScript)
- **Authentication**: `login.php`, `logout.php`, `check_session.php`
- **Player Management**: `addplayer.php`, `editplayer.php`, `getplayers.php`, `getplayerdetails.php`
- **Course Management**: `addcourse.php`, `editcourse.php`, `getcourses.php`, `getcompletecourses.php`
- **Round Management**: `startround.php`, `saveholescore.php`, `getroundsummary.php`, `completematch.php`
- **Statistics**: `getplayerstats.php`, `getmyrounds.php`, `getrounddetails.php`
- **Season Management**: `getseasons.php`, `addseason.php`, `activateseason.php`
- **Points System**: `getpoints.php`, `savepoints.php`, `resetdefaultpoints.php`

#### Data Flow Patterns
- **Form Submissions**: FormData objects sent to PHP endpoints via POST
- **Data Retrieval**: GET requests with query parameters for filtering
- **Error Response Handling**: Standardized JSON error responses
- **Success Confirmations**: User feedback for all major operations

## User Interface Components

### Layout Structure
- **Header**: TIGL Points branding with golf-themed styling
- **Main Content Area**: Dynamic section switching based on user role and actions
- **Login Section**: Simple PIN entry interface
- **User Welcome**: Compact user info display with logout option
- **Admin Panel**: Comprehensive administrative controls and sections
- **Player Panel**: User-friendly golf activity interface
- **Footer**: Modern golf-themed footer (commented out in current version)

### Design System
- **CSS Custom Properties**: Consistent theming with CSS variables
- **Color Scheme**: Professional golf-themed color palette (greens, earth tones)
- **Typography**: Modern, readable font stack with proper hierarchy  
- **Component Library**: Consistent button styles, form controls, and cards
- **Grid System**: Flexible grid layouts for responsive design
- **Interactive Elements**: Hover effects, transitions, and visual feedback

### Mobile Optimization
- **Touch Targets**: Appropriately sized buttons and interactive elements
- **Responsive Tables**: Mobile-friendly data display with stacking layouts
- **Viewport Optimization**: Proper meta tags and responsive design principles
- **Performance**: Optimized loading and minimal resource usage

## Key User Flows

### Admin Workflows

#### Player Management Flow
1. **Login** → Admin Panel → Player Management
2. **Add Player**: Fill form → Submit → Confirmation
3. **View Players**: Refresh list → Browse player details
4. **Edit Player**: Select player → Load data → Modify → Save → Confirmation

#### Course Creation Flow
1. **Course Setup**: Enter course name and hole count → Submit
2. **Hole Configuration**: Define par, yardage, handicaps for each hole → Save
3. **Course Verification**: Review complete course layout → Confirm

#### Season Management Flow
1. **View Seasons**: Load current seasons with status indicators
2. **Create Season**: Enter year → Set as active (optional) → Submit
3. **Activate Season**: Select season → Confirm activation → Update status

### Player Workflows

#### Round Playing Flow
1. **Start Round**: Course selection → Season selection → Handicap selection → Begin
2. **Hole-by-Hole Play**: 
   - View hole information (par, yardage, handicap)
   - Enter score, penalties, OB strokes
   - Progress to next hole or finish round
3. **Round Completion**: Review summary → Select match result → Submit → Confirmation
4. **Post-Round**: View completed round or start new round

#### Statistics and History Flow
1. **View Stats**: Select season → Load statistics → Review performance metrics
2. **Round History**: Select season → Load rounds → View individual round details
3. **Edit Rounds**: Select completed round → Reopen for editing → Modify scores → Re-complete

## Security and Session Management

### Authentication Security
- **PIN Validation**: Server-side PIN verification with prepared statements
- **Session Tokens**: Secure session token generation and validation
- **Session Expiration**: Automatic timeout with cleanup
- **CSRF Protection**: Form-based submissions with validation

### Data Protection
- **SQL Injection Prevention**: Prepared statements for all database queries
- **Input Validation**: Both client-side and server-side validation
- **Authorization Checks**: Role-based access control for all operations
- **Session Verification**: Continuous validation of user sessions

## JavaScript Functionality

### Core Functions

#### Session Management
- `checkLoginStatus()`: Verify session on page load
- `handleLogin()`: Process login form submission
- `showLoginSection()`: Handle logout and cleanup
- `checkSession()`: Validate current session with server

#### Player Panel Functions
- `loadAvailableCourses()`: Populate course selection dropdown
- `loadSeasons()`: Load seasons with active season pre-selection
- `loadUserHandicap()`: Load user's default handicap with options
- `startRound()`: Initialize new round with course and season
- `loadHoleInfo()`: Display current hole details and setup scoring
- `loadRoundSummary()`: Generate round completion summary
- `resumeRound()`: Continue incomplete rounds from last played hole

#### Admin Panel Functions
- `loadPlayersForEdit()`: Create player selection interface for editing
- `loadPlayerForEdit()`: Populate edit form with selected player data
- `generateHoleInputs()`: Create dynamic hole configuration forms
- `loadSeasonsList()`: Display seasons with management controls
- `activateSeason()`: Change active season status

#### Course and Round Management
- `viewRoundDetails()`: Display detailed round information in modal
- `deleteRound()`: Remove rounds with confirmation
- `editCompletedRound()`: Reopen completed rounds for modification
- `saveRoundEdits()`: Save modified round data

### Event Handling
- **Form Submissions**: All forms handled with preventDefault and async processing
- **Button Actions**: Comprehensive click handlers for all interface elements
- **Dynamic Content**: Show/hide sections based on user selections
- **Navigation**: Breadcrumb-style navigation between sections
- **Real-Time Updates**: Immediate feedback for user interactions

## Data Models (Inferred from Usage)

### Core Entities
- **Users**: Username, PIN, handicap, admin status
- **Courses**: Name, hole count, hole details (par, yardage, handicaps)
- **Seasons**: Year, active status
- **Rounds**: Player, course, season, date, scores, completion status
- **Hole Scores**: Round, hole number, score, penalties, OB strokes, points
- **Points Configuration**: All point values for different scoring scenarios

### Relationships
- **Player → Rounds**: One-to-many relationship for round history
- **Course → Rounds**: Many-to-many through round assignments
- **Season → Rounds**: One-to-many for seasonal organization
- **Round → Hole Scores**: One-to-many for detailed scoring

## Performance Considerations

### Optimization Features
- **Lazy Loading**: Content loaded on demand rather than page load
- **Caching**: Session storage for user data persistence
- **Minimal Requests**: Efficient API calls with proper error handling
- **Responsive Images**: Optimized resource loading
- **Code Organization**: Modular JavaScript with clear separation of concerns

## Browser Compatibility

### Modern Features Used
- **ES6+ JavaScript**: Arrow functions, async/await, template literals
- **CSS Grid and Flexbox**: Modern layout techniques
- **Fetch API**: Modern HTTP request handling
- **CSS Custom Properties**: Dynamic theming system
- **Mobile-First Design**: Progressive enhancement approach

## Accessibility Features

### Inclusive Design Elements
- **Semantic HTML**: Proper heading hierarchy and form labels
- **Keyboard Navigation**: Full keyboard accessibility
- **Screen Reader Support**: ARIA labels and proper markup
- **Color Contrast**: Professional color scheme with good contrast
- **Touch Targets**: Appropriately sized interactive elements

## Critical Dependencies

### External Resources
- **Google Fonts**: Font loading with preconnect optimization
- **Modern CSS**: Custom properties, grid, flexbox
- **Vanilla JavaScript**: No framework dependencies
- **PHP Backend**: Server-side processing and database interaction

## State Management

### Client-Side State
- **Session Storage**: User authentication data persistence
- **Current Round**: Active round state during play
- **Form State**: Temporary data during multi-step processes
- **UI State**: Section visibility and navigation state

## Error Handling

### User Experience
- **Graceful Degradation**: Functionality preserved during errors
- **User Feedback**: Clear error messages and success confirmations
- **Validation**: Both client and server-side validation
- **Recovery**: Easy paths to recover from errors

## Future Considerations

### Extensibility Points
- **Plugin Architecture**: Modular design allows for feature additions
- **Theme System**: CSS custom properties enable easy theming
- **API Expansion**: RESTful design supports additional endpoints
- **Mobile App**: Structure supports PWA conversion
- **Real-Time Features**: Architecture ready for WebSocket integration

---

**Document Version**: 1.0  
**Last Updated**: April 18, 2026  
**Purpose**: Baseline functionality reference for system rework planning  
**Scope**: Complete feature inventory and technical documentation