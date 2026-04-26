# GitHub Deployment Setup

This repository includes a GitHub Actions workflow at `.github/workflows/deploy.yml`.

## Deployment flow

- Push to `develop`: run CI, build Docker images, push them to GHCR, then deploy to the GitHub `stage` environment
- Push to `main`: run CI, build Docker images, push them to GHCR, then deploy to the GitHub `production` environment
- Manual deploy: run the `Deploy` workflow from GitHub Actions and choose `stage` or `production`

The workflow deploys over SSH to a server directory used only for runtime files.
GitHub Actions copies `compose.yml` and `Caddyfile` into that directory.
The server does not clone the repository and does not build the app. It only pulls prebuilt images from GitHub Container Registry and runs `docker compose`.

## Server requirements

Each target server should already have:

- Docker Engine and Docker Compose plugin
- an empty deployment directory, for example `/opt/carpetunique-stage`
- a valid `.env.runtime` file in that directory
- database access from the server
- ports `80` and `443` reachable from the internet

The database is expected to live on another server.

## GitHub environments

Create two GitHub environments in your repository settings:

- `stage`
- `production`

Add the same secret names to each environment, but with environment-specific values.

## Required secrets

- `SSH_HOST`: server hostname or IP
- `SSH_PORT`: SSH port, usually `22`
- `SSH_USERNAME`: SSH login user
- `SSH_PRIVATE_KEY`: private SSH key used by GitHub Actions to connect
- `DEPLOY_PATH`: absolute path to the deployment directory on the server, for example `/opt/carpetunique-stage`
- `GHCR_USERNAME`: GitHub username used for pulling images
- `GHCR_TOKEN`: GitHub token or classic PAT with permission to read GHCR packages
- `SERVER_NAME`: public domain name for Caddy, for example `stage.example.com`
- `LETSENCRYPT_EMAIL`: email used for TLS certificate registration

## Optional secrets

- `APP_URL`: site URL shown in the GitHub environment
- `SSH_PASSPHRASE`: passphrase for the private key, if the key is encrypted
- `SSH_FINGERPRINT`: recommended host fingerprint for SSH verification
- `POST_DEPLOY_COMMAND`: extra command run after deploy

## Remote deploy steps

The workflow connects to the server and runs:

```bash
mkdir -p <DEPLOY_PATH>
scp deploy/compose.yml deploy/Caddyfile <server>:<DEPLOY_PATH>/
cd <DEPLOY_PATH>
docker login ghcr.io
docker compose -f compose.yml pull
docker compose -f compose.yml up -d
docker compose -f compose.yml exec -T app php bin/console doctrine:migrations:migrate --env=prod --no-interaction --allow-no-migration
docker compose -f compose.yml exec -T app php bin/console cache:clear --env=prod --no-debug
docker compose -f compose.yml exec -T app php bin/console cache:warmup --env=prod --no-debug
```

## Recommended hardening

- Protect the `production` environment with required reviewers
- Restrict direct pushes to `main`
- Use a dedicated read-only GHCR pull token on the server side

## Runtime environment file

Create `.env.runtime` inside the deployment directory on the server based on [deploy/.env.runtime.example](/home/mahdi/Projects/mahdi/carpet/deploy/.env.runtime.example:1).
This file contains the runtime Symfony environment variables, including the external database connection.

The deployed containers are:

- `app`: PHP-FPM / Symfony / Sylius
- `web`: nginx serving the built storefront and proxying PHP requests to `app`
- `caddy`: public reverse proxy with automatic Let's Encrypt TLS

## SSH key setup

Generate a dedicated deploy key pair locally:

```bash
ssh-keygen -t ed25519 -C "github-actions-stage" -f ~/.ssh/carpetunique-stage
```

Add the public key to the target server user's `~/.ssh/authorized_keys`.

Add the private key content to the GitHub environment secret:

- `SSH_PRIVATE_KEY`

If you created the key with a passphrase, also add:

- `SSH_PASSPHRASE`
