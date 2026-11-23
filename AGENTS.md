# AGENTS.md

## Required Documentation

Before changing code, read and follow:

- `readme/how-to-start-development.md` — local setup, modules, migrations, seeders.
- `readme/branching-strategy.md` — branches, releases, hotfixes, tags/versioning.
- `readme/deploy.md` — build profiles, client deployment, patches, rollback.

Documents under `docs/**` are domain/feature documentation, not coding rules; read them only when relevant to the task.

Explicit user instructions take precedence. When instructions conflict, follow the most specific document for the topic.

## CLI commands

This checkout is served by Apache as `www-data`. Run Artisan, Composer, and PHPUnit as `www-data` so generated files in `storage/` and `bootstrap/cache/` remain writable by the web server:

```bash
sudo -u www-data -- php artisan <command>
sudo -u www-data -- composer <command>
sudo -u www-data -- ./vendor/bin/phpunit <args>
```

If a command must run as root, restore ownership afterward:

```bash
chown -R www-data:www-data storage bootstrap/cache public/filescontract public/filevendor public/tmpdata public/project_file public/accounting_file public/doc_recruitment
```

Public upload directories must stay writable by the web server. Runtime upload directories are not tracked in Git; create them on a fresh checkout or deployment with:

```bash
mkdir -p \
  public/filevendor \
  public/tmpdata \
  public/project_file \
  public/accounting_file \
  public/doc_recruitment \
  public/filescontract/contracts \
  public/filescontract/bookings

chown -R www-data:www-data \
  public/filevendor \
  public/tmpdata \
  public/project_file \
  public/accounting_file \
  public/doc_recruitment \
  public/filescontract

find public/filevendor public/tmpdata public/project_file public/accounting_file public/doc_recruitment public/filescontract -type d -exec chmod 775 {} \;
find public/filevendor public/tmpdata public/project_file public/accounting_file public/doc_recruitment public/filescontract -type f -exec chmod 664 {} \;
```
