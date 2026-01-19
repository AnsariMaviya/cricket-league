<template>
  <div class="predictions-page">
    <div class="page-header">
      <h1>🎯 Match Predictions</h1>
      <p>Use AI predictions and make your own to earn points</p>
    </div>

    <!-- Upcoming Matches for Predictions -->
    <div class="matches-list">
      <div v-for="match in upcomingMatches" :key="match.match_id" class="prediction-card">
        <div class="match-header">
          <div class="teams">
            <div class="team">
              <h3>{{ match.firstTeam.team_name }}</h3>
            </div>
            <div class="vs">VS</div>
            <div class="team">
              <h3>{{ match.secondTeam.team_name }}</h3>
            </div>
          </div>
          <div class="match-info">
            <span>{{ formatDate(match.match_date) }}</span>
            <span>{{ match.venue.name }}</span>
          </div>
        </div>

        <!-- AI Prediction -->
        <div v-if="predictions[match.match_id]" class="ai-prediction">
          <h4>🤖 AI Prediction</h4>
          <div class="prediction-content">
            <div class="prediction-winner">
              <strong>Predicted Winner:</strong> 
              {{ getPredictedWinnerName(match, predictions[match.match_id]) }}
            </div>
            <div class="confidence-bar">
              <div class="confidence-label">Confidence</div>
              <div class="confidence-progress">
                <div 
                  class="confidence-fill" 
                  :style="{ width: predictions[match.match_id].confidence_score + '%' }"
                ></div>
              </div>
              <div class="confidence-value">
                {{ predictions[match.match_id].confidence_score }}%
              </div>
            </div>
            
            <div v-if="predictions[match.match_id].factors" class="prediction-factors">
              <div class="factor">
                <span>Team 1 Win Probability:</span>
                <strong>{{ predictions[match.match_id].factors.team1_probability }}%</strong>
              </div>
              <div class="factor">
                <span>Team 2 Win Probability:</span>
                <strong>{{ predictions[match.match_id].factors.team2_probability }}%</strong>
              </div>
            </div>
          </div>
        </div>

        <!-- User Prediction Form -->
        <div class="user-prediction">
          <h4>Your Prediction</h4>
          <div v-if="userPredictions[match.match_id]" class="prediction-submitted">
            <p>✅ You predicted: <strong>{{ getUserPredictedTeam(match, userPredictions[match.match_id]) }}</strong></p>
          </div>
          <div v-else class="prediction-form">
            <button 
              @click="submitPrediction(match.match_id, match.first_team_id)"
              class="predict-btn team1"
            >
              Predict {{ match.firstTeam.team_name }}
            </button>
            <button 
              @click="submitPrediction(match.match_id, match.second_team_id)"
              class="predict-btn team2"
            >
              Predict {{ match.secondTeam.team_name }}
            </button>
          </div>
        </div>

        <button @click="viewDetails(match.match_id)" class="btn-details">
          View Full Analysis
        </button>
      </div>
    </div>

    <div v-if="!upcomingMatches.length" class="empty-state">
      <div class="empty-icon">🏏</div>
      <h3>No Upcoming Matches</h3>
      <p>Check back later for new matches to predict!</p>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Predictions',
  data() {
    return {
      upcomingMatches: [],
      predictions: {},
      userPredictions: {},
      loading: true
    }
  },
  mounted() {
    this.fetchData();
  },
  methods: {
    async fetchData() {
      try {
        const response = await fetch('/api/v1/live-matches/upcoming');
        this.upcomingMatches = await response.json();
        
        for (const match of this.upcomingMatches) {
          await this.fetchAIPrediction(match.match_id);
          await this.fetchUserPrediction(match.match_id);
        }
        
        this.loading = false;
      } catch (error) {
        console.error('Error fetching data:', error);
        this.loading = false;
      }
    },
    
    async fetchAIPrediction(matchId) {
      try {
        const response = await fetch(`/api/v1/predictions/match/${matchId}`);
        if (response.ok) {
          const data = await response.json();
          this.predictions[matchId] = data.prediction || data;
        }
      } catch (error) {
        console.error('Error fetching AI prediction:', error);
      }
    },
    
    async fetchUserPrediction(matchId) {
      try {
        const response = await fetch(`/api/v1/predictions/match/${matchId}/user`, {
          headers: {
            'Authorization': `Bearer ${this.getToken()}`
          }
        });
        
        if (response.ok) {
          const data = await response.json();
          if (data) {
            this.userPredictions[matchId] = data;
          }
        }
      } catch (error) {
        console.error('Error fetching user prediction:', error);
      }
    },
    
    async submitPrediction(matchId, predictedWinnerId) {
      try {
        const response = await fetch(`/api/v1/predictions/match/${matchId}`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.getToken()}`
          },
          body: JSON.stringify({ predicted_winner_id: predictedWinnerId })
        });
        
        if (response.ok) {
          const data = await response.json();
          this.userPredictions[matchId] = data.prediction;
          alert('Prediction submitted! +5 points earned!');
        }
      } catch (error) {
        console.error('Error submitting prediction:', error);
        alert('Please login to make predictions');
      }
    },
    
    getPredictedWinnerName(match, prediction) {
      if (prediction.predicted_winner_id === match.first_team_id) {
        return match.firstTeam.team_name;
      }
      return match.secondTeam.team_name;
    },
    
    getUserPredictedTeam(match, userPrediction) {
      if (userPrediction.predicted_winner_id === match.first_team_id) {
        return match.firstTeam.team_name;
      }
      return match.secondTeam.team_name;
    },
    
    viewDetails(matchId) {
      this.$router.push(`/matches/${matchId}`);
    },
    
    formatDate(date) {
      return new Date(date).toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      });
    },
    
    getToken() {
      return localStorage.getItem('auth_token') || '';
    }
  }
}
</script>

<style scoped>
.predictions-page {
  max-width: 1200px;
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
}

.matches-list {
  display: grid;
  gap: 2rem;
}

.prediction-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.match-header {
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e9ecef;
}

.teams {
  display: flex;
  justify-content: space-around;
  align-items: center;
  margin-bottom: 1rem;
}

.team h3 {
  font-size: 1.5rem;
  font-weight: 600;
}

.vs {
  font-size: 1.25rem;
  font-weight: bold;
  color: #6c757d;
}

.match-info {
  display: flex;
  justify-content: center;
  gap: 2rem;
  color: #6c757d;
  font-size: 0.875rem;
}

.ai-prediction {
  background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
}

.ai-prediction h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.prediction-winner {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.confidence-bar {
  display: grid;
  grid-template-columns: auto 1fr auto;
  gap: 1rem;
  align-items: center;
  margin-bottom: 1rem;
}

.confidence-progress {
  height: 24px;
  background: rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  overflow: hidden;
}

.confidence-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  transition: width 0.5s;
}

.confidence-value {
  font-weight: bold;
  font-size: 1.1rem;
}

.prediction-factors {
  display: grid;
  gap: 0.5rem;
  margin-top: 1rem;
}

.factor {
  display: flex;
  justify-content: space-between;
}

.user-prediction {
  margin-bottom: 1.5rem;
}

.user-prediction h4 {
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.prediction-submitted {
  padding: 1rem;
  background: #d4edda;
  border-radius: 8px;
  text-align: center;
}

.prediction-form {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.predict-btn {
  padding: 1rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s;
}

.predict-btn.team1 {
  background: #007bff;
  color: white;
}

.predict-btn.team2 {
  background: #28a745;
  color: white;
}

.predict-btn:hover {
  transform: scale(1.02);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.btn-details {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #667eea;
  background: white;
  color: #667eea;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-details:hover {
  background: #667eea;
  color: white;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}
</style>
