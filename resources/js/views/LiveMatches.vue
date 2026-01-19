<template>
  <div class="live-matches-page">
    <div class="page-header">
      <h1>Live Matches</h1>
      <p>Watch live cricket action with real-time updates</p>
    </div>

    <!-- Live Matches Section -->
    <section v-if="liveMatches.length" class="matches-section">
      <h2>🔴 Live Now</h2>
      <div class="matches-grid">
        <div v-for="match in liveMatches" :key="match.match_id" class="match-card live">
          <div class="live-badge">LIVE</div>
          <div class="match-info">
            <div class="teams">
              <div class="team">
                <h3>{{ match.first_team?.name || 'Team 1' }}</h3>
                <div class="score">{{ match.first_team?.score || 'Yet to bat' }}</div>
              </div>
              <div class="vs">vs</div>
              <div class="team">
                <h3>{{ match.second_team?.name || 'Team 2' }}</h3>
                <div class="score">{{ match.second_team?.score || 'Yet to bat' }}</div>
              </div>
            </div>
            <div class="match-meta">
              <span>Over: {{ match.current_over }}</span>
              <span>{{ match.viewers_count || 0 }} watching</span>
            </div>
          </div>
          <router-link :to="`/live-matches/${match.match_id}`" class="btn-watch">
            Watch Live
          </router-link>
        </div>
      </div>
    </section>

    <!-- Upcoming Matches Section -->
    <section v-if="upcomingMatches.length" class="matches-section">
      <h2>📅 Upcoming Matches</h2>
      <div class="matches-grid">
        <div v-for="match in upcomingMatches" :key="match.match_id" class="match-card upcoming">
          <div class="match-info">
            <div class="teams">
              <div class="team">
                <h3>{{ match.firstTeam.team_name }}</h3>
              </div>
              <div class="vs">vs</div>
              <div class="team">
                <h3>{{ match.secondTeam.team_name }}</h3>
              </div>
            </div>
            <div class="match-meta">
              <span>{{ formatDate(match.match_date) }}</span>
              <span>{{ match.venue.name }}</span>
              <span>{{ match.match_type }} - {{ match.overs }} overs</span>
            </div>
          </div>
          <div class="match-actions">
            <router-link :to="`/predictions/${match.match_id}`" class="btn-predict">
              Make Prediction
            </router-link>
            <button @click="startMatch(match.match_id)" class="btn-start">
              Start Simulation
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Empty State -->
    <div v-if="!liveMatches.length && !upcomingMatches.length" class="empty-state">
      <div class="empty-icon">🏏</div>
      <h3>No Matches Available</h3>
      <p>Create a new match to start simulating cricket action!</p>
      <router-link to="/matches" class="btn-primary">
        Go to Matches
      </router-link>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LiveMatches',
  data() {
    return {
      liveMatches: [],
      upcomingMatches: [],
      loading: true
    }
  },
  mounted() {
    this.fetchMatches();
    this.startPolling();
  },
  beforeUnmount() {
    this.stopPolling();
  },
  methods: {
    async fetchMatches() {
      try {
        const [liveRes, upcomingRes] = await Promise.all([
          fetch('/api/v1/live-matches'),
          fetch('/api/v1/live-matches/upcoming')
        ]);
        
        this.liveMatches = await liveRes.json();
        this.upcomingMatches = await upcomingRes.json();
        this.loading = false;
      } catch (error) {
        console.error('Error fetching matches:', error);
        this.loading = false;
      }
    },
    
    async startMatch(matchId) {
      try {
        const response = await fetch(`/api/v1/live-matches/${matchId}/start`, {
          method: 'POST'
        });
        
        if (response.ok) {
          this.$router.push(`/live-matches/${matchId}`);
        }
      } catch (error) {
        console.error('Error starting match:', error);
      }
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    startPolling() {
      this.pollInterval = setInterval(() => {
        this.fetchMatches();
      }, 10000);
    },
    
    stopPolling() {
      if (this.pollInterval) {
        clearInterval(this.pollInterval);
      }
    }
  }
}
</script>

<style scoped>
.live-matches-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  margin-bottom: 3rem;
}

.page-header h1 {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 0.5rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.matches-section {
  margin-bottom: 3rem;
}

.matches-section h2 {
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  font-weight: 600;
}

.matches-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
  gap: 1.5rem;
}

.match-card {
  background: white;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  position: relative;
  transition: transform 0.2s, box-shadow 0.2s;
}

.match-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
}

.match-card.live {
  border: 2px solid #dc3545;
}

.live-badge {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: #dc3545;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: bold;
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.teams {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.team {
  text-align: center;
  flex: 1;
}

.team h3 {
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
}

.score {
  font-size: 1.5rem;
  font-weight: bold;
  color: #667eea;
}

.vs {
  font-weight: 600;
  color: #6c757d;
  margin: 0 1rem;
}

.match-meta {
  display: flex;
  gap: 1rem;
  font-size: 0.875rem;
  color: #6c757d;
  margin-bottom: 1rem;
  flex-wrap: wrap;
}

.match-meta span {
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-watch, .btn-predict, .btn-start {
  display: block;
  width: 100%;
  padding: 0.75rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  text-align: center;
  text-decoration: none;
}

.btn-watch {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-watch:hover {
  transform: scale(1.02);
}

.match-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-predict {
  background: #28a745;
  color: white;
  flex: 1;
}

.btn-start {
  background: #007bff;
  color: white;
  flex: 1;
}

.btn-predict:hover, .btn-start:hover {
  opacity: 0.9;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  font-size: 1.5rem;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6c757d;
  margin-bottom: 2rem;
}

.btn-primary {
  display: inline-block;
  padding: 0.75rem 2rem;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
}
</style>
