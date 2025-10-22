<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h1 class="text-center font-display text-h1 text-gray-900">
          FPS
        </h1>
        <h2 class="mt-6 text-center text-h3 text-gray-900">
          Create your account
        </h2>
        <p class="mt-2 text-center text-body-sm text-gray-600">
          Or
          <router-link to="/login" class="font-medium text-primary-600 hover:text-primary-700">
            sign in to existing account
          </router-link>
        </p>
      </div>

      <form class="mt-8 space-y-6" @submit.prevent="handleRegister">
        <div v-if="errorMessage" class="rounded-button bg-error-50 p-4">
          <p class="text-body-sm text-error-700">{{ errorMessage }}</p>
        </div>

        <div class="space-y-4">
          <div>
            <label for="name" class="label">
              Full Name
            </label>
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.name }"
              placeholder="John Smith"
            >
            <p v-if="errors.name" class="mt-1 text-body-sm text-error-600">
              {{ errors.name[0] }}
            </p>
          </div>

          <div>
            <label for="email" class="label">
              Email address
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.email }"
              placeholder="you@example.com"
            >
            <p v-if="errors.email" class="mt-1 text-body-sm text-error-600">
              {{ errors.email[0] }}
            </p>
          </div>

          <div>
            <label for="password" class="label">
              Password
            </label>
            <input
              id="password"
              v-model="form.password"
              type="password"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.password }"
              placeholder="••••••••"
            >
            <p v-if="errors.password" class="mt-1 text-body-sm text-error-600">
              {{ errors.password[0] }}
            </p>
          </div>

          <div>
            <label for="password_confirmation" class="label">
              Confirm Password
            </label>
            <input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              required
              class="input-field"
              placeholder="••••••••"
            >
          </div>

          <div>
            <label for="date_of_birth" class="label">
              Date of Birth
            </label>
            <input
              id="date_of_birth"
              v-model="form.date_of_birth"
              type="date"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.date_of_birth }"
            >
            <p v-if="errors.date_of_birth" class="mt-1 text-body-sm text-error-600">
              {{ errors.date_of_birth[0] }}
            </p>
          </div>

          <div>
            <label for="gender" class="label">
              Gender
            </label>
            <select
              id="gender"
              v-model="form.gender"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.gender }"
            >
              <option value="">Select gender</option>
              <option value="male">Male</option>
              <option value="female">Female</option>
              <option value="other">Other</option>
              <option value="prefer_not_to_say">Prefer not to say</option>
            </select>
            <p v-if="errors.gender" class="mt-1 text-body-sm text-error-600">
              {{ errors.gender[0] }}
            </p>
          </div>

          <div>
            <label for="marital_status" class="label">
              Marital Status
            </label>
            <select
              id="marital_status"
              v-model="form.marital_status"
              required
              class="input-field"
              :class="{ 'border-error-600': errors.marital_status }"
            >
              <option value="">Select marital status</option>
              <option value="single">Single</option>
              <option value="married">Married</option>
              <option value="civil_partnership">Civil Partnership</option>
              <option value="divorced">Divorced</option>
              <option value="widowed">Widowed</option>
            </select>
            <p v-if="errors.marital_status" class="mt-1 text-body-sm text-error-600">
              {{ errors.marital_status[0] }}
            </p>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary"
            :class="{ 'opacity-50 cursor-not-allowed': loading }"
          >
            <span v-if="!loading">Create Account</span>
            <span v-else>Creating Account...</span>
          </button>
        </div>

        <p class="text-center text-body-sm text-gray-600">
          By creating an account, you agree to our Terms of Service and Privacy Policy
        </p>
      </form>
    </div>
  </div>
</template>

<script>
import { ref, computed } from 'vue';
import { useStore } from 'vuex';
import { useRouter } from 'vue-router';

export default {
  name: 'Register',

  setup() {
    const store = useStore();
    const router = useRouter();

    const form = ref({
      name: '',
      email: '',
      password: '',
      password_confirmation: '',
      date_of_birth: '',
      gender: '',
      marital_status: '',
    });

    const errors = ref({});
    const errorMessage = ref('');

    const loading = computed(() => store.getters['auth/loading']);

    const handleRegister = async () => {
      errors.value = {};
      errorMessage.value = '';

      try {
        await store.dispatch('auth/register', form.value);
        // Redirect to onboarding after successful registration
        router.push({ name: 'Onboarding' });
      } catch (error) {
        if (error.errors) {
          errors.value = error.errors;
        } else {
          errorMessage.value = error.message || 'Registration failed. Please try again.';
        }
      }
    };

    return {
      form,
      errors,
      errorMessage,
      loading,
      handleRegister,
    };
  },
};
</script>
