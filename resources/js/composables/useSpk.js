import { ref, computed } from 'vue';
import api from '../api';

// Shared state di luar fungsi agar singleton antar-halaman
const criteria = ref([]);
const alternatives = ref([]);
const lambda = ref(parseFloat(localStorage.getItem('hostpas_lambda') || '0.5'));
const provider = ref('');
const result = ref(null);
const loading = ref(false);
const error = ref(null);

export function useSpk() {
    /**
     * Memuat data kriteria
     */
    async function fetchCriteria() {
        try {
            loading.value = true;
            error.value = null;
            const data = await api.getCriteria();
            criteria.value = data;
        } catch (err) {
            error.value = err.message || 'Gagal memuat kriteria';
        } finally {
            loading.value = false;
        }
    }

    /**
     * Memuat data alternatif (berdasarkan provider yang sedang aktif jika ada)
     */
    async function fetchAlternatives() {
        try {
            loading.value = true;
            error.value = null;
            const data = await api.getAlternatives(provider.value);
            alternatives.value = data;
        } catch (err) {
            error.value = err.message || 'Gagal memuat alternatif';
        } finally {
            loading.value = false;
        }
    }

    /**
     * Memuat seluruh langkah perhitungan WASPAS
     */
    async function fetchWaspas() {
        try {
            loading.value = true;
            error.value = null;
            const data = await api.getWaspas(lambda.value, provider.value);
            result.value = data;
        } catch (err) {
            error.value = err.message || 'Gagal memuat perhitungan WASPAS';
        } finally {
            loading.value = false;
        }
    }

    /**
     * Mengatur nilai lambda (0 - 1)
     */
    function setLambda(val) {
        const parsed = Math.max(0, Math.min(1, parseFloat(val) || 0));
        lambda.value = parsed;
        localStorage.setItem('hostpas_lambda', parsed.toString());
        fetchWaspas();
    }

    /**
     * Mengatur filter penyedia hosting
     */
    function setProvider(val) {
        provider.value = val;
        fetchAlternatives();
        fetchWaspas();
    }

    /**
     * Memperbarui bobot dan atribut kriteria
     */
    async function updateCriteria(updatedList) {
        try {
            loading.value = true;
            error.value = null;
            await api.updateCriteria(updatedList);
            await fetchCriteria();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal memperbarui kriteria';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Memperbarui nilai skor alternatif
     */
    async function updateScores(alternativeId, scores) {
        try {
            loading.value = true;
            error.value = null;
            await api.updateScores(alternativeId, scores);
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal memperbarui nilai';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Menambahkan paket hosting baru untuk simulasi
     */
    async function addAlternative(payload) {
        try {
            loading.value = true;
            error.value = null;
            await api.addAlternative(payload);
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal menambahkan paket hosting';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Menghapus paket hosting alternatif
     */
    async function deleteAlternative(id) {
        try {
            loading.value = true;
            error.value = null;
            await api.deleteAlternative(id);
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal menghapus alternatif';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Reset default ke database seeder
     */
    async function resetToDefault() {
        try {
            loading.value = true;
            error.value = null;
            await api.resetDefault();
            await fetchCriteria();
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal mereset data';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Memperbarui data alternatif hosting (nama, provider, category, spec_raw)
     */
    async function updateAlternative(id, payload) {
        try {
            loading.value = true;
            error.value = null;
            await api.updateAlternative(id, payload);
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal memperbarui data alternatif';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    /**
     * Memuat dataset penelitian jurnal Bab 3.5 (Hosting A-E)
     */
    async function loadJournalDataset() {
        try {
            loading.value = true;
            error.value = null;
            provider.value = '';
            await api.loadJournalCase();
            await fetchCriteria();
            await fetchAlternatives();
            await fetchWaspas();
            return true;
        } catch (err) {
            error.value = err.message || 'Gagal memuat dataset jurnal';
            throw err;
        } finally {
            loading.value = false;
        }
    }

    // Top 5 alternatif teratas
    const top5 = computed(() => {
        if (!result.value?.alternatives) return [];
        return [...result.value.alternatives]
            .sort((a, b) => a.rank - b.rank)
            .slice(0, 5);
    });

    // Peringkat 1
    const top1 = computed(() => {
        if (!result.value?.alternatives || result.value.alternatives.length === 0) return null;
        return [...result.value.alternatives].sort((a, b) => a.rank - b.rank)[0] || null;
    });

    // Total bobot saat ini
    const totalWeight = computed(() => {
        if (!criteria.value.length) return 0;
        return criteria.value.reduce((sum, c) => sum + parseFloat(c.weight || 0), 0);
    });

    // Validitas bobot (apakah total mendekati 1)
    const isWeightValid = computed(() => {
        return Math.abs(totalWeight.value - 1.0) <= 0.0001;
    });

    // Daftar penyedia hosting dinamis berdasarkan data alternatif
    const providersList = computed(() => {
        const set = new Set();
        alternatives.value.forEach(a => {
            if (a.provider) set.add(a.provider);
        });
        if (set.size === 0) return ['Hostinger', 'Niagahoster', 'Rumahweb', 'IDCloudHost'];
        return Array.from(set);
    });

    return {
        criteria,
        alternatives,
        lambda,
        provider,
        result,
        loading,
        error,
        top5,
        top1,
        totalWeight,
        isWeightValid,
        providersList,
        fetchCriteria,
        fetchAlternatives,
        fetchWaspas,
        setLambda,
        setProvider,
        updateCriteria,
        updateScores,
        addAlternative,
        updateAlternative,
        deleteAlternative,
        resetToDefault,
        loadJournalDataset,
    };
}
