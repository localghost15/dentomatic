<template>
  <div class="card app-calendar-wrapper shadow-none border-0">
    <div class="row g-0">
      <!-- Calendar Sidebar -->
      <div class="col app-calendar-sidebar border-end" id="app-calendar-sidebar">
        <div class="p-3 my-sm-0 mb-3 border-bottom">
           <button class="btn btn-primary w-100" data-bs-toggle="offcanvas" data-bs-target="#bookingCanvas" @click="openCreateModal">
             <i class="ri-add-line me-1"></i> {{ t.add_event || 'Добавить запись' }}
           </button>
        </div>
        
        <div class="px-3 pt-2">
           <div class="mb-3">
             <h5 class="mb-2">{{ t.doctors || 'Врачи' }}</h5>
             <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="selectAll" v-model="selectAll" @change="toggleAllDoctors" />
                <label class="form-check-label" for="selectAll">{{ t.view_all || 'Все' }}</label>
             </div>
           </div>

           <div class="app-calendar-events-filter">
              <div v-for="doc in doctorsList" :key="doc.value" class="form-check mb-2">
                 <input 
                    class="form-check-input" 
                    type="checkbox" 
                    :id="`select-${doc.value}`" 
                    :value="doc.value" 
                    v-model="selectedDoctors"
                    @change="refetchEvents"
                    :style="{ backgroundColor: selectedDoctors.includes(doc.value) ? doc.color : '', borderColor: doc.color }"
                 />
                 <label class="form-check-label" :for="`select-${doc.value}`">
                    {{ doc.label }}
                 </label>
              </div>
           </div>
        </div>
      </div>

      <!-- Calendar Content -->
      <div class="col app-calendar-content">
        <div class="card shadow-none border-0 h-100">
           <!-- Removed card-body p-0 to allow full height fit or custom styling if needed. 
                Template usually has p-0 on similar wrapper. 
                But user wanted padding. Let's keep padding on the container div inside.
           -->
          <div class="app-calendar-events p-3">
               <FullCalendar :options="calendarOptions" ref="fullCalendar" />
          </div>
        </div>
        
        <!-- Offcanvas Booking Modal -->
        <BookingModal ref="bookingModalRef" :doctors="doctorsList" @booking-saved="handleBookingSaved" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import { useConfigStore } from '@/stores/useConfigStore';
import BookingModal from './BookingModal.vue';
import { Offcanvas } from 'bootstrap';
import axios from 'axios';

const configStore = useConfigStore();
const bookingModalRef = ref(null);
const fullCalendar = ref(null);
let bsOffcanvas = null;

const currentLanguage = computed(() => configStore.language);

// Doctors Configuration
// Doctors Configuration
const doctorsList = ref([]);
const selectedDoctors = ref([]);
const selectAll = ref(true);

const fetchDoctors = async () => {
    try {
        const response = await axios.get('/api/doctors');
        doctorsList.value = response.data.map(d => ({
            value: d.id, // ID from DB
            label: d.full_name,
            color: d.calendar_color, // Hex code
            original: d
        }));
        // Select all by default
        selectedDoctors.value = doctorsList.value.map(d => d.value);
    } catch (error) {
        console.error('Failed to fetch doctors', error);
    }
};

const toggleAllDoctors = () => {
    if (selectAll.value) {
        selectedDoctors.value = doctorsList.value.map(d => d.value);
    } else {
        selectedDoctors.value = [];
    }
    refetchEvents();
};

const refetchEvents = () => {
    if (selectedDoctors.value.length !== doctorsList.value.length) {
        selectAll.value = false;
    } else {
        selectAll.value = true;
    }
    
    if(fullCalendar.value) {
        fullCalendar.value.getApi().refetchEvents();
    }
};

onMounted(() => {
    fetchDoctors();
});

const openCreateModal = () => {
    if (bookingModalRef.value) {
        bookingModalRef.value.resetForm();
    }
};

const handleBookingSaved = () => {
    refetchEvents();
};

const handleDateClick = (arg) => {
    if (bookingModalRef.value) {
        const dateStr = arg.dateStr; 
        let formattedDate = dateStr;
        BookingModal
        if(dateStr.length === 10) formattedDate += 'T09:00'; 
        bookingModalRef.value.resetForm({ datetime: formattedDate });
    }
    
    const el = document.getElementById('bookingCanvas');
    if (el) {
        if (!bsOffcanvas) bsOffcanvas = new Offcanvas(el);
        bsOffcanvas.show();
    }
};

const handleEventClick = (info) => {
    const event = info.event;
    const props = event.extendedProps;
    
    const bookingData = {
        id: event.id,
        fullname: props.fullname || event.title.split(' (')[0],
        phone: props.phone,
        telegram: props.telegram,
        doctor: props.doctor,
        datetime: event.startStr, 
        source: props.source,
        lang_pref: props.lang_pref
    };

    if (bookingModalRef.value) {
        bookingModalRef.value.resetForm(bookingData);
        const el = document.getElementById('bookingCanvas');
        if (el) {
            const bsOffcanvas = new Offcanvas(el);
            bsOffcanvas.show();
        }
    }
};

// Map doctor to color (Hex)
const getEventClassNames = (arg) => {
    // We don't use class names for colors anymore, we use backgroundColor in event data
    return []; 
};

const calendarOptions = reactive({
  plugins: [ dayGridPlugin, timeGridPlugin, listPlugin, interactionPlugin ],
  initialView: window.innerWidth < 768 ? 'listDay' : 'timeGridDay',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
  },
  editable: true,
  selectable: true,
  selectMirror: true,
  dayMaxEvents: true,
  weekends: true,
  slotMinTime: '08:00:00',
  slotMaxTime: '20:00:00',
  firstDay: 1, 
  locale: currentLanguage.value, 
  
  eventDisplay: 'block',
  
  // Use eventDidMount/Content if we wanted to revert to classes, but backgroundColor is easier
  
  buttonText: {
      today:    'Сегодня',
      month:    'Месяц',
      week:     'Неделя',
      day:      'День',
      list:     'Список'
  },
  
  events: function(info, successCallback, failureCallback) {
      axios.get('/api/bookings', {
          params: {
              start: info.startStr,
              end: info.endStr
          }
      })
      .then(response => {
          // Client-side filtering & Color mapping
          const events = response.data.map(event => {
                const docId = event.extendedProps.doctor;
                // Find doctor config
                // Note: doctor stored in DB might be ID (int) but axios returns JSON.
                // Depending on how we store it. Assuming bookings table 'doctor' column is matching ID.
                // Wait, bookings table has 'doctor' which we treated as string 'dr_smith'.
                // If we switch to ID, we need to handle that.
                // For legacy 'dr_smith' we might not find it in new DB list (which are ints).
                // Let's assume user starts fresh or we handle loose matching.
                
                const doctorInList = doctorsList.value.find(d => d.value == docId);
                let color = '#6c757d'; // Default gray
                if (doctorInList) {
                    color = doctorInList.color;
                }

                return {
                    ...event,
                    backgroundColor: color,
                    borderColor: color
                };
          }).filter(event => {
               const docId = event.extendedProps.doctor;
               // If filtering is active
               if (selectedDoctors.value.length === 0) return false;
               if (!docId) return true; // Show unassigned
               return selectedDoctors.value.includes(Number(docId)) || selectedDoctors.value.includes(String(docId));
          });
          successCallback(events);
      })
      .catch(error => {
          console.error('Error fetching events:', error);
          failureCallback(error);
      });
  },
  
  dateClick: handleDateClick,
  eventClick: handleEventClick
});

// Watch language
watch(currentLanguage, (newLang) => {
     if (newLang === 'uz') {
        calendarOptions.locale = 'uz'; 
        calendarOptions.buttonText = {
             today:    'Bugun',
            month:    'Oy',
            week:     'Hafta',
            day:      'Kun',
            list:     'Ro\'yxat'
        }
    } else {
        calendarOptions.locale = 'ru';
        calendarOptions.buttonText = {
            today:    'Сегодня',
            month:    'Месяц',
            week:     'Неделя',
            day:      'День',
            list:     'Список'
        }
    }
}, { immediate: true });

import { translations } from '@/locales';

// Translations for helper text
const t = computed(() => translations[configStore.language] || translations.ru);

</script>

<style scoped>
.app-calendar-content {
    min-height: 80vh;
}
/* Ensure sidebar fits template style */
.app-calendar-sidebar {
    /* Styles usually come from app-calendar.css, but we can enforce some defaults */
    min-width: 260px;
    flex-basis: 260px;
    flex-grow: 0;
}
</style>
