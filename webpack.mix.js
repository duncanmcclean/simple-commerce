let mix = require("laravel-mix");

mix
  .js("resources/js/cp.js", "resources/dist/js/cp.js")
  .postCss("resources/css/cp.css", "resources/dist/css/cp.css")
  .setPublicPath("resources/dist");
