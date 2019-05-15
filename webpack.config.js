var Encore = require('@symfony/webpack-encore');
webpack = require("webpack");
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const UglifyJsPlugin = require('uglifyjs-webpack-plugin');

var fs = require('fs');
Encore
    .setOutputPath('web/build')
    .setPublicPath('/build')
    .addEntry('search', __dirname + '/src/MBH/Bundle/SearchBundle/Resources/private/search/index.ts')
    .addEntry('azovskyResults', __dirname + '/src/MBH/Bundle/OnlineBookingBundle/Resources/private/results/index.ts')
    .addEntry('azovskySpecials', __dirname + '/src/MBH/Bundle/OnlineBookingBundle/Resources/private/specials/index.ts')
    .enableSassLoader()
    .enableLessLoader()
    .enableSourceMaps(!Encore.isProduction())
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableVersioning()
    .enableTypeScriptLoader(function (typeScriptConfigOptions) {
        typeScriptConfigOptions.appendTsSuffixTo = [/\.vue$/];
        typeScriptConfigOptions.configFile = __dirname + '/src/MBH/Bundle/SearchBundle/Resources/private/search/tsconfig.json';
        typeScriptConfigOptions.transpileOnly = true;
    })
    .enableVueLoader()
    .addPlugin(
        new webpack.ContextReplacementPlugin(
            /node_modules\/moment\/locale/, /ru|en-gb/
        )
    )
    .addPlugin(new VueLoaderPlugin())
    .enableBuildNotifications(false)

;

let config = Encore.getWebpackConfig();

if(!Encore.isProduction()) {
    fs.writeFile("fakewebpack.config.js", "module.exports = "+JSON.stringify(config), function(err) {
        if(err) {
            return console.log(err);
        }
        console.log("fakewebpack.config.js written");
    });
}

/*https://github.com/symfony/webpack-encore/issues/139#issuecomment-323585179*/
if (Encore.isProduction()) {
    config.plugins = config.plugins.filter(
        plugin => !(plugin instanceof webpack.optimize.UglifyJsPlugin)
    );
    config.plugins.push(new UglifyJsPlugin());
}


module.exports = config;