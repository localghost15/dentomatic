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
            <option v-for="doc in computedDoctors" :key="doc.value" :value="doc.value">
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
                <button v-if="form.id && !showDeleteConfirm" type="button" class="btn btn-outline-danger btn-icon" @click="initiateDelete" title="Delete">
                    <i class="ri-delete-bin-line"></i>
                </button>
                 <!-- Delete Confirmation View (Inline) -->
                 <div v-if="showDeleteConfirm" class="d-flex align-items-center gap-2">
                    <span class="text-danger fw-medium me-2 small">{{ t.confirm_delete_text_short }}</span>
                    <button type="button" class="btn btn-danger btn-sm p-1 px-2" @click="confirmDelete"><i class="ri-check-line"></i></button>
                    <button type="button" class="btn btn-secondary btn-sm p-1 px-2" @click="cancelDelete"><i class="ri-close-line"></i></button>
                 </div>
            </div>

            <!-- Save/Cancel Buttons (Right side) -->
            <div v-if="!showDeleteConfirm">
                <button type="reset" class="btn btn-outline-secondary me-2" data-bs-dismiss="offcanvas">{{ t.cancel }}</button>
                <button type="submit" class="btn btn-primary data-submit">{{ t.save }}</button>
            </div>
        </div>
      </form>
      
       <!-- Conflict Warning Overlay -->
        <div v-if="showConflictModal" class="position-absolute top-0 start-0 w-100 h-100 bg-white d-flex flex-column justify-content-center align-items-center p-4 text-center" style="z-index: 1050; opacity: 0.98;">
            <div class="mb-3">
                 <i class="ri-alarm-warning-fill text-warning" style="font-size: 4rem;"></i>
            </div>
            <h4 class="fw-bold text-danger mb-3">{{ t.conflict_title }}</h4>
            <p class="fs-5 mb-4 text-dark" v-html="conflictMessage"></p>
            
            <div class="d-flex gap-3">
                 <button type="button" class="btn btn-outline-secondary btn-lg" @click="closeConflictModal">{{ t.cancel }}</button>
                 <button type="button" class="btn btn-danger btn-lg shadow" @click="confirmForceSave">{{ t.add_anyway }}</button>
            </div>
        </div>
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
const showConflictModal = ref(false);
const conflictData = ref(null);

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

// Computed doctors list to handle archived doctors
const computedDoctors = computed(() => {
    // Clone active list
    const list = [...props.doctors];
    
    // If form has a doctor ID that is NOT in the list (archived), append it temporarily
    if (form.doctor && !list.find(d => d.value == form.doctor)) {
        list.push({
            value: form.doctor,
            label: t.value.archived_doctor + ' ' + t.value.archived_label,
            disabled: true // Typically archived doctors shouldn't be re-selected if changed, but we just need to display it.
        });
    }
    return list;
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

const conflictMessage = computed(() => {
    if (!conflictData.value) return '';
    let msg = t.value.conflict_text;
    msg = msg.replace('{name}', `<b>${conflictData.value.doctor_name}</b>`);
    msg = msg.replace('{time}', `<b>${conflictData.value.existing_time}</b>`);
    return msg;
});

const handleSubmit = async () => {
   await submitWithForce(false);
};

const submitWithForce = async (force = false) => {
    try {
        const payload = { ...form, force };
        
        if (form.id) {
            // Update
            await axios.put(`/api/bookings/${form.id}`, payload);
        } else {
            // Create
            await axios.post('/api/bookings', payload);
        }
        
        emit('booking-saved'); // Notify parent to refresh calendar
        toastStore.add(t.value.saved_success, 'success', t.value.success_title);
        
        const closeBtn = document.querySelector('#bookingCanvas .btn-close');
        if(closeBtn) closeBtn.click();
        
        // Reset conflict state
        showConflictModal.value = false;
        conflictData.value = null;
        
    } catch (error) {
        if (error.response && error.response.status === 409) {
            // Handle Double Booking
            conflictData.value = error.response.data.conflict_data;
            showConflictModal.value = true;
        } else {
            console.error('Error saving booking:', error);
            toastStore.add('Ошибка при сохранении: ' + (error.response?.data?.message || error.message), 'error', t.value.error_title);
        }
    }
};

const closeConflictModal = () => {
    showConflictModal.value = false;
    conflictData.value = null;
};

const confirmForceSave = () => {
    submitWithForce(true);
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
    showConflictModal.value = false; // Reset conflict modal on open
    form.id = data.id || null;
    form.fullname = data.fullname || '';
    form.phone = data.phone || '';
    form.telegram = data.telegram || '';
    form.doctor = data.doctor || '';
    
    // Fix: Format date for datetime-local (YYYY-MM-DDTHH:mm)
    if (data.datetime) {
        let dt = data.datetime;
        // If it's the raw format YYYY-MM-DDTHH:mm, it fits perfectly.
        // If it's ISO, take first 16 chars.
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
