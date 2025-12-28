<template>
  <div class="position-relative">
    <LanguageSwitcher />
    <div class="authentication-wrapper authentication-basic container-p-y p-4 p-sm-0">
      <div class="authentication-inner py-6">
        <!-- Login -->
        <div class="card p-md-7 p-4">
          <!-- Logo -->
          <div class="app-brand justify-content-center mt-5 mb-4">
            <a href="#" class="app-brand-link gap-2">
              <span class="app-brand-logo demo">
                 <Logo :width="120" />
              </span>
              <span class="app-brand-text demo text-heading fw-semibold">Dentomatic</span>
            </a>
          </div>
          <!-- /Logo -->

          <div class="card-body mt-1">
            <h4 class="mb-1">{{ t.welcome }}</h4>
            <p class="mb-5">{{ t.subtitle }}</p>

            <form id="formAuthentication" class="mb-5" @submit.prevent="handleLogin">
                <!-- Alert for Error -->
                <div v-if="authStore.error" class="alert alert-danger" role="alert">
                    {{ authStore.error }}
                </div>

              <div class="form-floating form-floating-outline mb-5">
                <input
                  type="email"
                  class="form-control"
                  id="email"
                  name="email-username"
                  v-model="form.email"
                  :placeholder="t.email_placeholder"
                  autofocus />
                <label for="email">{{ t.email_label }}</label>
              </div>
              <div class="mb-5">
                <div class="form-password-toggle">
                  <div class="input-group input-group-merge">
                    <div class="form-floating form-floating-outline">
                      <input
                        :type="isPasswordVisible ? 'text' : 'password'"
                        id="password"
                        class="form-control"
                        name="password"
                        v-model="form.password"
                        :placeholder="t.password_placeholder"
                        aria-describedby="password" />
                      <label for="password">{{ t.password_label }}</label>
                    </div>
                    <span class="input-group-text cursor-pointer" @click="isPasswordVisible = !isPasswordVisible">
                        <i :class="isPasswordVisible ? 'ri-eye-line' : 'ri-eye-off-line'"></i>
                    </span>
                  </div>
                </div>
              </div>
              <div class="mb-5 d-flex justify-content-between mt-5">
                <div class="form-check mt-2">
                  <input class="form-check-input" type="checkbox" id="remember-me" v-model="form.remember" />
                  <label class="form-check-label" for="remember-me"> {{ t.remember_me }} </label>
                </div>
              </div>
              <div class="mb-5">
                <button class="btn btn-primary d-grid w-100" type="submit" :disabled="authStore.loading">
                    <span v-if="authStore.loading" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                    {{ authStore.loading ? t.loading : t.login_btn }}
                </button>
              </div>
            </form>
          </div>
        </div>
        <!-- /Login -->
        <img
          alt="mask"
          :src="'/assets/img/illustrations/auth-basic-login-mask-light.png'"
          class="authentication-image d-none d-lg-block"
          data-app-light-img="illustrations/auth-basic-login-mask-light.png"
          data-app-dark-img="illustrations/auth-basic-login-mask-dark.png" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { useAuthStore } from '@/stores/useAuthStore';
import { useConfigStore } from '@/stores/useConfigStore';
import LanguageSwitcher from '@/components/common/LanguageSwitcher.vue';
import { translations } from '@/locales';
import Logo from '@/components/common/Logo.vue';

const authStore = useAuthStore();
const configStore = useConfigStore();
const isPasswordVisible = ref(false);

const form = reactive({
    email: '',
    password: '',
    remember: false
});

const t = computed(() => translations[configStore.language] || translations.ru);

const handleLogin = async () => {
    await authStore.login(form);
};
</script>

<style scoped>
/* Removed custom flex styles to rely on Template's CSS for responsiveness */
</style>
