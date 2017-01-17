var gulp = require('gulp');
var $    = require('gulp-load-plugins')();
var babel = require('gulp-babel');
var browserSync = require('browser-sync').create();

var sassPaths = [
  // Paths to Sass libraries, which can then be loaded with @import
  'node_modules/normalize.scss/sass',
  'node_modules/foundation-sites/scss'
];
var assetsPaths = [
  '{img,fonts}/**/*'
];
var jsPaths = [
  'node_modules/jquery/dist/jquery.js',
  // Core Foundation files
  'node_modules/foundation-sites/js/foundation.core.js',
  'node_modules/foundation-sites/js/foundation.util.*.js',
  // Individual Foundation components
  'node_modules/foundation-sites/js/foundation.abide.js',
  'node_modules/foundation-sites/js/foundation.accordion.js',
  'node_modules/foundation-sites/js/foundation.accordionMenu.js',
  'node_modules/foundation-sites/js/foundation.drilldown.js',
  'node_modules/foundation-sites/js/foundation.dropdown.js',
  'node_modules/foundation-sites/js/foundation.dropdownMenu.js',
  'node_modules/foundation-sites/js/foundation.equalizer.js',
  'node_modules/foundation-sites/js/foundation.interchange.js',
  'node_modules/foundation-sites/js/foundation.magellan.js',
  'node_modules/foundation-sites/js/foundation.offcanvas.js',
  'node_modules/foundation-sites/js/foundation.orbit.js',
  'node_modules/foundation-sites/js/foundation.responsiveMenu.js',
  'node_modules/foundation-sites/js/foundation.responsiveToggle.js',
  'node_modules/foundation-sites/js/foundation.reveal.js',
  'node_modules/foundation-sites/js/foundation.slider.js',
  'node_modules/foundation-sites/js/foundation.sticky.js',
  'node_modules/foundation-sites/js/foundation.tabs.js',
  'node_modules/foundation-sites/js/foundation.toggler.js',
  'node_modules/foundation-sites/js/foundation.tooltip.js',
  // Paths to our own project code
  'js/init-foundation.js',
  'js/!(init-foundation).js'
];

var distPath = '../public/';

gulp.task('copy', function() {
    return gulp.src(assetsPaths)
        .pipe(gulp.dest(distPath))
        .pipe(browserSync.reload({ stream: true }));
})


gulp.task('sass', function() {
    return gulp.src('scss/app.scss')
        .pipe($.sass({
            includePaths: sassPaths,
            outputStyle: 'compressed' // if css compressed **file size**
        })
        .on('error', $.sass.logError))
        .pipe($.autoprefixer({
            browsers: ['last 2 versions', 'ie >= 9']
        }))
        .pipe(gulp.dest(distPath + 'css'))
        .pipe(browserSync.reload({ stream: true }));
});

// Combine JavaScript into one file
gulp.task('scripts', function() {
    return gulp.src(jsPaths)
        .pipe(babel({
            presets: ['es2015']
        }))
        .pipe($.concat('app.js', {
            newLine:'\n;'
        }))
        .pipe(gulp.dest(distPath + 'js'))
        .pipe(browserSync.reload({ stream: true }));
});


gulp.task('watch', function() {
    browserSync.init({
        server: {
            baseDir: '../views/Flat/',
            routes: {
                '/public': '../public'
            }
        }
    });

    gulp.watch('*.html').on('change', browserSync.reload);
    gulp.watch(['scss/**/*.scss'], ['sass']);
    gulp.watch(['js/**/*.js'], ['scripts']);
});

gulp.task('build', ['sass', 'scripts', 'copy']);

gulp.task('dev', ['build', 'watch']);

gulp.task('default', ['dev']);
