---
title: AI Skills
description: Install the package's AI assistant skill into a host application.
order: 3
---

# AI Assistant Skills

The package ships a canonical AI skill at `resources/stubs/ai-skill.md`. It is
synced into the package's own `.claude/` and `.junie/` skill directories on every
`composer install` via `scripts/sync-ai-skills.php`.

## Install into a host application

```bash
php artisan oi:install-ai-skill
```

This copies the skill into the host app's `.claude/skills/oilab-laravel-publish/`
and `.junie/skills/oilab-laravel-publish/` directories and idempotently upserts
the `=== oi-lab/oi-laravel-publish rules ===` section into the host `CLAUDE.md`.

## Updating the skill

After changing the package's behaviour, edit `resources/stubs/ai-skill.md` and
run:

```bash
composer sync-ai-skills
```
