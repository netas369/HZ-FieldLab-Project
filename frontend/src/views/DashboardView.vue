<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <WeatherWidget v-if="weatherView" class="mb-6" />

    <!-- KPI Cards -->
    <div class="grid grid-cols-6 gap-4 mb-6">
      <KPICard
          v-for="kpi in kpiData"
          :key="kpi.label"
          v-bind="kpi"
      />
    </div>

    <div class="grid grid-cols-3 gap-6 mb-6">
      <!-- Turbine Fleet Overview -->
      <div class="col-span-2 bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-lg font-semibold flex items-center gap-2">
            <Wind :size="20" />
            Turbine Fleet Overview
          </h2>
          <div class="flex gap-2">
            <button class="text-sm px-3 py-1 border rounded hover:bg-slate-50 flex items-center gap-1">
              <Filter :size="14" />
              Filter
            </button>
            <button class="text-sm px-3 py-1 border rounded hover:bg-slate-50 flex items-center gap-1">
              <Eye :size="14" />
              View
            </button>
          </div>
        </div>
        <div class="grid grid-cols-4 gap-3">
          <TurbineCard
              v-for="num in 16"
              :key="num"
              :turbineNumber="num"
              @click="goToDetail(num)"
          />
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <ActiveAlerts />
        <RealTimeMonitoring />
      </div>
    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-2 gap-6">
      <ProductionChart />
      <UpcomingMaintenance />
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue';
import { useRouter } from 'vue-router';
import { Wind, CheckCircle, Wrench, XCircle, Zap, AlertTriangle, Filter, Eye } from 'lucide-vue-next';
import WeatherWidget from '@/components/WeatherWidget.vue';
import KPICard from '@/components/KPICard.vue';
import TurbineCard from '@/components/TurbineCard.vue';
import ActiveAlerts from '@/components/ActiveAlerts.vue';
import RealTimeMonitoring from '@/components/RealTimeMonitoring.vue';
import ProductionChart from '@/components/ProductionChart.vue';
import UpcomingMaintenance from '@/components/UpcomingMaintenance.vue';

const router = useRouter();
const weatherView = inject('weatherView');

const kpiData = [
  { icon: Wind, label: 'Total Turbines', value: '24', badge: 'Total', iconColor: 'text-blue-500', badgeColor: 'bg-blue-100 text-blue-700' },
  { icon: CheckCircle, label: 'Operational', value: '21', badge: '87.5%', iconColor: 'text-green-500', badgeColor: 'bg-green-100 text-green-700', valueColor: 'text-green-600' },
  { icon: Wrench, label: 'Maintenance', value: '2', badge: '8.3%', iconColor: 'text-yellow-500', badgeColor: 'bg-yellow-100 text-yellow-700', valueColor: 'text-yellow-600' },
  { icon: XCircle, label: 'Stopped', value: '1', badge: '4.2%', iconColor: 'text-red-500', badgeColor: 'bg-red-100 text-red-700', valueColor: 'text-red-600' },
  { icon: Zap, label: 'Total Output', value: '52.5', unit: 'MW', badge: 'Live', iconColor: 'text-orange-500', badgeColor: 'bg-orange-100 text-orange-700', valueColor: 'text-orange-600' },
  { icon: AlertTriangle, label: 'Critical Alerts', value: '3', badge: 'Urgent', iconColor: 'text-red-500', badgeColor: 'bg-red-100 text-red-700', valueColor: 'text-red-600' }
];

const goToDetail = (num) => {
  router.push(`/turbine/${num}`);
};
</script>
