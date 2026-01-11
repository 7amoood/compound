/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],
    darkMode: "class",
    theme: {
        extend: {
            colors: {
                "primary": "#137fec",
                "background-light": "#f6f7f8",
                "background-dark": "#101922",
                "surface-light": "#ffffff",
                "surface-dark": "#1a2632",
                "text-primary-light": "#0d141b",
                "text-primary-dark": "#ffffff",
                "text-secondary-light": "#4c739a",
                "text-secondary-dark": "#9aaebb",
            },
            fontFamily: {
                "display": ["Inter", "Cairo", "sans-serif"]
            },
            borderRadius: {
                "DEFAULT": "0.25rem",
                "lg": "0.5rem",
                "xl": "0.75rem",
                "2xl": "1rem",
                "3xl": "1.5rem",
                "full": "9999px"
            },
        },
    },
    plugins: [],
}

