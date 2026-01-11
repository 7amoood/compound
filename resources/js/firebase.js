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
            const swUrl = '/service-worker.js';
            let registration = await navigator.serviceWorker.getRegistration(swUrl);

            if (!registration) {
                registration = await navigator.serviceWorker.register(swUrl);
            }

            // Wait for the service worker to be fully ready and active
            await navigator.serviceWorker.ready;

            // Double check secure context (FCM Requirement)
            if (!window.isSecureContext) {
                console.error('FCM requires a secure context (HTTPS or localhost). Current origin: ', window.location.origin);
                return;
            }

            const token = await getToken(messaging, {
                vapidKey: 'BC6EtV4AwjM5kQj92nuF9b31rzDE1Pj7htPT6ESqa8yGD10Uln3QHrApWppge6Uexd9UsQFCdbtXvoDBR-D4-6E',
                serviceWorkerRegistration: registration
            });

            if (token) {
                await axios.post('/api/notifications/save-token', { token });
                console.log('FCM Token saved successfully');
            }
        }
    } catch (error) {
        if (error.message && error.message.includes('push service error')) {
            console.error('FCM Error: Browser cannot connect to Push Service. This happens if you are not using HTTPS/localhost or if Google services are blocked.');
        } else {
            console.error('Notification permission error:', error);
        }
    }
}

export { messaging };
