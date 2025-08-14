// Подгружаем сам компилятор
const mix = require("laravel-mix");

// Основные стили
mix
    .css("resources/css/app.css", "public/css/app.css",
        [
            require('tailwindcss'),
        ])
    .version()
    .options({
        processCssUrls: false, // Для обработки URL в CSS
    })


    .scripts(
        [
            "resources/js/app.js",
        ],
        "public/js/app.js"
    )
    .js('resources/js/checklog.js', 'public/js/checklog.js')








mix.webpackConfig({
    devtool: 'inline-source-map'
})

//if (mix.config.inProduction) {
mix.version();
//}
