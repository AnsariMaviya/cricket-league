<template>
  <div class="live-scoreboard">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>Loading scoreboard...</p>
    </div>

    <div v-else-if="scoreboard" class="scoreboard-container">
      <!-- Match Header -->
      <div class="match-header">
        <div class="team-score">
          <h3>{{ scoreboard.match.first_team.team_name }}</h3>
          <div class="score">{{ scoreboard.match.first_team_score || '0/0' }}</div>
        </div>
        <div class="vs">VS</div>
        <div class="team-score">
          <h3>{{ scoreboard.match.second_team.team_name }}</h3>
          <div class="score">{{ scoreboard.match.second_team_score || '0/0' }}</div>
        </div>
      </div>

      <!-- Match Info -->
      <div class="match-info">
        <span class="venue">{{ scoreboard.match.venue.name }}</span>
        <span class="status" :class="statusClass">{{ scoreboard.match.status }}</span>
        <span class="over">Over: {{ scoreboard.match.current_over }}</span>
      </div>

      <!-- Match Result (for completed matches) -->
      <div v-if="scoreboard.match.status === 'completed' && scoreboard.match.outcome" class="match-result">
        <div class="result-banner">
          <i class="trophy-icon">🏆</i>
          <span class="result-text">{{ scoreboard.match.outcome }}</span>
        </div>
      </div>

      <!-- Tabs Navigation -->
      <div class="tabs-nav">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="['tab-btn', { active: activeTab === tab.id }]">
          {{ tab.label }}
        </button>
      </div>

      <!-- Tab Content -->
      <div class="tab-content">

        <!-- LIVE TAB -->
        <div v-show="activeTab === 'live'" class="live-tab">
          <!-- Target Info (2nd Innings) -->
          <div v-if="scoreboard.match.target_score" class="target-info">
            <p>Target: {{ scoreboard.match.target_score }}</p>
            <p>Required Run Rate: {{ scoreboard.required_run_rate }}</p>
          </div>

          <!-- Current Batsmen -->
          <div v-if="scoreboard.current_batsmen.length" class="current-batsmen">
            <h4>Current Batsmen</h4>
            <table class="batsmen-table">
              <thead>
                <tr>
                  <th class="text-left">Batter</th>
                  <th>R</th>
                  <th>B</th>
                  <th>4s</th>
                  <th>6s</th>
                  <th>SR</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="batsman in scoreboard.current_batsmen" :key="batsman.player_id">
                  <td class="text-left">{{ batsman.player.name }}</td>
                  <td><strong>{{ batsman.runs_scored }}</strong></td>
                  <td>{{ batsman.balls_faced }}</td>
                  <td>{{ batsman.fours || 0 }}</td>
                  <td>{{ batsman.sixes || 0 }}</td>
                  <td>{{ parseFloat(batsman.strike_rate || 0).toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Current Bowler -->
          <div v-if="scoreboard.current_bowler" class="current-bowler">
            <h4>Current Bowler</h4>
            <table class="bowler-table">
              <thead>
                <tr>
                  <th class="text-left">Bowler</th>
                  <th>O</th>
                  <th>M</th>
                  <th>R</th>
                  <th>W</th>
                  <th>ECO</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="text-left">{{ scoreboard.current_bowler.player.name }}</td>
                  <td>{{ (scoreboard.current_bowler.balls_bowled / 6).toFixed(1) }}</td>
                  <td>{{ scoreboard.current_bowler.maidens || 0 }}</td>
                  <td>{{ scoreboard.current_bowler.runs_conceded }}</td>
                  <td><strong>{{ scoreboard.current_bowler.wickets_taken }}</strong></td>
                  <td>{{ parseFloat(scoreboard.current_bowler.economy || 0).toFixed(2) }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Recent Balls -->
          <div v-if="scoreboard.recent_balls.length" class="recent-balls">
            <h4>Recent Balls</h4>
            <div class="balls-grid">
              <div v-for="ball in scoreboard.recent_balls.slice().reverse()" 
                   :key="ball.ball_id" 
                   class="ball"
                   :class="ballClass(ball)">
                {{ ballDisplay(ball) }}
              </div>
            </div>
          </div>

          <!-- Recent Commentary (last 5) -->
          <div v-if="scoreboard.commentary.length" class="commentary">
            <h4>Live Commentary</h4>
            <div class="commentary-feed">
              <div v-for="comment in scoreboard.commentary.slice(0, 5)" 
                   :key="comment.commentary_id" 
                   class="comment"
                   :class="`type-${comment.type}`">
                <span class="over">{{ formatOverNumber(comment.over_number) }}</span>
                <span class="text">{{ comment.commentary_text }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- SCORECARD TAB -->
        <div v-show="activeTab === 'scorecard'" class="scorecard-tab">
          <div v-if="fullScorecard">
            <!-- First Innings -->
            <div class="innings-scorecard">
              <h3>{{ scoreboard.match.first_team.team_name }} Innings</h3>
              <div class="score-summary">{{ scoreboard.match.first_team_score || 'Yet to bat' }}</div>
              
              <table class="full-scorecard-table">
                <thead>
                  <tr>
                    <th class="text-left">Batter</th>
                    <th>R</th>
                    <th>B</th>
                    <th>4s</th>
                    <th>6s</th>
                    <th>SR</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="player in fullScorecard.innings1_batting" :key="player.player_id">
                    <td class="text-left">
                      {{ player.player.name }}
                      <span v-if="player.wicket_type" class="dismissal">{{ player.wicket_type }}</span>
                    </td>
                    <td><strong>{{ player.runs_scored }}</strong></td>
                    <td>{{ player.balls_faced }}</td>
                    <td>{{ player.fours || 0 }}</td>
                    <td>{{ player.sixes || 0 }}</td>
                    <td>{{ player.strike_rate ? parseFloat(player.strike_rate).toFixed(2) : '0.00' }}</td>
                  </tr>
                  <tr class="extras-row">
                    <td colspan="6">Extras: {{ fullScorecard.innings1_extras || 0 }}</td>
                  </tr>
                  <tr class="total-row">
                    <td colspan="6"><strong>Total: {{ scoreboard.match.first_team_score || '0/0' }}</strong></td>
                  </tr>
                </tbody>
              </table>

              <h4 class="mt-3">Bowling</h4>
              <table class="full-scorecard-table">
                <thead>
                  <tr>
                    <th class="text-left">Bowler</th>
                    <th>O</th>
                    <th>M</th>
                    <th>R</th>
                    <th>W</th>
                    <th>ECO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="bowler in fullScorecard.innings1_bowling" :key="bowler.player_id">
                    <td class="text-left">{{ bowler.player.name }}</td>
                    <td>{{ (bowler.balls_bowled / 6).toFixed(1) }}</td>
                    <td>{{ bowler.maidens || 0 }}</td>
                    <td>{{ bowler.runs_conceded }}</td>
                    <td><strong>{{ bowler.wickets_taken }}</strong></td>
                    <td>{{ bowler.economy ? parseFloat(bowler.economy).toFixed(2) : '0.00' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Second Innings -->
            <div v-if="scoreboard.match.current_innings >= 2" class="innings-scorecard mt-4">
              <h3>{{ scoreboard.match.second_team.team_name }} Innings</h3>
              <div class="score-summary">{{ scoreboard.match.second_team_score || 'Yet to bat' }}</div>
              
              <table class="full-scorecard-table">
                <thead>
                  <tr>
                    <th class="text-left">Batter</th>
                    <th>R</th>
                    <th>B</th>
                    <th>4s</th>
                    <th>6s</th>
                    <th>SR</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="player in fullScorecard.innings2_batting" :key="player.player_id">
                    <td class="text-left">
                      {{ player.player.name }}
                      <span v-if="player.wicket_type" class="dismissal">{{ player.wicket_type }}</span>
                    </td>
                    <td><strong>{{ player.runs_scored }}</strong></td>
                    <td>{{ player.balls_faced }}</td>
                    <td>{{ player.fours || 0 }}</td>
                    <td>{{ player.sixes || 0 }}</td>
                    <td>{{ player.strike_rate ? parseFloat(player.strike_rate).toFixed(2) : '0.00' }}</td>
                  </tr>
                  <tr class="extras-row">
                    <td colspan="6">Extras: {{ fullScorecard.innings2_extras || 0 }}</td>
                  </tr>
                  <tr class="total-row">
                    <td colspan="6"><strong>Total: {{ scoreboard.match.second_team_score || '0/0' }}</strong></td>
                  </tr>
                </tbody>
              </table>

              <h4 class="mt-3">Bowling</h4>
              <table class="full-scorecard-table">
                <thead>
                  <tr>
                    <th class="text-left">Bowler</th>
                    <th>O</th>
                    <th>M</th>
                    <th>R</th>
                    <th>W</th>
                    <th>ECO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="bowler in fullScorecard.innings2_bowling" :key="bowler.player_id">
                    <td class="text-left">{{ bowler.player.name }}</td>
                    <td>{{ (bowler.balls_bowled / 6).toFixed(1) }}</td>
                    <td>{{ bowler.maidens || 0 }}</td>
                    <td>{{ bowler.runs_conceded }}</td>
                    <td><strong>{{ bowler.wickets_taken }}</strong></td>
                    <td>{{ bowler.economy ? parseFloat(bowler.economy).toFixed(2) : '0.00' }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- FULL COMMENTARY TAB -->
        <div v-show="activeTab === 'commentary'" class="full-commentary-tab">
          <h3>Full Commentary</h3>
          <div class="full-commentary-feed">
            <div v-for="comment in allCommentary" 
                 :key="comment.commentary_id" 
                 class="comment-detailed"
                 :class="`type-${comment.type}`">
              <div class="comment-over">{{ comment.over_number }}</div>
              <div class="comment-text">{{ comment.commentary_text }}</div>
            </div>
          </div>
        </div>

        <!-- INFO TAB -->
        <div v-show="activeTab === 'info'" class="info-tab">
          <h3>Match Info</h3>
          <div class="info-grid">
            <div class="info-item">
              <span class="label">Match:</span>
              <span class="value">{{ scoreboard.match.first_team.team_name }} vs {{ scoreboard.match.second_team.team_name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Venue:</span>
              <span class="value">{{ scoreboard.match.venue.name }}</span>
            </div>
            <div class="info-item">
              <span class="label">Date:</span>
              <span class="value">{{ formatDate(scoreboard.match.match_date) }}</span>
            </div>
            <div class="info-item">
              <span class="label">Match Type:</span>
              <span class="value">{{ scoreboard.match.match_type }}</span>
            </div>
            <div class="info-item">
              <span class="label">Overs:</span>
              <span class="value">{{ scoreboard.match.overs }} overs per side</span>
            </div>
            <div v-if="scoreboard.match.toss_winner" class="info-item">
              <span class="label">Toss:</span>
              <span class="value">{{ getTossInfo() }}</span>
            </div>
            <div v-if="scoreboard.match.outcome" class="info-item">
              <span class="label">Result:</span>
              <span class="value">{{ scoreboard.match.outcome }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Control Buttons (for demo) -->
      <div class="controls">
        <div class="flex justify-center space-x-4 mt-8">
          <!-- Start Match Button - shown when match needs initialization -->
          <button 
            v-if="(scoreboard?.match?.status === 'live' || scoreboard?.match?.status === 'scheduled') && !scoreboard?.current_innings"
            @click="startMatch" 
            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
            🏏 Start Match
          </button>
          
          <!-- Simulation Controls - shown when match is properly started -->
          <template v-if="scoreboard?.current_innings">
            <button 
              @click="simulateBall" 
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
              Simulate Next Ball
            </button>
            <button 
              @click="autoSimulate" 
              :disabled="isAutoSimulating"
              class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50">
              {{ isAutoSimulating ? 'Simulating...' : 'Auto Simulate Match' }}
            </button>
            <button 
              @click="stopMatch" 
              class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
              Stop Match
            </button>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LiveScoreboard',
  props: {
    matchId: {
      type: Number,
      required: true
    },
    autoRefresh: {
      type: Boolean,
      default: true
    },
    refreshInterval: {
      type: Number,
      default: 5000
    }
  },
  data() {
    return {
      scoreboard: null,
      loading: true,
      isAutoSimulating: false,
      lastFullRefresh: null,
      pollingInterval: null,
      activeTab: 'live',
      tabs: [
        { id: 'live', label: 'Live' },
        { id: 'scorecard', label: 'Scorecard' },
        { id: 'commentary', label: 'Full Commentary' },
        { id: 'info', label: 'Info' }
      ],
      fullScorecard: null,
      allCommentary: []
    }
  },
  computed: {
    isLive() {
      return this.scoreboard?.match?.status === 'live';
    },
    canStart() {
      return this.scoreboard?.match?.status === 'scheduled';
    },
    statusClass() {
      const status = this.scoreboard?.match?.status;
      return {
        'status-live': status === 'live',
        'status-completed': status === 'completed',
        'status-scheduled': status === 'scheduled'
      };
    }
  },
  mounted() {
    this.fetchScoreboard();
    this.setupWebSocket();
  },
  beforeUnmount() {
    this.disconnectWebSocket();
  },
  methods: {
    async fetchScoreboard() {
      try {
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/scoreboard`);
        const data = await response.json();
        this.scoreboard = data;
        this.loading = false;
        
        // Fetch full data when tab changes
        if (this.activeTab === 'scorecard' && !this.fullScorecard) {
          this.fetchFullScorecard();
        }
        if (this.activeTab === 'commentary' && !this.allCommentary.length) {
          this.fetchAllCommentary();
        }
      } catch (error) {
        console.error('Error fetching scoreboard:', error);
        this.loading = false;
      }
    },

    async fetchFullScorecard() {
      try {
        const response = await fetch(`/api/v1/stats/matches/${this.matchId}`);
        const data = await response.json();
        this.fullScorecard = data;
      } catch (error) {
        console.error('Error fetching full scorecard:', error);
      }
    },

    async fetchAllCommentary() {
      try {
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/commentary`);
        const data = await response.json();
        this.allCommentary = data;
      } catch (error) {
        console.error('Error fetching commentary:', error);
      }
    },
    
    async startMatch() {
      try {
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/start`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });
        const data = await response.json();
        
        if (data.success) {
          // WebSocket will handle update, only fetch if WS fails
          setTimeout(() => {
            if (!window.Echo) this.fetchScoreboard();
          }, 500);
          this.$emit('match-started', data);
        }
      } catch (error) {
        console.error('Error starting match:', error);
      }
    },
    
    async simulateBall() {
      try {
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/simulate-ball`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          }
        });
        const data = await response.json();
        
        if (data.success) {
          this.$emit('ball-simulated', data.ball);
          // Update scoreboard immediately from response data
          if (data.score) {
            this.updateScoreboardFromResponse(data);
          }
        }
      } catch (error) {
        console.error('Error simulating ball:', error);
      }
    },
    
    async autoSimulate() {
      if (this.isAutoSimulating) {
        console.log('Auto-simulation already running');
        return;
      }
      
      try {
        // Single API call to start background simulation
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/start-auto-simulation`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            delay: 2 // 2 seconds between balls
          })
        });
        const data = await response.json();
        
        if (data.success) {
          this.isAutoSimulating = true;
          console.log('🚀🚀🚀 WEBSOCKET-ONLY MODE ACTIVATED 🚀🚀🚀');
          console.log('DEBUG: NO POLLING - Updates via WebSocket only');
        }
      } catch (error) {
        console.error('Error starting auto-simulation:', error);
      }
    },
    
    async stopMatch() {
      try {
        // Stop background simulation
        const response = await fetch(`/api/v1/live-matches/${this.matchId}/stop-auto-simulation`, {
          method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
          this.isAutoSimulating = false;
          console.log('✅ Auto-simulation stopped');
        }
      } catch (error) {
        console.error('❌ Error stopping simulation:', error);
        this.isAutoSimulating = false;
      }
    },
    
    setupWebSocket() {
      console.log('=== DEBUG: setupWebSocket VERSION 3.0 ===');
      console.log('DEBUG: window.Echo exists?', !!window.Echo);
      
      if (window.Echo) {
        console.log('✅ Setting up WebSocket listener for match', this.matchId);
        
        const channel = window.Echo.channel(`match.${this.matchId}`);
        console.log('DEBUG: Channel object:', channel);
        
        channel.listen('.scoreboard.updated', (data) => {
          console.log('🔥🔥🔥 WEBSOCKET UPDATE RECEIVED 🔥🔥🔥');
          console.log('DEBUG: Data:', data);
          this.updateScoreboardIncremental(data);
        });
        
        console.log('DEBUG: WebSocket listener attached');
      } else {
        console.error('❌ Laravel Echo NOT initialized!');
      }
    },
    
    updateScoreboardFromResponse(data) {
      if (!this.scoreboard) return;
      
      // Update scores from API response or WebSocket
      if (data.score) {
        if (this.scoreboard.current_innings) {
          Object.assign(this.scoreboard.current_innings, {
            total_runs: data.score.runs,
            wickets: data.score.wickets,
            overs: data.score.overs
          });
        }
        Object.assign(this.scoreboard.match, {
          current_over: data.score.current_over,
          first_team_score: data.score.first_team_score,
          second_team_score: data.score.second_team_score
        });
      }
      
      if (data.status) {
        this.scoreboard.match.status = data.status;
        if (data.status === 'completed') {
          this.isAutoSimulating = false;
        }
      }
      
      // Add new ball to recent balls
      if (data.ball) {
        if (!this.scoreboard.recent_balls) {
          this.scoreboard.recent_balls = [];
        }
        
        // Normalize ball data structure
        const normalizedBall = {
          over_number: data.ball.over,
          runs_scored: data.ball.runs,
          is_wicket: data.ball.is_wicket || false,
          is_four: data.ball.is_four || false,
          is_six: data.ball.is_six || false,
          extra_type: data.ball.extra_type || 'none',
          commentary: data.ball.commentary
        };
        
        this.scoreboard.recent_balls = [normalizedBall, ...this.scoreboard.recent_balls.slice(0, 5)];
      }
      
      // Add commentary
      if (data.ball && data.ball.commentary && this.scoreboard.commentary) {
        const newCommentary = {
          commentary_text: data.ball.commentary,
          over_number: data.ball.over,
          type: data.ball.is_wicket ? 'wicket' : 'ball',
          created_at: new Date().toISOString()
        };
        this.scoreboard.commentary = [newCommentary, ...this.scoreboard.commentary.slice(0, 19)];
      }
    },
    
    updateScoreboardIncremental(data) {
      // For WebSocket updates (when WebSocket is configured)
      this.updateScoreboardFromResponse(data);
    },
    
    disconnectWebSocket() {
      if (window.Echo) {
        window.Echo.leave(`match.${this.matchId}`);
      }
    },
    
    ballDisplay(ball) {
      if (ball.is_wicket) return 'W';
      if (ball.is_six) return '6';
      if (ball.is_four) return '4';
      if (ball.extra_type === 'wide') return 'WD';
      if (ball.extra_type === 'no_ball') return 'NB';
      return ball.runs_scored.toString();
    },
    
    ballClass(ball) {
      return {
        'ball-wicket': ball.is_wicket,
        'ball-six': ball.is_six,
        'ball-four': ball.is_four,
        'ball-dot': ball.runs_scored === 0 && !ball.is_wicket,
        'ball-extra': ball.extra_type !== 'none'
      };
    },

    formatDate(date) {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },

    getTossInfo() {
      const match = this.scoreboard?.match;
      if (!match || !match.toss_winner) return '';
      const tossWinner = match.toss_winner === match.first_team_id 
        ? match.first_team.team_name 
        : match.second_team.team_name;
      return `${tossWinner} won the toss and chose to ${match.toss_decision}`;
    },

    formatDismissal(player) {
      if (!player.wicket_type) return '';
      let dismissal = player.wicket_type;
      // Add bowler name if available
      if (player.bowler_name && player.wicket_type !== 'run_out') {
        dismissal += ` b ${player.bowler_name}`;
      }
      return dismissal;
    }
  },
  watch: {
    activeTab(newTab) {
      if (newTab === 'scorecard' && !this.fullScorecard) {
        this.fetchFullScorecard();
      }
      if (newTab === 'commentary' && !this.allCommentary.length) {
        this.fetchAllCommentary();
      }
    }
  }
}
</script>

<style scoped>
.live-scoreboard {
  font-family: 'Arial', sans-serif;
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

.loading {
  text-align: center;
  padding: 40px;
}

.spinner {
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  width: 40px;
  height: 40px;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.match-header {
  display: flex;
  justify-content: space-around;
  align-items: center;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  padding: 30px;
  border-radius: 10px;
  margin-bottom: 20px;
}

.team-score {
  text-align: center;
}

.team-score h3 {
  margin: 0 0 10px 0;
  font-size: 24px;
}

.score {
  font-size: 36px;
  font-weight: bold;
}

.vs {
  font-size: 20px;
  font-weight: bold;
  opacity: 0.8;
}

.match-info {
  display: flex;
  justify-content: space-between;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 20px;
}

.status {
  padding: 5px 15px;
  border-radius: 20px;
  font-weight: bold;
  text-transform: uppercase;
  font-size: 12px;
}

.status-live {
  background: #28a745;
  color: white;
}

.status-completed {
  background: #6c757d;
  color: white;
}

.status-scheduled {
  background: #ffc107;
  color: #333;
}

/* Tabs Navigation */
.tabs-nav {
  display: flex;
  background: #f8f9fa;
  border-bottom: 2px solid #e0e0e0;
  margin: 20px 0 0 0;
  overflow-x: auto;
}

.tab-btn {
  padding: 12px 24px;
  border: none;
  background: transparent;
  cursor: pointer;
  font-weight: 500;
  color: #666;
  border-bottom: 3px solid transparent;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.tab-btn:hover {
  color: #333;
  background: rgba(0,0,0,0.05);
}

.tab-btn.active {
  color: #28a745;
  border-bottom-color: #28a745;
  background: white;
}

.tab-content {
  background: white;
  padding: 20px;
  min-height: 400px;
}

/* Tables */
.batsmen-table,
.bowler-table,
.full-scorecard-table {
  width: 100%;
  border-collapse: collapse;
  margin: 15px 0;
}

.batsmen-table thead,
.bowler-table thead,
.full-scorecard-table thead {
  background: #f5f5f5;
}

.batsmen-table th,
.bowler-table th,
.full-scorecard-table th {
  padding: 10px 8px;
  text-align: center;
  font-weight: 600;
  font-size: 12px;
  color: #666;
  border-bottom: 2px solid #e0e0e0;
}

.batsmen-table td,
.bowler-table td,
.full-scorecard-table td {
  padding: 12px 8px;
  text-align: center;
  border-bottom: 1px solid #f0f0f0;
}

.target-info {
  background: #fff3cd;
  padding: 15px;
  text-align: center;
  border-radius: 8px;
  margin-bottom: 20px;
}

/* Match Result Banner */
.match-result {
  margin: 20px 0;
  padding: 0;
}

.result-banner {
  background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
  color: white;
  padding: 20px;
  border-radius: 12px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
  animation: slideIn 0.5s ease-out;
}

.trophy-icon {
  font-size: 32px;
  display: block;
  margin-bottom: 10px;
}

.result-text {
  font-size: 20px;
  font-weight: bold;
  display: block;
  line-height: 1.4;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.text-left {
  text-align: left !important;
}

.batsmen-table tbody tr:hover,
.bowler-table tbody tr:hover,
.full-scorecard-table tbody tr:hover {
  background: #f9f9f9;
}

.current-batsmen,
.current-bowler {
  background: white;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.current-batsmen h4,
.current-bowler h4 {
  margin: 0 0 15px 0;
  font-size: 16px;
  color: #333;
}

/* Scorecard Tab */
.scorecard-tab {
  max-width: 100%;
  overflow-x: auto;
}

.innings-scorecard {
  margin-bottom: 30px;
}

.innings-scorecard h3 {
  color: #333;
  margin-bottom: 10px;
  font-size: 20px;
}

.score-summary {
  font-size: 18px;
  font-weight: bold;
  color: #28a745;
  margin-bottom: 15px;
}

.dismissal {
  display: block;
  font-size: 11px;
  color: #666;
  font-weight: normal;
}

.extras-row {
  background: #f9f9f9;
  font-style: italic;
}

.total-row {
  background: #e8f5e9;
  font-weight: bold;
}

.mt-3 {
  margin-top: 20px;
}

.mt-4 {
  margin-top: 30px;
}

/* Full Commentary Tab */
.full-commentary-tab {
  max-height: 600px;
  overflow-y: auto;
}

.full-commentary-feed {
  padding: 10px 0;
}

.comment-detailed {
  display: flex;
  padding: 15px;
  border-bottom: 1px solid #f0f0f0;
  gap: 15px;
}

.comment-detailed:hover {
  background: #f9f9f9;
}

.comment-over {
  min-width: 50px;
  font-weight: bold;
  color: #666;
  font-size: 14px;
}

.comment-text {
  flex: 1;
  line-height: 1.6;
  color: #333;
}

.comment-detailed.type-wicket {
  background: #fff3e0;
}

.comment-detailed.type-boundary {
  background: #e8f5e9;
}

/* Info Tab */
.info-tab {
  padding: 20px;
}

.info-grid {
  display: grid;
  gap: 20px;
}

.info-item {
  display: flex;
  padding: 15px;
  background: #f8f9fa;
  border-radius: 8px;
  border-left: 4px solid #28a745;
}

.info-item .label {
  font-weight: 600;
  min-width: 120px;
  color: #666;
}

.info-item .value {
  color: #333;
}

.recent-balls {
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.balls-grid {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.ball {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-weight: bold;
  font-size: 16px;
}

.ball-wicket {
  background: #dc3545;
  color: white;
}

.ball-six {
  background: #28a745;
  color: white;
}

.ball-four {
  background: #007bff;
  color: white;
}

.ball-dot {
  background: #6c757d;
  color: white;
}

.ball-extra {
  background: #ffc107;
  color: #333;
}

.commentary {
  background: white;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.commentary-feed {
  max-height: 400px;
  overflow-y: auto;
}

.comment {
  padding: 10px;
  border-left: 3px solid #667eea;
  margin-bottom: 10px;
  background: #f8f9fa;
}

.comment .over {
  font-weight: bold;
  color: #667eea;
  margin-right: 10px;
}

.type-wicket {
  border-left-color: #dc3545;
  background: #ffebee;
}

.type-boundary {
  border-left-color: #28a745;
  background: #e8f5e9;
}

.controls {
  display: flex;
  gap: 10px;
  justify-content: center;
  margin-top: 20px;
}

.btn {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-primary {
  background: #007bff;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #0056b3;
}

.btn-success {
  background: #28a745;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #218838;
}

.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #c82333;
}
</style>
