# GitHub Deployment Setup

This repository includes a GitHub Actions workflow at `.github/workflows/deploy.yml`.

## Deployment flow

- Push to `develop`: run CI, then deploy to the GitHub `stage` environment
- Push to `main`: run CI, then deploy to the GitHub `production` environment
- Manual deploy: run the `Deploy` workflow from GitHub Actions and choose `stage` or `production`

The workflow deploys over SSH to a server where the project is already cloned.
It does not provision the server for you.

## Server requirements

Each target server should already have:

- the project cloned in the target directory
- a valid `.env.local` or equivalent server environment configuration
- PHP 8.3
- Composer
- Node.js
- Yarn
- database access from the server
- permissions to write `var/` and `public/`

## GitHub environments

Create two GitHub environments in your repository settings:

- `stage`
- `production`

Add the same secret names to each environment, but with environment-specific values.

## Required secrets

- `SSH_HOST`: server hostname or IP
- `SSH_PORT`: SSH port, usually `22`
- `SSH_USERNAME`: SSH login user
- `SSH_PASSWORD`: SSH password
- `DEPLOY_PATH`: absolute path to the project on the server

## Optional secrets

- `APP_URL`: site URL shown in the GitHub environment
- `SSH_FINGERPRINT`: recommended host fingerprint for SSH verification
- `COMPOSER_OPTIONS`: extra options appended to `composer install`
- `POST_DEPLOY_COMMAND`: extra command run after deploy, for example restarting `php-fpm`

## Remote deploy steps

The workflow connects to the server and runs:

```bash
git fetch --all --prune
git checkout <branch>
git pull --ff-only origin <branch>
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts
yarn install --frozen-lockfile
yarn build:prod
php bin/console doctrine:migrations:migrate --env=prod --no-interaction --allow-no-migration
php bin/console cache:clear --env=prod --no-debug
php bin/console cache:warmup --env=prod --no-debug
php bin/console assets:install public --env=prod
```

## Recommended hardening

- Prefer SSH keys over passwords once the first deploy is working
- Protect the `production` environment with required reviewers
- Restrict direct pushes to `main`
- Add a `POST_DEPLOY_COMMAND` to reload `php-fpm` or your process manager if needed
