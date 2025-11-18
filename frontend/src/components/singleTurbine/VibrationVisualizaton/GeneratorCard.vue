    <template>
  <div class="flex justify-between items-center bg-gray-900 p-4 rounded-xl shadow">

        <div class="text-gray-100">
      <h2 class="font-semibold text-lg">Generator</h2>
      <p>Drive End: {{ turbine.generator_vibration_de }}</p>
      <p>Non-Drive End: {{ turbine.generator_vibration_nde }}</p>
      <p>Status: {{ turbine.generator_status.label }}</p>
    </div>

    <div class="w-16 h-16 rounded-full border-4" :class="statusColor"></div>

  </div>
</template>

<script setup>
import { computed } from "vue"
const props = defineProps({ turbine: Object })
const statusColor = computed(() => `border-${props.turbine.generator_status.color}-500`)
</script>
