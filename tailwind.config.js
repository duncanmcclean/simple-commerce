module.exports = {
    ...require('./vendor/statamic/cms/tailwind.config.js'),

    content: [
        './resources/**/*.{html,js,vue,blade.php}',
        './tests/**/*.{html,vue,blade.php}',
    ],
}