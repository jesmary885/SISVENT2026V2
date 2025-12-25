import './bootstrap';
import Chart from 'chart.js/auto';

import Alpine from 'alpinejs';
import focus from '@alpinejs/focus';
window.Alpine = Alpine;

window.Chart = Chart;
Alpine.plugin(focus);

Alpine.start();
