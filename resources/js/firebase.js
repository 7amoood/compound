import { initializeApp } from "firebase/app";
import { getMessaging, getToken, onMessage } from "firebase/messaging";
import axios from 'axios';

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
    if (!('serviceWorker' in navigator) || !('Notification' in window) || !('PushManager' in window)) {
        console.warn('Notifications not supported');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            // Register/Ensure the service worker is active
            const registration = await navigator.serviceWorker.register('/service-worker.js', {
                scope: '/'
            });

            // Wait until it's ready
            await navigator.serviceWorker.ready;

            // Attempt to get the token
            const token = await getToken(messaging, {
                vapidKey: 'BC6EtV4AwjM5kQj92nuF9b31rzDE1Pj7htPT6ESqa8yGD10Uln3QHrApWppge6Uexd9UsQFCdbtXvoDBR-D4-6E',
                serviceWorkerRegistration: registration
            });

            if (token) {
                await axios.post('/api/notifications/save-token', { token });
                console.log('FCM Token generated successfully');
            }
        }
    } catch (error) {
        console.error('Detailed Notification Error:', error);

        // Check for common production issues
        if (error.code === 'messaging/permission-blocked') {
            console.error('Please unblock notifications in your browser settings.');
        } else if (error.message.includes('push service error')) {
            console.error('Network/Security error: The browser cannot reach the push service. Check your internet connection or firewall.');
        }
    }
}

export { messaging };
