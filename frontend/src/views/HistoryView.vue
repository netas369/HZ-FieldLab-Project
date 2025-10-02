<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold">Maintenance History</h2>
      <div class="flex gap-3">
        <div class="relative">
          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400" :size="18" />
          <input
              v-model="searchQuery"
              type="text"
              placeholder="Search records..."
              class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
        </div>
        <select v-model="selectedTurbine" class="border rounded-lg px-3 py-2">
          <option value="all">All Turbines</option>
          <option v-for="num in 16" :key="num" :value="`WT-${num}`">WT-{{ num }}</option>
        </select>
        <select v-model="selectedPeriod" class="border rounded-lg px-3 py-2">
          <option value="7">Last 7 Days</option>
          <option value="30">Last 30 Days</option>
          <option value="90">Last 90 Days</option>
          <option value="365">Last Year</option>
          <option value="all">All Time</option>
        </select>
        <select v-model="selectedType" class="border rounded-lg px-3 py-2">
          <option value="all">All Types</option>
          <option value="preventive">Preventive</option>
          <option value="corrective">Corrective</option>
          <option value="emergency">Emergency</option>
        </select>
        <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2">
          <Download :size="16" />
          Export
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
            <Wrench class="text-blue-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Total Records</div>
            <div class="text-2xl font-bold">{{ filteredRecords.length }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <CheckCircle class="text-green-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Preventive</div>
            <div class="text-2xl font-bold text-green-600">{{ preventiveCount }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <AlertCircle class="text-yellow-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Corrective</div>
            <div class="text-2xl font-bold text-yellow-600">{{ correctiveCount }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
            <AlertTriangle class="text-red-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Emergency</div>
            <div class="text-2xl font-bold text-red-600">{{ emergencyCount }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Maintenance Records Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
      <table class="w-full">
        <thead class="bg-slate-200">
        <tr>
          <th class="text-left p-4 font-semibold">Date</th>
          <th class="text-left p-4 font-semibold">Turbine</th>
          <th class="text-left p-4 font-semibold">Component</th>
          <th class="text-left p-4 font-semibold">Type</th>
          <th class="text-left p-4 font-semibold">Action</th>
          <th class="text-left p-4 font-semibold">Technician</th>
          <th class="text-left p-4 font-semibold">Duration</th>
          <th class="text-left p-4 font-semibold">Cost</th>
          <th class="text-left p-4 font-semibold">Status</th>
          <th class="text-left p-4 font-semibold">Details</th>
        </tr>
        </thead>
        <tbody>
        <tr
            v-for="(record, i) in paginatedRecords"
            :key="i"
            class="border-b hover:bg-slate-50"
        >
          <td class="p-4">{{ record.date }}</td>
          <td class="p-4 font-semibold">{{ record.turbine }}</td>
          <td class="p-4">{{ record.component }}</td>
          <td class="p-4">
              <span :class="[
                'px-2 py-1 rounded text-xs font-semibold',
                record.type === 'preventive' ? 'bg-green-100 text-green-700' :
                record.type === 'corrective' ? 'bg-yellow-100 text-yellow-700' :
                'bg-red-100 text-red-700'
              ]">
                {{ record.type.charAt(0).toUpperCase() + record.type.slice(1) }}
              </span>
          </td>
          <td class="p-4">{{ record.action }}</td>
          <td class="p-4">{{ record.tech }}</td>
          <td class="p-4">{{ record.duration }}</td>
          <td class="p-4">€{{ record.cost }}K</td>
          <td class="p-4">
              <span :class="[
                'px-3 py-1 rounded text-sm',
                record.status === 'Active' ? 'bg-blue-100 text-blue-700' : 
                record.status === 'Completed' ? 'bg-green-100 text-green-700' :
                'bg-slate-100 text-slate-700'
              ]">
                {{ record.status }}
              </span>
          </td>
          <td class="p-4">
            <button
                @click="selectedRecord = record; showDetailsModal = true"
                class="text-blue-600 hover:underline text-sm flex items-center gap-1"
            >
              <Eye :size="14" />
              View
            </button>
          </td>
        </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex items-center justify-between">
      <div class="text-sm text-slate-600">
        Showing {{ (currentPage - 1) * recordsPerPage + 1 }} to {{ Math.min(currentPage * recordsPerPage, filteredRecords.length) }} of {{ filteredRecords.length }} records
      </div>
      <div class="flex gap-2">
        <button
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-2 border rounded hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Previous
        </button>
        <button
            v-for="page in totalPages"
            :key="page"
            @click="currentPage = page"
            :class="[
            'px-3 py-2 border rounded',
            currentPage === page ? 'bg-blue-600 text-white' : 'hover:bg-slate-50'
          ]"
        >
          {{ page }}
        </button>
        <button
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 border rounded hover:bg-slate-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Next
        </button>
      </div>
    </div>

    <!-- Details Modal -->
    <div
        v-if="showDetailsModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click="showDetailsModal = false"
    >
      <div
          class="bg-white rounded-lg shadow-xl p-6 max-w-2xl w-full m-4"
          @click.stop
      >
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold">Maintenance Record Details</h3>
          <button @click="showDetailsModal = false" class="text-slate-400 hover:text-slate-600">
            <XCircle :size="24" />
          </button>
        </div>

        <div v-if="selectedRecord" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="text-sm text-slate-600">Date</div>
              <div class="font-semibold">{{ selectedRecord.date }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Turbine</div>
              <div class="font-semibold">{{ selectedRecord.turbine }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Component</div>
              <div class="font-semibold">{{ selectedRecord.component }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Type</div>
              <div class="font-semibold">{{ selectedRecord.type }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Duration</div>
              <div class="font-semibold">{{ selectedRecord.duration }}</div>
            </div>
            <div>
              <div class="text-sm text-slate-600">Cost</div>
              <div class="font-semibold">€{{ selectedRecord.cost }}K</div>
            </div>
          </div>

          <div>
            <div class="text-sm text-slate-600 mb-1">Action Performed</div>
            <div class="font-semibold">{{ selectedRecord.action }}</div>
          </div>

          <div>
            <div class="text-sm text-slate-600 mb-1">Technician</div>
            <div class="font-semibold">{{ selectedRecord.tech }}</div>
          </div>

          <div>
            <div class="text-sm text-slate-600 mb-1">Notes</div>
            <div class="bg-slate-50 p-3 rounded text-sm">
              {{ selectedRecord.notes || 'No additional notes provided.' }}
            </div>
          </div>

          <div v-if="selectedRecord.parts" class="border-t pt-4">
            <div class="text-sm text-slate-600 mb-2">Parts Used</div>
            <div class="space-y-1">
              <div v-for="(part, i) in selectedRecord.parts" :key="i" class="flex justify-between text-sm">
                <span>{{ part.name }}</span>
                <span class="font-semibold">€{{ part.cost }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Calendar, Download, Wrench, CheckCircle, AlertCircle, AlertTriangle, Search, Eye, XCircle } from 'lucide-vue-next';

const searchQuery = ref('');
const selectedTurbine = ref('all');
const selectedPeriod = ref('30');
const selectedType = ref('all');
const currentPage = ref(1);
const recordsPerPage = 10;
const showDetailsModal = ref(false);
const selectedRecord = ref(null);

const maintenanceRecords = [
  { date: 'Oct 1, 2025', turbine: 'WT-3', component: 'Gearbox', type: 'emergency', action: 'Emergency Repair', tech: 'Emergency Team', duration: 'In Progress', cost: 45, status: 'Active', notes: 'Critical overheating issue. Cooling system failure detected. Emergency shutdown initiated.', parts: [{name: 'Cooling pump', cost: 8000}, {name: 'Temperature sensors', cost: 2500}] },
  { date: 'Sep 28, 2025', turbine: 'WT-12', component: 'Control System', type: 'preventive', action: 'Software Update', tech: 'R. Smit', duration: '2h 15m', cost: 2, status: 'Completed', notes: 'Routine firmware update to version 4.2.1' },
  { date: 'Sep 25, 2025', turbine: 'WT-7', component: 'Generator', type: 'corrective', action: 'Bearing Replacement', tech: 'M. Jansen', duration: '8h 30m', cost: 38, status: 'Completed', notes: 'Generator bearing showed signs of excessive wear. Preventive replacement performed.', parts: [{name: 'Main bearing', cost: 28000}, {name: 'Seals', cost: 3500}] },
  { date: 'Sep 19, 2025', turbine: 'WT-8', component: 'Blades', type: 'preventive', action: 'Routine Inspection', tech: 'J. van der Berg', duration: '3h 20m', cost: 3, status: 'Completed', notes: 'Visual inspection completed. No issues found.' },
  { date: 'Sep 17, 2025', turbine: 'WT-2', component: 'Generator', type: 'corrective', action: 'Bearing Replacement', tech: 'M. Jansen', duration: '6h 45m', cost: 32, status: 'Completed', notes: 'Scheduled bearing replacement due to vibration analysis results.' },
  { date: 'Sep 15, 2025', turbine: 'WT-5', component: 'Yaw System', type: 'corrective', action: 'Calibration', tech: 'P. de Vries', duration: '2h 15m', cost: 5, status: 'Completed', notes: 'Yaw misalignment corrected. System recalibrated.' },
  { date: 'Sep 12, 2025', turbine: 'WT-11', component: 'Blades', type: 'preventive', action: 'Cleaning', tech: 'L. Bakker', duration: '4h 30m', cost: 4, status: 'Completed', notes: 'Regular blade cleaning to maintain optimal performance.' },
  { date: 'Sep 10, 2025', turbine: 'WT-7', component: 'Control System', type: 'preventive', action: 'Software Update', tech: 'R. Smit', duration: '1h 45m', cost: 2, status: 'Completed', notes: 'Security patch applied.' },
  { date: 'Sep 6, 2025', turbine: 'WT-1', component: 'Gearbox', type: 'preventive', action: 'Oil Change', tech: 'J. van der Berg', duration: '3h 0m', cost: 8, status: 'Completed', notes: 'Routine oil change. Oil analysis shows normal wear patterns.' },
  { date: 'Sep 3, 2025', turbine: 'WT-14', component: 'Main Bearing', type: 'preventive', action: 'Lubrication', tech: 'P. de Vries', duration: '2h 30m', cost: 3, status: 'Completed', notes: 'Scheduled lubrication service.' },
  { date: 'Aug 29, 2025', turbine: 'WT-4', component: 'Blades', type: 'preventive', action: 'Inspection', tech: 'J. van der Berg', duration: '3h 15m', cost: 3, status: 'Completed', notes: 'No defects found during inspection.' },
  { date: 'Aug 25, 2025', turbine: 'WT-9', component: 'Yaw System', type: 'corrective', action: 'Motor Replacement', tech: 'M. Jansen', duration: '5h 40m', cost: 18, status: 'Completed', notes: 'Yaw motor replaced due to overheating issues.', parts: [{name: 'Yaw motor', cost: 15000}] },
  { date: 'Aug 20, 2025', turbine: 'WT-6', component: 'Generator', type: 'preventive', action: 'Inspection', tech: 'L. Bakker', duration: '2h 50m', cost: 3, status: 'Completed', notes: 'Routine generator inspection.' },
  { date: 'Aug 15, 2025', turbine: 'WT-13', component: 'Control System', type: 'corrective', action: 'Sensor Replacement', tech: 'R. Smit', duration: '1h 30m', cost: 4, status: 'Completed', notes: 'Wind speed sensor replaced after calibration issues.' },
  { date: 'Aug 10, 2025', turbine: 'WT-10', component: 'Gearbox', type: 'preventive', action: 'Oil Analysis', tech: 'P. de Vries', duration: '1h 15m', cost: 2, status: 'Completed', notes: 'Oil sample taken for laboratory analysis.' }
];

const filteredRecords = computed(() => {
  return maintenanceRecords.filter(record => {
    const matchesTurbine = selectedTurbine.value === 'all' || record.turbine === selectedTurbine.value;
    const matchesType = selectedType.value === 'all' || record.type === selectedType.value;
    const matchesSearch = searchQuery.value === '' ||
        record.turbine.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        record.component.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        record.action.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        record.tech.toLowerCase().includes(searchQuery.value.toLowerCase());

    return matchesTurbine && matchesType && matchesSearch;
  });
});

const paginatedRecords = computed(() => {
  const start = (currentPage.value - 1) * recordsPerPage;
  const end = start + recordsPerPage;
  return filteredRecords.value.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(filteredRecords.value.length / recordsPerPage);
});

const preventiveCount = computed(() =>
    filteredRecords.value.filter(r => r.type === 'preventive').length
);

const correctiveCount = computed(() =>
    filteredRecords.value.filter(r => r.type === 'corrective').length
);

const emergencyCount = computed(() =>
    filteredRecords.value.filter(r => r.type === 'emergency').length
);
</script>