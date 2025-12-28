<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
      <h4 class="fw-bold py-3 mb-0">{{ t.doctors_title }}</h4>
      
      <div class="d-flex gap-2">
          <div class="input-group">
            <span class="input-group-text"><i class="ri-search-line"></i></span>
            <input type="text" class="form-control" :placeholder="t.search_doctor" v-model="searchQuery">
          </div>
          <button class="btn btn-primary text-nowrap" @click="openModal()">
            <i class="ri-add-line me-1"></i> {{ t.add_doctor }}
          </button>
      </div>
    </div>

    <!-- Doctors Grid -->
    <div v-if="paginatedDoctors.length > 0" class="row g-4 mb-4">
      <div v-for="doctor in paginatedDoctors" :key="doctor.id" class="col-xl-3 col-lg-4 col-md-6">
        <div class="card h-100 shadow-sm border-0">
           <!-- Card Content -->
          <div class="card-body">
            <div class="bg-label-primary text-center mb-4 pt-4 pb-3 rounded-3 position-relative">
              <div class="mx-auto mb-2">
                  <img 
                    :src="doctor.photo_path ? '/storage/' + doctor.photo_path : '/assets/img/avatars/1.png'" 
                    alt="Avatar"
                    class="rounded-circle object-fit-cover shadow"
                    style="width: 100px; height: 100px;"
                  />
              </div>
            </div>
            
            <h5 class="mb-1 text-center fw-bold text-dark">{{ doctor.full_name }}</h5>
            <p class="mb-4 text-center text-muted">{{ doctor.specialization }}</p>
            
            <div class="row mb-4 g-3">
                <div class="col-6">
                   <div class="d-flex align-items-center">
                       <div class="avatar flex-shrink-0 me-3">
                           <span class="avatar-initial rounded-3 bg-label-info"><i class="ri-phone-line fs-4"></i></span>
                       </div>
                       <div class="overflow-hidden">
                           <h6 class="mb-0 text-nowrap fw-semibold text-truncate">{{ doctor.phone || '-' }}</h6>
                           <small class="text-muted">{{ t.phone }}</small>
                       </div>
                   </div>
                </div>
                <div class="col-6">
                   <div class="d-flex align-items-center">
                        <div class="avatar flex-shrink-0 me-3">
                           <span class="avatar-initial rounded-3" :class="doctor.status === 'active' ? 'bg-label-success' : 'bg-label-warning'">
                               <i :class="doctor.status === 'active' ? 'ri-check-line' : 'ri-time-line'" class="ri-history-line fs-4"></i>
                           </span>
                       </div>
                       <div class="overflow-hidden">
                           <h6 class="mb-0 text-nowrap fw-semibold text-truncate">
                               {{ doctor.status === 'active' ? t.status_active : t.status_vacation }}
                           </h6>
                           <small class="text-muted">{{ t.status }}</small>
                       </div>
                   </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button class="btn btn-primary w-100 waves-effect waves-light fw-medium" @click="openModal(doctor)">
                    <i class="ri-edit-line me-1"></i> {{ t.edit_doctor }}
                </button>
                <button class="btn btn-label-danger" @click="confirmDelete(doctor)">
                    <i class="ri-delete-bin-line"></i>
                </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Pagination -->
    <div v-if="totalPages > 1" class="d-flex justify-content-center mt-4">
        <nav aria-label="Doctors pagination">
            <ul class="pagination">
                <li class="page-item" :class="{ disabled: currentPage === 1 }">
                    <button class="page-link" @click="currentPage--">
                        <i class="ri-arrow-left-s-line"></i>
                    </button>
                </li>
                <li v-for="page in totalPages" :key="page" class="page-item" :class="{ active: currentPage === page }">
                    <button class="page-link" @click="currentPage = page">{{ page }}</button>
                </li>
                <li class="page-item" :class="{ disabled: currentPage === totalPages }">
                    <button class="page-link" @click="currentPage++">
                        <i class="ri-arrow-right-s-line"></i>
                    </button>
                </li>
            </ul>
        </nav>
    </div>

    <!-- Empty State -->
    <div v-else class="text-center py-5">
        <i class="ri-stethoscope-line text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
        <h5 class="mt-3 text-muted">{{ t.doctors_empty_title }}</h5>
        <p class="text-muted mb-2">{{ t.doctors_empty_text }}</p>
    </div>



    <!-- Modal -->
    <div v-if="showModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">{{ isEditing ? t.edit_doctor : t.add_doctor }}</h5>
            <button type="button" class="btn-close" @click="closeModal()"></button>
          </div>
          <div class="modal-body">
            <form @submit.prevent="saveDoctor">
                <div class="mb-3">
                    <label class="form-label">{{ t.full_name }}</label>
                    <input type="text" class="form-control" v-model="form.full_name" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ t.specialization }}</label>
                    <input type="text" class="form-control" v-model="form.specialization">
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ t.phone }}</label>
                         <input type="text" class="form-control" v-model="form.phone">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">{{ t.color }}</label>
                        <input type="color" class="form-control form-control-color w-100" v-model="form.calendar_color" title="Choose your color">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ t.status }}</label>
                    <select class="form-select" v-model="form.status">
                        <option value="active">{{ t.status_active }}</option>
                        <option value="vacation">{{ t.status_vacation }}</option>
                    </select>
                </div>
                 <div class="mb-3">
                    <label class="form-label">{{ t.telegram_id }} (Optional)</label>
                    <input type="text" class="form-control" v-model="form.telegram_id">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{ t.photo }}</label>
                    <input type="file" class="form-control" @change="handleFileUpload">
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="button" class="btn btn-outline-secondary" @click="closeModal()">{{ t.cancel }}</button>
                    <button type="submit" class="btn btn-primary">{{ t.save }}</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content text-center p-4">
                <div class="mb-3">
                    <i class="ri-error-warning-line text-warning display-4"></i>
                </div>
                <h5>{{ t.delete_doctor }}</h5>
                <p class="text-muted">{{ t.delete_doctor_confirm }}</p>
                <div class="d-flex justify-content-center gap-2 mt-3">
                    <button class="btn btn-outline-secondary" @click="showDeleteModal = false">{{ t.cancel }}</button>
                    <button class="btn btn-danger" @click="deleteDoctor">{{ t.delete }}</button>
                </div>
            </div>
        </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import axios from 'axios';
import { useConfigStore } from '@/stores/useConfigStore';
import { useToastStore } from '@/stores/useToastStore';
import { translations } from '@/locales';

const configStore = useConfigStore();
const toastStore = useToastStore();
const t = computed(() => translations[configStore.language] || translations.ru);

const doctors = ref([]);
const searchQuery = ref('');

// Pagination State
const currentPage = ref(1);
const itemsPerPage = 32; // 4 columns * 8 rows

const filteredDoctorsList = computed(() => {
    if (!searchQuery.value) return doctors.value;
    const query = searchQuery.value.toLowerCase();
    return doctors.value.filter(doc => 
        doc.full_name.toLowerCase().includes(query) ||
        (doc.specialization && doc.specialization.toLowerCase().includes(query)) ||
        (doc.phone && doc.phone.includes(query))
    );
});

const totalPages = computed(() => Math.ceil(filteredDoctorsList.value.length / itemsPerPage));

const paginatedDoctors = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredDoctorsList.value.slice(start, end);
});

// Reset page on search
watch(searchQuery, () => {
    currentPage.value = 1;
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditing = ref(false);
const currentDoctorId = ref(null);
const doctorToDelete = ref(null);

const form = reactive({
    full_name: '',
    specialization: '',
    phone: '',
    calendar_color: '#3788d8',
    status: 'active',
    telegram_id: '',
    photo: null
});

const fetchDoctors = async () => {
    try {
        const response = await axios.get('/api/doctors');
        doctors.value = response.data;
    } catch (error) {
        console.error('Failed to fetch doctors', error);
    }
};

const openModal = (doctor = null) => {
    isEditing.value = !!doctor;
    if (doctor) {
        currentDoctorId.value = doctor.id;
        form.full_name = doctor.full_name;
        form.specialization = doctor.specialization;
        form.phone = doctor.phone;
        form.calendar_color = doctor.calendar_color;
        form.status = doctor.status;
        form.telegram_id = doctor.telegram_id;
        form.photo = null; // Don't preload file input
    } else {
        resetForm();
    }
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    resetForm();
};

const resetForm = () => {
    currentDoctorId.value = null;
    form.full_name = '';
    form.specialization = '';
    form.phone = '';
    form.calendar_color = '#3788d8';
    form.status = 'active';
    form.telegram_id = '';
    form.photo = null;
};

const handleFileUpload = (event) => {
    form.photo = event.target.files[0];
};

const saveDoctor = async () => {
    try {
        const formData = new FormData();
        formData.append('full_name', form.full_name);
        formData.append('specialization', form.specialization || '');
        formData.append('phone', form.phone || '');
        formData.append('calendar_color', form.calendar_color);
        formData.append('status', form.status);
        if (form.telegram_id) formData.append('telegram_id', form.telegram_id);
        if (form.photo) formData.append('photo', form.photo);

        if (isEditing.value) {
             // For update, Laravel sometimes needs POST with _method=PUT for FormData
            formData.append('_method', 'PUT'); // Or just use POST on specific route
            await axios.post(`/api/doctors/${currentDoctorId.value}`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        } else {
            await axios.post('/api/doctors', formData, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
        }

        toastStore.add({
            title: 'Success',
            message: t.value.doctor_saved,
            type: 'success'
        });
        closeModal();
        fetchDoctors();
    } catch (error) {
        console.error(error);
        const errorMsg = error.response?.data?.message || 'Failed to save doctor';
         toastStore.add({
            title: 'Error',
            message: errorMsg,
            type: 'error'
        });
    }
};

const confirmDelete = (doctor) => {
    doctorToDelete.value = doctor;
    showDeleteModal.value = true;
};

const deleteDoctor = async () => {
    if (!doctorToDelete.value) return;
    try {
        await axios.delete(`/api/doctors/${doctorToDelete.value.id}`);
        toastStore.add({
            title: 'Deleted',
            message: t.value.doctor_deleted,
            type: 'success'
        });
        showDeleteModal.value = false;
        fetchDoctors();
    } catch (error) {
         toastStore.add({
            title: 'Error',
            message: 'Failed to delete doctor',
            type: 'error'
        });
    }
};

onMounted(() => {
    fetchDoctors();
});
</script>
