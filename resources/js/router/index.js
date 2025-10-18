import { createRouter, createWebHistory } from 'vue-router';
import store from '@/store';

// Lazy load components
const Login = () => import('@/views/Login.vue');
const Register = () => import('@/views/Register.vue');
const Dashboard = () => import('@/views/Dashboard.vue');
const Settings = () => import('@/views/Settings.vue');
const UserProfile = () => import('@/views/UserProfile.vue');
const NetWorthDashboard = () => import('@/views/NetWorth/NetWorthDashboard.vue');
const NetWorthOverview = () => import('@/components/NetWorth/NetWorthOverview.vue');
const PropertyList = () => import('@/components/NetWorth/PropertyList.vue');
const PropertyDetail = () => import('@/components/NetWorth/Property/PropertyDetail.vue');
const BusinessInterestsList = () => import('@/components/NetWorth/BusinessInterestsList.vue');
const ChattelsList = () => import('@/components/NetWorth/ChattelsList.vue');
const ProtectionDashboard = () => import('@/views/Protection/ProtectionDashboard.vue');
const SavingsDashboard = () => import('@/views/Savings/SavingsDashboard.vue');
const InvestmentDashboard = () => import('@/views/Investment/InvestmentDashboard.vue');
const RetirementDashboard = () => import('@/views/Retirement/RetirementDashboard.vue');
const EstateDashboard = () => import('@/views/Estate/EstateDashboard.vue');
const TrustsDashboard = () => import('@/views/Trusts/TrustsDashboard.vue');
const HolisticPlan = () => import('@/views/HolisticPlan.vue');
const UKTaxesDashboard = () => import('@/views/UKTaxes/UKTaxesDashboard.vue');
const Version = () => import('@/views/Version.vue');

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
    path: '/profile',
    name: 'UserProfile',
    component: UserProfile,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Profile', path: '/profile' },
      ],
    },
  },
  {
    path: '/net-worth',
    component: NetWorthDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Net Worth', path: '/net-worth' },
      ],
    },
    children: [
      {
        path: '',
        redirect: 'overview',
      },
      {
        path: 'overview',
        name: 'NetWorthOverview',
        component: NetWorthOverview,
      },
      {
        path: 'property',
        name: 'NetWorthProperty',
        component: PropertyList,
      },
      {
        path: 'investments',
        name: 'NetWorthInvestments',
        component: InvestmentDashboard,
      },
      {
        path: 'cash',
        name: 'NetWorthCash',
        component: SavingsDashboard,
      },
      {
        path: 'business',
        name: 'NetWorthBusiness',
        component: BusinessInterestsList,
      },
      {
        path: 'chattels',
        name: 'NetWorthChattels',
        component: ChattelsList,
      },
    ],
  },
  {
    path: '/property/:id',
    name: 'PropertyDetail',
    component: PropertyDetail,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Net Worth', path: '/net-worth' },
        { label: 'Property', path: '/property/:id' },
      ],
    },
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
    path: '/trusts',
    name: 'Trusts',
    component: TrustsDashboard,
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Trusts', path: '/trusts' },
      ],
    },
  },
  {
    path: '/actions',
    name: 'Actions',
    component: () => import('@/views/Actions/ActionsDashboard.vue'),
    meta: {
      requiresAuth: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'Actions & Recommendations', path: '/actions' },
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
  {
    path: '/uk-taxes',
    name: 'UKTaxes',
    component: UKTaxesDashboard,
    meta: {
      requiresAuth: true,
      requiresAdmin: true,
      breadcrumb: [
        { label: 'Home', path: '/dashboard' },
        { label: 'UK Taxes & Allowances', path: '/uk-taxes' },
      ],
    },
  },
  {
    path: '/version',
    name: 'Version',
    component: Version,
    meta: {
      requiresAuth: false,
    },
  },
];

const router = createRouter({
  history: createWebHistory('/'),
  routes,
});

// Navigation guards
router.beforeEach((to, from, next) => {
  const isAuthenticated = store.getters['auth/isAuthenticated'];
  const isAdmin = store.getters['auth/isAdmin'];

  if (to.meta.requiresAuth && !isAuthenticated) {
    // Redirect to login if route requires authentication
    next({ name: 'Login' });
  } else if (to.meta.requiresGuest && isAuthenticated) {
    // Redirect to dashboard if already authenticated
    next({ name: 'Dashboard' });
  } else if (to.meta.requiresAdmin && !isAdmin) {
    // Redirect to dashboard if route requires admin access
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;
