<template>
  <div v-if="result && result.ok && top1" class="bg-gradient-to-br from-indigo-950 via-indigo-900 to-slate-900 text-white rounded-2xl p-6 sm:p-8 shadow-xl border border-indigo-500/20 space-y-6">
    <!-- Header Insight -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-indigo-700/50">
      <div class="space-y-1">
        <div class="inline-flex items-center gap-2">
          <span class="bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold px-3 py-1 rounded-full flex items-center gap-1.5">
            <Sparkles class="w-3.5 h-3.5 text-emerald-400" />
            Decision Insight & Kesimpulan
          </span>
          <span class="text-xs text-indigo-300 font-mono">
            Evaluasi Multi-Kriteria WASPAS (&lambda; = {{ lambda }})
          </span>
        </div>
        <h2 class="text-xl sm:text-2xl font-black tracking-tight text-white">
          Kesimpulan Rekomendasi Pemilihan Hosting
        </h2>
        <p class="text-xs sm:text-sm text-indigo-200">
          Interpretasi cerdas berdasarkan perankingan gabungan aditif (SAW) dan multiplikatif (WP).
        </p>
      </div>

      <!-- Tombol Salin Ringkasan untuk Jurnal -->
      <button
        type="button"
        @click="copyJournalSummary"
        class="self-start sm:self-auto bg-white/10 hover:bg-white/20 text-indigo-100 hover:text-white border border-white/20 px-4 py-2.5 rounded-xl text-xs font-semibold transition flex items-center gap-2 shadow-sm"
      >
        <Copy class="w-4 h-4" />
        <span>{{ copySuccess ? 'Tersalin ke Clipboard!' : 'Salin untuk Naskah Jurnal' }}</span>
      </button>
    </div>

    <!-- 1. Narasi Rekomendasi Utama -->
    <div class="bg-white/5 rounded-xl p-5 border border-white/10 space-y-3">
      <div class="flex items-center gap-2 text-emerald-300 font-bold text-sm">
        <Award class="w-5 h-5 text-emerald-400" />
        Pilihan Utama: {{ top1.name }} (Peringkat #1)
      </div>

      <p class="text-xs sm:text-sm text-indigo-100 leading-relaxed">
        Berdasarkan kalkulasi WASPAS, <strong>{{ top1.name }}</strong> dari <strong>{{ top1.provider }}</strong> menempati urutan teratas dengan nilai preferensi akhir <strong class="text-emerald-300 font-mono">Q = {{ top1.q?.toFixed(4) }}</strong>. 
        {{ mainReasonNarrative }}
      </p>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-2 text-xs">
        <div class="bg-white/5 p-2.5 rounded-lg border border-white/5">
          <span class="text-indigo-300 block text-[11px]">Harga Bulanan (Cost):</span>
          <span class="font-bold text-white font-mono">Rp {{ formatCurrency(top1.x?.C1) }}</span>
        </div>
        <div class="bg-white/5 p-2.5 rounded-lg border border-white/5">
          <span class="text-indigo-300 block text-[11px]">Storage (Benefit):</span>
          <span class="font-bold text-white font-mono">{{ top1.x?.C2 }} GB</span>
        </div>
        <div class="bg-white/5 p-2.5 rounded-lg border border-white/5">
          <span class="text-indigo-300 block text-[11px]">RAM (Benefit):</span>
          <span class="font-bold text-white font-mono">{{ top1.x?.C3 }} GB</span>
        </div>
        <div class="bg-white/5 p-2.5 rounded-lg border border-white/5">
          <span class="text-indigo-300 block text-[11px]">CPU (Benefit):</span>
          <span class="font-bold text-white font-mono">{{ top1.x?.C4 }} vCPU</span>
        </div>
      </div>
    </div>

    <!-- 2. Kategori Pilihan Cerdas (Smart Picks) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Best Value / Budget -->
      <div class="bg-white/5 rounded-xl p-4 border border-white/10 space-y-2">
        <div class="flex items-center gap-2 text-amber-300 font-bold text-xs uppercase tracking-wider">
          <TrendingUp class="w-4 h-4 text-amber-400" />
          Pilihan Paling Hemat (Best Budget)
        </div>
        <div v-if="bestBudget" class="space-y-1">
          <div class="font-bold text-white text-sm">{{ bestBudget.name }}</div>
          <div class="text-[11px] text-indigo-200">
            Biaya: <span class="font-mono text-emerald-300 font-semibold">Rp {{ formatCurrency(bestBudget.x?.C1) }}</span>/bulan
          </div>
          <p class="text-[11px] text-indigo-300 leading-snug">
            Pilihan ideal untuk website pemula atau UMKM dengan biaya termurah di jajaran 5 besar (Rank #{{ bestBudget.rank }}).
          </p>
        </div>
      </div>

      <!-- Best Performance -->
      <div class="bg-white/5 rounded-xl p-4 border border-white/10 space-y-2">
        <div class="flex items-center gap-2 text-cyan-300 font-bold text-xs uppercase tracking-wider">
          <Cpu class="w-4 h-4 text-cyan-400" />
          Performa Maksimal (Best Specs)
        </div>
        <div v-if="bestPerformance" class="space-y-1">
          <div class="font-bold text-white text-sm">{{ bestPerformance.name }}</div>
          <div class="text-[11px] text-indigo-200">
            Kapasitas: <span class="font-mono text-cyan-300 font-semibold">{{ bestPerformance.x?.C3 }} GB RAM / {{ bestPerformance.x?.C4 }} vCPU</span>
          </div>
          <p class="text-[11px] text-indigo-300 leading-snug">
            Paket dengan alokasi memori dan inti prosesor tertinggi untuk komputasi berat dan traffic tinggi.
          </p>
        </div>
      </div>

      <!-- Sensitivitas Lambda -->
      <div class="bg-white/5 rounded-xl p-4 border border-white/10 space-y-2">
        <div class="flex items-center gap-2 text-purple-300 font-bold text-xs uppercase tracking-wider">
          <Sliders class="w-4 h-4 text-purple-400" />
          Pengaruh Parameter &lambda; = {{ lambda }}
        </div>
        <p class="text-[11px] text-indigo-200 leading-snug">
          {{ lambdaExplanation }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useSpk } from '../composables/useSpk';
import {
  Sparkles,
  Award,
  Copy,
  Cpu,
  TrendingUp,
  Sliders,
} from 'lucide-vue-next';

const { result, lambda, top1, top5 } = useSpk();

const copySuccess = ref(false);

function formatCurrency(val) {
  if (val === null || val === undefined) return '-';
  return Number(val).toLocaleString('id-ID');
}

// Narasi Mengapa Rank 1 Menang
const mainReasonNarrative = computed(() => {
  if (!top1.value) return '';
  const alt = top1.value;
  return `Paket ini unggul karena memiliki efisiensi skor kombinasi SAW (Q¹ = ${alt.saw?.toFixed(4)}) dan model perkalian WP (Q² = ${alt.wp?.toFixed(4)}). Dengan kapasitas storage ${alt.x?.C2} GB dan prosesor ${alt.x?.C4} vCPU pada harga Rp ${formatCurrency(alt.x?.C1)}, paket ini memberikan rasio performa terhadap biaya yang paling optimal dibandingkan 19 alternatif lainnya.`;
});

// Pilihan Paling Hemat di antara Top 5
const bestBudget = computed(() => {
  if (!top5.value || !top5.value.length) return null;
  // Cari di antara top 5 yang harga C1 paling rendah
  const sortedByPrice = [...top5.value].sort((a, b) => (a.x?.C1 || 0) - (b.x?.C1 || 0));
  return sortedByPrice[0] || null;
});

// Pilihan Performa RAM & CPU Tertinggi di antara semua alternatif
const bestPerformance = computed(() => {
  if (!result.value?.alternatives || !result.value.alternatives.length) return null;
  const sortedBySpecs = [...result.value.alternatives].sort((a, b) => {
    const specB = (b.x?.C3 || 0) * 2 + (b.x?.C4 || 0) * 3;
    const specA = (a.x?.C3 || 0) * 2 + (a.x?.C4 || 0) * 3;
    return specB - specA;
  });
  return sortedBySpecs[0] || null;
});

// Penjelasan Pengaruh Nilai Lambda
const lambdaExplanation = computed(() => {
  const l = parseFloat(lambda.value);
  if (l === 0.5) {
    return 'Nilai λ = 0.5 menyeimbangkan kontribusi model penjumlahan linier (SAW 50%) dan model perkalian pangkat (WP 50%), menghasilkan perankingan yang netral dan stabil sesuai acuan jurnal.';
  } else if (l > 0.5) {
    return `Nilai λ = ${l.toFixed(1)} lebih dominan ke model SAW (${(l * 100).toFixed(0)}%). Keunggulan tinggi pada salah satu kriteria spesifikasi dapat menutupi kriteria harga yang lebih mahal.`;
  } else {
    return `Nilai λ = ${l.toFixed(1)} lebih dominan ke model WP (${((1 - l) * 100).toFixed(0)}%). Alternatif yang memiliki nilai rendah pada suatu kriteria akan menerima penalti geometris yang lebih ketat.`;
  }
});

// Salin teks ringkasan untuk kutipan naskah riset
async function copyJournalSummary() {
  if (!top1.value) return;
  const text = `Berdasarkan hasil analisis Sistem Pendukung Keputusan menggunakan metode WASPAS (Weighted Aggregated Sum Product Assessment) dengan parameter lambda λ = ${lambda.value} terhadap paket web hosting di Indonesia, paket "${top1.value.name}" dari penyedia ${top1.value.provider} memperoleh nilai preferensi tertinggi Q = ${top1.value.q?.toFixed(4)} (Peringkat #1). Paket ini dinilai memiliki perpaduan terbaik antara efisiensi biaya (Rp ${formatCurrency(top1.value.x?.C1)}/bulan) dan spesifikasi teknis (${top1.value.x?.C2} GB Storage, ${top1.value.x?.C3} GB RAM, dan ${top1.value.x?.C4} vCPU).`;

  try {
    await navigator.clipboard.writeText(text);
    copySuccess.value = true;
    setTimeout(() => {
      copySuccess.value = false;
    }, 3000);
  } catch (err) {
    alert('Gagal menyalin teks secara otomatis. Silakan salin manual.');
  }
}
</script>
