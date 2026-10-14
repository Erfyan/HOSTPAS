<template>
  <div class="space-y-6">
    <!-- Header Page Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Layers class="w-6 h-6 text-indigo-600" />
          Alternatif Hosting & Nilai Kriteria
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Daftar 20 paket hosting dari 4 penyedia terkemuka. Nilai kriteria dapat disunting secara langsung pada tabel di bawah.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button
          type="button"
          @click="handleReset"
          :disabled="isUpdating"
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
          Filter Penyedia Hosting:
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
            Semua Penyedia (20)
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
        Pedoman Konversi Spesifikasi Mentah ke Angka (Sesuai Jurnal):
      </div>
      <ul class="list-disc list-inside space-y-1 text-indigo-900/90 pl-1">
        <li><strong>Rentang angka:</strong> Jika spesifikasi berupa rentang (misal Rp12.900–29.900), nilai diambil <em>nilai tengahnya</em>.</li>
        <li><strong>Storage Unlimited:</strong> Dikonversi menjadi <strong>200 GB</strong> (kapasitas storage terbesar non-unlimited pada dataset).</li>
        <li><strong>CPU Virtual:</strong> Shared Hosting = <strong>0.5</strong> vCPU, 1 core = <strong>1</strong> vCPU, 2 core / 2 vCPU = <strong>2</strong> vCPU.</li>
      </ul>
    </div>

    <!-- Tabel Nilai Alternatif -->
    <SectionCard
      :title="`Data Matriks Keputusan (${filteredAlternatives.length} Alternatif)`"
      description="Ubah nilai langsung pada kolom input numerik. Nilai otomatis disimpan dan memperbarui kalkulasi WASPAS."
    >
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
            <tr>
              <th class="px-4 py-3.5 w-16">Kode</th>
              <th class="px-4 py-3.5 w-44">Paket Hosting</th>
              <th class="px-4 py-3.5 w-32">Penyedia</th>
              <th class="px-4 py-3.5">Spesifikasi Asli</th>
              <th class="px-4 py-3.5 w-32 text-right">
                C1: Harga
                <span class="block text-[10px] text-amber-600 font-normal">Rp (Cost)</span>
              </th>
              <th class="px-4 py-3.5 w-28 text-right">
                C2: Storage
                <span class="block text-[10px] text-emerald-600 font-normal">GB (Benefit)</span>
              </th>
              <th class="px-4 py-3.5 w-28 text-right">
                C3: RAM
                <span class="block text-[10px] text-emerald-600 font-normal">GB (Benefit)</span>
              </th>
              <th class="px-4 py-3.5 w-28 text-right">
                C4: CPU
                <span class="block text-[10px] text-emerald-600 font-normal">vCPU (Benefit)</span>
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

              <!-- Spesifikasi Mentah -->
              <td class="px-4 py-3.5 text-xs text-slate-500 font-mono">
                {{ alt.spec_raw }}
              </td>

              <!-- C1: Harga -->
              <td class="px-4 py-3.5 text-right">
                <input
                  type="text"
                  :value="formatInput(alt.scores?.C1)"
                  @change="onScoreChange(alt.id, 'C1', $event.target.value)"
                  class="w-24 px-2 py-1 text-right text-xs font-mono border border-slate-200 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  :class="{ 'border-rose-400 bg-rose-50/30': isInvalidScore(alt.scores?.C1) }"
                  title="Harga per bulan dalam Rupiah"
                />
              </td>

              <!-- C2: Storage -->
              <td class="px-4 py-3.5 text-right">
                <input
                  type="text"
                  :value="formatInput(alt.scores?.C2)"
                  @change="onScoreChange(alt.id, 'C2', $event.target.value)"
                  class="w-20 px-2 py-1 text-right text-xs font-mono border border-slate-200 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  :class="{ 'border-rose-400 bg-rose-50/30': isInvalidScore(alt.scores?.C2) }"
                  title="Storage dalam GB"
                />
              </td>

              <!-- C3: RAM -->
              <td class="px-4 py-3.5 text-right">
                <input
                  type="text"
                  :value="formatInput(alt.scores?.C3)"
                  @change="onScoreChange(alt.id, 'C3', $event.target.value)"
                  class="w-20 px-2 py-1 text-right text-xs font-mono border border-slate-200 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  :class="{ 'border-rose-400 bg-rose-50/30': isInvalidScore(alt.scores?.C3) }"
                  title="RAM dalam GB"
                />
              </td>

              <!-- C4: CPU -->
              <td class="px-4 py-3.5 text-right">
                <input
                  type="text"
                  :value="formatInput(alt.scores?.C4)"
                  @change="onScoreChange(alt.id, 'C4', $event.target.value)"
                  class="w-20 px-2 py-1 text-right text-xs font-mono border border-slate-200 rounded-md focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                  :class="{ 'border-rose-400 bg-rose-50/30': isInvalidScore(alt.scores?.C4) }"
                  title="CPU dalam Core / vCPU"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </SectionCard>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useSpk } from '../composables/useSpk';
import SectionCard from '../components/SectionCard.vue';
import {
  Layers,
  RotateCcw,
  ListFilter,
  Info,
  CheckCircle2,
  AlertCircle,
} from 'lucide-vue-next';

const {
  alternatives,
  provider,
  providersList,
  fetchAlternatives,
  setProvider,
  updateScores,
  resetToDefault,
} = useSpk();

const isUpdating = ref(false);
const statusMessage = ref('');
const statusType = ref('success');

const filteredAlternatives = computed(() => {
  if (!provider.value) return alternatives.value;
  return alternatives.value.filter((alt) => alt.provider === provider.value);
});

function formatInput(val) {
  if (val === null || val === undefined || val === '') return '';
  return val.toString();
}

function isInvalidScore(val) {
  if (val === null || val === undefined || val === '') return false;
  const num = parseFloat(val);
  return isNaN(num) || num < 0;
}

function onSelectProvider(selected) {
  setProvider(selected);
}

async function onScoreChange(altId, criterionCode, rawValue) {
  // Parsing angka, mengizinkan koma sebagai titik
  const normalizedValue = rawValue.replace(',', '.').trim();
  const parsed = normalizedValue === '' ? null : parseFloat(normalizedValue);

  if (parsed !== null && (isNaN(parsed) || parsed < 0)) {
    statusType.value = 'error';
    statusMessage.value = 'Nilai harus berupa angka positif atau nol.';
    return;
  }

  // Cari alternatif yang bersangkutan
  const targetAlt = alternatives.value.find((a) => a.id === altId);
  if (!targetAlt) return;

  const currentScores = { ...targetAlt.scores };
  currentScores[criterionCode] = parsed;

  try {
    isUpdating.value = true;
    await updateScores(altId, currentScores);
    statusType.value = 'success';
    statusMessage.value = `Nilai ${criterionCode} untuk ${targetAlt.name} berhasil diperbarui!`;
    setTimeout(() => {
      statusMessage.value = '';
    }, 3000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = err.message || 'Gagal menyimpan nilai kriteria.';
  } finally {
    isUpdating.value = false;
  }
}

async function handleReset() {
  if (!confirm('Apakah Anda yakin ingin mereset seluruh data alternatif dan nilainya ke default?')) {
    return;
  }
  try {
    isUpdating.value = true;
    await resetToDefault();
    statusType.value = 'success';
    statusMessage.value = 'Seluruh data alternatif berhasil direset ke nilai awal!';
    setTimeout(() => {
      statusMessage.value = '';
    }, 4000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = 'Gagal mereset data default.';
  } finally {
    isUpdating.value = false;
  }
}

onMounted(async () => {
  if (!alternatives.value.length) {
    await fetchAlternatives();
  }
});
</script>
