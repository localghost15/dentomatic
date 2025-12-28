<template>
  <nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
      <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)" @click="toggleSidebar" style="color: #697a8d !important; z-index: 1050;">
        <i class="mdi mdi-menu mdi-24px"></i>
      </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- Search -->
      <div class="navbar-nav align-items-center">
        <div class="nav-item d-flex align-items-center">
          <i class="mdi mdi-magnify mdi-24px lh-0"></i>
          <input
            type="text"
            class="form-control border-0 shadow-none bg-body"
            :placeholder="t.search_placeholder"
            :aria-label="t.search_placeholder" />
        </div>
      </div>
      <!-- /Search -->

      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!-- Language -->
        <li class="nav-item dropdown-language dropdown me-2 me-xl-0">
          <a class="nav-link dropdown-toggle hide-arrow fw-bold" href="javascript:void(0);" data-bs-toggle="dropdown">
            <i class="ri-translate-2 ri-22px"></i>
            <span class="ms-1">{{ currentLanguage.toUpperCase() }}</span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="javascript:void(0);" @click="setLanguage('ru')" :class="{ active: currentLanguage === 'ru' }">
                <span class="align-middle">Русский</span>
              </a>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" @click="setLanguage('uz')" :class="{ active: currentLanguage === 'uz' }">
                <span class="align-middle">O'zbek</span>
              </a>
            </li>
          </ul>
        </li>
        <!--/ Language -->

        <!-- Style Switcher -->
        <li class="nav-item me-2 me-xl-0">
          <a class="nav-link hide-arrow" href="javascript:void(0);">
            <i class="mdi mdi-weather-sunny mdi-24px"></i>
          </a>
        </li>
        <!--/ Style Switcher -->

        <!-- User -->
        <li class="nav-item navbar-dropdown dropdown-user dropdown">
          <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
            <div class="avatar avatar-online">
              <img :src="'/assets/img/avatars/1.png'" alt class="w-px-40 h-auto rounded-circle" />
            </div>
          </a>
          <ul class="dropdown-menu dropdown-menu-end">
            <li>
              <a class="dropdown-item" href="#">
                <div class="d-flex">
                  <div class="flex-shrink-0 me-3">
                    <div class="avatar avatar-online">
                      <img :src="'/assets/img/avatars/1.png'" alt class="w-px-40 h-auto rounded-circle" />
                    </div>
                  </div>
                  <div class="flex-grow-1">
                    <span class="fw-semibold d-block">{{ user?.name || 'User' }}</span>
                    <small class="text-muted">{{ t.user_role }}</small>
                  </div>
                </div>
              </a>
            </li>
            <li>
              <div class="dropdown-divider"></div>
            </li>
            <li>
              <a class="dropdown-item" href="javascript:void(0);" @click="handleLogout">
                <i class="mdi mdi-logout me-2"></i>
                <span class="align-middle">{{ t.logout }}</span>
              </a>
            </li>
          </ul>
        </li>
        <!--/ User -->
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useAuthStore } from '@/stores/useAuthStore';
import { useConfigStore } from '@/stores/useConfigStore';
import { useRouter } from 'vue-router';
import { translations } from '@/locales';

const authStore = useAuthStore();
const configStore = useConfigStore();
const router = useRouter();

const t = computed(() => translations[configStore.language] || translations.ru);
const user = computed(() => authStore.user);
const currentLanguage = computed(() => configStore.language);

const toggleSidebar = () => {
   // Logic to toggle class on html/body element
   // In template usually 'layout-menu-expanded' class on <html>
   document.documentElement.classList.toggle('layout-menu-expanded');
};

const setLanguage = (lang) => {
    configStore.setLanguage(lang);
};

const handleLogout = async () => {
    await authStore.logout();
};
</script>
