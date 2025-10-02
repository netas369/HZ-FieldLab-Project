<template>
  <div class="bg-slate-800 text-white p-4 flex items-center justify-between border-b-2 border-slate-700">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-2">
        <Wind class="text-blue-400" :size="24" />
        <h1 class="text-xl font-bold">Zephyros Control Room</h1>
      </div>
      <nav class="flex gap-2">
        <router-link
            v-for="item in navItems"
            :key="item.path"
            :to="item.path"
            custom
            v-slot="{ navigate, isActive }"
        >
          <button
              @click="navigate"
              :class="[
              'px-4 py-2 rounded flex items-center gap-2',
              isActive ? 'bg-blue-600' : 'hover:bg-slate-700'
            ]"
          >
            <component :is="item.icon" :size="16" />
            {{ item.label }}
          </button>
        </router-link>
      </nav>
    </div>
    <div class="flex items-center gap-4">
      <button
          @click="weatherView = !weatherView"
          class="hover:bg-slate-700 p-2 rounded"
      >
        <Cloud :size="20" />
      </button>
      <div class="relative">
        <button
            @click="showNotifications = !showNotifications"
            class="hover:bg-slate-700 p-2 rounded relative"
        >
          <Bell :size="20" />
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <NotificationDropdown v-if="showNotifications" />
      </div>
      <Settings class="cursor-pointer hover:text-blue-400" :size="20" />
      <div class="flex items-center gap-2 border-l pl-4 ml-2">
        <User :size="20" />
        <div>
          <div class="text-sm font-semibold">Admin User</div>
          <div class="text-xs text-slate-400">Supervisor</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { inject } from 'vue';
import {
  Wind, Cloud, Bell, Settings, User, Activity, MapPin,
  TrendingUp, BarChart3, Calendar, Clock
} from 'lucide-vue-next';
import NotificationDropdown from './NotificationDropdown.vue';

const weatherView = inject('weatherView');
const showNotifications = inject('showNotifications');

const navItems = [
  { path: '/', label: 'Dashboard', icon: Activity },
  { path: '/map', label: 'Map View', icon: MapPin },
  { path: '/predictive', label: 'Predictive', icon: TrendingUp },
  { path: '/analytics', label: 'Analytics', icon: BarChart3 },
  { path: '/history', label: 'History', icon: Calendar },
  { path: '/scheduler', label: 'Scheduler', icon: Clock }
];
</script>
