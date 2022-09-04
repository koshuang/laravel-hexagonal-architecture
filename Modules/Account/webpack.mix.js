const dotenvExpand = require('dotenv-expand');
dotenvExpand(require('dotenv').config({ path: '../../.env'/*, debug: true*/}));

const mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.setPublicPath('../../public').mergeManifest();

mix.js(__dirname + '/Infrastructure/Adapter/In/Web/Resources/assets/js/app.js', 'js/account.js')
    .sass( __dirname + '/Infrastructure/Adapter/In/Web/Resources/assets/sass/app.scss', 'css/account.css');

if (mix.inProduction()) {
    mix.version();
}
