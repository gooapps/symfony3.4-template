var Encore = require('@symfony/webpack-encore');

Encore
    //Ruta de los archivos compilados
    .setOutputPath('web/build/')

    .setPublicPath('web/build')

    .setManifestKeyPrefix('build/')

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    // Preset de react para que webpack sepa que compilar
    .enableReactPreset()

    // $0 = nombre que tendrán los archivos compilados por webpack
    // $1 = ruta del componente principal de React
    .addEntry('compiled', './assets/js/component.js')

    // uncomment if you use Sass/SCSS files
    //.enableSassLoader()

    ;

module.exports = Encore.getWebpackConfig();