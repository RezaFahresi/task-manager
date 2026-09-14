import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    primary: '#4361EE',
                    'primary-hover': '#3751D4',
                    'primary-subtle': '#EEF2FF',
                    secondary: '#8D99AE',
                    dark: '#2B2D42',
                    canvas: '#EDF2F4',
                    surface: '#FFFFFF',
                    border: '#DFE5EC',
                    success: '#10B981',
                    warning: '#F59E0B',
                    danger: '#EF233C',
                },
                canvas: '#EDF2F4',
                surface: '#FFFFFF',
                border: '#DFE5EC',
                primary: '#4361EE',
                secondary: '#8D99AE',
                dark: '#2B2D42',
                success: '#10B981',
                warning: '#F59E0B',
                danger: '#EF233C',
            },
        },
    },

    plugins: [forms],
};
