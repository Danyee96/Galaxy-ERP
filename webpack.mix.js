const mix = require('laravel-mix');
const exec = require('child_process').exec;
require('dotenv').config();

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.sass('resources/sass/branding/galaxy.scss', 'public/brand/galaxy.css');
mix.sass('resources/sass/branding/erp.scss', 'public/brand/erp.css');
mix.sass('resources/sass/branding/estimation.scss', 'public/brand/estimation.css');
mix.scripts('resources/js/branding/estimation.js', 'public/brand/estimation.js');
mix.scripts('resources/js/branding/erp.js', 'public/brand/erp.js');
mix.scripts('resources/js/branding/erp-dashboard.js', 'public/brand/erp-dashboard.js');

const glob = require('glob')
const path = require('path')

/*
 |--------------------------------------------------------------------------
 | Vendor assets
 |--------------------------------------------------------------------------
 */

function mixAssetsDir(query, cb) {
  (glob.sync('resources/' + query) || []).forEach(f => {
    f = f.replace(/[\\\/]+/g, '/');
    cb(f, f.replace('resources', 'public'));
  });
}


// themes Core stylesheets
mixAssetsDir('sass/core/**/!(_)*.scss', (src, dest) => mix.sass(src, dest.replace(/(\\|\/)sass(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css')));

// pages Core stylesheets
mixAssetsDir('sass/pages/**/!(_)*.scss', (src, dest) => mix.sass(src, dest.replace(/(\\|\/)sass(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css')));

// Themescss task
mixAssetsDir('sass/plugins/**/!(_)*.scss', (src, dest) => mix.sass(src, dest.replace(/(\\|\/)sass(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css')));

// Core stylesheets
mixAssetsDir('sass/themes/**/!(_)*.scss', (src, dest) => mix.sass(src, dest.replace(/(\\|\/)sass(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css')));

// custom blank file for users
mixAssetsDir('assets/scss/**/!(_)*.scss', (src, dest) => mix.sass(src, dest.replace(/(\\|\/)sass(\\|\/)/, '$1css$2').replace(/(\\|\/)scss(\\|\/)/, '$1css$2').replace(/\.scss$/, '.css')));

// script js
mixAssetsDir('js/core/**/*.js', (src, dest) => mix.scripts(src, dest));

// custom script js
mixAssetsDir('js/scripts/**/*.js', (src, dest) => mix.scripts(src, dest));

// custom script js for users
mixAssetsDir('assets/js/**/*.js', (src, dest) => mix.scripts(src, dest));

/*
 |--------------------------------------------------------------------------
 | Application assets
 |--------------------------------------------------------------------------
 */

mix.copyDirectory('resources/images', 'public/images');
mix.copyDirectory('resources/vendors', 'public/vendors');
mix.copyDirectory('resources/fonts', 'public/fonts');
mix.copyDirectory('resources/data', 'public/data');



mix.sass('resources/sass/bootstrap-extended.scss', 'public/css')
  .sass('resources/sass/bootstrap.scss', 'public/css')
  .sass('resources/sass/colors.scss', 'public/css')
  .sass('resources/sass/components.scss', 'public/css')
  .sass('resources/sass/custom-rtl.scss', 'public/css')

mix.then(() => {
  if (process.env.MIX_CONTENT_DIRECTION === "rtl") {
    let command = `node ${path.resolve('node_modules/rtlcss/bin/rtlcss.js')} -d -e ".css" ./public/css/ ./public/css/`;
    exec(command, function (err, stdout, stderr) {
      if (err !== null) {
        console.log(err);
      }
    });
    // exec('./node_modules/rtlcss/bin/rtlcss.js -d -e ".css" ./public/css/ ./public/css/');
  }
});

mix.webpackConfig({
  module: {
    rules: [
      {
        test: /\.s[ac]ss$/i,
        use: [
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                includePaths: ['node_modules', 'resources/assets']
              }
            }
          }
        ]
        //   test: /\.s[ac]ss$/,
        //   use: 'css-loader!sass-loader?{"includePaths":["frontend/node_modules"]}'
      }
    ]
  }
})

mix.version();

mix.scripts('resources/js/branding/warehouse-context.js', 'public/brand/warehouse-context.js');

mix.scripts('resources/js/branding/list-tools.js', 'public/brand/list-tools.js');
mix.copy('resources/sass/branding/list-tools.css', 'public/brand/list-tools.css');
