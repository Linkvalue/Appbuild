const __STATIC_ASSETS_BASE_PATH__ = 'assets';

const path = require('path');
const isDev = require('isdev');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const MinifyPlugin = require('babel-minify-webpack-plugin');
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
    path: path.resolve(__dirname, 'web/'+__STATIC_ASSETS_BASE_PATH__),
    publicPath: '/'+__STATIC_ASSETS_BASE_PATH__+'/',
  },
  module: {
    rules: [
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
        },
      },
      {
        test: /\.css$/,
        use: extractCSS.extract({
          fallback: {
            loader: 'style-loader',
          },
          use: [
            {
              loader: 'css-loader',
            },
          ],
        }),
      },
      {
        test: /\.scss$/,
        use: extractSASS.extract({
          fallback: {
            loader: 'style-loader',
          },
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
        from: './src/AppBundle/Resources/public/img',
        to: 'img',
      },
      {
        from: './src/AppBundle/Resources/public/favicons',
        transform: function (content) {
          return content.toString().replace(/__STATIC_ASSETS_BASE_PATH__/g, __STATIC_ASSETS_BASE_PATH__);
        },
        to: 'favicons',
      },
      {
        from: './node_modules/jquery/dist/jquery.min.js',
        to: 'jquery.js',
      },
      {
        from: './vendor/friendsofsymfony/jsrouting-bundle/Resources/public/js/router.js',
        to: 'fos-js-routing.js',
      },
      {
        from: './vendor/willdurand/js-translation-bundle/Resources/public/js/translator.min.js',
        to: 'bazinga-translator.js',
      },
    ]),
    new optimize.CommonsChunkPlugin({ name: 'common', filename: 'common.js' }),
    extractCSS,
    extractSASS,
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
  devtool: isDev ? 'cheap-module-eval-source-map' : false,
  devServer: {
    host: '0.0.0.0',
    disableHostCheck: true,
  },
};

if (isDev) {
  config.module.rules.push({
    enforce: 'pre',
    test: /\.js$/,
    exclude: /node_modules/,
    use: {
      loader: 'eslint-loader',
    },
  });
  config.module.rules.push({
    enforce: 'pre',
    test: /\.js$/,
    exclude: /node_modules/,
    use: {
      loader: 'source-map-loader',
    },
  });
} else {
  config.plugins.push(
    new MinifyPlugin({ mangle: false/* <- to fix Safari issue, hope this will be fixed in a newer version than 0.2.0 */ })
  );
}

module.exports = config;
