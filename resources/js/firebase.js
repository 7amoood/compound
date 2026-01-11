import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import axios from 'axios';

// These should be in your .env / shareable via Inertia props
const firebaseConfig = {
    apiKey: "AIzaSyCf6DUu21s4qhbD2fLkSypCRDkSnCbzQCo",
    authDomain: "garden-city-compound.firebaseapp.com",
    projectId: "garden-city-compound",
    storageBucket: "garden-city-compound.firebasestorage.app",
    messagingSenderId: "358088420520",
    appId: "1:358088420520:web:7cdd88dc6d98d2c6f0269d"
};

const app = initializeApp(firebaseConfig);
const messaging = getMessaging(app);

export async function requestNotificationPermission() {
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        console.warn('Push notifications are not supported in this browser');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            // Wait for existing service worker or register new one
            let registration = await navigator.serviceWorker.getRegistration('/service-worker.js');

            if (!registration) {
                registration = await navigator.serviceWorker.register('/service-worker.js');
            }

            // Ensure the service worker is active before getting the token
            if (registration.installing) {
                await new Promise((resolve) => {
                    registration.installing.addEventListener('statechange', (e) => {
                        if (e.target.state === 'activated') resolve();
                    });
                });
            }

            // Double check compatibility
            if (!window.isSecureContext) {
                console.error('FCM requires a secure context (HTTPS or localhost)');
                return;
            }

            const token = await getToken(messaging, {
                vapidKey: 'BC6EtV4AwjM5kQj92nuF9b31rzDE1Pj7htPT6ESqa8yGD10Uln3QHrApWppge6Uexd9UsQFCdbtXvoDBR-D4-6E',
                serviceWorkerRegistration: registration
            });

            if (token) {
                await axios.post('/api/notifications/save-token', { token });
                console.log('FCM Token saved');
            }
        }
    } catch (error) {
        // If it's a "push service error", it often means the browser can't reach Google services 
        // or the environment is not considered secure.
        console.error('Notification permission error:', error);
    }
}

export { messaging };
