<template>
  <div class="bg-slate-900 p-6 rounded-lg">
    <!-- Header with Status -->
    <div class="flex justify-between items-start mb-6">
      <div>
        <h2 class="text-2xl font-bold text-white mb-1">Turbine #{{ selectedTurbine.turbine_id }}</h2>
        <p class="text-slate-400 text-sm">Last 24 Hours</p>
      </div>
      <div class="text-right">
        <div :class="getSeverityClass(selectedTurbine.status_severity)" 
             class="inline-block px-3 py-1 rounded-full text-sm font-semibold mb-2">
          {{ selectedTurbine.status_description }}
        </div>
        <div v-if="selectedTurbine.alarm_code" 
             :class="getAlarmSeverityClass(selectedTurbine.alarm_severity)"
             class="inline-block px-3 py-1 rounded-full text-xs font-semibold">
          {{ selectedTurbine.alarm_description }}
        </div>
        <div class="text-slate-400 text-xs mt-2">
          {{ formatTimestamp(selectedTurbine.latest_reading) }}
        </div>
      </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <div class="text-slate-400 text-xs mb-1">Power Output</div>
        <div class="text-white text-2xl font-bold">{{ selectedTurbine.power_kw.toFixed(0) }}</div>
        <div class="text-slate-400 text-xs">kW</div>
      </div>
      
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <div class="text-slate-400 text-xs mb-1">Wind Speed</div>
        <div class="text-white text-2xl font-bold">{{ selectedTurbine.wind_speed_ms.toFixed(1) }}</div>
        <div class="text-slate-400 text-xs">m/s</div>
      </div>
      
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <div class="text-slate-400 text-xs mb-1">Rotor Speed</div>
        <div class="text-white text-2xl font-bold">{{ selectedTurbine.rotor_speed_rpm.toFixed(1) }}</div>
        <div class="text-slate-400 text-xs">RPM</div>
      </div>
      
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <div class="text-slate-400 text-xs mb-1">Ambient Temp</div>
        <div class="text-white text-2xl font-bold">{{ selectedTurbine.ambient_temp_c.toFixed(1) }}</div>
        <div class="text-slate-400 text-xs">°C</div>
      </div>
    </div>

    <!-- Main Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Power vs Wind Speed -->
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <h3 class="text-white font-semibold mb-4">Power Output & Wind Speed</h3>
        <div class="relative" style="height: 250px;">
          <canvas ref="powerWindCanvas"></canvas>
        </div>
        <div class="flex justify-center gap-6 mt-3 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-blue-500 rounded"></div>
            <span class="text-slate-400">Power (kW)</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-emerald-500 rounded"></div>
            <span class="text-slate-400">Wind Speed (m/s)</span>
          </div>
        </div>
      </div>

      <!-- Rotor & Generator Speed -->
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <h3 class="text-white font-semibold mb-4">Rotor & Generator Speed</h3>
        <div class="relative" style="height: 250px;">
          <canvas ref="speedCanvas"></canvas>
        </div>
        <div class="flex justify-center gap-6 mt-3 text-sm">
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-purple-500 rounded"></div>
            <span class="text-slate-400">Rotor (RPM)</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-3 h-3 bg-pink-500 rounded"></div>
            <span class="text-slate-400">Generator (RPM)</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Angles and Direction -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Wind Direction Compass -->
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <h3 class="text-white font-semibold mb-4">Wind Direction</h3>
        <div class="relative" style="height: 200px;">
          <canvas ref="windDirectionCanvas"></canvas>
        </div>
        <div class="text-center mt-3">
          <div class="text-slate-400 text-xs">Wind: {{ selectedTurbine.wind_direction_deg }}°</div>
          <div class="text-slate-400 text-xs">Nacelle: {{ selectedTurbine.nacelle_direction_deg }}°</div>
        </div>
      </div>

      <!-- Pitch Angle -->
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <h3 class="text-white font-semibold mb-4">Pitch Angle</h3>
        <div class="relative" style="height: 200px;">
          <canvas ref="pitchCanvas"></canvas>
        </div>
        <div class="text-center mt-3 text-slate-400 text-xs">
          Current: {{ selectedTurbine.pitch_angle_deg.toFixed(1) }}°
        </div>
      </div>

      <!-- Yaw Angle -->
      <div class="bg-slate-800 rounded-lg p-4 border border-slate-700">
        <h3 class="text-white font-semibold mb-4">Yaw Angle</h3>
        <div class="relative" style="height: 200px;">
          <canvas ref="yawCanvas"></canvas>
        </div>
        <div class="text-center mt-3 text-slate-400 text-xs">
          Current: {{ selectedTurbine.yaw_angle_deg.toFixed(1) }}°
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';

const props = defineProps({
  selectedTurbine: {
    type: Object,
    required: false
  },
  historicalData: {
    type: Array,
    default: () => []
  }
});

const powerWindCanvas = ref(null);
const speedCanvas = ref(null);
const windDirectionCanvas = ref(null);
const pitchCanvas = ref(null);
const yawCanvas = ref(null);

const getSeverityClass = (severity) => {
  const classes = {
    'critical': 'bg-red-500/20 text-red-400 border border-red-500',
    'warning': 'bg-amber-500/20 text-amber-400 border border-amber-500',
    'normal': 'bg-green-500/20 text-green-400 border border-green-500',
    'info': 'bg-blue-500/20 text-blue-400 border border-blue-500'
  };
  return classes[severity?.toLowerCase()] || classes.info;
};

const getAlarmSeverityClass = (severity) => {
  const classes = {
    'critical': 'bg-red-500/30 text-red-300 border border-red-500',
    'high': 'bg-orange-500/30 text-orange-300 border border-orange-500',
    'medium': 'bg-amber-500/30 text-amber-300 border border-amber-500',
    'low': 'bg-yellow-500/30 text-yellow-300 border border-yellow-500'
  };
  return classes[severity?.toLowerCase()] || classes.low;
};

const formatTimestamp = (timestamp) => {
  return new Date(timestamp).toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const drawLineChart = (canvas, data, key1, key2, color1, color2, label1, label2) => {
  if (!canvas || !data.length) return;
  
  const ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * window.devicePixelRatio;
  canvas.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const width = rect.width;
  const height = rect.height;
  const padding = 40;
  const chartWidth = width - padding * 2;
  const chartHeight = height - padding * 2;
  
  ctx.clearRect(0, 0, width, height);
  
  // Get data ranges
  const values1 = data.map(d => d[key1]);
  const values2 = data.map(d => d[key2]);
  const max1 = Math.max(...values1);
  const max2 = Math.max(...values2);
  const min1 = Math.min(...values1);
  const min2 = Math.min(...values2);
  
  // Grid
  ctx.strokeStyle = 'rgba(71, 85, 105, 0.3)';
  ctx.lineWidth = 1;
  for (let i = 0; i <= 5; i++) {
    const y = padding + (chartHeight * i / 5);
    ctx.beginPath();
    ctx.moveTo(padding, y);
    ctx.lineTo(width - padding, y);
    ctx.stroke();
  }
  
  // Draw lines
  const drawLine = (values, max, min, color) => {
    ctx.strokeStyle = color;
    ctx.lineWidth = 2;
    ctx.beginPath();
    
    values.forEach((value, i) => {
      const x = padding + (chartWidth * i / (values.length - 1));
      const normalizedValue = (value - min) / (max - min || 1);
      const y = padding + chartHeight - (normalizedValue * chartHeight);
      
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    
    ctx.stroke();
    
    // Fill area
    ctx.lineTo(width - padding, padding + chartHeight);
    ctx.lineTo(padding, padding + chartHeight);
    ctx.closePath();
    ctx.fillStyle = color.replace('rgb', 'rgba').replace(')', ', 0.1)');
    ctx.fill();
  };
  
  drawLine(values1, max1, min1, color1);
  drawLine(values2, max2, min2, color2);
  
  // Y-axis labels
  ctx.fillStyle = 'rgb(148, 163, 184)';
  ctx.font = '11px sans-serif';
  ctx.textAlign = 'right';
  
  for (let i = 0; i <= 5; i++) {
    const value1 = max1 - ((max1 - min1) * i / 5);
    const y = padding + (chartHeight * i / 5);
    ctx.fillText(value1.toFixed(0), padding - 5, y + 4);
  }
};

const drawCompass = (canvas, windDir, nacelleDir) => {
  if (!canvas) return;
  
  const ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * window.devicePixelRatio;
  canvas.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const width = rect.width;
  const height = rect.height;
  const centerX = width / 2;
  const centerY = height / 2;
  const radius = Math.min(width, height) / 2 - 20;
  
  ctx.clearRect(0, 0, width, height);
  
  // Outer circle
  ctx.strokeStyle = 'rgba(71, 85, 105, 0.5)';
  ctx.lineWidth = 2;
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, Math.PI * 2);
  ctx.stroke();
  
  // Cardinal directions
  ctx.fillStyle = 'rgb(148, 163, 184)';
  ctx.font = 'bold 14px sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  
  const directions = ['N', 'E', 'S', 'W'];
  directions.forEach((dir, i) => {
    const angle = (i * Math.PI / 2) - Math.PI / 2;
    const x = centerX + Math.cos(angle) * (radius + 10);
    const y = centerY + Math.sin(angle) * (radius + 10);
    ctx.fillText(dir, x, y);
  });
  
  // Wind direction arrow
  const windRad = (windDir - 90) * Math.PI / 180;
  ctx.strokeStyle = 'rgb(16, 185, 129)';
  ctx.fillStyle = 'rgb(16, 185, 129)';
  ctx.lineWidth = 3;
  ctx.beginPath();
  ctx.moveTo(centerX, centerY);
  ctx.lineTo(
    centerX + Math.cos(windRad) * radius * 0.7,
    centerY + Math.sin(windRad) * radius * 0.7
  );
  ctx.stroke();
  
  // Arrow head
  ctx.beginPath();
  ctx.arc(
    centerX + Math.cos(windRad) * radius * 0.7,
    centerY + Math.sin(windRad) * radius * 0.7,
    5, 0, Math.PI * 2
  );
  ctx.fill();
  
  // Nacelle direction arrow
  const nacelleRad = (nacelleDir - 90) * Math.PI / 180;
  ctx.strokeStyle = 'rgb(59, 130, 246)';
  ctx.fillStyle = 'rgb(59, 130, 246)';
  ctx.lineWidth = 2;
  ctx.setLineDash([5, 5]);
  ctx.beginPath();
  ctx.moveTo(centerX, centerY);
  ctx.lineTo(
    centerX + Math.cos(nacelleRad) * radius * 0.5,
    centerY + Math.sin(nacelleRad) * radius * 0.5
  );
  ctx.stroke();
  ctx.setLineDash([]);
};

const drawGauge = (canvas, value, max, color) => {
  if (!canvas) return;
  
  const ctx = canvas.getContext('2d');
  const rect = canvas.getBoundingClientRect();
  canvas.width = rect.width * window.devicePixelRatio;
  canvas.height = rect.height * window.devicePixelRatio;
  ctx.scale(window.devicePixelRatio, window.devicePixelRatio);
  
  const width = rect.width;
  const height = rect.height;
  const centerX = width / 2;
  const centerY = height / 2 + 20;
  const radius = Math.min(width, height) / 2 - 20;
  
  ctx.clearRect(0, 0, width, height);
  
  const startAngle = Math.PI * 0.75;
  const endAngle = Math.PI * 2.25;
  const valueAngle = startAngle + (endAngle - startAngle) * (value / max);
  
  // Background arc
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, startAngle, endAngle);
  ctx.lineWidth = 15;
  ctx.strokeStyle = 'rgba(71, 85, 105, 0.3)';
  ctx.stroke();
  
  // Value arc
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, startAngle, valueAngle);
  ctx.lineWidth = 15;
  ctx.strokeStyle = color;
  ctx.stroke();
  
  // Value text
  ctx.fillStyle = 'white';
  ctx.font = 'bold 24px sans-serif';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  ctx.fillText(value.toFixed(1), centerX, centerY);
  
  // Max text
  ctx.fillStyle = 'rgb(148, 163, 184)';
  ctx.font = '12px sans-serif';
  ctx.fillText(`/ ${max}`, centerX, centerY + 20);
};

const renderCharts = () => {
  const historical = props.historicalData.length > 0 
    ? props.historicalData 
    : Array(24).fill(null).map((_, i) => ({
        power_kw: props.selectedTurbine.power_kw + (Math.random() - 0.5) * 200,
        wind_speed_ms: props.selectedTurbine.wind_speed_ms + (Math.random() - 0.5) * 2,
        rotor_speed_rpm: props.selectedTurbine.rotor_speed_rpm + (Math.random() - 0.5) * 2,
        generator_speed_rpm: props.selectedTurbine.generator_speed_rpm + (Math.random() - 0.5) * 50
      }));
  
  drawLineChart(
    powerWindCanvas.value,
    historical,
    'power_kw',
    'wind_speed_ms',
    'rgb(59, 130, 246)',
    'rgb(16, 185, 129)',
    'Power',
    'Wind Speed'
  );
  
  drawLineChart(
    speedCanvas.value,
    historical,
    'rotor_speed_rpm',
    'generator_speed_rpm',
    'rgb(168, 85, 247)',
    'rgb(236, 72, 153)',
    'Rotor',
    'Generator'
  );
  
  drawCompass(
    windDirectionCanvas.value,
    props.selectedTurbine.wind_direction_deg,
    props.selectedTurbine.nacelle_direction_deg
  );
  
  drawGauge(pitchCanvas.value, props.selectedTurbine.pitch_angle_deg, 90, 'rgb(245, 158, 11)');
  drawGauge(yawCanvas.value, props.selectedTurbine.yaw_angle_deg, 360, 'rgb(239, 68, 68)');
};

onMounted(() => {
  renderCharts();
});

watch(() => props.selectedTurbine, () => {
  renderCharts();
}, { deep: true });

watch(() => props.historicalData, () => {
  renderCharts();
}, { deep: true });
</script>