<template>
  <div class="what-if-scenarios">
    <WhatIfScenariosBuilder />
  </div>
</template>

<script>
import WhatIfScenariosBuilder from './WhatIfScenariosBuilder.vue';

export default {
  name: 'WhatIfScenarios',

  components: {
    WhatIfScenariosBuilder,
  },
};
</script>
