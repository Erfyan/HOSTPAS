<template>
  <div class="relative w-full h-80">
    <canvas ref="canvasRef"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import {
  Chart,
  BarController,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
  alternatives: {
    type: Array,
    required: true,
  },
});

const canvasRef = ref(null);
let chartInstance = null;

function renderChart() {
  if (!canvasRef.value) return;

  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }

  // Urutkan berdasarkan rank untuk grafik yang informatif
  const sorted = [...props.alternatives].sort((a, b) => (a.rank || 0) - (b.rank || 0));

  const labels = sorted.map((alt) => alt.name);
  const data = sorted.map((alt) => (typeof alt.q === 'number' ? alt.q : 0));

  // Berikan warna khusus untuk ranking 1 (emerald) dan sisanya indigo
  const bgColors = sorted.map((alt) =>
    alt.rank === 1 ? 'rgba(16, 185, 129, 0.85)' : 'rgba(99, 102, 241, 0.8)'
  );
  const borderColors = sorted.map((alt) =>
    alt.rank === 1 ? 'rgb(16, 185, 129)' : 'rgb(99, 102, 241)'
  );

  chartInstance = new Chart(canvasRef.value, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          data,
          backgroundColor: bgColors,
          borderColor: borderColors,
          borderWidth: 1.5,
          borderRadius: 8,
          borderSkipped: false,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: 'rgba(15, 23, 42, 0.9)',
          titleFont: { family: 'Inter', size: 12, weight: 'bold' },
          bodyFont: { family: 'Inter', size: 12 },
          padding: 10,
          cornerRadius: 8,
          callbacks: {
            label(context) {
              const item = sorted[context.dataIndex];
              return `Nilai WASPAS (Q): ${context.parsed.y.toFixed(4)} (Peringkat #${item.rank})`;
            },
          },
        },
      },
      scales: {
        x: {
          grid: {
            display: false,
          },
          ticks: {
            font: { family: 'Inter', size: 11 },
            maxRotation: 45,
            minRotation: 20,
            color: '#64748b',
          },
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(226, 232, 240, 0.6)',
          },
          ticks: {
            font: { family: 'Inter', size: 11 },
            color: '#64748b',
            callback(val) {
              return Number(val).toFixed(2);
            },
          },
        },
      },
    },
  });
}

onMounted(() => {
  renderChart();
});

watch(
  () => props.alternatives,
  () => {
    renderChart();
  },
  { deep: true }
);

onBeforeUnmount(() => {
  if (chartInstance) {
    chartInstance.destroy();
    chartInstance = null;
  }
});
</script>
