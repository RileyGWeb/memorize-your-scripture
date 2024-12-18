import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                text: '#292D32',
                textLight: 'rgba(41, 45, 50, 0.5)',
                stroke: 'rgba(41, 45, 50, 0.1)',
                bg: '#F6F0F0',
                lightBlue: '#60E3D5',
                blue: '#05A0BF',
                darkBlue: '#0567A6',
            },
            fontSize: {
                base: '12px',
                lg: '16px',
            },
        },
    },

    plugins: [forms, typography],
};