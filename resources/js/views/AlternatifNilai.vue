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
          Daftar paket hosting yang dievaluasi. Anda dapat mengubah nilai secara langsung pada tabel atau menambahkan paket kustom untuk disimulasikan.
        </p>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-wrap items-center gap-2 self-start sm:self-auto">
        <button
          type="button"
          @click="openAddModal"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-sm px-4 py-2.5 rounded-xl transition flex items-center gap-2 shadow-sm"
        >
          <Plus class="w-4 h-4" />
          <span>Uji Paket Hosting Baru</span>
        </button>

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
              <th class="px-4 py-3.5 w-12 text-center">Aksi</th>
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

              <!-- Aksi Hapus -->
              <td class="px-4 py-3.5 text-center">
                <button
                  type="button"
                  @click="handleDeleteAlternative(alt)"
                  title="Hapus paket alternatif"
                  class="text-slate-400 hover:text-rose-600 transition p-1 rounded-md hover:bg-rose-50"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </SectionCard>

    <!-- Modal Tambah Alternatif Kustom -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="bg-indigo-100 text-indigo-700 p-2 rounded-xl">
              <Plus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800">Simulasi Paket Hosting Baru</h3>
              <p class="text-xs text-slate-500">Tambahkan paket hosting untuk diuji perankingannya.</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeModal"
            class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none p-1"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="submitAddAlternative" class="space-y-3.5 text-xs">
          <!-- Nama Paket -->
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Nama Paket Hosting <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="newAlt.name"
              placeholder="Contoh: Biznet Cloud Max"
              required
              class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>

          <!-- Penyedia & Kategori -->
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Penyedia Hosting <span class="text-rose-500">*</span></label>
              <input
                type="text"
                v-model="newAlt.provider"
                placeholder="Contoh: Biznet Gio"
                required
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Kategori Layanan</label>
              <select
                v-model="newAlt.category"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              >
                <option value="Shared Hosting">Shared Hosting</option>
                <option value="Cloud Hosting">Cloud Hosting</option>
                <option value="Virtual Private Server">Virtual Private Server (VPS)</option>
                <option value="Dedicated Server">Dedicated Server</option>
              </select>
            </div>
          </div>

          <!-- Spesifikasi Asli (Teks) -->
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Spesifikasi Asli (Keterangan)</label>
            <input
              type="text"
              v-model="newAlt.spec_raw"
              placeholder="Contoh: 100 GB SSD / 2 GB RAM / 2 Core / Rp50.000"
              class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            />
          </div>

          <!-- 4 Nilai Kriteria -->
          <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
            <span class="block font-bold text-slate-700 text-xs uppercase tracking-wider">
              Nilai Numerik Kriteria:
            </span>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">
                  C1: Harga per bulan (Rp) <span class="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  v-model.number="newAlt.scores.C1"
                  placeholder="Contoh: 45000"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">
                  C2: Storage (GB) <span class="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="0.1"
                  v-model.number="newAlt.scores.C2"
                  placeholder="Contoh: 100"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">
                  C3: RAM (GB) <span class="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="0.1"
                  v-model.number="newAlt.scores.C3"
                  placeholder="Contoh: 2"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                />
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">
                  C4: CPU (vCPU / Core) <span class="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="0.5"
                  v-model.number="newAlt.scores.C4"
                  placeholder="Contoh: 2"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                />
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Modal -->
          <div class="pt-2 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-semibold transition"
            >
              Batal
            </button>

            <button
              type="submit"
              :disabled="isSubmitting"
              class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2 rounded-xl transition shadow-sm flex items-center gap-2 disabled:opacity-50"
            >
              <Save class="w-4 h-4" />
              <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan & Hitung Ranking' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
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
  Plus,
  Trash2,
  Save,
} from 'lucide-vue-next';

const {
  alternatives,
  provider,
  providersList,
  fetchAlternatives,
  setProvider,
  updateScores,
  addAlternative,
  deleteAlternative,
  resetToDefault,
} = useSpk();

const isUpdating = ref(false);
const isSubmitting = ref(false);
const statusMessage = ref('');
const statusType = ref('success');
const showModal = ref(false);

const newAlt = ref({
  name: '',
  provider: 'Hostinger',
  category: 'Shared Hosting',
  spec_raw: '',
  scores: {
    C1: '',
    C2: '',
    C3: '',
    C4: '',
  },
});

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

function openAddModal() {
  newAlt.value = {
    name: '',
    provider: 'Hostinger',
    category: 'Shared Hosting',
    spec_raw: '',
    scores: {
      C1: '',
      C2: '',
      C3: '',
      C4: '',
    },
  };
  showModal.value = true;
}

function closeModal() {
  showModal.value = false;
}

async function submitAddAlternative() {
  try {
    isSubmitting.value = true;
    await addAlternative(newAlt.value);
    showModal.value = false;
    statusType.value = 'success';
    statusMessage.value = `Paket "${newAlt.value.name}" berhasil ditambahkan ke dalam perankingan!`;
    setTimeout(() => {
      statusMessage.value = '';
    }, 4000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = err.message || 'Gagal menambahkan paket hosting.';
  } finally {
    isSubmitting.value = false;
  }
}

async function handleDeleteAlternative(alt) {
  if (!confirm(`Apakah Anda yakin ingin menghapus paket "${alt.name}" (${alt.code})?`)) {
    return;
  }
  try {
    isUpdating.value = true;
    await deleteAlternative(alt.id);
    statusType.value = 'success';
    statusMessage.value = `Paket "${alt.name}" berhasil dihapus.`;
    setTimeout(() => {
      statusMessage.value = '';
    }, 3000);
  } catch (err) {
    statusType.value = 'error';
    statusMessage.value = 'Gagal menghapus alternatif.';
  } finally {
    isUpdating.value = false;
  }
}

async function onScoreChange(altId, criterionCode, rawValue) {
  const normalizedValue = rawValue.replace(',', '.').trim();
  const parsed = normalizedValue === '' ? null : parseFloat(normalizedValue);

  if (parsed !== null && (isNaN(parsed) || parsed < 0)) {
    statusType.value = 'error';
    statusMessage.value = 'Nilai harus berupa angka positif atau nol.';
    return;
  }

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
    statusMessage.value = 'Seluruh data alternatif berhasil direset ke nilai awal seeder!';
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
