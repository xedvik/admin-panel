const preset = require('../../../../vendor/filament/filament/tailwind.config.preset');

module.exports = {
    presets: [preset],
    content: [
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
        './vendor/filament/**/*.blade.php',
        './vendor/filament/**/*.js',
        './vendor/filament/**/*.php',
        './app/Filament/**/*.php',
        './resources/css/filament/admin/theme.css',
    ],
};
