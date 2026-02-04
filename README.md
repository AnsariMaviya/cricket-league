# 🏏 Cricket League Management System

A comprehensive cricket league management platform built with **Laravel 12** and **Vue.js 3**, featuring real-time live match simulation, AI-powered predictions, WebSocket broadcasting, and gamification.

![Laravel](https://img.shields.io/badge/Laravel-12.0-red?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.0-green?style=flat-square&logo=vue.js)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=flat-square&logo=php)
![WebSocket](https://img.shields.io/badge/WebSocket-Real_Time-orange?style=flat-square)
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
- [Real-Time Features](#-real-time-features)
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


### 📡 Real-Time Features
- **WebSocket Broadcasting** - Live updates via Laravel Reverb
- **Match Events** - Real-time ball and match updates
- **Live Commentary Feed** - Streaming commentary with WebSocket
- **Score Updates** - Instant score changes without polling
- **Multiple Viewers** - Track concurrent viewers
- **Zero-Polling Architecture** - Efficient real-time updates

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
- **Laravel Reverb** - WebSocket Broadcasting

### Frontend
- **Vue.js 3** - JavaScript Framework with Composition API
- **Vue Router** - SPA Routing
- **Vite** - Build Tool with HMR
- **TailwindCSS** - Utility-First CSS
- **Axios** - HTTP Client
- **Laravel Echo** - WebSocket Client

### Additional Tools
- **Composer** - PHP Dependency Manager
- **NPM** - JavaScript Package Manager
- **Laravel Tinker** - REPL for Laravel
- **Intervention Image** - Image Processing
- **League Fractal** - API Transformer
- **Laravel Horizon** - Queue Dashboard

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

### Step 5: Configure WebSocket (Reverb)
```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
```

### Step 6: Run Migrations
```bash
php artisan migrate
```

### Step 7: Build Frontend Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### Step 8: Start Services
```bash
# Start Laravel development server
php artisan serve

# Start Reverb WebSocket server (in separate terminal)
php artisan reverb:start

# Start queue worker (for background jobs)
php artisan queue:work
```

Visit: `http://localhost:8000`

---

## 🚀 Quick Start


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
- **[AI_COMMENTARY_GUIDE.md](AI_COMMENTARY_GUIDE.md)** - AI commentary system guide

---

## 📁 Project Structure

```
cricket-league-laravel/
├── app/
│   ├── Console/Commands/
│   │   └── SimulateMatch.php          # CLI match simulation
│   ├── Events/
│   │   ├── MatchUpdated.php           # Match update events
│   │   ├── BallSimulated.php          # Ball simulation events
│   │   └── ScoreboardUpdated.php      # Real-time scoreboard updates
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── LiveMatchController.php
│   │   │   ├── PredictionController.php
│   │   │   ├── MatchController.php
│   │   │   ├── TeamController.php
│   │   │   ├── PlayerController.php
│   │   │   └── ...
│   │   └── Resources/                 # API Resources
│   ├── Jobs/
│   │   ├── SimulateMatchJob.php       # Background match simulation
│   │   ├── ProcessMatchData.php       # Match data processing
│   │   └── CacheMatchStatistics.php   # Statistics caching
│   ├── Models/
│   │   ├── CricketMatch.php
│   │   ├── MatchInnings.php
│   │   ├── BallByBall.php
│   │   ├── PlayerMatchStats.php
│   │   ├── MatchPrediction.php
│   │   └── ...
│   └── Services/
│       ├── MatchSimulationEngine.php  # Core simulation logic
│       ├── CommentaryGenerator.php    # AI commentary
│       ├── LiveScoreboardService.php  # Scoreboard data
│       └── AIMatchPredictionService.php
├── database/
│   ├── migrations/                    # 25+ database migrations
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── views/
│   │   │   ├── Home.vue
│   │   │   ├── LiveMatches.vue
│   │   │   ├── LiveMatchDetail.vue
│   │   │   ├── Predictions.vue
│   │   │   └── ...
│   │   ├── components/
│   │   │   ├── LiveScoreboard.vue     # Real-time scoreboard component
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
POST   /api/v1/live-matches/{id}/start-auto-simulation   # Auto-simulate match
POST   /api/v1/live-matches/{id}/stop-auto-simulation    # Stop simulation
```

### Predictions
```
GET    /api/v1/predictions/match/{id}            # AI prediction
GET    /api/v1/predictions/match/{id}/user       # User's prediction
POST   /api/v1/predictions/match/{id}            # Submit prediction
GET    /api/v1/predictions/player/{id}/analysis  # Player analysis
GET    /api/v1/predictions/team/{id}/recommend   # Team recommendation
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


### Prediction Tables
- `match_predictions` - AI predictions
- `user_predictions` - User predictions

---

## 📡 Real-Time Features

### WebSocket Architecture
The system uses a **zero-polling architecture** for real-time updates:

- **Laravel Reverb**: Native WebSocket server for broadcasting
- **Laravel Echo**: Frontend WebSocket client for listening
- **Event Broadcasting**: Real-time score and commentary updates
- **Channel-Based**: Private channels per match (`match.{id}`)
- **Efficient**: Only sends delta updates, not full data refreshes

### Real-Time Components
- **LiveScoreboard.vue**: Real-time scoreboard component
- **ScoreboardUpdated Event**: Broadcasts score changes
- **Commentary Updates**: Live ball-by-ball commentary
- **Match Status**: Real-time match state changes

### WebSocket Events
```javascript
// Listen for scoreboard updates
Echo.channel(`match.${matchId}`)
    .listen('.scoreboard.updated', (data) => {
        // Update scores, commentary, ball-by-ball data
        this.updateScoreboard(data);
    });
```

### Performance Benefits
- **No Polling**: Eliminates repeated API calls
- **Instant Updates**: Real-time response to match events
- **Scalable**: Efficient for multiple concurrent viewers
- **Bandwidth**: Only sends changed data, not full refreshes

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


---

## 🔧 Configuration

### Broadcasting (Required for Real-Time)
For real-time features, configure Reverb in `.env`:
```env
BROADCAST_DRIVER=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_key
REVERB_APP_SECRET=your_secret
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
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
- Real-time features by [Laravel Reverb](https://reverb.laravel.com)

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

## 📈 Recent Updates

### Version 3.0 - Real-Time Architecture
- ✅ Implemented WebSocket broadcasting with Laravel Reverb
- ✅ Eliminated polling for real-time updates
- ✅ Added zero-polling scoreboard updates
- ✅ Enhanced live match commentary system
- ✅ Improved performance with delta updates

### Version 2.5 - Enhanced Features
- ✅ Added AI-powered match predictions
- ✅ Enhanced search functionality
- ✅ Added pagination for large datasets
- ✅ Improved API responses with proper resources
- ✅ Removed gamification system (simplified architecture)

---

**Made with ❤️ and 🏏 by the Cricket League Team**
