import { defineStore } from 'pinia';
import axios from 'axios';
import router from '@/router';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: JSON.parse(localStorage.getItem('user')) || null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
    }),
    getters: {
        isAuthenticated: (state) => !!state.token,
    },
    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                // Determine base URL - assuming /api prefix based on Laravel default
                const response = await axios.post('/api/login', credentials);

                const { access_token, user } = response.data;

                this.token = access_token;
                this.user = user;

                // Secure persistence
                localStorage.setItem('token', access_token);
                localStorage.setItem('user', JSON.stringify(user));

                // Configure axios defaults for future requests
                axios.defaults.headers.common['Authorization'] = `Bearer ${access_token}`;

                await router.push({ name: 'home' });
            } catch (err) {
                console.error('Login Error:', err);
                if (err.response && err.response.data && err.response.data.errors) {
                    this.error = Object.values(err.response.data.errors).flat().join(', ');
                } else if (err.response && err.response.data.message) {
                    this.error = err.response.data.message;
                } else {
                    this.error = 'Login failed. Please check your connection.';
                }
                throw err;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                if (this.token) {
                    await axios.post('/api/logout');
                }
            } catch (e) {
                console.warn('Logout api call failed', e);
            } finally {
                this.token = null;
                this.user = null;
                localStorage.removeItem('token');
                localStorage.removeItem('user');
                delete axios.defaults.headers.common['Authorization'];
                router.push({ name: 'login' });
            }
        },
        initialize() {
            if (this.token) {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
            }
        }
    }
});
