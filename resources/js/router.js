import { createRouter, createWebHistory } from 'vue-router';
import Beranda from './views/Beranda.vue';
import Kriteria from './views/Kriteria.vue';
import AlternatifNilai from './views/AlternatifNilai.vue';
import Perhitungan from './views/Perhitungan.vue';

const routes = [
    {
        path: '/',
        name: 'beranda',
        component: Beranda,
    },
    {
        path: '/kriteria',
        name: 'kriteria',
        component: Kriteria,
    },
    {
        path: '/alternatif',
        name: 'alternatif',
        component: AlternatifNilai,
    },
    {
        path: '/perhitungan',
        name: 'perhitungan',
        component: Perhitungan,
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
