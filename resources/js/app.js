import './bootstrap';
import CanvasJS from '@canvasjs/charts';
import '@fortawesome/fontawesome-free/js/all.js';

import Echo from 'laravel-echo';

import toastr from 'toastr';
import 'toastr/build/toastr.min.css';

import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-top-right",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "3000",
  "hideDuration": "1000",
  "timeOut": "5000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}


if (window.Echo) {
    window.Echo.channel('system-notification')
        .listen('systemNotification', (e) => {
            console.log('system-notification');
            toastr["success"]("My name is Inigo Montoya. You killed my father. Prepare to die!")
        });
    window.Echo.private(`private-notify.${id}`)
        .listen('PrivateNotify', (e) => {
            console.log('private'); // Replace with your logic
            toastr["success"]("private");
        });

} else {
    console.log("Echo is not defined");
}


