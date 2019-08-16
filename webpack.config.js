var Encore = require('@symfony/webpack-encore');
var path = require("path");
var base_url = '';

if (!Encore.isProduction())
    base_url = '/Uca/web';

Encore
    .setOutputPath('web/build/')
    .setPublicPath(base_url + '/build')
    .addEntry('uca', './assets/uca.js')
    .addEntry('onReady', './assets/js/onReady.js')
    .addEntry('scheduler', './assets/js/scheduler/scheduler.js')
    .addEntry('calandar', './assets/js/calandar/main.js')
    .addEntry('cssGest', './assets/css/globalGest.scss')
    .addEntry('cssWeb', './assets/css/globalWeb.scss')
    .cleanupOutputBeforeBuild()
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(!Encore.isProduction())
    .splitEntryChunks()
    .configureSplitChunks(function (splitChunks) {
        splitChunks.minSize = 30000;
    })
    .copyFiles([
        {
            from: './assets/images',
            to: 'images/[path][name].[ext]',
            pattern: /\.(ico|png|jpg|jpeg|gif)$/
        },
        { from: './node_modules/ckeditor/', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false },
        { from: './node_modules/ckeditor/adapters', to: 'ckeditor/adapters/[path][name].[ext]' },
        { from: './node_modules/ckeditor/lang', to: 'ckeditor/lang/[path][name].[ext]' },
        { from: './node_modules/ckeditor/plugins', to: 'ckeditor/plugins/[path][name].[ext]' },
        { from: './node_modules/ckeditor/skins', to: 'ckeditor/skins/[path][name].[ext]' }
    ])

    .addLoader({ test: /\.json$/i, include: [require('path').resolve(__dirname, 'node_modules/ckeditor')], loader: 'raw-loader', type: 'javascript/auto' })


    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })
    .enableSingleRuntimeChunk()
    ;

config = Encore.getWebpackConfig();

module.exports = config;