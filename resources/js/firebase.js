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
    if (!('serviceWorker' in navigator) || !('Notification' in window)) {
        console.warn('Push is not supported');
        return;
    }

    try {
        const permission = await Notification.requestPermission();
        if (permission === 'granted') {
            console.log('Permission granted, setting up Service Worker...');

            // 1. Register or get existing registration
            const registration = await navigator.serviceWorker.register('/service-worker.js');

            // 2. Wait until it's absolutely ready and active
            await navigator.serviceWorker.ready;

            // Give it a tiny bit of time to settle (helps with some browsers)
            await new Promise(r => setTimeout(r, 500));

            console.log('Requesting FCM token...');
            const token = await getToken(messaging, {
                vapidKey: 'BC6EtV4AwjM5kQj92nuF9b31rzDE1Pj7htPT6ESqa8yGD10Uln3QHrApWppge6Uexd9UsQFCdbtXvoDBR-D4-6E',
                serviceWorkerRegistration: registration
            });

            if (token) {
                console.log('Token received:', token);
                await axios.post('/api/notifications/save-token', { token });
            }
        }
    } catch (error) {
        console.error('FCM Detailed Error:', error);

        // Final fallback diagnostics
        if (error.message.includes('push service error')) {
            alert('خطأ في خدمة الإشعارات: المتصفح لا يستطيع الاتصال بسيرفر جوجل. يرجى التأكد من عدم استخدام VPN أو مانع إعلانات، أو جرب متصفحاً آخر مثل Chrome أو Edge.');
        }
    }
}

export { messaging };
