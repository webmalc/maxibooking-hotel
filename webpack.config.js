var Encore = require('@symfony/webpack-encore');
webpack = require("webpack");
const VueLoaderPlugin = require('vue-loader/lib/plugin');
var fs = require("fs");
Encore
    .setOutputPath('web/build')
    .setPublicPath('/build')
    .addEntry('search', __dirname + '/src/MBH/Bundle/SearchBundle/Resources/private/search/index.ts')
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
    .addPlugin(new VueLoaderPlugin());

let config = Encore.getWebpackConfig();
if(!Encore.isProduction()) {
    fs.writeFile("fakewebpack.config.js", "module.exports = "+JSON.stringify(config), function(err) {
        if(err) {
            return console.log(err);
        }
        console.log("fakewebpack.config.js written");
    });
}


module.exports = config;