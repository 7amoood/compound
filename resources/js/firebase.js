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
    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            // Use the unified service worker
            const registration = await navigator.serviceWorker.register('/service-worker.js');

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
        console.error('Notification permission error:', error);
    }
}

export { messaging };
