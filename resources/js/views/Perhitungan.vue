<template>
  <div class="space-y-8">
    <!-- Header Page Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Calculator class="w-6 h-6 text-indigo-600" />
          Kalkulasi Transparan WASPAS
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Tahapan lengkap perhitungan metode WASPAS: dari matriks keputusan mentah, normalisasi matriks R, skor SAW & WP, hingga ranking akhir Q.
        </p>
      </div>

      <!-- Filter Penyedia -->
      <div class="flex items-center gap-1.5 self-start sm:self-auto bg-white p-1 rounded-xl border border-slate-200">
        <button
          type="button"
          @click="onSelectProvider('')"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-semibold transition',
            provider === '' ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
          ]"
        >
          Semua (20)
        </button>
        <button
          v-for="p in providersList"
          :key="p"
          type="button"
          @click="onSelectProvider(p)"
          :class="[
            'px-3 py-1.5 rounded-lg text-xs font-semibold transition',
            provider === p ? 'bg-indigo-600 text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'
          ]"
        >
          {{ p }}
        </button>
      </div>
    </div>

    <!-- Kontrol Parameter Lambda (λ) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
          <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
            <Sliders class="w-5 h-5 text-indigo-600" />
            Parameter Kombinasi Lambda (&lambda;)
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">
            Formula: <span class="font-mono font-semibold text-slate-700">Q<sub>i</sub> = &lambda; &times; Q<sup>1</sup><sub>i</sub> + (1 - &lambda;) &times; Q<sup>2</sup><sub>i</sub></span>
          </p>
        </div>

        <div class="flex items-center gap-3">
          <span class="text-xs text-slate-500">Nilai &lambda;:</span>
          <span class="text-lg font-mono font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-3 py-1 rounded-xl">
            {{ localLambda.toFixed(1) }}
          </span>
        </div>
      </div>

      <div class="space-y-1 pt-1">
        <input
          type="range"
          min="0"
          max="1"
          step="0.1"
          v-model.number="localLambda"
          @input="onLambdaChange"
          class="w-full accent-indigo-600 cursor-pointer h-2 bg-slate-200 rounded-lg"
        />
        <div class="flex items-center justify-between text-xs text-slate-500 font-mono pt-1">
          <span>&lambda; = 0.0 (WP Murni)</span>
          <span class="text-indigo-600 font-bold">&lambda; = 0.5 (Seimbang / Default Jurnal)</span>
          <span>&lambda; = 1.0 (SAW Murni)</span>
        </div>
      </div>
    </div>

    <!-- Alert Excluded Alternatif jika ada nilai kosong -->
    <div
      v-if="result?.excluded && result.excluded.length"
      class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-900 space-y-1"
    >
      <div class="font-bold flex items-center gap-1.5">
        <AlertCircle class="w-4 h-4 text-amber-600" />
        Alternatif Dikeluarkan dari Perhitungan:
      </div>
      <p>
        Ada {{ result.excluded.length }} alternatif yang nilainya belum lengkap sehingga tidak disertakan dalam matriks keputusan.
      </p>
    </div>

    <!-- Alert jika perhitungan gagal / kurang dari 2 alternatif -->
    <div
      v-if="result && !result.ok"
      class="bg-rose-50 border border-rose-200 rounded-xl p-6 text-center space-y-2 text-rose-800"
    >
      <AlertCircle class="w-8 h-8 text-rose-500 mx-auto" />
      <div class="font-bold text-base">Tidak Dapat Melakukan Perhitungan</div>
      <p class="text-xs text-rose-700">
        {{ result.message || 'Minimal diperlukan 2 alternatif lengkap untuk melakukan normalisasi dan perankingan WASPAS.' }}
      </p>
    </div>

    <!-- Container Langkah Perhitungan (Jika result.ok) -->
    <div v-if="result && result.ok" class="space-y-8">
      <!-- LANGKAH 1: MATRIKS KEPUTUSAN X -->
      <SectionCard
        title="Matriks Keputusan (X) & Nilai Pembanding"
        description="Matriks data mentah alternatif yang dievaluasi beserta nilai ekstrim kriteria (Max untuk Benefit, Min untuk Cost)."
      >
        <template #action>
          <StepBadge :step="1" />
        </template>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
              <tr>
                <th class="px-6 py-3.5 w-20">Kode</th>
                <th class="px-6 py-3.5">Paket Hosting</th>
                <th
                  v-for="crit in result.criteria"
                  :key="crit.code"
                  class="px-6 py-3.5 text-right font-mono"
                >
                  {{ crit.code }} ({{ crit.type }})
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr
                v-for="alt in result.alternatives"
                :key="alt.code"
                class="hover:bg-slate-50 transition"
              >
                <td class="px-6 py-3 font-mono font-bold text-xs text-indigo-600">{{ alt.code }}</td>
                <td class="px-6 py-3 font-medium text-slate-800 text-xs">{{ alt.name }}</td>
                <td
                  v-for="crit in result.criteria"
                  :key="crit.code"
                  class="px-6 py-3 text-right font-mono text-xs text-slate-700"
                >
                  {{ formatNumber(alt.x[crit.code]) }}
                </td>
              </tr>
            </tbody>
            <!-- Baris Nilai Pembanding (Extremes) -->
            <tfoot class="bg-indigo-50/70 border-t-2 border-indigo-200 font-semibold text-indigo-950 text-xs">
              <tr>
                <td colspan="2" class="px-6 py-3.5 font-bold uppercase tracking-wider text-indigo-900">
                  Nilai Ekstrim Pembanding (Max / Min):
                </td>
                <td
                  v-for="crit in result.criteria"
                  :key="crit.code"
                  class="px-6 py-3.5 text-right font-mono font-bold text-indigo-700"
                >
                  {{ formatNumber(result.extremes[crit.code]) }}
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </SectionCard>

      <!-- LANGKAH 2: NORMALISASI MATRIKS R -->
      <SectionCard
        title="Matriks Normalisasi (R)"
        description="Nilai mentah dinormalkan berdasarkan jenis atribut: r = x / max(x) untuk Benefit, dan r = min(x) / x untuk Cost."
      >
        <template #action>
          <StepBadge :step="2" />
        </template>

        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-left border-collapse text-sm">
            <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
              <tr>
                <th class="px-6 py-3.5 w-20">Kode</th>
                <th class="px-6 py-3.5">Paket Hosting</th>
                <th
                  v-for="crit in result.criteria"
                  :key="crit.code"
                  class="px-6 py-3.5 text-right font-mono"
                >
                  r<sub>{{ crit.code }}</sub> (w: {{ crit.normalized_weight }})
                </th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
              <tr
                v-for="alt in result.alternatives"
                :key="alt.code"
                class="hover:bg-slate-50 transition"
              >
                <td class="px-6 py-3 font-mono font-bold text-xs text-indigo-600">{{ alt.code }}</td>
                <td class="px-6 py-3 font-medium text-slate-800 text-xs">{{ alt.name }}</td>
                <td
                  v-for="crit in result.criteria"
                  :key="crit.code"
                  class="px-6 py-3 text-right font-mono text-xs text-slate-700"
                >
                  {{ alt.r[crit.code]?.toFixed(4) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </SectionCard>

      <!-- LANGKAH 3: SKOR SAW & WP -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kolom SAW -->
        <SectionCard
          title="Skor SAW (Q¹)"
          description="Model Aditif Terbobot: Q¹ᵢ = Σ (wⱼ × rᵢⱼ)"
        >
          <template #action>
            <StepBadge :step="3" />
          </template>

          <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse text-sm">
              <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
                <tr>
                  <th class="px-4 py-3">Kode</th>
                  <th class="px-4 py-3">Paket Hosting</th>
                  <th class="px-4 py-3 text-right font-mono">Nilai Q¹</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-xs">
                <tr v-for="alt in result.alternatives" :key="alt.code" class="hover:bg-slate-50">
                  <td class="px-4 py-2.5 font-mono text-indigo-600 font-semibold">{{ alt.code }}</td>
                  <td class="px-4 py-2.5 text-slate-800 font-medium">{{ alt.name }}</td>
                  <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-700">
                    {{ alt.saw?.toFixed(4) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </SectionCard>

        <!-- Kolom WP -->
        <SectionCard
          title="Skor WP (Q²)"
          description="Model Perkalian Terbobot: Q²ᵢ = Π (rᵢⱼ ^ wⱼ)"
        >
          <template #action>
            <StepBadge :step="3" />
          </template>

          <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="w-full text-left border-collapse text-sm">
              <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
                <tr>
                  <th class="px-4 py-3">Kode</th>
                  <th class="px-4 py-3">Paket Hosting</th>
                  <th class="px-4 py-3 text-right font-mono">Nilai Q²</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white text-xs">
                <tr v-for="alt in result.alternatives" :key="alt.code" class="hover:bg-slate-50">
                  <td class="px-4 py-2.5 font-mono text-indigo-600 font-semibold">{{ alt.code }}</td>
                  <td class="px-4 py-2.5 text-slate-800 font-medium">{{ alt.name }}</td>
                  <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-700">
                    {{ alt.wp?.toFixed(4) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </SectionCard>
      </div>

      <!-- LANGKAH 4: NILAI AKHIR WASPAS & PERANGKINGAN -->
      <SectionCard
        title="Langkah 4: Nilai Akhir Preferensi WASPAS (Q) & Perankingan"
        description="Kombinasi linier: Qᵢ = λ × Q¹ᵢ + (1 − λ) × Q²ᵢ. Alternatif diurutkan dari skor Q tertinggi."
      >
        <template #action>
          <StepBadge :step="4" />
        </template>

        <RankingTable :alternatives="result.alternatives" :showDetails="true" />
      </SectionCard>

      <!-- GRAFIK VISUALISASI WASPAS -->
      <SectionCard
        title="Visualisasi Grafik Perbandingan Nilai WASPAS (Q)"
        description="Representasi grafis perankingan alternatif untuk mempermudah analisis komparatif dalam lampiran riset."
      >
        <template #title-icon>
          <BarChart3 class="w-5 h-5 text-indigo-600" />
        </template>

        <RankingChart :alternatives="result.alternatives" />
      </SectionCard>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useSpk } from '../composables/useSpk';
import SectionCard from '../components/SectionCard.vue';
import StepBadge from '../components/StepBadge.vue';
import RankingTable from '../components/RankingTable.vue';
import RankingChart from '../components/RankingChart.vue';
import {
  Calculator,
  Sliders,
  BarChart3,
  AlertCircle,
} from 'lucide-vue-next';

const {
  lambda,
  provider,
  providersList,
  result,
  fetchWaspas,
  setLambda,
  setProvider,
} = useSpk();

const localLambda = ref(lambda.value);
let debounceTimer = null;

function onLambdaChange() {
  if (debounceTimer) clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    setLambda(localLambda.value);
  }, 300);
}

function onSelectProvider(selected) {
  setProvider(selected);
}

function formatNumber(num) {
  if (num === null || num === undefined) return '-';
  return Number(num).toLocaleString('id-ID');
}

onMounted(async () => {
  if (!result.value) {
    await fetchWaspas();
  }
});
</script>
