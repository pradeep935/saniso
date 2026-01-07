const mix = require('laravel-mix')
const path = require('path')

const directory = path.basename(path.resolve(__dirname))
const source = `platform/plugins/${directory}`
const dist = `public/vendor/core/plugins/${directory}`

mix
    .sass(`${source}/resources/sass/app.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/front-affiliate.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/affiliate-commission-info.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/short-links.scss`, `${dist}/css`)
    .sass(`${source}/resources/sass/license-activation.scss`, `${dist}/css`)
    .js(`${source}/resources/js/app.js`, `${dist}/js/app.js`)
    .js(`${source}/resources/js/affiliate-setting.js`, `${dist}/js`)
    .js(`${source}/resources/js/affiliate-ban-actions.js`, `${dist}/js`)
    .js(`${source}/resources/js/affiliate-actions.js`, `${dist}/js`)
    .js(`${source}/resources/js/front-affiliate.js`, `${dist}/js`)
    .js(`${source}/resources/js/affiliate-coupon.js`, `${dist}/js`)
    .js(`${source}/resources/js/affiliate-commission-info.js`, `${dist}/js/`)
    .js(`${source}/resources/js/short-links-management.js`, `${dist}/js`)
    .js(`${source}/resources/js/license-activation.js`, `${dist}/js`)

    .copy(`${source}/public/images`, `${dist}/images`)

if (mix.inProduction()) {
    mix
        .copy(`${dist}/js/app.js`, `${source}/public/js`)
        .copy(`${dist}/js/affiliate-setting.js`, `${source}/public/js`)
        .copy(`${dist}/js/affiliate-ban-actions.js`, `${source}/public/js`)
        .copy(`${dist}/js/affiliate-actions.js`, `${source}/public/js`)
        .copy(`${dist}/js/front-affiliate.js`, `${source}/public/js`)
        .copy(`${dist}/js/affiliate-coupon.js`, `${source}/public/js`)
        .copy(`${dist}/js/affiliate-commission-info.js`, `${source}/public/js`)
        .copy(`${dist}/js/short-links-management.js`, `${source}/public/js`)
        .copy(`${dist}/js/license-activation.js`, `${source}/public/js`)
        .copy(`${dist}/css/app.css`, `${source}/public/css`)
        .copy(`${dist}/css/front-affiliate.css`, `${source}/public/css`)
        .copy(`${dist}/css/affiliate-commission-info.css`, `${source}/public/css`)
        .copy(`${dist}/css/short-links.css`, `${source}/public/css`)
        .copy(`${dist}/css/license-activation.css`, `${source}/public/css`)
}
