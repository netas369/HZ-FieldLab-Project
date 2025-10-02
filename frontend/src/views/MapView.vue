<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-bold flex items-center gap-2">
          <MapPin :size="24" />
          Wind Farm Geographic View
        </h2>
        <div class="flex gap-2">
          <button class="px-3 py-2 border rounded hover:bg-slate-50 flex items-center gap-2">
            <Filter :size="16" />
            Filter Status
          </button>
          <button class="px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-2">
            <Download :size="16" />
            Export Map
          </button>
        </div>
      </div>

      <div class="h-[600px] bg-gradient-to-br from-blue-100 to-blue-50 rounded-lg relative overflow-hidden border-2 border-slate-300">
        <!-- Grid lines for visual reference -->
        <div class="absolute inset-0 opacity-20">
          <div class="absolute top-0 left-1/4 w-px h-full bg-slate-400"></div>
          <div class="absolute top-0 left-2/4 w-px h-full bg-slate-400"></div>
          <div class="absolute top-0 left-3/4 w-px h-full bg-slate-400"></div>
          <div class="absolute top-1/4 left-0 w-full h-px bg-slate-400"></div>
          <div class="absolute top-2/4 left-0 w-full h-px bg-slate-400"></div>
          <div class="absolute top-3/4 left-0 w-full h-px bg-slate-400"></div>
        </div>

        <!-- Turbine markers -->
        <div class="relative w-full h-full">
          <div
              v-for="turbine in turbinePositions"
              :key="turbine.id"
              class="absolute transform -translate-x-1/2 -translate-y-1/2 cursor-pointer group"
              :style="{ left: turbine.x + '%', top: turbine.y + '%' }"
              @click="goToDetail(turbine.id)"
          >
            <!-- Turbine marker -->
            <div :class="[
              'w-10 h-10 rounded-full border-4 flex items-center justify-center shadow-lg transition-transform hover:scale-110',
              getStatusClass(turbine.status)
            ]">
              <Wind :size="20" class="text-white" />
            </div>

            <!-- Turbine label (always visible) -->
            <div class="absolute top-full left-1/2 transform -translate-x-1/2 mt-1 bg-white px-2 py-1 rounded shadow-lg text-xs font-semibold whitespace-nowrap border border-slate-200">
              WT-{{ turbine.id }}
            </div>

            <!-- Hover tooltip -->
            <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 bg-slate-800 text-white px-3 py-2 rounded shadow-xl text-xs whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
              <div class="font-semibold mb-1">WT-{{ turbine.id }}</div>
              <div>Power: {{ turbine.power }} MW</div>
              <div>Status: {{ turbine.status }}</div>
            </div>

            <!-- Alert icon for critical turbines -->
            <div v-if="turbine.status === 'critical'" class="absolute -top-2 -right-2">
              <AlertTriangle class="text-red-600 animate-pulse" :size="20" />
            </div>

            <!-- Warning icon -->
            <div v-if="turbine.status === 'warning'" class="absolute -top-2 -right-2">
              <AlertCircle class="text-yellow-600" :size="18" />
            </div>
          </div>

          <!-- Wind direction indicator -->
          <div class="absolute top-4 right-4 bg-white p-4 rounded-lg shadow-lg border border-slate-200">
            <div class="text-xs text-slate-600 mb-2 font-semibold">Wind Direction</div>
            <div class="flex items-center gap-3">
              <div class="w-16 h-16 border-2 border-blue-500 rounded-full flex items-center justify-center relative">
                <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                  <div class="w-0 h-0 border-l-[6px] border-r-[6px] border-b-[10px] border-l-transparent border-r-transparent border-b-blue-500"></div>
                </div>
                <span class="text-sm font-bold text-blue-600">NW</span>
              </div>
              <div>
                <div class="text-lg font-semibold">12.4 m/s</div>
                <div class="text-xs text-slate-600">310°</div>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="absolute bottom-4 left-4 bg-white p-4 rounded-lg shadow-lg border border-slate-200">
            <div class="text-sm font-semibold mb-3">Status Legend</div>
            <div class="space-y-2">
              <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-green-500 rounded-full border-2 border-green-700"></div>
                <span class="text-xs">Operational ({{ operationalCount }})</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-yellow-500 rounded-full border-2 border-yellow-700"></div>
                <span class="text-xs">Warning ({{ warningCount }})</span>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-5 h-5 bg-red-500 rounded-full border-2 border-red-700 animate-pulse"></div>
                <span class="text-xs">Critical ({{ criticalCount }})</span>
              </div>
            </div>
          </div>

          <!-- Coordinates reference -->
          <div class="absolute bottom-4 right-4 bg-white px-3 py-2 rounded shadow text-xs text-slate-600 border border-slate-200">
            Vlissingen Wind Farm - North Sea
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Grid -->
    <div class="grid grid-cols-4 gap-4">
      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
            <CheckCircle class="text-green-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Operational</div>
            <div class="text-2xl font-bold text-green-600">{{ operationalCount }}</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
            <AlertCircle class="text-yellow-600" :size="24" />
          </div>
          <div>
            <div class="text-sm text-slate-600">Warning</div>
          <div class="text-2xl font-bold text-yellow-600">{{ warningCount }}</div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
          <XCircle class="text-red-600" :size="24" />
        </div>
        <div>
          <div class="text-sm text-slate-600">Critical</div>
          <div class="text-2xl font-bold text-red-600">{{ criticalCount }}</div>
        </div>
      </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
          <Zap class="text-blue-600" :size="24" />
        </div>
        <div>
          <div class="text-sm text-slate-600">Total Output</div>
          <div class="text-2xl font-bold text-blue-600">{{ totalPower }} MW</div>
        </div>
      </div>
    </div>
  </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { MapPin, Filter, Download, Wind, AlertTriangle, AlertCircle, CheckCircle, XCircle, Zap } from 'lucide-vue-next';

const router = useRouter();

const turbinePositions = [
  { id: 1, x: 15, y: 20, status: 'good', power: 2.5 },
  { id: 2, x: 25, y: 25, status: 'good', power: 2.5 },
  { id: 3, x: 35, y: 15, status: 'critical', power: 0.0 },
  { id: 4, x: 45, y: 30, status: 'good', power: 2.5 },
  { id: 5, x: 55, y: 20, status: 'warning', power: 2.1 },
  { id: 6, x: 65, y: 35, status: 'good', power: 2.5 },
  { id: 7, x: 75, y: 25, status: 'critical', power: 0.0 },
  { id: 8, x: 85, y: 40, status: 'good', power: 2.5 },
  { id: 9, x: 20, y: 55, status: 'good', power: 2.5 },
  { id: 10, x: 30, y: 60, status: 'good', power: 2.5 },
  { id: 11, x: 40, y: 65, status: 'warning', power: 2.1 },
  { id: 12, x: 50, y: 55, status: 'good', power: 2.5 },
  { id: 13, x: 60, y: 70, status: 'good', power: 2.5 },
  { id: 14, x: 70, y: 60, status: 'good', power: 2.5 },
  { id: 15, x: 80, y: 75, status: 'good', power: 2.5 },
  { id: 16, x: 90, y: 65, status: 'good', power: 2.5 }
];

const getStatusClass = (status) => {
  if (status === 'critical') return 'bg-red-500 border-red-700';
  if (status === 'warning') return 'bg-yellow-500 border-yellow-700';
  return 'bg-green-500 border-green-700';
};

const operationalCount = computed(() =>
    turbinePositions.filter(t => t.status === 'good').length
);

const warningCount = computed(() =>
    turbinePositions.filter(t => t.status === 'warning').length
);

const criticalCount = computed(() =>
    turbinePositions.filter(t => t.status === 'critical').length
);

const totalPower = computed(() =>
    turbinePositions.reduce((sum, t) => sum + t.power, 0).toFixed(1)
);

const goToDetail = (id) => {
  router.push(`/turbine/${id}`);
};
</script>