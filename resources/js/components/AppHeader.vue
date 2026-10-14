<template>
  <header class="bg-indigo-600 text-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <!-- Logo & Title -->
        <router-link to="/" class="flex items-center gap-3 group">
          <div class="bg-white/10 p-2.5 rounded-xl border border-white/10 group-hover:bg-white/20 transition">
            <Server class="w-6 h-6 text-white" />
          </div>
          <div class="flex flex-col">
            <div class="flex items-center gap-2">
              <span class="text-xl font-bold tracking-tight text-white">HOSTPAS</span>
              <span class="text-[10px] tracking-wide uppercase font-semibold bg-white/20 text-indigo-100 px-2 py-0.5 rounded-md">
                WASPAS
              </span>
            </div>
            <span class="text-xs text-indigo-200">
              Sistem Pendukung Keputusan Pemilihan Hosting Terbaik
            </span>
          </div>
        </router-link>

        <!-- Navigation Tabs -->
        <nav class="bg-indigo-700/50 p-1.5 rounded-xl border border-indigo-500/30 flex items-center gap-1 overflow-x-auto">
          <router-link
            to="/"
            v-slot="{ isActive, navigate }"
            custom
          >
            <button
              @click="navigate"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2 whitespace-nowrap',
                isActive ? 'bg-white text-indigo-700 shadow-sm' : 'text-indigo-100 hover:bg-indigo-600'
              ]"
            >
              <LayoutDashboard class="w-4 h-4" />
              <span>Beranda</span>
            </button>
          </router-link>

          <router-link
            to="/kriteria"
            v-slot="{ isActive, navigate }"
            custom
          >
            <button
              @click="navigate"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2 whitespace-nowrap',
                isActive ? 'bg-white text-indigo-700 shadow-sm' : 'text-indigo-100 hover:bg-indigo-600'
              ]"
            >
              <Sliders class="w-4 h-4" />
              <span>Kriteria</span>
            </button>
          </router-link>

          <router-link
            to="/alternatif"
            v-slot="{ isActive, navigate }"
            custom
          >
            <button
              @click="navigate"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2 whitespace-nowrap',
                isActive ? 'bg-white text-indigo-700 shadow-sm' : 'text-indigo-100 hover:bg-indigo-600'
              ]"
            >
              <Layers class="w-4 h-4" />
              <span>Alternatif & Nilai</span>
            </button>
          </router-link>

          <router-link
            to="/perhitungan"
            v-slot="{ isActive, navigate }"
            custom
          >
            <button
              @click="navigate"
              :class="[
                'px-4 py-2 text-sm font-medium rounded-lg transition-all flex items-center gap-2 whitespace-nowrap',
                isActive ? 'bg-white text-indigo-700 shadow-sm' : 'text-indigo-100 hover:bg-indigo-600'
              ]"
            >
              <Calculator class="w-4 h-4" />
              <span>Perhitungan WASPAS</span>
            </button>
          </router-link>
        </nav>
      </div>
    </div>
  </header>
</template>

<script setup>
import { Server, LayoutDashboard, Sliders, Layers, Calculator } from 'lucide-vue-next';
</script>
