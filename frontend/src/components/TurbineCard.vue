<template>
  <div
      class="border-2 border-slate-300 rounded-lg p-3 cursor-pointer hover:border-blue-500 hover:shadow-md transition"
      @click="$emit('click')"
  >
    <div class="flex items-center justify-between mb-2">
      <span class="font-semibold text-sm">WT-{{ turbineNumber }}</span>
      <div :class="['w-3 h-3 rounded-full', getStatusClass()]" />
    </div>
    <div class="text-xs text-slate-600 space-y-1">
      <div class="flex justify-between">
        <span>Power:</span>
        <span class="font-semibold">{{ getPower() }} MW</span>
      </div>
      <div class="flex justify-between">
        <span>Uptime:</span>
        <span class="font-semibold">{{ getUptime() }}%</span>
      </div>
      <div class="w-full bg-slate-200 rounded-full h-1.5 mt-1">
        <div
            :class="['h-1.5 rounded-full', getHealthBarClass()]"
            :style="{ width: getHealth() + '%' }"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  turbineNumber: Number
});

defineEmits(['click']);

const getStatusClass = () => {
  if (props.turbineNumber === 3) return 'bg-red-500 animate-pulse';
  if (props.turbineNumber === 7) return 'bg-red-500';
  if (props.turbineNumber === 5 || props.turbineNumber === 11) return 'bg-yellow-500';
  return 'bg-green-500';
};

const getPower = () => {
  if (props.turbineNumber === 3) return '0.0';
  if (props.turbineNumber === 5) return '2.1';
  return '2.5';
};

const getUptime = () => props.turbineNumber === 3 ? '89' : '98';
const getHealth = () => {
  if (props.turbineNumber === 3) return '45';
  if (props.turbineNumber === 5) return '75';
  return '92';
};

const getHealthBarClass = () => {
  if (props.turbineNumber === 3 || props.turbineNumber === 7) return 'bg-red-500';
  if (props.turbineNumber === 5 || props.turbineNumber === 11) return 'bg-yellow-500';
  return 'bg-green-500';
};
</script>
