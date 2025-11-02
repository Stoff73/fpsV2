<template>
  <div class="recommendations">
    <InvestmentRecommendationsTracker />
  </div>
</template>

<script>
import InvestmentRecommendationsTracker from './InvestmentRecommendationsTracker.vue';

export default {
  name: 'Recommendations',

  components: {
    InvestmentRecommendationsTracker,
  },
};
</script>
