/**
 * API Client untuk berkomunikasi dengan endpoint backend HOSTPAS
 */

const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
};

async function handleResponse(response) {
    const data = await response.json();
    if (!response.ok) {
        const error = new Error(data.message || 'Terjadi kesalahan pada permintaan API');
        error.errors = data.errors || {};
        error.status = response.status;
        throw error;
    }
    return data;
}

export const api = {
    /**
     * Mengambil daftar kriteria
     */
    async getCriteria() {
        const res = await fetch('/api/criteria', { headers });
        return handleResponse(res);
    },

    /**
     * Memperbarui bobot dan atribut kriteria
     */
    async updateCriteria(criteria) {
        const res = await fetch('/api/criteria', {
            method: 'PUT',
            headers,
            body: JSON.stringify(criteria),
        });
        return handleResponse(res);
    },

    /**
     * Mengambil daftar alternatif beserta nilainya (opsional filter provider)
     */
    async getAlternatives(provider = '') {
        const url = provider ? `/api/alternatives?provider=${encodeURIComponent(provider)}` : '/api/alternatives';
        const res = await fetch(url, { headers });
        return handleResponse(res);
    },

    /**
     * Menambahkan alternatif hosting baru untuk simulasi
     */
    async addAlternative(payload) {
        const res = await fetch('/api/alternatives', {
            method: 'POST',
            headers,
            body: JSON.stringify(payload),
        });
        return handleResponse(res);
    },

    /**
     * Memperbarui informasi data alternatif hosting
     */
    async updateAlternative(id, payload) {
        const res = await fetch(`/api/alternatives/${id}`, {
            method: 'PUT',
            headers,
            body: JSON.stringify(payload),
        });
        return handleResponse(res);
    },

    /**
     * Menghapus alternatif hosting
     */
    async deleteAlternative(id) {
        const res = await fetch(`/api/alternatives/${id}`, {
            method: 'DELETE',
            headers,
        });
        return handleResponse(res);
    },

    /**
     * Memperbarui skor/nilai kriteria suatu alternatif
     */
    async updateScores(alternativeId, scores) {
        const res = await fetch(`/api/alternatives/${alternativeId}/scores`, {
            method: 'PUT',
            headers,
            body: JSON.stringify(scores),
        });
        return handleResponse(res);
    },

    /**
     * Mengambil seluruh hasil langkah kalkulasi WASPAS
     */
    async getWaspas(lambda = 0.5, provider = '') {
        const params = new URLSearchParams();
        if (lambda !== undefined && lambda !== null) params.append('lambda', lambda);
        if (provider) params.append('provider', provider);

        const res = await fetch(`/api/waspas?${params.toString()}`, { headers });
        return handleResponse(res);
    },

    /**
     * Reset seluruh data alternatif, kriteria, dan nilai ke bawaan HostingSeeder
     */
    async resetDefault() {
        const res = await fetch('/api/reset', {
            method: 'POST',
            headers,
        });
        return handleResponse(res);
    },

    /**
     * Memuat dataset kasus uji penelitian jurnal Bab 3.5 (Hosting A-E)
     */
    async loadJournalCase() {
        const res = await fetch('/api/load-journal-case', {
            method: 'POST',
            headers,
        });
        return handleResponse(res);
    },
};

export default api;
