var path = require('path');

var gulp = require('gulp');
var $    = require('gulp-load-plugins')();
var webpack = require('webpack-stream');
var babel = require('gulp-babel');
var browserSync = require('browser-sync').create();

var baseSources = path.resolve('.', 'src', 'AppBundle', 'Resources', 'assets');
var baseResource = path.resolve('.', 'src', 'AppBundle', 'Resources', 'views');
var distPath = './web/';

var PATHS = {
  src: {
    html: path.join(distPath, 'flat'),
    js: path.join(baseSources, 'js'),
    css: path.join(baseSources, 'scss'),
  },
  dest: {
    js: path.join(distPath, 'js'),
    css: path.join(distPath, 'css'),
  }
};

var sassModulesPaths = [
  // Paths to Sass libraries, which can then be loaded with @import
  'node_modules/normalize.scss/sass',
  'node_modules/foundation-sites/scss',
  'node_modules/fine-uploader/fine-uploader',
];
var assetsPaths = [
  '{img,fonts}/**/*',
];
const scssPaths = [
  './app.scss',
];
var jsPaths = [
  './index.js',
];

const scssCleanPaths = [
  '**/*.scss',
  '!./foundation/**.scss',
  '!./base/fonts/**.scss',
];



gulp.task('copy', function() {
  return gulp.src(assetsPaths, { cwd: baseSources })
    .pipe(gulp.dest(distPath))
    .pipe(browserSync.stream())
})

/**
 * BUILD CSS
 */
gulp.task('sass', function() {
  return gulp.src(scssPaths, { cwd: PATHS.src.css })
    .pipe($.sass({
        includePaths: sassModulesPaths,
        outputStyle: 'compressed' // if css compressed **file size**
    })
    .on('error', $.sass.logError))
    .pipe($.autoprefixer({
        browsers: ['last 2 versions', 'ie >= 9']
    }))
    .pipe(gulp.dest(PATHS.dest.css))
    .pipe(browserSync.reload({ stream: true }));
});

/**
 * BUILD JS
 */


gulp.task('scripts', function() {
  return gulp.src(jsPaths, { cwd: PATHS.src.js })
    .pipe(webpack({
      module: {
        loaders: [
          {
            test: /.js$/,
            exclude: /node_modules/,
            loader: 'babel-loader',
            query: {
              presets: ['es2015']
            }
          }
        ]
      },
      devtool: "#source-map",
      output: {
        filename: '[name].js',
        sourceMapFilename: '[name].js.map'
      }
    }))
    .pipe(gulp.dest(PATHS.dest.js))
    .pipe(browserSync.stream());
});

gulp.task('watch', function() {
  browserSync.init({
    server: {
      baseDir: './web/flat',
      routes: {
        '/css': './web/css',
        '/js': './web/js',
        '/img': './web/img',
        '/fonts': './web/fonts'
      }
    }
  });

  gulp.watch(['**/*.html'], { cwd: PATHS.src.html }).on('change', browserSync.reload);
  gulp.watch(['**/*.scss'], { cwd: PATHS.src.css }, ['sass']);
  gulp.watch(['**/*.js'], { cwd: PATHS.src.js }, ['scripts']);
});

gulp.task('browserSync:reload', function() {
  browserSync.reload();
});

gulp.task('build', ['sass', 'scripts', 'copy']);

gulp.task('dev', ['build', 'watch']);

gulp.task('default', ['dev']);
