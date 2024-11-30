import Echo from 'laravel-echo';

import toastr from 'toastr';

import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});

if (window.Echo) {
    window.Echo.channel('system-notification')
        .listen('systemNotification', (e) => {
            console.log('hello');
            toastr.info('Are you the 6 fingered man?');
        });
} else {
    console.log("Echo is not defined");
}

