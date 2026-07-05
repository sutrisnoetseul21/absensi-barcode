import './bootstrap';
import Chart from 'chart.js/auto';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

window.Chart = Chart;
window.FullCalendar = { Calendar, dayGridPlugin, interactionPlugin };
