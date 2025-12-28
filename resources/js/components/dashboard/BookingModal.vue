<template>
  <div
    class="offcanvas offcanvas-end"
    tabindex="-1"
    id="bookingCanvas"
    aria-labelledby="bookingCanvasLabel">
    <div class="offcanvas-header border-bottom">
      <h5 id="bookingCanvasLabel" class="offcanvas-title">{{ t.modal_title }}</h5>
      <button
        type="button"
        class="btn-close text-reset"
        data-bs-dismiss="offcanvas"
        aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-grow-1">
      <form class="add-new-record pt-0" @submit.prevent="handleSubmit">
        <!-- Full Name -->
        <div class="form-floating form-floating-outline mb-4">
          <input
            type="text"
            class="form-control"
            id="basicFullname"
            placeholder="John Doe"
            v-model="form.fullname"
            required />
          <label for="basicFullname">{{ t.fullname }}</label>
        </div>

        <!-- Phone -->
        <div class="form-floating form-floating-outline mb-4">
          <input
            type="text"
            id="basicPhone"
            class="form-control"
            placeholder="+998 90 123 45 67"
            v-model="form.phone"
            required />
          <label for="basicPhone">{{ t.phone }}</label>
        </div>

        <!-- Telegram -->
        <div class="form-floating form-floating-outline mb-4">
          <input
            type="text"
            id="basicTelegram"
            class="form-control"
            placeholder="@username"
            v-model="form.telegram" />
          <label for="basicTelegram">Telegram</label>
        </div>

        <!-- Doctor -->
        <div class="form-floating form-floating-outline mb-4">
          <select id="basicDoctor" class="form-select" v-model="form.doctor" required>
            <option value="" disabled>{{ t.select_doctor }}</option>
            <option v-for="doc in doctors" :key="doc.value" :value="doc.value">
                {{ doc.label }}
            </option>
          </select>
          <label for="basicDoctor">{{ t.doctor }}</label>
        </div>

        <!-- Date & Time -->
        <div class="form-floating form-floating-outline mb-4">
          <input
            type="datetime-local"
            id="basicDate"
            class="form-control"
            v-model="form.datetime"
            required />
          <label for="basicDate">{{ t.datetime }}</label>
        </div>
        
        <!-- Source -->
        <div class="form-floating form-floating-outline mb-4">
          <select id="basicSource" class="form-select" v-model="form.source">
            <option value="instagram">{{ t.instagram }}</option>
            <option value="telegram">{{ t.telegram }}</option>
             <option value="google">{{ t.google }}</option>
            <option value="recommendation">{{ t.recommendation }}</option>
             <option value="other">{{ t.other }}</option>
          </select>
          <label for="basicSource">{{ t.source }}</label>
        </div>

        <!-- Language Preferance -->
         <div class="form-floating form-floating-outline mb-4">
          <select id="basicLang" class="form-select" v-model="form.lang_pref">
            <option value="ru">Русский</option>
            <option value="uz">O'zbek</option>
          </select>
          <label for="basicLang">{{ t.lang_pref }}</label>
        </div>

        <!-- Buttons -->
        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
            <!-- Delete Button (Left side) -->
            <div>
                <button v-if="form.id && !showDeleteConfirm" type="button" class="btn btn-danger" @click="initiateDelete">
                    <i class="ri-delete-bin-line me-1"></i> {{ t.delete }}
                </button>
                 <!-- Delete Confirmation View (Inline) -->
                 <div v-if="showDeleteConfirm" class="d-flex align-items-center gap-2">
                    <span class="text-danger fw-medium me-2">{{ t.confirm_delete_text_short }}</span>
                    <button type="button" class="btn btn-danger btn-sm" @click="confirmDelete">{{ t.yes_delete }}</button>
                    <button type="button" class="btn btn-secondary btn-sm" @click="cancelDelete">{{ t.cancel }}</button>
                 </div>
            </div>

            <!-- Save/Cancel Buttons (Right side) -->
            <div v-if="!showDeleteConfirm">
                <button type="reset" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">{{ t.cancel }}</button>
                <button type="submit" class="btn btn-primary data-submit">{{ t.save }}</button>
            </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, computed, onMounted, ref } from 'vue';
import { useConfigStore } from '@/stores/useConfigStore';
import { useToastStore } from '@/stores/useToastStore';
import axios from 'axios';
import { translations } from '@/locales';

const configStore = useConfigStore();
const toastStore = useToastStore();
const bookingModalRef = ref(null);

// Define emits to notify parent
const emit = defineEmits(['booking-saved', 'booking-deleted']);

const showDeleteConfirm = ref(false);

const props = defineProps({
    initialData: {
        type: Object,
        default: () => ({})
    },
    doctors: {
        type: Array,
        default: () => []
    }
});

const form = reactive({
    id: null,
    fullname: '',
    phone: '',
    telegram: '',
    doctor: '',
    datetime: '',
    source: 'instagram',
    lang_pref: 'ru'
});

const t = computed(() => translations[configStore.language] || translations.ru);

const handleSubmit = async () => {
    try {
        if (form.id) {
            // Update
            await axios.put(`/api/bookings/${form.id}`, form);
        } else {
            // Create
            await axios.post('/api/bookings', form);
        }
        
        emit('booking-saved'); // Notify parent to refresh calendar
        toastStore.add(t.value.saved_success, 'success', t.value.success_title);
        
        const closeBtn = document.querySelector('#bookingCanvas .btn-close');
        if(closeBtn) closeBtn.click();
        
    } catch (error) {
        console.error('Error saving booking:', error);
        toastStore.add('Ошибка при сохранении: ' + (error.response?.data?.message || error.message), 'error', t.value.error_title);
    }
};

const initiateDelete = () => {
    showDeleteConfirm.value = true;
};

const cancelDelete = () => {
    showDeleteConfirm.value = false;
};

const confirmDelete = async () => {
    try {
        await axios.delete(`/api/bookings/${form.id}`);
        emit('booking-saved'); // Refresh calendar
        toastStore.add(t.value.deleted_success, 'success', t.value.success_title);
        
        const closeBtn = document.querySelector('#bookingCanvas .btn-close');
        if(closeBtn) closeBtn.click();
    } catch (error) {
        console.error('Error deleting booking:', error);
        toastStore.add('Ошибка при удалении: ' + (error.response?.data?.message || error.message), 'error', t.value.error_title);
    } finally {
        showDeleteConfirm.value = false;
    }
};

const handleDelete = initiateDelete; // Alias for backward compatibility if needed, but we use initiateDelete in template

// Expose a method to reset/populate form
const resetForm = (data = {}) => {
    showDeleteConfirm.value = false;
    form.id = data.id || null;
    form.fullname = data.fullname || '';
    form.phone = data.phone || '';
    form.telegram = data.telegram || '';
    form.doctor = data.doctor || '';
    
    // Fix: Format date for datetime-local (YYYY-MM-DDTHH:mm)
    // Remove timezone and seconds if present
    if (data.datetime) {
        let dt = data.datetime;
        // If ISO string with timezone, take first 16 chars: "2025-12-28T09:30"
        if (dt.length > 16) {
             dt = dt.substring(0, 16);
        }
        form.datetime = dt;
    } else {
        form.datetime = '';
    }

    form.source = data.source || 'instagram';
    form.lang_pref = data.lang_pref || 'ru';
};

defineExpose({ resetForm });
</script>
