import './bootstrap';
// import CanvasJS from '@canvasjs/charts';
import '@fortawesome/fontawesome-free/js/all.js';

import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true
});

Echo.channel('account-status')
    .listen('AccountStatusUpdated', e => {
        console.log('hello')
    })


