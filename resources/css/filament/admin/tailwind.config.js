import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Clusters/Finances/**/*.php',
        './resources/views/filament/clusters/finances/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
