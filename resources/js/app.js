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
  "iconClass":"toast-custom-icon",
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
        const audio = new Audio('/sounds/preview.mp3'); // Path to your sound file in the public folder
        audio.play();
        toastr["success"](e.title);
    });

     // Listen for private notifications using the logged-in user's ID
    if (window.userId) {
        window.Echo.private(`private-notify.${window.userId}`)
            .listen('PrivateNotify', (e) => {
                // console.log('Private notification received');
                const audio = new Audio('/sounds/preview.mp3'); // Path to your sound file in the public folder
                audio.play();

                toastr["success"]("You have a new private notification!");
            });
    }

} else {
    console.log("Echo is not defined");
}


