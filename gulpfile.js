var gulp = require('gulp');
var fs = require('fs');
var replace = require('gulp-replace');
var config = require('./config.json');

gulp.task( 'init', function(done) {
    if( fs.existsSync('./sm-plugin-starter/sm-plugin-starter.php')) {
        console.log('Setting entrypoint name...');
        fs.rename('./sm-plugin-starter/sm-plugin-starter.php', './sm-plugin-starter/' + config.pluginName + '.php', function ( err) {
            if( err ) {
                throw err;
            }
        });
    } else {
        console.log('Entrypoint name is already set...')
    }

    if( fs.existsSync('./sm-plugin-starter')) {
        console.log('Setting folder name...');
        fs.rename('./sm-plugin-starter', './' + config.pluginName, function ( err) {
            if( err ) {
                throw err;
            }
        });
    } else {
        console.log('Theme folder name is already set...')
    }

    console.log('Generating configs...');
    gulp.src(['./build/docker-compose.yml'])
    .pipe(replace('{PLUGIN_NAME}', config.pluginName))
    .pipe(gulp.dest('./'));

    gulp.src(['./build/composer.json'])
    .pipe(replace('{PLUGIN_NAME}', config.pluginName))
    .pipe(replace('{PLUGIN_DESCRIPTION}', config.pluginDescription))
    .pipe(replace('{PLUGIN_NAMESPACE}', config.pluginNamespace))
    .pipe(replace('{PLUGIN_AUTHOR_NAME}', config.pluginAuthorName))
    .pipe(replace('{PLUGIN_AUTHOR_EMAIL}', config.pluginAuthorEmail))
    .pipe(gulp.dest('./' + config.pluginName));

    done();
});