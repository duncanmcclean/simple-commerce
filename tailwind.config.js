const statamic = require('./vendor/statamic/cms/tailwind.config.js');

module.exports = {
    darkMode: 'class',

    content: [
        './resources/js/components/**/*.vue',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        colors: {
            ...statamic.theme.colors,
        },
    },

    extend: {
        boxShadow: {
            ...statamic.theme.boxShadow,
        },
    },
}