var gulp = require('gulp'),
  plumber = require('gulp-plumber'),
  sass = require('gulp-sass'),
  notify = require('gulp-notify'),
  sourcemaps = require('gulp-sourcemaps'),
  autoprefixer  = require('gulp-autoprefixer');

var dist = 'app/design/frontend/Oander/istyle/web/';
var distCss = dist + 'css/';

// Path array
var paths = {
  scss : [
    dist + 'scss/**/*.scss'
  ]
};

gulp.task('sass', function() {
  return gulp.src(paths.scss)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'compressed'
    })).on('error', notify.onError(function (error) {
      return error.message;
    }))
    .pipe(autoprefixer({
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
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(distCss));
});

gulp.task('default',  function(){
  gulp.watch(paths.scss, ['sass']);
});