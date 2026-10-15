<template>
  <div class="space-y-6">
    <!-- Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <div class="inline-flex items-center gap-2 mb-1">
          <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
            Gambar 4 &bull; Implementasi 3.4
          </span>
        </div>
        <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
          <Layers class="w-6 h-6 text-indigo-600" />
          Halaman Data Alternatif Hosting
        </h1>
        <p class="text-sm text-slate-500 mt-1">
          Kelola data paket hosting yang dievaluasi dalam SPK WASPAS: menambah paket baru, mengubah data, dan menghapus paket hosting.
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
          <span>Tambah Alternatif</span>
        </button>

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
          title="Reset kembali ke 20 paket hosting default"
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
          Filter Berdasarkan Penyedia:
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
            Semua ({{ alternatives.length }})
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

    <!-- Tabel Data Alternatif (CRUD) -->
    <SectionCard
      :title="`Daftar Data Alternatif Hosting (${filteredAlternatives.length} Paket)`"
      description="Menampilkan data paket hosting yang terdaftar dalam sistem. Gunakan tombol Ubah untuk mengedit informasi paket atau tombol Hapus untuk mengeluarkan paket dari evaluasi."
    >
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table class="w-full text-left border-collapse text-sm">
          <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold tracking-wider">
            <tr>
              <th class="px-4 py-3.5 w-16">Kode</th>
              <th class="px-4 py-3.5 w-52">Nama Paket Hosting</th>
              <th class="px-4 py-3.5 w-36">Penyedia Hosting</th>
              <th class="px-4 py-3.5 w-44">Kategori Layanan</th>
              <th class="px-4 py-3.5">Spesifikasi Asli</th>
              <th class="px-4 py-3.5 w-28 text-center">Aksi</th>
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
              <td class="px-4 py-3.5 font-semibold text-slate-900 text-sm">
                {{ alt.name }}
              </td>

              <!-- Penyedia -->
              <td class="px-4 py-3.5 text-xs text-slate-600">
                <span class="inline-flex px-2.5 py-1 rounded-md bg-slate-100 text-slate-800 font-medium">
                  {{ alt.provider }}
                </span>
              </td>

              <!-- Kategori Layanan -->
              <td class="px-4 py-3.5 text-xs text-slate-600">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                  {{ alt.category || 'Shared Hosting' }}
                </span>
              </td>

              <!-- Spesifikasi Mentah / Keterangan -->
              <td class="px-4 py-3.5 text-xs text-slate-600 font-mono">
                {{ alt.spec_raw || '-' }}
              </td>

              <!-- Aksi: Edit & Hapus -->
              <td class="px-4 py-3.5 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button
                    type="button"
                    @click="openEditModal(alt)"
                    title="Ubah data alternatif"
                    class="text-indigo-600 hover:text-indigo-800 hover:bg-indigo-50 p-1.5 rounded-lg transition"
                  >
                    <Edit3 class="w-4 h-4" />
                  </button>
                  <button
                    type="button"
                    @click="handleDeleteAlternative(alt)"
                    title="Hapus paket alternatif"
                    class="text-slate-400 hover:text-rose-600 hover:bg-rose-50 p-1.5 rounded-lg transition"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>

            <tr v-if="!filteredAlternatives.length">
              <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                Tidak ada data alternatif yang sesuai dengan filter.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Tombol Lanjut ke Halaman Penilaian -->
      <div class="mt-4 pt-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <span class="text-xs text-slate-500">
          Setelah data alternatif selesai dikelola, lanjutkan untuk mengisi atau memverifikasi nilai pada matriks keputusan.
        </span>
        <router-link
          to="/penilaian"
          class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition flex items-center justify-center gap-2 self-start sm:self-auto shadow-xs"
        >
          <span>Lanjut ke Halaman Penilaian (Gambar 6)</span>
          <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>
    </SectionCard>

    <!-- Modal Tambah Alternatif Baru -->
    <div
      v-if="showAddModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="bg-indigo-100 text-indigo-700 p-2 rounded-xl">
              <Plus class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800">Tambah Data Alternatif Hosting</h3>
              <p class="text-xs text-slate-500">Masukkan informasi paket hosting baru untuk dievaluasi.</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeModals"
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
                <option value="Advanced Web">Advanced Web</option>
                <option value="Budget Hosting">Budget Hosting</option>
                <option value="Business Hosting">Business Hosting</option>
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

          <!-- Input Nilai Kriteria Dinamis -->
          <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-200 space-y-2">
            <span class="block font-bold text-slate-700 text-xs uppercase tracking-wider">
              Nilai Numerik Awal Kriteria:
            </span>
            <div class="grid grid-cols-2 gap-3">
              <div v-for="c in criteria" :key="c.code">
                <label class="block text-[11px] font-semibold text-slate-600 mb-0.5">
                  {{ c.code }}: {{ c.name }} ({{ c.unit }}) <span class="text-rose-500">*</span>
                </label>
                <input
                  type="number"
                  min="0"
                  step="any"
                  v-model.number="newAlt.scores[c.code]"
                  :placeholder="`Nilai ${c.code}`"
                  required
                  class="w-full px-3 py-1.5 border border-slate-200 rounded-lg font-mono text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white"
                />
              </div>
            </div>
          </div>

          <!-- Tombol Aksi Modal Tambah -->
          <div class="pt-2 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModals"
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
              <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan Alternatif' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Ubah / Edit Data Alternatif -->
    <div
      v-if="showEditModal"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
      <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 max-w-lg w-full p-6 space-y-4 animate-in fade-in zoom-in-95 duration-150">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <div class="flex items-center gap-2">
            <div class="bg-amber-100 text-amber-700 p-2 rounded-xl">
              <Edit3 class="w-5 h-5" />
            </div>
            <div>
              <h3 class="text-base font-bold text-slate-800">Ubah Data Alternatif Hosting</h3>
              <p class="text-xs text-slate-500">Edit informasi paket hosting <strong class="text-indigo-600">({{ editAlt.code }})</strong>.</p>
            </div>
          </div>
          <button
            type="button"
            @click="closeModals"
            class="text-slate-400 hover:text-slate-600 text-xl font-bold leading-none p-1"
          >
            &times;
          </button>
        </div>

        <form @submit.prevent="submitEditAlternative" class="space-y-3.5 text-xs">
          <!-- Nama Paket -->
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Nama Paket Hosting <span class="text-rose-500">*</span></label>
            <input
              type="text"
              v-model="editAlt.name"
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
                v-model="editAlt.provider"
                required
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block font-semibold text-slate-700 mb-1">Kategori Layanan</label>
              <input
                type="text"
                v-model="editAlt.category"
                class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
              />
            </div>
          </div>

          <!-- Spesifikasi Asli (Teks) -->
          <div>
            <label class="block font-semibold text-slate-700 mb-1">Spesifikasi Asli (Keterangan)</label>
            <textarea
              rows="2"
              v-model="editAlt.spec_raw"
              class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            ></textarea>
          </div>

          <!-- Tombol Aksi Modal Ubah -->
          <div class="pt-2 flex items-center justify-end gap-2">
            <button
              type="button"
              @click="closeModals"
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
              <span>{{ isSubmitting ? 'Memperbarui...' : 'Simpan Perubahan' }}</span>
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
  CheckCircle2,
  AlertCircle,
  Plus,
  Trash2,
  Save,
  Edit3,
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
  addAlternative,
  updateAlternative,
  deleteAlternative,
  resetToDefault,
  loadJournalDataset,
} = useSpk();

const statusMessage = ref('');
const statusType = ref('success');
const isSubmitting = ref(false);

const showAddModal = ref(false);
const showEditModal = ref(false);

const newAlt = ref({
  name: '',
  provider: '',
  category: 'Shared Hosting',
  spec_raw: '',
  scores: {},
});

const editAlt = ref({
  id: null,
  code: '',
  name: '',
  provider: '',
  category: '',
  spec_raw: '',
});

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

function openAddModal() {
  const initialScores = {};
  criteria.value.forEach((c) => {
    initialScores[c.code] = 0;
  });
  newAlt.value = {
    name: '',
    provider: provider.value || 'Hostinger',
    category: 'Shared Hosting',
    spec_raw: '',
    scores: initialScores,
  };
  showAddModal.value = true;
}

function openEditModal(alt) {
  editAlt.value = {
    id: alt.id,
    code: alt.code,
    name: alt.name,
    provider: alt.provider,
    category: alt.category,
    spec_raw: alt.spec_raw,
  };
  showEditModal.value = true;
}

function closeModals() {
  showAddModal.value = false;
  showEditModal.value = false;
}

async function submitAddAlternative() {
  try {
    isSubmitting.value = true;
    await addAlternative({
      name: newAlt.value.name,
      provider: newAlt.value.provider,
      category: newAlt.value.category,
      spec_raw: newAlt.value.spec_raw,
      scores: newAlt.value.scores,
    });
    closeModals();
    showFeedback('Data alternatif baru berhasil ditambahkan');
  } catch (err) {
    showFeedback(err.message || 'Gagal menambahkan alternatif', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

async function submitEditAlternative() {
  try {
    isSubmitting.value = true;
    await updateAlternative(editAlt.value.id, {
      name: editAlt.value.name,
      provider: editAlt.value.provider,
      category: editAlt.value.category,
      spec_raw: editAlt.value.spec_raw,
    });
    closeModals();
    showFeedback('Data alternatif berhasil diperbarui');
  } catch (err) {
    showFeedback(err.message || 'Gagal mengubah alternatif', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

async function handleDeleteAlternative(alt) {
  if (confirm(`Yakin ingin menghapus alternatif ${alt.name} (${alt.code})?`)) {
    try {
      await deleteAlternative(alt.id);
      showFeedback(`Alternatif ${alt.name} berhasil dihapus`);
    } catch (err) {
      showFeedback('Gagal menghapus alternatif', 'error');
    }
  }
}

async function handleReset() {
  if (confirm('Reset seluruh data ke pengaturan default (20 paket hosting)?')) {
    try {
      await resetToDefault();
      showFeedback('Data berhasil dikembalikan ke 20 paket hosting default');
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
