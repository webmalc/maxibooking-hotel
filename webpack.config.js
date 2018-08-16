//Vue-loader!!
// https://github.com/symfony/webpack-encore/issues/311#issuecomment-384386874

var Encore = require('@symfony/webpack-encore');
webpack = require("webpack");
const VueLoaderPlugin = require('vue-loader/lib/plugin');
Encore
    .setOutputPath('web/build')
    .setPublicPath('/build')
    .addEntry('search', './src/MBH/Bundle/SearchBundle/Resources/private/search/index.ts')
    .enableLessLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableVersioning()
    // .enableTypeScriptLoader(function (config) {
    //
    // })
    // .enableTypeScriptLoader(function (typeScriptConfigOptions) {
    //     typeScriptConfigOptions.transpileOnly = true;
    //     typeScriptConfigOptions.configFile = 'src/MBH/Bundle/SearchBundle/Resources/private/search/tsconfig.json';
    // })
    .addLoader({
        test: /\.tsx?$/,
        loader: 'ts-loader',
        exclude: /node_modules/,
        options: {
            appendTsSuffixTo: [/\.vue$/],
            transpileOnly: true,
            configFile: 'src/MBH/Bundle/SearchBundle/Resources/private/search/tsconfig.json'
        }
    })
    .addLoader({
        test: /\.js/,
        loaders: ['babel-loader']
    })
    // .enableVueLoader()
    .addLoader({
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
            loaders: {
                ts: 'ts-loader'
            },
            esModule: true
        }
    })

    .addPlugin(
        new webpack.ContextReplacementPlugin(
            /node_modules\/moment\/locale/, /ru|en-gb/
        )
    )
    .addPlugin(new VueLoaderPlugin());

let config = Encore.getWebpackConfig();


config.resolve.extensions = ['.tsx', '.js', '.ts'];
config.resolve.alias = {
    "vue$": __dirname + '/src/MBH/Bundle/SearchBundle/Resources/private/search/vue-shims.d.ts'
}

;

module.exports = Encore.getWebpackConfig();
