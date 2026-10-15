<template>
  <div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 mb-1">
          <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
            Gambar 6 &bull; Implementasi 3.4
          </span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Edit class="w-6 h-6 text-indigo-600" />
          Halaman Penilaian Alternatif
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Mengisi dan memperbarui nilai tiap alternatif pada setiap kriteria (Matriks Keputusan X) berdasarkan data spesifikasi hosting yang dikumpulkan.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
        <button
          type="button"
          @click="handleLoadJournalCase"
          :disabled="loading"
          class="bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 font-semibold text-sm px-3.5 py-2.5 rounded-xl transition flex items-center gap-2 disabled:opacity-50"
          title="Muat dataset 5 Hosting (Bab 3.5 Pembahasan)"
        >
          <BookOpen class="w-4 h-4 text-purple-600" />
          <span>Kasus Uji Jurnal (Bab 3.5)</span>
        </button>

        <button
          type="button"
          @click="handleReset"
          :disabled="loading"
          class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2 disabled:opacity-50"
        >
          <RotateCcw class="w-4 h-4" />
          <span>Reset Default</span>
        </button>
      </div>
    </div>

    <!-- Alert / Feedback Banner -->
    <div
      v-if="statusMessage"
      :class="[
        'p-4 rounded-xl text-sm flex items-center gap-2.5 transition',
        statusType === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'
      ]"
    >
      <CheckCircle2 v-if="statusType === 'success'" class="w-5 h-5 text-emerald-600 shrink-0" />
      <AlertCircle v-else class="w-5 h-5 text-rose-600 shrink-0" />
      <span>{{ statusMessage }}</span>
    </div>

    <!-- Filter Penyedia Hosting -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <span class="text-xs font-semibold uppercase tracking-wider text-slate-500 flex items-center gap-2">
          <ListFilter class="w-4 h-4 text-indigo-600" />
          Filter Berdasarkan Penyedia Hosting:
        </span>
        <div class="flex flex-wrap items-center gap-1.5">
          <button
            type="button"
            @click="onSelectProvider('')"
            :class="[
              'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
              provider === '' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            ]"
          >
            Semua Penyedia ({{ alternatives.length }})
          </button>
          <button
            v-for="p in providersList"
            :key="p"
            type="button"
            @click="onSelectProvider(p)"
            :class="[
              'px-3.5 py-1.5 rounded-lg text-xs font-semibold transition',
              provider === p ? 'bg-indigo-600 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
            ]"
          >
            {{ p }}
          </button>
        </div>
      </div>
    </div>

    <!-- Catatan Konversi Nilai (Aturan Riset Jurnal) -->
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4 text-indigo-900 text-xs space-y-2">
      <div class="font-bold flex items-center gap-1.5 text-indigo-950">
        <Info class="w-4 h-4 text-indigo-600" />
        Pedoman Pengisian dan Konversi Nilai Spesifikasi:
      </div>
      <ul class="list-disc list-inside space-y-1 text-indigo-900/90 pl-1">
        <li><strong>Rentang spesifikasi:</strong> Jika berupa rentang (misal Rp12.900–29.900), nilai diambil <em>nilai tengahnya</em>.</li>
        <li><strong>Kapasitas Unlimited:</strong> Dikonversi menjadi <strong>200 GB</strong> (kapasitas tertinggi non-unlimited pada dataset).</li>
        <li><strong>CPU Virtual:</strong> Shared Hosting = <strong>0.5</strong> vCPU, 1 core = <strong>1</strong> vCPU, 2 core / 2 vCPU = <strong>2</strong> vCPU.</li>
        <li><strong>Penyimpanan Otomatis:</strong> Nilai yang Anda ubah pada kolom input akan langsung disimpan secara otomatis ke server dan memperbarui kalkulasi WASPAS.</li>
      </ul>
    </div>

    <!-- Tabel Matriks Penilaian Alternatif -->
    <SectionCard
      :title="`Tabel Matriks Keputusan X (${filteredAlternatives.length} Alternatif &times; ${criteria.length} Kriteria)`"
      description="Ubah nilai langsung pada setiap sel numerik kriteria. Nilai otomatis divalidasi dan disimpan ke database."
    >
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
            <tr>
              <th class="px-4 py-3.5 w-16">Kode</th>
              <th class="px-4 py-3.5 w-52">Paket Hosting</th>
              <th class="px-4 py-3.5 w-32">Penyedia</th>
              <th
                v-for="c in criteria"
                :key="c.code"
                class="px-4 py-3.5 text-right w-36"
              >
                {{ c.code }}: {{ c.name }}
                <span
                  :class="[
                    'block text-[10px] font-normal',
                    c.type === 'cost' ? 'text-amber-600' : 'text-emerald-600'
                  ]"
                >
                  {{ c.unit }} ({{ c.type === 'cost' ? 'Cost' : 'Benefit' }})
                </span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="alt in filteredAlternatives"
              :key="alt.id"
              class="hover:bg-slate-50 transition"
            >
              <!-- Kode -->
              <td class="px-4 py-3.5 font-mono font-bold text-xs text-indigo-600">
                {{ alt.code }}
              </td>

              <!-- Nama Paket -->
              <td class="px-4 py-3.5 font-semibold text-slate-900 text-xs">
                {{ alt.name }}
                <div class="text-[11px] text-slate-400 font-normal">{{ alt.category }}</div>
              </td>

              <!-- Penyedia -->
              <td class="px-4 py-3.5 text-xs text-slate-600">
                <span class="inline-flex px-2 py-0.5 rounded bg-slate-100 font-medium">
                  {{ alt.provider }}
                </span>
              </td>

              <!-- Kolom Nilai Kriteria Dinamis -->
              <td
                v-for="c in criteria"
                :key="c.code"
                class="px-4 py-3.5 text-right"
              >
                <input
                  type="text"
                  :value="formatInput(alt.scores?.[c.code])"
                  @change="onScoreChange(alt.id, c.code, $event.target.value)"
                  class="w-24 px-2 py-1 text-right text-xs font-mono border border-slate-200 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                  :class="{ 'border-rose-400 bg-rose-50/40': isInvalidScore(alt.scores?.[c.code]) }"
                  :title="`${c.name} (${c.unit})`"
                />
              </td>
            </tr>

            <tr v-if="!filteredAlternatives.length">
              <td :colspan="3 + criteria.length" class="px-6 py-8 text-center text-slate-400">
                Tidak ada data alternatif yang tersedia.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tombol Lanjut ke Halaman Hasil Penilaian -->
      <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <span class="text-xs text-slate-500">
          Penilaian telah terisi. Lihat hasil perangkingan, nilai Q1, Q2, Q akhir, serta grafik perbandingannya.
        </span>
        <router-link
          to="/hasil-penilaian"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition flex items-center justify-center gap-2 self-start sm:self-auto shadow-xs"
        >
          <span>Lanjut ke Hasil Penilaian & Grafik (Gambar 7)</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </SectionCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSpk } from '../composables/useSpk';
import SectionCard from '../components/SectionCard.vue';
import {
  Edit,
  RotateCcw,
  ListFilter,
  Info,
  CheckCircle2,
  AlertCircle,
  BookOpen,
  ArrowRight,
} from 'lucide-vue-next';

const {
  alternatives,
  criteria,
  provider,
  loading,
  providersList,
  fetchAlternatives,
  fetchCriteria,
  fetchWaspas,
  setProvider,
  updateScores,
  resetToDefault,
  loadJournalDataset,
} = useSpk();

const statusMessage = ref('');
const statusType = ref('success');

function showFeedback(msg, type = 'success') {
  statusMessage.value = msg;
  statusType.value = type;
  setTimeout(() => {
    statusMessage.value = '';
  }, 4000);
}

const filteredAlternatives = computed(() => {
  if (!provider.value) return alternatives.value;
  return alternatives.value.filter((a) => a.provider === provider.value);
});

function onSelectProvider(p) {
  setProvider(p);
}

function formatInput(val) {
  if (val === undefined || val === null || isNaN(val)) return '';
  return val.toString();
}

function isInvalidScore(val) {
  if (val === null || val === undefined || val === '') return false;
  const num = Number(val);
  return isNaN(num) || num < 0;
}

async function onScoreChange(altId, critCode, rawValue) {
  const cleanedStr = rawValue.replace(',', '.').trim();

  if (cleanedStr === '') {
    return;
  }

  const num = parseFloat(cleanedStr);
  if (isNaN(num) || num < 0) {
    showFeedback(`Nilai untuk ${critCode} harus berupa angka positif`, 'error');
    return;
  }

  const targetAlt = alternatives.value.find((a) => a.id === altId);
  if (!targetAlt) return;

  const currentScores = { ...(targetAlt.scores || {}) };
  currentScores[critCode] = num;

  try {
    await updateScores(altId, currentScores);
    showFeedback(`Nilai ${critCode} untuk ${targetAlt.name} berhasil diperbarui`);
  } catch (err) {
    showFeedback('Gagal menyimpan perubahan nilai', 'error');
  }
}

async function handleReset() {
  if (confirm('Reset seluruh nilai dan kriteria ke dataset default (20 paket hosting)?')) {
    try {
      await resetToDefault();
      showFeedback('Data penilaian berhasil dikembalikan ke default');
    } catch (err) {
      showFeedback('Gagal mereset data', 'error');
    }
  }
}

async function handleLoadJournalCase() {
  if (confirm('Muat dataset kasus uji penelitian jurnal Bab 3.5 (5 Hosting A-E)?')) {
    try {
      await loadJournalDataset();
      showFeedback('Dataset penelitian jurnal Bab 3.5 (Hosting A-E) berhasil dimuat');
    } catch (err) {
      showFeedback('Gagal memuat dataset jurnal', 'error');
    }
  }
}

onMounted(() => {
  fetchCriteria();
  fetchAlternatives();
  fetchWaspas();
});
</script>
