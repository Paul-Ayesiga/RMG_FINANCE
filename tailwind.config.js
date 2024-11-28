import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    
    darkMode: 'class',
    content: [
		'./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
		 './storage/framework/views/*.php',
		 './resources/views/**/*.blade.php',
		 "./vendor/robsontenorio/mary/src/View/Components/**/*.php",
         './vendor/spatie/laravel-support-bubble/config/**/*.php',
         './vendor/spatie/laravel-support-bubble/resources/views/**/*.blade.php',
           "./vendor/wireui/wireui/src/*.php",

            "./vendor/wireui/wireui/ts/**/*.ts",

            "./vendor/wireui/wireui/src/WireUi/**/*.php",

            "./vendor/wireui/wireui/src/Components/**/*.php",
	],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
		require("daisyui"),
        require("./vendor/wireui/wireui/tailwind.config.js")
	],
};
