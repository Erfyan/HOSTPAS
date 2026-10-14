<template>
  <div class="space-y-6">
    <!-- Header Page Info -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Sliders class="w-6 h-6 text-indigo-600" />
          Manajemen Kriteria & Pembobotan
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Ubah bobot preferensi dan jenis atribut (Benefit atau Cost). Sistem otomatis menormalkan bobot jika total bobot berbeda dari 1 (100%).
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center gap-2 self-start sm:self-auto">
        <button
          type="button"
          @click="handleReset"
          :disabled="isSaving"
          class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2 disabled:opacity-50"
        >
          <RotateCcw class="w-4 h-4" />
          <span>Reset Default</span>
        </button>

        <button
          type="button"
          @click="handleSave"
          :disabled="isSaving"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-sm disabled:opacity-50"
        >
          <Save class="w-4 h-4" />
          <span>{{ isSaving ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
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

    <!-- Warning jika bobot tidak 100% -->
    <div
      v-if="!isWeightValid"
      class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3"
    >
      <AlertTriangle class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" />
      <div class="text-xs text-amber-900 leading-relaxed">
        <strong>Peringatan Total Bobot:</strong> Total bobot saat ini adalah <strong>{{ (totalWeight * 100).toFixed(1) }}%</strong>. Sistem WASPAS akan otomatis menormalkan bobot (<span class="font-mono">w<sub>j</sub> / &Sigma;w</span>) saat kalkulasi, namun disarankan mengatur total bobot tepat 1.0 (100%) agar hasil sesuai publikasi jurnal.
      </div>
    </div>

    <!-- Tabel Kriteria -->
    <SectionCard
      title="Daftar Kriteria Penilaian"
      description="Silakan sesuaikan jenis atribut serta bobot masing-masing kriteria sesuai preferensi pengujian."
    >
      <template #action>
        <WeightBadge :valid="isWeightValid" :total="totalWeight" />
      </template>

      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
            <tr>
              <th class="px-6 py-3.5 w-20">Kode</th>
              <th class="px-6 py-3.5">Nama Kriteria</th>
              <th class="px-6 py-3.5 w-28">Satuan</th>
              <th class="px-6 py-3.5 w-44">Atribut</th>
              <th class="px-6 py-3.5 w-44 text-right">Bobot (0.01 - 1.00)</th>
              <th class="px-6 py-3.5 w-40 text-right">Bobot Normalisasi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 bg-white">
            <tr
              v-for="crit in editableCriteria"
              :key="crit.id"
              class="hover:bg-slate-50 transition"
            >
              <!-- Kode -->
              <td class="px-6 py-4 font-mono font-bold text-indigo-600 text-xs">
                {{ crit.code }}
              </td>

              <!-- Nama -->
              <td class="px-6 py-4 font-medium text-slate-800">
                {{ crit.name }}
              </td>

              <!-- Satuan -->
              <td class="px-6 py-4 text-slate-500 text-xs">
                <span class="bg-slate-100 px-2 py-1 rounded text-slate-700 font-mono">
                  {{ crit.unit }}
                </span>
              </td>

              <!-- Atribut Selector -->
              <td class="px-6 py-4">
                <select
                  v-model="crit.type"
                  class="px-3 py-1.5 border border-slate-200 rounded-lg text-xs font-semibold focus:ring-2 focus:ring-indigo-500 focus:outline-none transition w-full"
                  :class="crit.type === 'benefit' ? 'text-emerald-700 bg-emerald-50/50' : 'text-amber-700 bg-amber-50/50'"
                >
                  <option value="benefit">Benefit (Makin Besar Makin Baik)</option>
                  <option value="cost">Cost (Makin Kecil Makin Baik)</option>
                </select>
              </td>

              <!-- Input Bobot -->
              <td class="px-6 py-4 text-right">
                <div class="flex items-center justify-end gap-1">
                  <input
                    type="number"
                    step="0.05"
                    min="0.01"
                    max="1.0"
                    v-model.number="crit.weight"
                    class="w-24 px-3 py-1.5 border border-slate-200 rounded-lg text-right text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"
                    :class="{ 'border-rose-400 bg-rose-50/30': crit.weight <= 0 || crit.weight > 1 }"
                  />
                </div>
              </td>

              <!-- Bobot Normalisasi -->
              <td class="px-6 py-4 text-right font-mono text-xs text-slate-600">
                {{ calculateNormalized(crit.weight) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-50/70 border-t border-slate-200 font-semibold text-slate-700">
            <tr>
              <td colspan="4" class="px-6 py-3.5 text-right text-xs uppercase tracking-wider">
                Total Akumulasi Bobot:
              </td>
              <td class="px-6 py-3.5 text-right font-mono text-sm" :class="isWeightValid ? 'text-emerald-700' : 'text-rose-600'">
                {{ totalWeight.toFixed(2) }}
              </td>
              <td class="px-6 py-3.5 text-right font-mono text-xs text-slate-600">
                1.0000 (100%)
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </SectionCard>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import { useSpk } from '../composables/useSpk';
import SectionCard from '../components/SectionCard.vue';
import WeightBadge from '../components/WeightBadge.vue';
import {
  Sliders,
  RotateCcw,
  Save,
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
} from 'lucide-vue-next';

const {
  criteria,
  totalWeight,
  isWeightValid,
  fetchCriteria,
  updateCriteria,
  resetToDefault,
} = useSpk();

const editableCriteria = ref([]);
const isSaving = ref(false);
const statusMessage = ref('');
const statusType = ref('success');

function syncData() {
  editableCriteria.value = criteria.value.map((c) => ({
    id: c.id,
    code: c.code,
    name: c.name,
    unit: c.unit,
    type: c.type,
    weight: parseFloat(c.weight),
  }));
}

watch(criteria, () => {
  syncData();
}, { immediate: true });

function calculateNormalized(weight) {
  const sum = editableCriteria.value.reduce((acc, c) => acc + (parseFloat(c.weight) || 0), 0);
  if (sum <= 0) return '0.0000';
  return ((weight / sum)).toFixed(4);
}

async function handleSave() {
  try {
    isSaving.value = true;
    statusMessage.value = '';

    // Validasi dasar
    for (const item of editableCriteria.value) {
      if (item.weight <= 0 || item.weight > 1) {
        statusType.value = 'error';
        statusMessage.value = `Bobot untuk ${item.code} harus di antara 0.01 dan 1.00`;
        isSaving.value = false;
        return;
      }
    }

    await updateCriteria(editableCriteria.value);
    statusType.value = 'success';
    statusMessage.value = 'Perubahan kriteria dan bobot berhasil disimpan!';
    setTimeout(() => {
      statusMessage.value = '';
    }, 4000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = err.message || 'Gagal menyimpan perubahan kriteria';
  } finally {
    isSaving.value = false;
  }
}

async function handleReset() {
  if (!confirm('Apakah Anda yakin ingin mereset seluruh kriteria dan bobot ke nilai default?')) {
    return;
  }
  try {
    isSaving.value = true;
    await resetToDefault();
    statusType.value = 'success';
    statusMessage.value = 'Seluruh data berhasil dikembalikan ke nilai default!';
    setTimeout(() => {
      statusMessage.value = '';
    }, 4000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = 'Gagal mereset data default';
  } finally {
    isSaving.value = false;
  }
}

onMounted(async () => {
  if (!criteria.value.length) {
    await fetchCriteria();
  }
});
</script>
