import { createRouter, createWebHistory } from 'vue-router';
import store from '@/store';

// Lazy load components
const Login = () => import('@/views/Login.vue');
const Register = () => import('@/views/Register.vue');
const Dashboard = () => import('@/views/Dashboard.vue');
const Settings = () => import('@/views/Settings.vue');
const ProtectionDashboard = () => import('@/views/Protection/ProtectionDashboard.vue');
const SavingsDashboard = () => import('@/views/Savings/SavingsDashboard.vue');
const InvestmentDashboard = () => import('@/views/Investment/InvestmentDashboard.vue');
const RetirementDashboard = () => import('@/views/Retirement/RetirementDashboard.vue');
const EstateDashboard = () => import('@/views/Estate/EstateDashboard.vue');
const HolisticPlan = () => import('@/views/HolisticPlan.vue');

const routes = [
  {
    path: '/',
    redirect: '/dashboard',
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: { requiresGuest: true },
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings,
    meta: { requiresAuth: true },
  },
  {
    path: '/protection',
    name: 'Protection',
    component: ProtectionDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Protection', path: '/protection' },
      ],
    },
  },
  {
    path: '/savings',
    name: 'Savings',
    component: SavingsDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Savings', path: '/savings' },
      ],
    },
  },
  {
    path: '/investment',
    name: 'Investment',
    component: InvestmentDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Investment', path: '/investment' },
      ],
    },
  },
  {
    path: '/retirement',
    name: 'Retirement',
    component: RetirementDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Retirement Planning', path: '/retirement' },
      ],
    },
  },
  {
    path: '/estate',
    name: 'Estate',
    component: EstateDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Estate Planning', path: '/estate' },
      ],
    },
  },
  {
    path: '/holistic-plan',
    name: 'HolisticPlan',
    component: HolisticPlan,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Holistic Plan', path: '/holistic-plan' },
      ],
    },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters['auth/isAuthenticated'];

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Redirect to login if route requires authentication
    next({ name: 'Login' });
  } else if (to.meta.requiresGuest && isAuthenticated) {
    // Redirect to dashboard if already authenticated
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;
