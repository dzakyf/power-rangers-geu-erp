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
chown -R www-data:www-data storage bootstrap/cache public/filescontract
```

Public upload directories must stay writable by the web server. If a fresh checkout or deployment creates them as root, repair them with:

```bash
mkdir -p public/filescontract/contracts public/filescontract/bookings
chown -R www-data:www-data public/filescontract
find public/filescontract -type d -exec chmod 775 {} \;
find public/filescontract -type f -exec chmod 664 {} \;
```
