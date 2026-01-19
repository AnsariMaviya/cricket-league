# 🏏 Cricket League Management System

A comprehensive cricket league management platform built with **Laravel 12** and **Vue.js 3**, featuring live match simulation, AI-powered predictions, and gamification.

![Laravel](https://img.shields.io/badge/Laravel-12.0-red?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.0-green?style=flat-square&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![License](https://img.shields.io/badge/License-MIT-yellow?style=flat-square)

---

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation](#-installation)
- [Quick Start](#-quick-start)
- [Documentation](#-documentation)
- [Project Structure](#-project-structure)
- [API Endpoints](#-api-endpoints)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🏆 Core Management Features
- **Countries Management** - Manage cricket-playing nations
- **Teams Management** - Create and manage cricket teams with logos
- **Players Management** - Player profiles with roles, stats, and images
- **Venues Management** - Cricket stadiums and grounds database
- **Matches Management** - Schedule and manage cricket matches
- **Analytics Dashboard** - Comprehensive statistics and insights
- **Advanced Search** - Fast, indexed search across all entities

### 🔴 Live Match Simulation
- **Ball-by-Ball Simulation** - Realistic cricket match simulation
- **AI Commentary** - Context-aware commentary for every ball
- **Live Scoreboard** - Real-time score updates with detailed stats
- **Player Statistics** - Batting, bowling, and fielding stats tracking
- **Over-by-Over Analysis** - Detailed breakdown of each over
- **Match Summary** - Complete post-match analysis
- **CLI Simulation** - Run matches from command line
- **Auto-Simulation** - Fully automated match simulation

### 🎯 AI-Powered Predictions
- **Match Predictions** - AI-generated win probabilities
- **Team Analysis** - Comprehensive team strength evaluation
- **Player Performance Analysis** - Trending and consistency metrics
- **Head-to-Head Records** - Historical matchup analysis
- **Venue Advantage** - Home ground advantage calculation
- **Recent Form** - Performance trend analysis
- **Team Recommendations** - AI-suggested best XI selection

### 🏆 Gamification System
- **Points System** - Earn points for engagement
- **Achievements** - 10+ unlockable achievements (Common to Legendary)
- **Leaderboards** - Global, weekly, and monthly rankings
- **User Levels** - Progressive level system (1000 points per level)
- **Prediction Game** - Make predictions and earn rewards
- **Transaction History** - Complete point audit trail
- **Badges & Rewards** - Visual achievement system

### 📡 Real-Time Features
- **WebSocket Broadcasting** - Live updates via Pusher
- **Match Events** - Real-time ball and match updates
- **Live Commentary Feed** - Streaming commentary
- **Score Updates** - Instant score changes
- **Multiple Viewers** - Track concurrent viewers

### 🔌 API Features
- **RESTful API** - 40+ API endpoints
- **API Resources** - Formatted JSON responses
- **Rate Limiting** - API protection
- **Pagination** - Efficient data loading
- **Caching** - Redis-based caching for performance
- **External API Integration** - Support for CricAPI, Entity Sport, etc.

---

## 🛠️ Tech Stack

### Backend
- **Laravel 12** - PHP Framework
- **PHP 8.2** - Programming Language
- **MySQL** - Primary Database
- **Redis** - Caching & Queue Management
- **Pusher** - WebSocket Broadcasting

### Frontend
- **Vue.js 3** - JavaScript Framework
- **Vue Router** - SPA Routing
- **Vite** - Build Tool
- **TailwindCSS** - Utility-First CSS
- **Axios** - HTTP Client

### Additional Tools
- **Composer** - PHP Dependency Manager
- **NPM** - JavaScript Package Manager
- **Laravel Tinker** - REPL for Laravel
- **Intervention Image** - Image Processing
- **League Fractal** - API Transformer

---

## 📦 Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 16+ and NPM
- MySQL 8.0+
- Redis (optional, for caching)

### Step 1: Clone Repository
```bash
git clone <repository-url>
cd cricket-league-laravel
```

### Step 2: Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### Step 3: Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 4: Database Configuration
Update `.env` with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cricket_league
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### Step 5: Run Migrations
```bash
php artisan migrate
```

### Step 6: Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### Step 7: Start Development Server
```bash
php artisan serve
```

Visit: `http://localhost:8000`

---

## 🚀 Quick Start

### Initialize Achievements
```bash
php artisan tinker
```
```php
$service = new \App\Services\GamificationService();
$service->initializeAchievements();
exit
```

### Simulate Your First Match
```bash
# Via CLI (with live commentary)
php artisan match:simulate 1 --delay=3

# Via API
curl -X POST http://localhost:8000/api/v1/live-matches/1/start
curl -X POST http://localhost:8000/api/v1/live-matches/1/auto-simulate
```

### Access Features
- **Dashboard**: `http://localhost:8000/`
- **Live Matches**: `http://localhost:8000/live-matches`
- **Predictions**: `http://localhost:8000/predictions`
- **Gamification**: `http://localhost:8000/gamification`
- **Analytics**: `http://localhost:8000/analytics`

---

## 📚 Documentation

Comprehensive documentation is available in the following files:

- **[QUICK_START.md](QUICK_START.md)** - Get started in 5 minutes
- **[LIVE_MATCH_SYSTEM_GUIDE.md](LIVE_MATCH_SYSTEM_GUIDE.md)** - Complete system documentation (15,000+ words)
- **[NEW_FEATURES_SETUP.md](NEW_FEATURES_SETUP.md)** - Setup guide for new features
- **[EXTERNAL_API_INTEGRATION.md](EXTERNAL_API_INTEGRATION.md)** - Integrate real cricket APIs
- **[PERFORMANCE_GUIDE.md](PERFORMANCE_GUIDE.md)** - Performance optimization guide
- **[PAGINATION_ADDED.md](PAGINATION_ADDED.md)** - Pagination implementation details

---

## 📁 Project Structure

```
cricket-league-laravel/
├── app/
│   ├── Console/Commands/
│   │   └── SimulateMatch.php          # CLI match simulation
│   ├── Events/
│   │   ├── MatchUpdated.php           # Match update events
│   │   └── BallSimulated.php          # Ball simulation events
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LiveMatchController.php
│   │   │   ├── PredictionController.php
│   │   │   ├── GamificationController.php
│   │   │   ├── MatchController.php
│   │   │   ├── TeamController.php
│   │   │   ├── PlayerController.php
│   │   │   └── ...
│   │   └── Resources/                 # API Resources
│   ├── Models/
│   │   ├── CricketMatch.php
│   │   ├── MatchInnings.php
│   │   ├── BallByBall.php
│   │   ├── PlayerMatchStats.php
│   │   ├── Achievement.php
│   │   ├── UserPoints.php
│   │   ├── MatchPrediction.php
│   │   └── ...
│   └── Services/
│       ├── MatchSimulationEngine.php  # Core simulation logic
│       ├── CommentaryGenerator.php    # AI commentary
│       ├── LiveScoreboardService.php  # Scoreboard data
│       ├── AIMatchPredictionService.php
│       └── GamificationService.php
├── database/
│   ├── migrations/                    # 16+ database migrations
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── views/
│   │   │   ├── Home.vue
│   │   │   ├── LiveMatches.vue
│   │   │   ├── LiveMatchDetail.vue
│   │   │   ├── Predictions.vue
│   │   │   ├── Gamification.vue
│   │   │   └── ...
│   │   ├── components/
│   │   │   ├── LiveScoreboard.vue
│   │   │   └── Navigation.vue
│   │   └── router/
│   │       └── index.js
│   └── views/
│       └── app.blade.php              # Main SPA entry point
├── routes/
│   ├── web.php                        # Web routes
│   └── api.php                        # API routes (40+ endpoints)
└── public/
    └── build/                         # Compiled assets
```

---

## 🔌 API Endpoints

### Live Matches
```
GET    /api/v1/live-matches                      # All live matches
GET    /api/v1/live-matches/upcoming             # Upcoming matches
GET    /api/v1/live-matches/{id}/scoreboard      # Full scoreboard
GET    /api/v1/live-matches/{id}/mini-scoreboard # Mini scoreboard
GET    /api/v1/live-matches/{id}/summary         # Match summary
GET    /api/v1/live-matches/{id}/over/{num}      # Over details
POST   /api/v1/live-matches/{id}/start           # Start match
POST   /api/v1/live-matches/{id}/simulate-ball   # Simulate next ball
POST   /api/v1/live-matches/{id}/auto-simulate   # Auto-simulate match
POST   /api/v1/live-matches/{id}/stop            # Stop match
```

### Predictions
```
GET    /api/v1/predictions/match/{id}            # AI prediction
GET    /api/v1/predictions/match/{id}/user       # User's prediction
POST   /api/v1/predictions/match/{id}            # Submit prediction
GET    /api/v1/predictions/player/{id}/analysis  # Player analysis
GET    /api/v1/predictions/team/{id}/recommend   # Team recommendation
```

### Gamification (Auth Required)
```
GET    /api/v1/gamification/stats                # User stats
GET    /api/v1/gamification/leaderboard          # Leaderboard
GET    /api/v1/gamification/achievements         # All achievements
GET    /api/v1/gamification/transactions         # Point history
POST   /api/v1/gamification/achievements/init    # Initialize achievements
```

### CRUD Operations
```
GET    /api/v1/countries                         # List countries
POST   /api/v1/countries                         # Create country
PUT    /api/v1/countries/{id}                    # Update country
DELETE /api/v1/countries/{id}                    # Delete country

# Similar endpoints for: teams, players, venues, matches
```

---

## 📊 Database Schema

### Core Tables
- `countries` - Cricket nations
- `teams` - Cricket teams
- `players` - Player profiles
- `venues` - Cricket stadiums
- `matches` - Match information

### Live Match Tables
- `match_innings` - Innings tracking
- `ball_by_ball` - Ball-by-ball data
- `player_match_stats` - Player statistics
- `match_commentary` - Commentary feed

### Gamification Tables
- `achievements` - Achievement definitions
- `user_achievements` - Unlocked achievements
- `user_points` - User points and levels
- `point_transactions` - Point history

### Prediction Tables
- `match_predictions` - AI predictions
- `user_predictions` - User predictions

---

## 🎮 Usage Examples

### Simulate a Match
```php
use App\Services\MatchSimulationEngine;
use App\Models\CricketMatch;

$engine = new MatchSimulationEngine();
$match = CricketMatch::find(1);

// Auto-simulate with 3-second delays
$result = $engine->autoSimulate($match, 3);

echo "Match completed: " . $result->outcome;
```

### Generate AI Prediction
```php
use App\Services\AIMatchPredictionService;

$service = new AIMatchPredictionService();
$prediction = $service->predictMatch($match);

echo "Predicted Winner: " . $prediction->predictedWinner->team_name;
echo "Confidence: " . $prediction->confidence_score . "%";
```

### Award Points to User
```php
use App\Services\GamificationService;

$service = new GamificationService();
$service->awardPoints($user, 50, 'Correct prediction', 'prediction');
```

---

## 🎨 Key Features in Detail

### Live Match Simulation
The system simulates realistic cricket matches with:
- Toss simulation (bat/bowl decision)
- Player selection based on roles
- Ball outcomes (dot, single, boundary, six, wicket)
- Wicket types (bowled, caught, lbw, run out, stumped)
- Extras (wide, no-ball, bye, leg-bye)
- Over completion and bowler rotation
- Innings breaks with target calculation
- Real-time broadcasting via WebSockets

### AI Commentary
Contextual commentary generated for:
- Every ball (dot balls, singles, boundaries)
- Wickets (different templates by dismissal type)
- Milestones (50s, 100s, team totals)
- Over summaries
- Partnership milestones
- Special achievements (hat-tricks, five-wickets)

### Gamification
**Point Sources:**
- Watch match: +10 points
- Make prediction: +5 points
- Correct prediction: +50 points
- Unlock achievement: Variable points

**Achievement Categories:**
- Points-based (100, 1000, 10000 points)
- Prediction-based (10 correct, 80% accuracy)
- Engagement-based (10 matches, 50 matches)
- Level milestones (Level 5, 10)

---

## 🔧 Configuration

### Broadcasting (Optional)
For real-time features, configure Pusher in `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_key
PUSHER_APP_SECRET=your_secret
PUSHER_APP_CLUSTER=your_cluster
```

### Caching
Enable Redis caching for better performance:
```env
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue
For background processing:
```env
QUEUE_CONNECTION=redis
```

Then run:
```bash
php artisan queue:work
```

---

## 🧪 Testing

Run tests:
```bash
php artisan test
```

---

## 🚀 Deployment

### Build for Production
```bash
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Optimize Performance
```bash
php artisan optimize
```

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

---

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

## 🙏 Acknowledgments

- Built with [Laravel](https://laravel.com) - The PHP Framework for Web Artisans
- Frontend powered by [Vue.js](https://vuejs.org)
- Styled with [TailwindCSS](https://tailwindcss.com)
- Real-time features by [Pusher](https://pusher.com)

---

## 📞 Support

For issues, questions, or suggestions:
- Open an issue on GitHub
- Check the [documentation files](LIVE_MATCH_SYSTEM_GUIDE.md)
- Review the [Quick Start Guide](QUICK_START.md)

---

## 🎯 Roadmap

- [ ] Mobile app (React Native)
- [ ] Fantasy league integration
- [ ] Tournament management
- [ ] Player auctions
- [ ] Video highlights integration
- [ ] Social features (comments, sharing)
- [ ] Multi-language support
- [ ] Dark mode

---

**Made with ❤️ and 🏏 by the Cricket League Team**
