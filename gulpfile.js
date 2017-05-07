var gulp = require('gulp'),
  plumber = require('gulp-plumber'),
  sass = require('gulp-sass'),
  sassLint = require('gulp-sass-lint'),
  autoPrefixer  = require('gulp-autoprefixer'),
  gulpif = require('gulp-if'),
  browserSync = require('browser-sync'),
  notify = require('gulp-notify'),
  sourcemaps = require('gulp-sourcemaps'),
  argv = require('yargs').argv;

var dist = 'app/design/frontend/Oander/istyle/web/';
var distCss = dist + 'css/';
var paths = {
  scss : [
    dist + 'scss/**/*.scss'
  ]
};

/**
 * Tasks
 */
gulp.task('browser-sync', function() {
  browserSync({
    proxy: 'http://istylem2.tld'
  });
});

gulp.task('bs-reload', function () {
  browserSync.reload();
});

gulp.task('sass-lint', function () {
  return gulp.src(paths.scss)
    .pipe(sassLint(
      {
        options: {
        },
        rules: {
          'property-sort-order': 0,
          'nesting-depth': 0,
          'empty-line-between-blocks': 0,
          'pseudo-element': 0,
          'force-element-nesting': 0
        }
      }
    ))
    .pipe(sassLint.format())
    .pipe(sassLint.failOnError())
});

gulp.task('sass', function() {
  
  var outputStyle = (argv.dev) ? 'expanded' : 'compressed';

  return gulp.src(paths.scss)
    .pipe(plumber())
    .pipe(gulpif(argv.dev, sourcemaps.init()))
    .pipe(sass({
      outputStyle: outputStyle
    })).on('error', notify.onError(function (error) {
      return error.message;
    }))
    .pipe(autoPrefixer({
      browsers: [
        'Chrome >= 45',
        'Firefox >= 45',
        'Edge >= 12',
        'Explorer >= 9',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12'
      ]
    }))
    .pipe(gulpif(argv.dev, sourcemaps.write('./')))
    .pipe(gulp.dest(distCss))
    .pipe(gulpif(argv.dev, browserSync.reload({
      stream: true
    })));
});

gulp.task('watch', function() {
  gulp.watch(paths.scss, ['sass']);
});

gulp.task('default', ['sass', 'browser-sync', 'watch']);