<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <h2 class="text-2xl font-bold mb-6">Predictive Maintenance Overview</h2>

    <!-- Priority Categories -->
    <div class="grid grid-cols-3 gap-6 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 text-red-600 flex items-center gap-2">
          <AlertTriangle :size="20" />
          Immediate Action Required
        </h3>
        <div class="space-y-2">
          <div
              v-for="item in immediateActions"
              :key="item.turbine"
              class="border-l-4 border-red-600 pl-3 py-2 bg-red-50 rounded cursor-pointer hover:bg-red-100 transition"
              @click="selectComponent(item)"
          >
            <div class="font-semibold">{{ item.turbine }} {{ item.component }}</div>
            <div class="text-sm text-slate-600">RUL: {{ item.rul }}</div>
            <div class="text-xs text-red-600 font-semibold mt-1">Failure Risk: {{ item.risk }}%</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 text-yellow-600 flex items-center gap-2">
          <Clock :size="20" />
          Schedule Within 30 Days
        </h3>
        <div class="space-y-2">
          <div
              v-for="item in scheduled30Days"
              :key="item.turbine"
              class="border-l-4 border-yellow-600 pl-3 py-2 bg-yellow-50 rounded cursor-pointer hover:bg-yellow-100 transition"
              @click="selectComponent(item)"
          >
            <div class="font-semibold">{{ item.turbine }} {{ item.component }}</div>
            <div class="text-sm text-slate-600">RUL: {{ item.rul }}</div>
            <div class="text-xs text-yellow-600 font-semibold mt-1">Failure Risk: {{ item.risk }}%</div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="font-semibold mb-4 text-green-600 flex items-center gap-2">
          <Eye :size="20" />
          Monitor (30-90 Days)
        </h3>
        <div class="space-y-2">
          <div
              v-for="item in monitorItems"
              :key="item.turbine"
              class="border-l-4 border-green-600 pl-3 py-2 bg-green-50 rounded cursor-pointer hover:bg-green-100 transition"
              @click="selectComponent(item)"
          >
            <div class="font-semibold">{{ item.turbine }} {{ item.component }}</div>
            <div class="text-sm text-slate-600">RUL: {{ item.rul }}</div>
            <div class="text-xs text-green-600 font-semibold mt-1">Failure Risk: {{ item.risk }}%</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Detailed Degradation View -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="font-semibold mb-4 flex items-center justify-between">
        <span>Degradation Trajectory - {{ selectedItem.turbine }} {{ selectedItem.component }}</span>
        <div class="flex gap-2">
          <button
              v-for="comp in allComponents"
              :key="comp.turbine + comp.component"
              @click="selectComponent(comp)"
              :class="[
              'text-xs px-3 py-1 rounded border',
              selectedItem.turbine === comp.turbine && selectedItem.component === comp.component 
                ? 'bg-blue-600 text-white border-blue-600' 
                : 'bg-white text-slate-700 border-slate-300 hover:border-blue-400'
            ]"
          >
            {{ comp.turbine }} {{ comp.component }}
          </button>
        </div>
      </h3>

      <!-- Degradation Chart -->
      <div class="border rounded p-4 bg-slate-50 mb-4">
        <div class="h-64 flex items-end justify-between gap-1">
          <div
              v-for="(val, i) in selectedItem.degradationData"
              :key="i"
              class="flex-1 flex flex-col items-center justify-end"
          >
            <div class="text-xs text-slate-600 mb-1">{{ val }}%</div>
            <div
                :class="[
                'w-full rounded-t transition-all',
                val > 80 ? 'bg-green-500' : val > 60 ? 'bg-yellow-500' : 'bg-red-500'
              ]"
                :style="{ height: val + '%' }"
            />
          </div>
        </div>
        <div class="flex justify-between text-xs text-slate-600 mt-3 px-2">
          <span>{{ selectedItem.timeRange.start }}</span>
          <span class="font-semibold text-blue-600">Today</span>
          <span class="text-red-600 font-semibold">Predicted failure ({{ selectedItem.daysToFailure }} days)</span>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-4 gap-4 mb-4">
        <div class="border rounded p-4 bg-white">
          <div class="text-slate-600 mb-1 text-sm">Current Health</div>
          <div :class="[
            'text-3xl font-bold',
            selectedItem.currentHealth > 80 ? 'text-green-600' : 
            selectedItem.currentHealth > 60 ? 'text-yellow-600' : 
            'text-red-600'
          ]">
            {{ selectedItem.currentHealth }}%
          </div>
        </div>
        <div class="border rounded p-4 bg-white">
          <div class="text-slate-600 mb-1 text-sm">Failure Probability (7 days)</div>
          <div :class="[
            'text-3xl font-bold',
            selectedItem.failureProbability > 50 ? 'text-red-600' : 
            selectedItem.failureProbability > 30 ? 'text-yellow-600' : 
            'text-green-600'
          ]">
            {{ selectedItem.failureProbability }}%
          </div>
        </div>
        <div class="border rounded p-4 bg-white">
          <div class="text-slate-600 mb-1 text-sm">Degradation Rate</div>
          <div class="text-3xl font-bold text-orange-600">
            {{ selectedItem.degradationRate }}%/day
          </div>
        </div>
        <div class="border rounded p-4 bg-white">
          <div class="text-slate-600 mb-1 text-sm">Estimated Cost</div>
          <div class="text-3xl font-bold text-purple-600">
            €{{ selectedItem.estimatedCost }}K
          </div>
        </div>
      </div>

      <!-- Contributing Factors -->
      <div class="grid grid-cols-2 gap-6">
        <div class="border rounded p-4 bg-white">
          <h4 class="font-semibold mb-3 flex items-center gap-2">
            <Activity :size="18" />
            Contributing Factors
          </h4>
          <div class="space-y-2">
            <div v-for="factor in selectedItem.factors" :key="factor.name" class="flex items-center justify-between">
              <span class="text-sm text-slate-600">{{ factor.name }}</span>
              <div class="flex items-center gap-2">
                <div class="w-32 bg-slate-200 rounded-full h-2">
                  <div
                      :class="[
                      'h-2 rounded-full',
                      factor.impact > 70 ? 'bg-red-500' : 
                      factor.impact > 40 ? 'bg-yellow-500' : 
                      'bg-blue-500'
                    ]"
                      :style="{ width: factor.impact + '%' }"
                  />
                </div>
                <span class="text-sm font-semibold w-10 text-right">{{ factor.impact }}%</span>
              </div>
            </div>
          </div>
        </div>

        <div class="border rounded p-4 bg-white">
          <h4 class="font-semibold mb-3 flex items-center gap-2">
            <Wrench :size="18" />
            Recommended Actions
          </h4>
          <div class="space-y-2">
            <div
                v-for="(action, i) in selectedItem.actions"
                :key="i"
                class="flex items-start gap-2 text-sm"
            >
              <div class="w-5 h-5 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold flex-shrink-0 mt-0.5">
                {{ i + 1 }}
              </div>
              <span class="text-slate-700">{{ action }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Comparison Table -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="font-semibold mb-4">Component Health Comparison</h3>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-slate-100">
          <tr>
            <th class="text-left p-3 font-semibold">Turbine</th>
            <th class="text-left p-3 font-semibold">Component</th>
            <th class="text-left p-3 font-semibold">Current Health</th>
            <th class="text-left p-3 font-semibold">RUL</th>
            <th class="text-left p-3 font-semibold">Failure Risk</th>
            <th class="text-left p-3 font-semibold">Priority</th>
            <th class="text-left p-3 font-semibold">Action</th>
          </tr>
          </thead>
          <tbody>
          <tr
              v-for="item in allComponentsSorted"
              :key="item.turbine + item.component"
              class="border-b hover:bg-slate-50 cursor-pointer"
              @click="selectComponent(item)"
          >
            <td class="p-3 font-semibold">{{ item.turbine }}</td>
            <td class="p-3">{{ item.component }}</td>
            <td class="p-3">
              <div class="flex items-center gap-2">
                <div class="w-20 bg-slate-200 rounded-full h-2">
                  <div
                      :class="[
                        'h-2 rounded-full',
                        item.currentHealth > 80 ? 'bg-green-500' : 
                        item.currentHealth > 60 ? 'bg-yellow-500' : 
                        'bg-red-500'
                      ]"
                      :style="{ width: item.currentHealth + '%' }"
                  />
                </div>
                <span class="text-sm font-semibold">{{ item.currentHealth }}%</span>
              </div>
            </td>
            <td class="p-3">{{ item.rul }}</td>
            <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded text-xs font-semibold',
                  item.risk > 70 ? 'bg-red-100 text-red-700' : 
                  item.risk > 40 ? 'bg-yellow-100 text-yellow-700' : 
                  'bg-green-100 text-green-700'
                ]">
                  {{ item.risk }}%
                </span>
            </td>
            <td class="p-3">
                <span :class="[
                  'px-2 py-1 rounded text-xs font-semibold',
                  item.priority === 'critical' ? 'bg-red-100 text-red-700' : 
                  item.priority === 'high' ? 'bg-yellow-100 text-yellow-700' : 
                  'bg-green-100 text-green-700'
                ]">
                  {{ item.priority.toUpperCase() }}
                </span>
            </td>
            <td class="p-3">
              <button class="text-blue-600 hover:underline text-sm">View Details</button>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { AlertTriangle, Clock, Eye, Activity, Wrench, TrendingUp } from 'lucide-vue-next';

const immediateActions = [
  {
    turbine: 'WT-3',
    component: 'Gearbox',
    rul: '2-5 days',
    risk: 85,
    currentHealth: 45,
    failureProbability: 78,
    degradationRate: 2.3,
    estimatedCost: 45,
    priority: 'critical',
    daysToFailure: 5,
    timeRange: { start: '60 days ago', end: '5 days' },
    degradationData: [95, 92, 88, 85, 80, 75, 70, 65, 58, 52, 45, 38, 30],
    factors: [
      { name: 'Operating Temperature', impact: 85 },
      { name: 'Vibration Levels', impact: 72 },
      { name: 'Load Cycles', impact: 65 },
      { name: 'Oil Quality', impact: 58 }
    ],
    actions: [
      'Schedule immediate inspection within 48 hours',
      'Reduce operating load by 30% until maintenance',
      'Order replacement parts (lead time: 3-5 days)',
      'Arrange emergency maintenance crew availability'
    ]
  },
  {
    turbine: 'WT-7',
    component: 'Generator',
    rul: '3-7 days',
    risk: 72,
    currentHealth: 52,
    failureProbability: 68,
    degradationRate: 1.9,
    estimatedCost: 38,
    priority: 'critical',
    daysToFailure: 7,
    timeRange: { start: '60 days ago', end: '7 days' },
    degradationData: [98, 96, 93, 90, 87, 82, 78, 73, 68, 62, 56, 52, 45],
    factors: [
      { name: 'Winding Temperature', impact: 88 },
      { name: 'Bearing Wear', impact: 70 },
      { name: 'Insulation Degradation', impact: 62 },
      { name: 'Cooling Efficiency', impact: 55 }
    ],
    actions: [
      'Monitor temperature continuously',
      'Reduce power output to 70% capacity',
      'Schedule generator replacement',
      'Prepare backup power management strategy'
    ]
  }
];

const scheduled30Days = [
  {
    turbine: 'WT-5',
    component: 'Yaw Motor',
    rul: '18-25 days',
    risk: 45,
    currentHealth: 75,
    failureProbability: 35,
    degradationRate: 0.8,
    estimatedCost: 15,
    priority: 'high',
    daysToFailure: 25,
    timeRange: { start: '90 days ago', end: '25 days' },
    degradationData: [100, 98, 96, 94, 91, 88, 85, 82, 79, 76, 75, 72, 68],
    factors: [
      { name: 'Motor Temperature', impact: 52 },
      { name: 'Brake Wear', impact: 48 },
      { name: 'Hydraulic Pressure', impact: 42 },
      { name: 'Position Accuracy', impact: 38 }
    ],
    actions: [
      'Schedule maintenance window within 2 weeks',
      'Order replacement motor components',
      'Plan for 4-6 hour downtime',
      'Calibrate yaw control system post-repair'
    ]
  },
  {
    turbine: 'WT-11',
    component: 'Blade Bearing',
    rul: '20-30 days',
    risk: 42,
    currentHealth: 78,
    failureProbability: 32,
    degradationRate: 0.7,
    estimatedCost: 28,
    priority: 'high',
    daysToFailure: 30,
    timeRange: { start: '90 days ago', end: '30 days' },
    degradationData: [100, 98, 95, 93, 90, 88, 85, 83, 81, 79, 78, 76, 73],
    factors: [
      { name: 'Bearing Temperature', impact: 48 },
      { name: 'Pitch Movement Friction', impact: 44 },
      { name: 'Grease Quality', impact: 40 },
      { name: 'Load Distribution', impact: 35 }
    ],
    actions: [
      'Schedule bearing inspection and lubrication',
      'Monitor pitch control accuracy',
      'Plan bearing replacement if needed',
      'Review load distribution patterns'
    ]
  },
  {
    turbine: 'WT-2',
    component: 'Main Bearing',
    rul: '22-32 days',
    risk: 38,
    currentHealth: 80,
    failureProbability: 28,
    degradationRate: 0.6,
    estimatedCost: 52,
    priority: 'high',
    daysToFailure: 32,
    timeRange: { start: '90 days ago', end: '32 days' },
    degradationData: [100, 99, 97, 95, 93, 91, 89, 87, 85, 83, 81, 80, 77],
    factors: [
      { name: 'Bearing Temperature', impact: 45 },
      { name: 'Vibration Levels', impact: 42 },
      { name: 'Lubrication Quality', impact: 38 },
      { name: 'Rotational Resistance', impact: 32 }
    ],
    actions: [
      'Perform detailed vibration analysis',
      'Schedule bearing replacement',
      'Order long-lead-time components now',
      'Plan for 24-48 hour maintenance window'
    ]
  }
];

const monitorItems = [
  {
    turbine: 'WT-1',
    component: 'Gearbox',
    rul: '45-60 days',
    risk: 25,
    currentHealth: 88,
    failureProbability: 18,
    degradationRate: 0.4,
    estimatedCost: 45,
    priority: 'medium',
    daysToFailure: 60,
    timeRange: { start: '120 days ago', end: '60 days' },
    degradationData: [100, 99, 98, 97, 96, 94, 93, 91, 90, 89, 88, 87, 85],
    factors: [
      { name: 'Operating Hours', impact: 32 },
      { name: 'Temperature Cycles', impact: 28 },
      { name: 'Oil Condition', impact: 24 },
      { name: 'Load Variations', impact: 20 }
    ],
    actions: [
      'Continue routine monitoring',
      'Schedule preventive maintenance in 30-45 days',
      'Monitor oil analysis reports',
      'Track vibration trends weekly'
    ]
  },
  {
    turbine: 'WT-8',
    component: 'Generator',
    rul: '60-80 days',
    risk: 20,
    currentHealth: 92,
    failureProbability: 12,
    degradationRate: 0.3,
    estimatedCost: 38,
    priority: 'medium',
    daysToFailure: 80,
    timeRange: { start: '120 days ago', end: '80 days' },
    degradationData: [100, 99, 98, 97, 96, 95, 94, 93, 93, 92, 92, 91, 90],
    factors: [
      { name: 'Thermal Stress', impact: 28 },
      { name: 'Electrical Load', impact: 25 },
      { name: 'Bearing Condition', impact: 22 },
      { name: 'Cooling Performance', impact: 18 }
    ],
    actions: [
      'Maintain current monitoring schedule',
      'Plan preventive maintenance in 45-60 days',
      'Monitor bearing temperatures',
      'Review power output patterns'
    ]
  },
  {
    turbine: 'WT-12',
    component: 'Yaw System',
    rul: '70-90 days',
    risk: 15,
    currentHealth: 94,
    failureProbability: 8,
    degradationRate: 0.2,
    estimatedCost: 15,
    priority: 'low',
    daysToFailure: 90,
    timeRange: { start: '120 days ago', end: '90 days' },
    degradationData: [100, 99, 99, 98, 97, 97, 96, 95, 95, 94, 94, 93, 93],
    factors: [
      { name: 'Motor Cycles', impact: 22 },
      { name: 'Brake Wear', impact: 18 },
      { name: 'Hydraulic System', impact: 15 },
      { name: 'Control Accuracy', impact: 12 }
    ],
    actions: [
      'Continue routine inspections',
      'Plan maintenance in normal schedule',
      'Monitor brake pad thickness',
      'Track positioning accuracy'
    ]
  }
];

const allComponents = [...immediateActions, ...scheduled30Days, ...monitorItems];

const selectedItem = ref(immediateActions[0]);

const allComponentsSorted = computed(() => {
  return [...allComponents].sort((a, b) => b.risk - a.risk);
});

const selectComponent = (item) => {
  selectedItem.value = item;
};
</script>