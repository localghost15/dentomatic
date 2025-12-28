<template>
  <div class="card h-100">
    <div class="card-header border-bottom">
      <h5 class="card-title mb-0">{{ t.telegram_settings }}</h5>
    </div>
    <div class="card-body pt-4">
      <form @submit.prevent="saveSettings">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label">{{ t.telegram_bot_token }}</label>
            <input 
              type="password" 
              class="form-control" 
              v-model="form.telegram_bot_token"
              placeholder="123456:ABC-DEF1234ghIkl-zyx57W2v1u123ew11"
            />
            <div class="form-text">Stored securely. Returned as masked.</div>
          </div>
          
          <div class="col-12 col-md-6">
            <label class="form-label">{{ t.telegram_bot_username }}</label>
            <div class="input-group">
                <span class="input-group-text">@</span>
                <input 
                type="text" 
                class="form-control" 
                v-model="form.telegram_bot_username"
                placeholder="dentomatic_bot"
                />
            </div>
          </div>
          
          <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary me-2">{{ t.save }}</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import axios from 'axios';
import { useConfigStore } from '@/stores/useConfigStore';
import { useToastStore } from '@/stores/useToastStore';
import { translations } from '@/locales';

const configStore = useConfigStore();
const toastStore = useToastStore();
const t = computed(() => translations[configStore.language] || translations.ru);

const form = reactive({
  telegram_bot_token: '',
  telegram_bot_username: ''
});

const fetchSettings = async () => {
  try {
    const response = await axios.get('/api/settings');
    const data = response.data;
    if (data.telegram_bot_token) form.telegram_bot_token = data.telegram_bot_token;
    if (data.telegram_bot_username) form.telegram_bot_username = data.telegram_bot_username;
  } catch (error) {
    console.error('Error fetching settings:', error);
  }
};

const saveSettings = async () => {
  try {
    await axios.post('/api/settings', form);
    toastStore.add({
      title: 'Success',
      message: t.value.settings_saved,
      type: 'success'
    });
    // Refresh to get masked values if needed, or just stay as is
    fetchSettings();
  } catch (error) {
    toastStore.add({
      title: 'Error',
      message: 'Failed to save settings',
      type: 'error'
    });
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
