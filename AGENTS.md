# AGENTS.md

## CLI commands

This checkout is served by Apache as `www-data`. Run Artisan, Composer, and PHPUnit as `www-data` so generated files in `storage/` and `bootstrap/cache/` remain writable by the web server:

```bash
sudo -u www-data -- php artisan <command>
sudo -u www-data -- composer <command>
sudo -u www-data -- ./vendor/bin/phpunit <args>
```

If a command must run as root, restore ownership afterward:

```bash
chown -R www-data:www-data storage bootstrap/cache
```
