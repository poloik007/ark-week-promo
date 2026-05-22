const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');

module.exports = {
    mode: 'production',
    entry: {
        'plugin-style': './_dev/ark-style.scss',
    },
    output: {
        path: path.resolve(__dirname, './views/css/'),
        filename: 'empty.js',  //this js is actually required, but will be removed by the webpack plugin. remove-empty-scripts
    },
    module: {
        rules: [
            {
            test: /\.s[ac]ss$/i,
            use: [
                MiniCssExtractPlugin.loader, 
                'css-loader',
                'sass-loader',
            ],
            },
        ],
    },
    plugins: [
        new RemoveEmptyScriptsPlugin(), //used to remove the empty.js file.
        new MiniCssExtractPlugin({
            filename: 'ark-style.css',
        }),
    ],
    optimization: {
        //minify css for build production
        minimizer: [      
            '...', //use deafult minify
            new CssMinimizerPlugin(),
        ],
    },
};