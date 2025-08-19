import defaultTheme from 'tailwindcss/defaultTheme';
const colors = require('tailwindcss/colors');

/** @type {import('tailwindcss').Config} */
export default {
    // important: true,
    darkMode: 'class',
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './vendor/robsontenorio/mary/src/View/Components/**/*.php',
        './vendor/spatie/laravel-support-bubble/config/**/*.php',
        './vendor/spatie/laravel-support-bubble/resources/views/**/*.blade.php',
        './vendor/wireui/wireui/src/*.php',
        './vendor/wireui/wireui/ts/**/*.ts',
        './vendor/wireui/wireui/src/WireUi/**/*.php',
        './vendor/wireui/wireui/src/Components/**/*.php',
    ],
    safelist: [
    'hover:text-white',
    'hover:bg-primary-500',
    'focus:bg-primary-100',
    'dark:hover:bg-secondary-700',
    'dark:focus:bg-secondary-700',
    'dark:focus:bg-secondary-800',
  ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {

            },
            animation: {
                emphasis: 'emphasis 1s ease-in-out infinite',
            },
            keyframes: {
                emphasis: {
                    '0%, 100%': { transform: 'scale(1)' },
                    '50%': { transform: 'scale(1.1)' },
                },
            },
        },
    },
    plugins: [
        require('daisyui'),
        require('./vendor/wireui/wireui/tailwind.config.js'),

    ],
};
