const path = require('path');
const isDev = require('isdev');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');
const { optimize } = require('webpack');

const ExtractTextPlugin = require('extract-text-webpack-plugin');
const extractCSS = new ExtractTextPlugin({
  filename: '[name].css?[contenthash]',
  allChunks: true,
});
const extractSASS = new ExtractTextPlugin({
  filename: '[name].css?[contenthash]',
  allChunks: true,
});

const config = {
  entry: {
    vendor: './src/AppBundle/Resources/public/js/vendor.js',
    main: './src/AppBundle/Resources/public/js/main.js',
    'application-form': './src/AppBundle/Resources/public/js/application-form.js',
    'build-form': './src/AppBundle/Resources/public/js/build-form.js',
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'web/assets'),
    publicPath: '/assets/',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['es2015'],
          },
        },
      },
      {
        test: /\.css$/,
        use: isDev
          ? [
            {
              loader: 'style-loader',
              options: {sourceMap: true},
            },
            {
              loader: 'css-loader',
              options: {sourceMap: true},
            },
          ]
          : extractCSS.extract({
            fallback: 'style-loader',
            use: {
              loader: 'css-loader',
            },
          }),
      },
      {
        test: /\.scss$/,
        use: isDev
          ? [
            {
              loader: 'style-loader',
              options: {sourceMap: true},
            },
            {
              loader: 'css-loader',
              options: {sourceMap: true},
            },
            {
              loader: 'sass-loader',
              options: {sourceMap: true},
            },
          ]
          : extractSASS.extract({
            fallback: 'style-loader',
            use: [
              {
                loader: 'css-loader',
              },
              {
                loader: 'sass-loader',
              },
            ],
          }),
      },
      {
        test: /\.(png|gif|jpg|svg|woff2?|ttf|eot)$/,
        use: [
          'url-loader?limit=10000',
        ],
      },
    ],
  },
  plugins: [
    new CopyWebpackPlugin([
      {
        from: './node_modules/jquery/dist/jquery.min.js',
        to: 'jquery.js',
      },
      {
        from: './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js',
        to: 'fos-js-routing.js',
      },
      {
        from: './vendor/willdurand/js-translation-bundle/Bazinga/Bundle/JsTranslationBundle/Resources/public/js/translator.min.js',
        to: 'bazinga-translator.js',
      },
    ]),
    new optimize.CommonsChunkPlugin({ name: 'common', filename: 'common.js' }),
  ],
  externals: {
    jquery: 'jQuery',
    'fos-js-routing': 'Routing',
    'bazinga-translator': 'Translator',
  },
  resolve: {
    modules: [
      path.resolve(__dirname, 'node_modules'),
    ],
  },
  devtool: 'cheap-module-source-map',
  devServer: {
    host: '0.0.0.0',
    disableHostCheck: true,
  },
};

if (!isDev) {
  config.plugins.push(new UglifyJSPlugin());
  config.plugins.push(extractCSS);
  config.plugins.push(extractSASS);
}

module.exports = config;
