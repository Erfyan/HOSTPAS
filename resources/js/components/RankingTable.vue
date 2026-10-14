<template>
  <div class="overflow-x-auto rounded-xl border border-slate-200">
    <table class="w-full text-left border-collapse text-sm">
      <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
        <tr>
          <th class="px-6 py-3.5 text-center w-24">Peringkat</th>
          <th class="px-6 py-3.5">Kode</th>
          <th class="px-6 py-3.5">Paket Hosting</th>
          <th class="px-6 py-3.5">Penyedia</th>
          <th class="px-6 py-3.5">Kategori</th>
          <th v-if="showDetails" class="px-6 py-3.5 text-right">Skor SAW (Q<sup>1</sup>)</th>
          <th v-if="showDetails" class="px-6 py-3.5 text-right">Skor WP (Q<sup>2</sup>)</th>
          <th class="px-6 py-3.5 text-right">Nilai Akhir (Q<sub>i</sub>)</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100 bg-white">
        <tr
          v-for="alt in sortedAlternatives"
          :key="alt.code"
          :class="[
            'hover:bg-slate-50/80 transition',
            alt.rank === 1 ? 'bg-emerald-50/40 font-medium' : ''
          ]"
        >
          <!-- Peringkat Badge -->
          <td class="px-6 py-3.5 text-center">
            <span
              :class="[
                'inline-flex items-center justify-center px-2.5 py-1 text-xs rounded-full font-bold',
                alt.rank === 1
                  ? 'bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-xs'
                  : 'bg-slate-100 text-slate-700'
              ]"
            >
              <Award v-if="alt.rank === 1" class="w-3.5 h-3.5 mr-1 text-emerald-600" />
              #{{ alt.rank }}
            </span>
          </td>

          <!-- Kode -->
          <td class="px-6 py-3.5 font-mono text-xs text-slate-500 font-semibold">
            {{ alt.code }}
          </td>

          <!-- Nama Paket -->
          <td class="px-6 py-3.5 font-semibold text-slate-900">
            {{ alt.name }}
            <span
              v-if="alt.rank === 1"
              class="ml-2 text-[10px] bg-emerald-600 text-white font-semibold px-2 py-0.5 rounded-full"
            >
              Rekomendasi Terbaik
            </span>
          </td>

          <!-- Penyedia -->
          <td class="px-6 py-3.5 text-slate-600">
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-800">
              {{ alt.provider }}
            </span>
          </td>

          <!-- Kategori -->
          <td class="px-6 py-3.5 text-slate-500 text-xs">
            {{ alt.category }}
          </td>

          <!-- SAW Details -->
          <td v-if="showDetails" class="px-6 py-3.5 text-right font-mono text-slate-600">
            {{ typeof alt.saw === 'number' ? alt.saw.toFixed(4) : '-' }}
          </td>

          <!-- WP Details -->
          <td v-if="showDetails" class="px-6 py-3.5 text-right font-mono text-slate-600">
            {{ typeof alt.wp === 'number' ? alt.wp.toFixed(4) : '-' }}
          </td>

          <!-- Nilai Akhir Q -->
          <td class="px-6 py-3.5 text-right">
            <span
              :class="[
                'font-mono font-bold text-sm',
                alt.rank === 1 ? 'text-emerald-700 text-base' : 'text-indigo-600'
              ]"
            >
              {{ typeof alt.q === 'number' ? alt.q.toFixed(4) : '-' }}
            </span>
          </td>
        </tr>

        <tr v-if="!sortedAlternatives.length">
          <td :colspan="showDetails ? 8 : 6" class="px-6 py-8 text-center text-slate-400">
            Tidak ada data alternatif yang tersedia untuk ditampilkan.
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Award } from 'lucide-vue-next';

const props = defineProps({
  alternatives: {
    type: Array,
    required: true,
  },
  showDetails: {
    type: Boolean,
    default: false,
  },
});

const sortedAlternatives = computed(() => {
  return [...props.alternatives].sort((a, b) => (a.rank || 0) - (b.rank || 0));
});
</script>
