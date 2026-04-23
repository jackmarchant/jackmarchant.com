# jackmarchant.com

Website for jackmarchant.com built with [Slim Framework](https://www.slimframework.com) and [Twig](https://twig.symfony.com/doc/3.x/) to render markdown posts into html.

### Development
1. Run `make install`
2. Run local web server `make up`

### Static Build
Run `make build` to generate the static site in the `dist/` directory.

### Deployment
- **Heroku** — serves the PHP app dynamically via `Procfile`.
- **Netlify** — builds a static version via `build.php`. See `netlify.toml` for build configuration.
