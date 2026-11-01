# Upgrade Guide

## Upgrading from v5 to v6

### Node 20 Support Dropped

Node 20 is no longer supported. The minimum is now Node 22, and the latest LTS (Node 26) is recommended.
Run `npm ci` and `npm run build` after upgrading.

### Laravel 13

BMAC now runs on Laravel 13.

**After pulling:**

- Run `composer install`.
- No `.env` changes are needed. The cache prefix and session cookie names are set explicitly in the config files, so existing cache keys and sessions keep working.
- Cached values are no longer unserialized into PHP objects (`cache.serializable_classes` is `false`).
  If you added custom code that caches objects, cache arrays or scalars instead, or list the classes in `config/cache.php`.
- The airport, airport link type and event type dropdown caches now use new cache keys.
  The old entries expire on their own within an hour.

## Upgrading from v4 to v5

### PHP 8.2 Support Dropped

PHP 8.2 is no longer supported. Please upgrade to PHP 8.3 or higher.

### Environment Variables

Environment variable names now follow standard Laravel 12 conventions. Fallbacks for the old variable names have been added for now, but these will be removed in a future release.

It is recommended to re-create your `.env` file from `.env.example` and fill in your values.

### Laravel Mix Replaced by Vite

The frontend build tooling has been migrated from Laravel Mix (Webpack) to Vite.

**What changed:**

- `webpack.mix.js` has been removed and replaced by `vite.config.js`.
- The `mix()` Blade helper has been replaced by the `@vite` directive.
- NPM scripts have changed: use `npm run dev` (Vite dev server) and `npm run build` (production build).
- Built assets are now output to `public/build/` instead of `public/js/` and `public/css/`.

**After pulling:**

- Run `npm install` to install the new dependencies.
- Run `npm run build` to compile assets.
- The `.gitignore` has been updated to match Vite's output. You may see unstaged changes in the `public/` folder from the old Mix output (`public/js/`, `public/css/`, `public/mix-manifest.json`). These can safely be removed.
