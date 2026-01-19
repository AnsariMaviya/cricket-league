<template>
  <div class="live-match-detail">
    <LiveScoreboard 
      v-if="matchId" 
      :match-id="matchId" 
      :auto-refresh="true"
      @ball-simulated="handleBallSimulated"
      @match-simulated="handleMatchComplete"
    />
  </div>
</template>

<script>
import LiveScoreboard from '../components/LiveScoreboard.vue';

export default {
  name: 'LiveMatchDetail',
  components: {
    LiveScoreboard
  },
  computed: {
    matchId() {
      return parseInt(this.$route.params.id);
    }
  },
  methods: {
    handleBallSimulated(ball) {
      console.log('Ball simulated:', ball);
    },
    handleMatchComplete(match) {
      console.log('Match completed:', match);
      this.$router.push(`/matches`);
    }
  }
}
</script>

<style scoped>
.live-match-detail {
  min-height: 100vh;
  background: #f8f9fa;
}
</style>
