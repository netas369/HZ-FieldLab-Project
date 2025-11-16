
<template>
    <div class="poc-view">
        <Poc />
    </div>
</template>

<script>
import Poc from '@/components/Poc.vue'

export default {
    name: 'PocView',
    components: {
        Poc
    }
}
</script>

<style scoped>
.poc-view {
    text-align: center;
}
</style>