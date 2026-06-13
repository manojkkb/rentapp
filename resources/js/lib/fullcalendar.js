import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import timeGridPlugin from '@fullcalendar/timegrid';

const plugins = [dayGridPlugin, listPlugin, timeGridPlugin];

window.FullCalendar = {
    Calendar(el, options) {
        return new Calendar(el, {
            plugins,
            ...options,
        });
    },
};
