import { createRouter, createWebHistory } from 'vue-router';
import AppLayout from '@/components/layout/AppLayout.vue';
import Login from '@/components/auth/Login.vue';
import Dashboard from '@/components/dashboard/Dashboard.vue';
import { useAuthStore } from '@/stores/useAuthStore';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        component: AppLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'home',
                component: Dashboard
            },
            {
                path: 'doctors',
                name: 'doctors',
                component: () => import('@/components/dashboard/Doctors.vue')
            },
            {
                path: 'settings',
                name: 'settings',
                component: () => import('@/components/dashboard/Settings.vue')
            }
        ]
    },
    // Catch-all route for SPA logic (let Laravel handle the view, Vue router handles content)
    { path: '/:pathMatch(.*)*', redirect: '/' }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    // Initialize headers if token exists in state
    if (authStore.token && !authStore.isAuthenticated) {
        // This condition is slightly redundant with store logic but good for safety
        // authStore.isAuthenticated relies on token existence
    }

    // Ensure axios defaults are set on refresh
    if (authStore.token) {
        authStore.initialize();
    }

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        next({ name: 'login' });
    } else if (to.meta.guest && authStore.isAuthenticated) {
        next({ name: 'home' });
    } else {
        next();
    }
});

export default router;
