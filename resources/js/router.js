import { createRouter, createWebHistory } from 'vue-router';
import Beranda from './views/Beranda.vue';
import DataAlternatif from './views/DataAlternatif.vue';
import Kriteria from './views/Kriteria.vue';
import Penilaian from './views/Penilaian.vue';
import HasilPenilaian from './views/HasilPenilaian.vue';

const routes = [
    {
        path: '/',
        name: 'beranda',
        component: Beranda,
    },
    {
        path: '/data-alternatif',
        alias: '/alternatif',
        name: 'data-alternatif',
        component: DataAlternatif,
    },
    {
        path: '/kriteria',
        name: 'kriteria',
        component: Kriteria,
    },
    {
        path: '/penilaian',
        name: 'penilaian',
        component: Penilaian,
    },
    {
        path: '/hasil-penilaian',
        alias: '/perhitungan',
        name: 'hasil-penilaian',
        component: HasilPenilaian,
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/',
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

export default router;
