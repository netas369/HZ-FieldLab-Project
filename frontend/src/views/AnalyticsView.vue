<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <h2 class="text-2xl font-bold mb-6">Analytics Dashboard</h2>

    <!-- Top Metrics -->
    <div class="grid grid-cols-3 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <BarChart3 :size="20" />
          Fleet Performance
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Average Uptime</span>
            <span class="text-lg font-bold text-green-600">96.8%</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Total Energy (Today)</span>
            <span class="text-lg font-bold">1,248 MWh</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Capacity Factor</span>
            <span class="text-lg font-bold">42.3%</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">MTBF</span>
            <span class="text-lg font-bold">2,845 hrs</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <TrendingUp :size="20" />
          Efficiency Metrics
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Power Curve Efficiency</span>
            <span class="text-lg font-bold text-green-600">94.2%</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Blade Pitch Accuracy</span>
            <span class="text-lg font-bold">±0.8°</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Yaw Alignment Avg</span>
            <span class="text-lg font-bold">±3.5°</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Loss Factor</span>
            <span class="text-lg font-bold text-yellow-600">5.8%</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <Wrench :size="20" />
          Maintenance Stats
        </h3>
        <div class="space-y-3">
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Total This Month</span>
            <span class="text-lg font-bold">28</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Preventive</span>
            <span class="text-lg font-bold text-green-600">21</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Corrective</span>
            <span class="text-lg font-bold text-yellow-600">5</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm text-slate-600">Emergency</span>
            <span class="text-lg font-bold text-red-600">2</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-2 gap-6 mb-6">
      <!-- Monthly Production -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4">Monthly Energy Production (MWh)</h3>
        <div class="h-64 border rounded bg-slate-50 flex items-end justify-between p-4 gap-2">
          <div
              v-for="(month, i) in monthlyProduction"
              :key="i"
              class="flex-1 flex flex-col items-center gap-1 group"
          >
            <div class="text-xs text-slate-600 opacity-0 group-hover:opacity-100 transition">
              {{ month.value }}
            </div>
            <div
                class="w-full bg-gradient-to-t from-green-500 to-green-300 rounded-t hover:from-green-600 hover:to-green-400 transition cursor-pointer"
                :style="{ height: (month.value / 45000 * 100) + '%' }"
            />
            <span class="text-xs text-slate-600 mt-1">{{ month.label }}</span>
          </div>
        </div>
      </div>

      <!-- Component Failure Rate -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4">Component Failure Rate (%)</h3>
        <div class="space-y-3">
          <div
              v-for="component in componentFailures"
              :key="component.name"
              class="space-y-1"
          >
            <div class="flex justify-between text-sm">
              <span>{{ component.name }}</span>
              <span class="font-semibold">{{ component.rate }}%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div
                  :class="[
                  'h-2 rounded-full transition-all',
                  component.rate > 5 ? 'bg-red-500' : 
                  component.rate > 2 ? 'bg-yellow-500' : 
                  'bg-green-500'
                ]"
                  :style="{ width: (component.rate * 10) + '%' }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Analytics Grid -->
    <div class="grid grid-cols-3 gap-6 mb-6">
      <!-- Downtime Analysis -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <Clock :size="18" />
          Downtime Analysis
        </h3>
        <div class="space-y-3">
          <div class="border-b pb-2">
            <div class="flex justify-between text-sm mb-1">
              <span class="text-slate-600">Scheduled Maintenance</span>
              <span class="font-semibold">45%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="h-2 rounded-full bg-blue-500" style="width: 45%"></div>
            </div>
          </div>
          <div class="border-b pb-2">
            <div class="flex justify-between text-sm mb-1">
              <span class="text-slate-600">Unscheduled Repairs</span>
              <span class="font-semibold">30%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="h-2 rounded-full bg-yellow-500" style="width: 30%"></div>
            </div>
          </div>
          <div class="border-b pb-2">
            <div class="flex justify-between text-sm mb-1">
              <span class="text-slate-600">Weather Related</span>
              <span class="font-semibold">20%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="h-2 rounded-full bg-purple-500" style="width: 20%"></div>
            </div>
          </div>
          <div>
            <div class="flex justify-between text-sm mb-1">
              <span class="text-slate-600">Grid Issues</span>
              <span class="font-semibold">5%</span>
            </div>
            <div class="w-full bg-slate-200 rounded-full h-2">
              <div class="h-2 rounded-full bg-red-500" style="width: 5%"></div>
            </div>
          </div>
      </div>
    </div>

    <!-- Performance Trends -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="font-semibold mb-4 flex items-center gap-2">
        <Activity :size="18" />
        Performance Trends
      </h3>
      <div class="space-y-4">
        <div>
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-slate-600">vs Last Month</span>
            <span class="text-sm font-semibold text-green-600 flex items-center gap-1">
                <TrendingUp :size="14" />
                +3.2%
              </span>
          </div>
          <div class="text-xs text-slate-500">Energy production increased</div>
        </div>
        <div>
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-slate-600">vs Last Year</span>
            <span class="text-sm font-semibold text-green-600 flex items-center gap-1">
                <TrendingUp :size="14" />
                +8.7%
              </span>
          </div>
          <div class="text-xs text-slate-500">Year-over-year improvement</div>
        </div>
        <div>
          <div class="flex justify-between items-center mb-2">
            <span class="text-sm text-slate-600">Downtime Reduction</span>
            <span class="text-sm font-semibold text-green-600 flex items-center gap-1">
                <TrendingDown :size="14" />
                -12%
              </span>
          </div>
          <div class="text-xs text-slate-500">Better maintenance planning</div>
        </div>
      </div>
    </div>

    <!-- Cost Analysis -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="font-semibold mb-4 flex items-center gap-2">
        <DollarSign :size="18" />
        Cost Analysis (This Month)
      </h3>
      <div class="space-y-3">
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Preventive Maintenance</span>
          <span class="font-semibold">€45K</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Emergency Repairs</span>
          <span class="font-semibold text-red-600">€82K</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-slate-600">Parts & Materials</span>
          <span class="font-semibold">€38K</span>
        </div>
        <div class="flex justify-between items-center pt-3 border-t font-bold">
          <span>Total Costs</span>
          <span class="text-lg">€165K</span>
        </div>
        <div class="bg-blue-50 p-3 rounded mt-3">
          <div class="text-xs text-slate-600 mb-1">Cost per MWh</div>
          <div class="text-2xl font-bold text-blue-600">€4.32</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Turbine Performance Comparison -->
  <div class="bg-white rounded-lg shadow p-6">
    <h3 class="font-semibold mb-4">Turbine Performance Comparison</h3>
    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-100">
        <tr>
          <th class="text-left p-3 font-semibold">Turbine</th>
          <th class="text-left p-3 font-semibold">Availability</th>
          <th class="text-left p-3 font-semibold">Energy Output (MWh)</th>
          <th class="text-left p-3 font-semibold">Efficiency</th>
          <th class="text-left p-3 font-semibold">Downtime (hrs)</th>
          <th class="text-left p-3 font-semibold">Maintenance Cost</th>
          <th class="text-left p-3 font-semibold">Status</th>
        </tr>
        </thead>
        <tbody>
        <tr v-for="turbine in turbinePerformance" :key="turbine.id" class="border-b hover:bg-slate-50">
          <td class="p-3 font-semibold">{{ turbine.name }}</td>
          <td class="p-3">
                <span :class="[
                  'font-semibold',
                  turbine.availability > 95 ? 'text-green-600' : 
                  turbine.availability > 90 ? 'text-yellow-600' : 
                  'text-red-600'
                ]">
                  {{ turbine.availability }}%
                </span>
          </td>
          <td class="p-3">{{ turbine.energy }}</td>
          <td class="p-3">{{ turbine.efficiency }}%</td>
          <td class="p-3">{{ turbine.downtime }}</td>
          <td class="p-3">€{{ turbine.cost }}K</td>
          <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded text-xs font-semibold',
                  turbine.status === 'Excellent' ? 'bg-green-100 text-green-700' :
                  turbine.status === 'Good' ? 'bg-blue-100 text-blue-700' :
                  turbine.status === 'Fair' ? 'bg-yellow-100 text-yellow-700' :
                  'bg-red-100 text-red-700'
                ]">
                  {{ turbine.status }}
                </span>
          </td>
        </tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>
</template>

<script setup>
import { BarChart3, TrendingUp, TrendingDown, Wrench, Clock, Activity, DollarSign } from 'lucide-vue-next';

const monthlyProduction = [
  { label: 'Jan', value: 32500 },
  { label: 'Feb', value: 28900 },
  { label: 'Mar', value: 35200 },
  { label: 'Apr', value: 38100 },
  { label: 'May', value: 42300 },
  { label: 'Jun', value: 39800 },
  { label: 'Jul', value: 41200 },
  { label: 'Aug', value: 38900 },
  { label: 'Sep', value: 36700 },
  { label: 'Oct', value: 33200 }
];

const componentFailures = [
  { name: 'Gearbox', rate: 6.2 },
  { name: 'Generator', rate: 4.8 },
  { name: 'Blades', rate: 3.5 },
  { name: 'Yaw System', rate: 2.1 },
  { name: 'Main Bearing', rate: 1.8 },
  { name: 'Control System', rate: 1.2 }
];

const turbinePerformance = [
  { id: 1, name: 'WT-1', availability: 98.2, energy: 2450, efficiency: 94, downtime: 13, cost: 8, status: 'Excellent' },
  { id: 2, name: 'WT-2', availability: 97.8, energy: 2420, efficiency: 93, downtime: 16, cost: 9, status: 'Excellent' },
  { id: 3, name: 'WT-3', availability: 89.3, energy: 1950, efficiency: 82, downtime: 78, cost: 52, status: 'Poor' },
  { id: 4, name: 'WT-4', availability: 98.5, energy: 2475, efficiency: 95, downtime: 11, cost: 7, status: 'Excellent' },
  { id: 5, name: 'WT-5', availability: 95.8, energy: 2310, efficiency: 91, downtime: 31, cost: 18, status: 'Good' },
  { id: 6, name: 'WT-6', availability: 97.2, energy: 2405, efficiency: 93, downtime: 20, cost: 10, status: 'Excellent' },
  { id: 7, name: 'WT-7', availability: 91.5, energy: 2080, efficiency: 86, downtime: 62, cost: 45, status: 'Fair' },
  { id: 8, name: 'WT-8', availability: 98.1, energy: 2445, efficiency: 94, downtime: 14, cost: 8, status: 'Excellent' }
];
</script>