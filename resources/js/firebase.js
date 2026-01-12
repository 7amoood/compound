import axios from 'axios';

const firebaseConfig = {
    apiKey: "AIzaSyCf6DUu21s4qhbD2fLkSypCRDkSnCbzQCo",
    authDomain: "garden-city-compound.firebaseapp.com",
    projectId: "garden-city-compound",
    storageBucket: "garden-city-compound.firebasestorage.app",
    messagingSenderId: "358088420520",
    appId: "1:358088420520:web:7cdd88dc6d98d2c6f0269d"
};

let app = null;
let messaging = null;
let onMessageUnsubscribe = null;

async function initFirebase() {
    if (!app) {
        const { initializeApp } = await import("firebase/app");
        const { getMessaging } = await import("firebase/messaging");
        app = initializeApp(firebaseConfig);
        messaging = getMessaging(app);
    }
    return messaging;
}

// Setup foreground message handler
async function setupOnMessageListener() {
    if (onMessageUnsubscribe) return; // Already set up

    const { onMessage } = await import("firebase/messaging");
    const messaging = await initFirebase();

    onMessageUnsubscribe = onMessage(messaging, (payload) => {
        console.log('Foreground message received:', payload);

        // Dispatch custom event for dashboards to listen
        window.dispatchEvent(new CustomEvent('fcm-message', {
            detail: payload
        }));
    });
}

export async function requestNotificationPermission() {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        console.warn('Push is not supported');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            // Lazy load Firebase only when needed
            const { getToken } = await import("firebase/messaging");
            const messaging = await initFirebase();

            // Register or get existing registration
            const registration = await navigator.serviceWorker.register('/service-worker.js');
            await navigator.serviceWorker.ready;

            // Small delay for browser settling
            await new Promise(r => setTimeout(r, 300));

            const token = await getToken(messaging, {
                vapidKey: 'BC6EtV4AwjM5kQj92nuF9b31rzDE1Pj7htPT6ESqa8yGD10Uln3QHrApWppge6Uexd9UsQFCdbtXvoDBR-D4-6E',
                serviceWorkerRegistration: registration
            });

            if (token) {
                // Only save if token changed or not saved in this session to reduce redundant requests
                if (sessionStorage.getItem('fcm_token') !== token) {
                    await axios.post('/api/notifications/save-token', { token });
                    sessionStorage.setItem('fcm_token', token);
                }
            }

            // Setup foreground message listener after successful token registration
            await setupOnMessageListener();
        }
    } catch (error) {
        console.error('FCM Error:', error);
    }
}

export { messaging };


