const Encore = require('@symfony/webpack-encore');
let dotenv = require('dotenv');

dotenv.config();

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
// directory where compiled assets will be stored
    .setOutputPath(process.env.WEBPACK_OUTPUT_PATH)
    // public path used by the web server to access the output path
    .setPublicPath(process.env.WEBPACK_PUBLIC_PATH)
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

/*
 * ENTRY CONFIG
 *
 * Each entry will result in one JavaScript file (e.g. app.js)
 * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
 */

.addEntry('uca', './assets/uca.js')
    .addEntry('onReady', './assets/js/onReady.js')
    .addEntry('scheduler', './assets/js/scheduler/scheduler.js')
    .addEntry('creneau', './assets/js/creneau/main.js')
    .addEntry('calendar', './assets/js/calendar/main.js')

.addEntry('cssGest', './assets/css/globalGest.scss')
    .addEntry('cssWeb', './assets/css/globalWeb.scss')

// When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
.splitEntryChunks()

// will require an extra script tag for runtime.js
// but, you probably want this, unless you're building a single-page app
.enableSingleRuntimeChunk()

/*
 * FEATURE CONFIG
 *
 * Enable & configure other features below. For a full
 * list of features, see:
 * https://symfony.com/doc/current/frontend.html#adding-more-features
 */
.cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

.configureBabel((config) => {
    config.plugins.push('@babel/plugin-proposal-class-properties');
})

// enables @babel/preset-env polyfills
.configureBabelPresetEnv((config) => {
    config.useBuiltIns = 'usage';
    config.corejs = 3;
})

// enables Sass/SCSS support
.enableSassLoader()

// uncomment if you use TypeScript
//.enableTypeScriptLoader()

// uncomment if you use React
//.enableReactPreset()

// uncomment to get integrity="..." attributes on your script & link tags
// requires WebpackEncoreBundle 1.4 or higher
//.enableIntegrityHashes(Encore.isProduction())

// uncomment if you're having problems with a jQuery plugin
//.autoProvidejQuery()
.autoProvidejQuery()
    .autoProvideVariables({
        $: 'jquery',
        jQuery: 'jquery',
        'window.jQuery': 'jquery',
    })

.copyFiles([{
        from: './assets/images',
        to: 'images/[path][name].[ext]',
        pattern: /\.(ico|png|jpg|jpeg|gif)$/
    },
    { from: './vendor/friendsofsymfony/ckeditor-bundle/src/Resources/public', to: 'ckeditor/[path][name].[ext]', pattern: /\.(js|css)$/, includeSubdirectories: false },
    { from: './vendor/friendsofsymfony/ckeditor-bundle/src/Resources/public/adapters', to: 'ckeditor/adapters/[path][name].[ext]' },
    { from: './vendor/friendsofsymfony/ckeditor-bundle/src/Resources/public/lang', to: 'ckeditor/lang/[path][name].[ext]' },
    { from: './vendor/friendsofsymfony/ckeditor-bundle/src/Resources/public/plugins', to: 'ckeditor/plugins/[path][name].[ext]' },
    { from: './vendor/friendsofsymfony/ckeditor-bundle/src/Resources/public/skins', to: 'ckeditor/skins/[path][name].[ext]' }
])

// module.exports = Encore.getWebpackConfig();

let config = Encore.getWebpackConfig();
config.resolve = {
    fallback: {
        fs: false
    }
};

module.exports = config;