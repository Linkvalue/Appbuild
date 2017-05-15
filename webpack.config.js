const path = require('path');
const isDev = require('isdev');
const CopyWebpackPlugin = require('copy-webpack-plugin');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

const config = {
  entry: {
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
        test: /\.css/,
        use: [
          {
            loader: 'style-loader',
          },
          {
            loader: 'css-loader',
          },
        ],
      },
      {
        test: /\.scss$/,
        use: [
          {
            loader: 'style-loader',
          },
          {
            loader: 'css-loader',
          },
          {
            loader: 'sass-loader',
          },
        ],
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
};

if (isDev) {
  config['devServer'] = {
    host: '0.0.0.0',
    disableHostCheck: true,
  };
} else {
  config.plugins.push(new UglifyJSPlugin());
}

module.exports = config;
