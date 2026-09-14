import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

let echoInstance = null;

try {
    const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;
    const reverbHost = import.meta.env.VITE_REVERB_HOST ?? (typeof window !== 'undefined' ? window.location.hostname : 'localhost');
    const reverbPort = import.meta.env.VITE_REVERB_PORT ? Number(import.meta.env.VITE_REVERB_PORT) : 8080;
    const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

    if (reverbKey) {
        echoInstance = new Echo({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: reverbHost,
            wsPort: reverbPort,
            wssPort: reverbPort,
            forceTLS: reverbScheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });

        if (echoInstance.connector && echoInstance.connector.pusher) {
            echoInstance.connector.pusher.connection.bind('error', (err) => {
                console.warn('Realtime connection error (fallback to database notifications):', err);
            });
        }
    }
} catch (error) {
    console.warn('Laravel Echo initialization skipped or failed:', error);
}

window.Echo = echoInstance;

export default echoInstance;
