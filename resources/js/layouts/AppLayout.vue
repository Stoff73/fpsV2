<template>
  <div class="min-h-screen flex flex-col">
    <Navbar />

    <main class="flex-grow bg-gray-50">
      <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <slot />
      </div>
    </main>

    <Footer />
  </div>
</template>

<script>
import Navbar from '@/components/Navbar.vue';
import Footer from '@/components/Footer.vue';

export default {
  name: 'AppLayout',

  components: {
    Navbar,
    Footer,
  },
};
</script>
