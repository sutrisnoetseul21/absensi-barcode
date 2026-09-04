// Import Local Fonts (Plus Jakarta Sans & Inter)
import '@fontsource/plus-jakarta-sans/400.css';
import '@fontsource/plus-jakarta-sans/500.css';
import '@fontsource/plus-jakarta-sans/600.css';
import '@fontsource/plus-jakarta-sans/700.css';
import '@fontsource/plus-jakarta-sans/800.css';
import '@fontsource/inter/400.css';
import '@fontsource/inter/500.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';
import '@fontsource/inter/800.css';

// Import Local Icons (FontAwesome)
import '@fortawesome/fontawesome-free/css/all.min.css';

// Import Local Swiper & GLightbox Styles
import 'swiper/css';
import 'swiper/css/pagination';
import 'swiper/css/navigation';
import 'swiper/css/autoplay';
import 'glightbox/dist/css/glightbox.min.css';

import './bootstrap';
import Chart from 'chart.js/auto';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import Swiper from 'swiper';
import { Autoplay, Pagination, Navigation } from 'swiper/modules';
import GLightbox from 'glightbox';

// Automatically configure Swiper with essential modules
Swiper.use([Autoplay, Pagination, Navigation]);

// Expose to window for inline scripts & Alpine.js
window.Chart = Chart;
window.FullCalendar = { Calendar, dayGridPlugin, interactionPlugin };
window.Swiper = Swiper;
window.GLightbox = GLightbox;
