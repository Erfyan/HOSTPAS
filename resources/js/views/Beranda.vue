<template>
  <div class="space-y-6">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-indigo-900 to-indigo-700 rounded-2xl p-8 text-white shadow-xl">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
        <!-- Kolom Kiri: Deskripsi Sistem -->
        <div class="lg:col-span-7 space-y-4">
          <div class="inline-flex items-center gap-2">
            <span class="bg-indigo-500/40 text-indigo-200 text-xs font-semibold px-3 py-1 rounded-full border border-indigo-400/20">
              Sistem Pendukung Keputusan Jurnal Ilmiah
            </span>
          </div>

          <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight leading-tight">
            Pemilihan Hosting Terbaik Berbasis Metode WASPAS
          </h1>

          <p class="text-indigo-100 text-sm sm:text-base leading-relaxed">
            <strong>HOSTPAS</strong> menggabungkan pendekatan <em>Simple Additive Weighting (SAW)</em> dan <em>Weighted Product (WP)</em> untuk menghasilkan rekomendasi paket hosting web yang objektif, akurat, dan transparan langkah demi langkah.
          </p>

          <div class="pt-2 flex flex-wrap items-center gap-3">
            <router-link
              to="/perhitungan"
              class="bg-white text-indigo-700 hover:bg-indigo-50 font-semibold text-sm px-5 py-2.5 rounded-xl shadow-md transition flex items-center gap-2"
            >
              <Calculator class="w-4 h-4" />
              <span>Lihat Kalkulasi Transparan</span>
              <ArrowRight class="w-4 h-4 ml-1" />
            </router-link>

            <router-link
              to="/kriteria"
              class="bg-indigo-800/60 hover:bg-indigo-800 text-indigo-100 font-semibold text-sm px-4 py-2.5 rounded-xl border border-indigo-400/30 transition flex items-center gap-2"
            >
              <Sliders class="w-4 h-4" />
              <span>Atur Bobot Kriteria</span>
            </router-link>
          </div>
        </div>

        <!-- Kolom Kanan: Rekomendasi Teratas (Rank 1) -->
        <div class="lg:col-span-5">
          <div class="bg-white/10 p-6 rounded-2xl border border-white/15 backdrop-blur-md space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-xs uppercase font-bold tracking-wider text-emerald-300 flex items-center gap-1.5">
                <Award class="w-4 h-4" />
                Rekomendasi Peringkat #1
              </span>
              <span class="text-xs font-mono bg-white/20 px-2 py-0.5 rounded text-white">
                &lambda; = {{ lambda }}
              </span>
            </div>

            <div v-if="top1" class="space-y-2">
              <div class="text-2xl font-black text-white leading-snug">
                {{ top1.name }}
              </div>
              <div class="flex items-center gap-2 text-xs text-indigo-200">
                <span class="bg-indigo-500/50 px-2 py-0.5 rounded font-semibold text-white">
                  {{ top1.provider }}
                </span>
                <span>&bull;</span>
                <span>{{ top1.category }}</span>
              </div>
              <p class="text-xs text-indigo-100/80 pt-1 line-clamp-2">
                Spesifikasi: {{ top1.spec_raw || 'Sesuai data paket' }}
              </p>

              <div class="pt-3 border-t border-white/10 flex items-center justify-between">
                <div>
                  <span class="text-[11px] text-indigo-200 block">Skor Akhir WASPAS (Q):</span>
                  <span class="text-2xl font-black text-emerald-300 font-mono">
                    {{ typeof top1.q === 'number' ? top1.q.toFixed(4) : '-' }}
                  </span>
                </div>
                <router-link
                  to="/perhitungan"
                  class="text-xs text-indigo-200 hover:text-white underline underline-offset-4"
                >
                  Rincian Rumus &rarr;
                </router-link>
              </div>
            </div>

            <div v-else class="text-center py-6 text-indigo-200 text-sm">
              Memuat data rekomendasi...
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- 3 Kartu Ringkasan -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Kartu 1: Alternatif -->
      <StatCard
        title="Total Alternatif"
        :value="alternatives.length || 20"
        subvalue="4 Penyedia: Hostinger, Niagahoster, Rumahweb, IDCloudHost"
        iconBg="bg-blue-100 text-blue-600"
      >
        <template #icon>
          <Server class="w-6 h-6" />
        </template>
      </StatCard>

      <!-- Kartu 2: Kriteria -->
      <StatCard
        title="Kriteria Evaluasi"
        :value="criteria.length || 4"
        subvalue="Harga (Cost), Storage, RAM, CPU (Benefit)"
        iconBg="bg-purple-100 text-purple-600"
      >
        <template #icon>
          <Sliders class="w-6 h-6" />
        </template>
      </StatCard>

      <!-- Kartu 3: Slider Lambda -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Parameter Lambda (&lambda;)</span>
          <span class="text-sm font-bold font-mono text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded">
            {{ localLambda.toFixed(1) }}
          </span>
        </div>

        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          v-model.number="localLambda"
          @input="onLambdaInput"
          class="w-full accent-indigo-600 cursor-pointer"
        />

        <div class="flex items-center justify-between text-[11px] text-slate-500 font-mono">
          <span>0.0 (WP Murni)</span>
          <span class="text-indigo-600 font-semibold">0.5 (Bawaan)</span>
          <span>1.0 (SAW Murni)</span>
        </div>
      </div>
    </div>

    <!-- 5 Peringkat Teratas -->
    <SectionCard
      title="5 Paket Hosting Rekomendasi Teratas"
      description="Alternatif dengan nilai preferensi Q tertinggi berdasarkan perhitungan gabungan SAW dan WP."
    >
      <template #title-icon>
        <Award class="w-5 h-5 text-amber-500" />
      </template>

      <template #action>
        <router-link
          to="/perhitungan"
          class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1"
        >
          <span>Buka Semua 20 Alternatif</span>
          <ArrowRight class="w-3.5 h-3.5" />
        </router-link>
      </template>

      <RankingTable :alternatives="top5" :showDetails="true" />
    </SectionCard>

    <!-- Panel Kesimpulan & Insight Cerdas -->
    <DecisionInsight />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSpk } from '../composables/useSpk';
import StatCard from '../components/StatCard.vue';
import SectionCard from '../components/SectionCard.vue';
import RankingTable from '../components/RankingTable.vue';
import DecisionInsight from '../components/DecisionInsight.vue';
import {
  Server,
  Sliders,
  Calculator,
  ArrowRight,
  Award,
} from 'lucide-vue-next';

const {
  criteria,
  alternatives,
  lambda,
  result,
  top5,
  top1,
  fetchCriteria,
  fetchAlternatives,
  fetchWaspas,
  setLambda,
} = useSpk();

const localLambda = ref(lambda.value);
let debounceTimer = null;

function onLambdaInput() {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    setLambda(localLambda.value);
  }, 300);
}

onMounted(async () => {
  if (!criteria.value.length) await fetchCriteria();
  if (!alternatives.value.length) await fetchAlternatives();
  if (!result.value) await fetchWaspas();
});
</script>
