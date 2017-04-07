var gulp = require('gulp'),
  plumber = require('gulp-plumber'),
  sass = require('gulp-sass'),
  notify = require('gulp-notify'),
  sourcemaps = require('gulp-sourcemaps'),
  autoprefixer  = require('gulp-autoprefixer'),
  browserSync = require('browser-sync');

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
    .pipe(sass())
    .pipe(autoprefixer('last 2 versions'))
    .pipe(sourcemaps.write('./'))
    .pipe(gulp.dest(distCss));
});

gulp.task('default',  function(){
  gulp.watch(paths.scss, ['sass']);
});