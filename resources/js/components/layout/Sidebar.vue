<template>
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
    <a href="/" class="app-brand-link">
        <span class="app-brand-logo demo">
            <Logo :width="60" />
        </span>
        <span class="app-brand-text demo menu-text fw-semibold ms-2">Dentomatic</span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="mdi mdi-close align-middle"></i>
    </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
    <!-- Main -->
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ t.menu }}</span>
    </li>
    
    <!-- Custom logic for Home to avoid matching everything -->
    <router-link :to="{ name: 'home' }" custom v-slot="{ href, navigate, isActive, isExactActive }">
        <li class="menu-item" :class="{ active: isExactActive }">
            <a :href="href" @click="navigate" class="menu-link">
                <i class="menu-icon tf-icons ri-home-smile-line"></i>
                <div>{{ t.home }}</div>
            </a>
        </li>
    </router-link>

    <!-- Clinic -->
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ t.clinic }}</span>
    </li>
    
    <li class="menu-item">
        <a href="#" class="menu-link">
             <i class="menu-icon tf-icons ri-user-line"></i>
             <div>{{ t.patients }}</div>
        </a>
    </li>
    
    <router-link :to="{ name: 'doctors' }" custom v-slot="{ href, navigate, isActive }">
        <li class="menu-item" :class="{ active: isActive }">
            <a :href="href" @click="navigate" class="menu-link">
                <i class="menu-icon tf-icons ri-stethoscope-line"></i>
                <div>{{ t.doctors }}</div>
            </a>
        </li>
    </router-link>
    
     <!-- Admin -->
    <li class="menu-header small text-uppercase">
        <span class="menu-header-text">{{ t.admin }}</span>
    </li>
    
    <li class="menu-item">
        <a href="#" class="menu-link">
             <i class="menu-icon tf-icons ri-archive-line"></i>
             <div>{{ t.warehouse }}</div>
        </a>
    </li>
    <li class="menu-item">
        <a href="#" class="menu-link">
             <i class="menu-icon tf-icons ri-money-dollar-circle-line"></i>
             <div>{{ t.finance }}</div>
        </a>
    </li>
    </ul>
    
    <!-- Settings Section (Fixed at bottom or just appended) -->
    <ul class="menu-inner py-1">
        <router-link :to="{ name: 'settings' }" custom v-slot="{ href, navigate, isActive }">
            <li class="menu-item" :class="{ active: isActive }">
                <a :href="href" @click="navigate" class="menu-link">
                    <i class="menu-icon tf-icons ri-settings-4-line"></i>
                    <div>{{ t.settings }}</div>
                </a>
            </li>
        </router-link>
    </ul>
</aside>
</template>

<script setup>
import { computed } from 'vue';
import { useConfigStore } from '@/stores/useConfigStore';
import Logo from '@/components/common/Logo.vue';

const configStore = useConfigStore();

import { translations } from '@/locales';

const t = computed(() => translations[configStore.language] || translations.ru);
</script>
