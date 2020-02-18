const __STATIC_ASSETS_BASE_PATH__ = 'assets';

const path = require('path');
const isDev = require('isdev');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const config = {
  entry: {
    vendor: './src/AppBundle/Resources/public/js/vendor.js',
    main: './src/AppBundle/Resources/public/js/main.js',
    'application-form': './src/AppBundle/Resources/public/js/application-form.js',
    'build-form': './src/AppBundle/Resources/public/js/build-form.js',
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, 'web/' + __STATIC_ASSETS_BASE_PATH__),
    publicPath: '/' + __STATIC_ASSETS_BASE_PATH__ + '/',
  },
  optimization: {
    splitChunks: {
      name: 'common',
      chunks: 'all',
    },
    minimizer: [new TerserPlugin()],
  },
  mode: process.env.NODE_ENV === 'production' ? 'production' : 'development',
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
        use: [{
          loader: MiniCssExtractPlugin.loader,
          options: {
            hmr: process.env.NODE_ENV !== 'production',
          },
        }, 'css-loader'],
      },
      {
        test: /\.scss$/,
        use: [{
          loader: MiniCssExtractPlugin.loader,
          options: {
            hmr: process.env.NODE_ENV !== 'production',
          },
        }, 'css-loader', 'sass-loader'],
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
    new MiniCssExtractPlugin(),
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
    writeToDisk: true,
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
}

module.exports = config;
