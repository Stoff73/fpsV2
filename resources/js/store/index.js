import { createStore } from 'vuex';
import auth from './modules/auth';
import user from './modules/user';
import dashboard from './modules/dashboard';
import protection from './modules/protection';
import savings from './modules/savings';
import investment from './modules/investment';
import retirement from './modules/retirement';
import estate from './modules/estate';
import holistic from './modules/holistic';

const store = createStore({
  modules: {
    auth,
    user,
    dashboard,
    protection,
    savings,
    investment,
    retirement,
    estate,
    holistic,
  },
  strict: process.env.NODE_ENV !== 'production',
});

export default store;
