cat > SchedulerView.vue << 'EOF'
<template>
  <div class="p-6 bg-slate-100 min-h-screen">
    <div class="bg-white rounded-lg shadow p-6">
      <h2 class="text-xl font-bold flex items-center gap-2 mb-4">
        <Clock :size="24" />
        Maintenance Scheduler
      </h2>
      <div class="h-96 bg-slate-50 rounded-lg flex items-center justify-center border-2 border-slate-200">
        <div class="text-center">
          <Clock :size="48" class="mx-auto mb-2 text-slate-400" />
          <p class="text-slate-600">Interactive scheduling system</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Clock } from 'lucide-vue-next';
</script>
EOF