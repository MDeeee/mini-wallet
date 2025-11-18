import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: false,
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            get Authorization() {
                const token = localStorage.getItem('token');
                return token ? `Bearer ${token}` : '';
            },
            Accept: 'application/json',
        },
    },
});
