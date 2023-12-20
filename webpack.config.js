var webpack = require('webpack')
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
var config = require('./config.json')
var pluginName = config.pluginName;

var cleanCSSLoader = {
    loader: 'clean-css-loader',
    options: {
        compatibility: 'ie9',
        level: 2,
        inline: ['remote']
    }
}

var sassLoader = {
    loader: 'sass-loader',
    options: {
        sourceMap: true
    }
}

var imageLoader = {
    loader: 'file-loader',
    options: {
        name: '[name].[ext]',
        publicPath: '../images/',
        outputPath: '../images/',
        useRelativePath: false,
    }
}

var fontLoader = {
    loader: 'file-loader',
    options: {
        name: '[name].[ext]',
        publicPath: '../fonts/',
        outputPath: '../fonts/',
        useRelativePath: false,
    }
}

module.exports = function (env) {
    return {
        entry: {
            main: "./" + pluginName + "/src/js/script.js",
            style: "./" + pluginName + "/src/scss/style.scss",
        },
        output: {
            path: __dirname + "/" + pluginName + "/assets/js",
            publicPath: "/wp-content/plugins/" + pluginName + "/assets/js/",
            filename: "[name].js",
            chunkFilename: '[name].[chunkhash].js'
        },
        mode: 'development',
        module: {
            rules: [
                {
                    test: /\.js?$/,
                    loader: 'babel-loader',
                    exclude: /node_modules/,
                    options: {
                        presets: [
                            ['@babel/env', { targets: { browsers: ['last 2 versions'] } }]
                        ]
                    }
                },
                {
                    test: /\.css$/,
                    use: [{ loader: MiniCssExtractPlugin.loader }, 'css-loader'],
                    exclude: [/node_modules/],
                },
                {
                    test: /\.scss$/,
                    use: [{ loader: MiniCssExtractPlugin.loader }, 'css-loader', cleanCSSLoader, 'resolve-url-loader', sassLoader],
                    exclude: [/node_modules/],
                },
                {
                    test: /\.png($|\?)|\.jpg($|\?)|\.jpeg($|\?)|\.gif($|\?)|\.svg($|\?)/,
                    use: [imageLoader],
                    exclude: [/fonts/]
                },
                {
                    test: /\.woff($|\?)|\.woff2($|\?)|\.ttf($|\?)|\.eot($|\?)|\.svg($|\?)/,
                    use: [fontLoader],
                    exclude: [/images/]
                }
            ]
        },
        plugins: [
            new webpack.ProvidePlugin({
                $: "jquery",
                jQuery: "jquery",
                'window.jQuery': 'jquery',
            }),
            new MiniCssExtractPlugin({
                filename: '../styles/[name].css',
            }),
            new CleanWebpackPlugin()
        ],
        externals: {
            jquery: 'jQuery',
            '@wordpress': 'wp'
        },
        optimization: {
            splitChunks: {
                cacheGroups: {
                    style: {
                        name: 'style',
                        test: /style\.scss$/,
                        chunks: 'all',
                        enforce: true
                    }
                }
            }
        }
    }
}