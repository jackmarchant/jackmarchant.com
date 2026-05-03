# Deploying a static-built PHP site to EC2 from an iPad

End-to-end guide for hosting a PHP-rendered static site on a small EC2 box without ever installing PHP, Composer, git, or an SSH client locally. All work happens in Safari on the iPad: GitHub web UI for code, AWS Console for infra, AWS Systems Manager Session Manager for the server shell.

This is the recipe used to migrate `jackmarchant.com` off Heroku in May 2026. It generalises to any project that can produce a static `dist/` from a build script.

---

## Architecture

- **GitHub Actions** runs the build (`composer install && php build.php`, or whatever your project uses) on every push to `main` and force-pushes the resulting `dist/` to a dedicated `deploy` branch whose root **is** the static site.
- **EC2 (Amazon Linux 2023, t3.micro)** clones the `deploy` branch and serves it with **Nginx**. No PHP runtime, no Composer, no application code on the server.
- **Custom domain + Let's Encrypt HTTPS** terminating on the box, auto-renewing.
- **AWS Systems Manager Session Manager** for browser-based shell access — no SSH keys, no port 22 open.

Day-to-day publishing: edit Markdown in GitHub web UI → wait ~30s for the Action → SSM in → run `deploy.sh`.

---

## Replace these placeholders before running anything

| Placeholder | Example | Notes |
|---|---|---|
| `OWNER/REPO` | `jackmarchant/jackmarchant.com` | GitHub repo path |
| `SITENAME` | `jackmarchant` | Short slug used for IAM role / SG / paths |
| `DOMAIN` | `jackmarchant.com` | Your apex domain |
| `EMAIL` | `you@example.com` | Used for Let's Encrypt expiry warnings |

⚠️ **Do not paste angle brackets into commands or config files.** They are placeholder syntax only. A stray `<DOMAIN>` left inside an Nginx `server_name` directive will silently misconfigure the site and break certbot's ability to find the server block.

---

## Step 1 — Repo: GitHub Actions build → `deploy` branch

Create `.github/workflows/build.yml` on the `main` branch via the GitHub web editor:

```yaml
name: build-and-publish-dist

on:
  push:
    branches: [main]
  workflow_dispatch:

permissions:
  contents: write

jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
          tools: composer
      - run: composer install --no-dev --optimize-autoloader
      - run: php build.php
      - name: Publish dist/ to deploy branch
        run: |
          cd dist
          git init -b deploy
          git config user.name  "github-actions[bot]"
          git config user.email "github-actions[bot]@users.noreply.github.com"
          git add -A
          git commit -m "build: ${GITHUB_SHA::7}"
          git push --force "https://x-access-token:${{ secrets.GITHUB_TOKEN }}@github.com/${{ github.repository }}.git" deploy
```

Trigger the first run via Actions → *build-and-publish-dist* → "Run workflow", or push any commit. Confirm the `deploy` branch appears in GitHub and its root contains `index.html`.

---

## Step 2 — AWS infrastructure (Safari on iPad)

### 2a. IAM role for SSM
IAM → **Roles** → Create role:
- Trusted entity: **AWS service** → **EC2**
- Permissions: attach **`AmazonSSMManagedInstanceCore`**
- Name: `SITENAME-web-ssm`

### 2b. Launch the EC2 instance
EC2 → **Launch instance**:
- **Name:** `SITENAME-web`
- **AMI:** Amazon Linux 2023 (x86_64)
- **Instance type:** `t3.micro` (free-tier) or `t4g.micro` (ARM, slightly cheaper)
- **Key pair:** *Proceed without a key pair* (SSM provides shell access)
- ⚠️ **IAM instance profile: `SITENAME-web-ssm`** — this field is **inside the "Advanced details" accordion at the bottom** of the launch wizard. Easy to miss on mobile. Without it, SSM Agent boots with no credentials and Session Manager refuses to connect.
- **Security group** `SITENAME-web-sg` — **inbound only**:
  - HTTP (80) from `0.0.0.0/0` and `::/0` (required for Let's Encrypt HTTP-01)
  - HTTPS (443) from `0.0.0.0/0` and `::/0`
  - **No port 22**
- **Storage:** 8 GB gp3

### 2c. Elastic IP
EC2 → **Elastic IPs** → Allocate → Associate with the instance. Free while attached, prevents the public IP changing on stop/start.

### 2d. Open a shell via SSM
EC2 → Instances → select instance → **Connect** → **Session Manager** tab → **Connect**. Run `sudo -i` to become root.

**Troubleshooting — instance shows Offline / "SSM Agent unable to acquire credentials":** the IAM instance profile from 2b wasn't attached. Fix without relaunching:
1. EC2 → Instances → select → **Actions → Security → Modify IAM role** → attach `SITENAME-web-ssm`
2. Wait ~60s. If still Offline, **Instance state → Reboot**.

---

## Step 3 — Server setup (run as root in SSM)

```bash
dnf update -y
dnf install -y nginx git
systemctl enable --now nginx

mkdir -p /var/www && chown ec2-user:ec2-user /var/www
cd /var/www
git clone --branch deploy --single-branch \
    https://github.com/OWNER/REPO.git SITENAME
```

The `deploy` branch's root **is** the static site, so `/var/www/SITENAME/index.html` exists immediately after clone.

---

## Step 4 — Nginx config

⚠️ Substitute `DOMAIN` and `SITENAME` literally. **Do not include the angle brackets.**

```bash
cat > /etc/nginx/conf.d/SITENAME.conf <<'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name DOMAIN www.DOMAIN;

    root /var/www/SITENAME;
    index index.html;

    location / {
        try_files $uri $uri/ $uri.html $uri/index.html =404;
    }

    error_page 404 /404.html;

    location ~* \.(?:css|js|woff2?|ttf|eot|svg|png|jpg|jpeg|gif|ico)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    access_log /var/log/nginx/SITENAME.access.log;
    error_log  /var/log/nginx/SITENAME.error.log;
}
EOF

nginx -t && systemctl reload nginx
```

After the heredoc, **always verify the file**:
```bash
grep server_name /etc/nginx/conf.d/SITENAME.conf
```
The output must show your bare domain. If you see angle brackets, fix them before continuing:
```bash
sed -i 's/<//g; s/>//g' /etc/nginx/conf.d/SITENAME.conf
```

Hitting `http://<elastic-ip>/` from the iPad should return the homepage.

---

## Step 5 — DNS

In your DNS provider:
- `A`  `DOMAIN`     → elastic IP
- `A`  `www.DOMAIN` → elastic IP

Confirm propagation before continuing:
```bash
dig +short DOMAIN
```

---

## Step 6 — HTTPS with Let's Encrypt

```bash
dnf install -y python3-certbot python3-certbot-nginx
certbot --nginx -d DOMAIN -d www.DOMAIN \
        --redirect --agree-tos -m EMAIL --no-eff-email
```

Certbot edits the Nginx config to add `listen 443 ssl` plus an HTTP→HTTPS redirect. Auto-renewal runs as a systemd timer:
```bash
systemctl list-timers | grep certbot
certbot renew --dry-run
```

**If certbot says "Could not automatically find a matching server block"**: your `server_name` doesn't match the `-d` argument. The cert was still issued — fix `server_name`, reload Nginx, then run `certbot install --cert-name DOMAIN` (no new Let's Encrypt request, no rate-limit risk).

---

## Step 7 — Redeploy script

```bash
cat > /var/www/SITENAME/deploy.sh <<'EOF'
#!/usr/bin/env bash
set -euo pipefail
cd /var/www/SITENAME
git fetch origin deploy
git reset --hard origin/deploy
echo "Deployed $(git rev-parse --short HEAD) at $(date -u +%FT%TZ)"
EOF
chmod +x /var/www/SITENAME/deploy.sh
```

`git reset --hard` (not `pull`) is correct because the Action **force-pushes** `deploy` on every build.

---

## Step 8 — Verification

1. `curl -I http://DOMAIN/` → `301` to `https://`
2. `curl -I https://DOMAIN/` → `200`, `server: nginx`, valid TLS
3. Browser: load homepage, click into a sub-page (tests `try_files`)
4. `tail -f /var/log/nginx/SITENAME.error.log` while clicking around — should stay quiet
5. `certbot renew --dry-run` — confirms unattended renewal works
6. Edit any post in GitHub web UI → commit to `main` → wait for Action → run `/var/www/SITENAME/deploy.sh` → refresh

---

## Daily publishing loop

1. Edit Markdown in GitHub web UI on iPad, commit to `main`.
2. Wait ~30s for the Action to finish (visible in GitHub iOS app).
3. AWS Console → EC2 → Connect → Session Manager → `sudo /var/www/SITENAME/deploy.sh`.

---

## Files that matter on the box

| Path | Purpose |
|---|---|
| `/var/www/SITENAME/` | Served files (= `deploy` branch checkout) |
| `/etc/nginx/conf.d/SITENAME.conf` | Nginx site config |
| `/etc/letsencrypt/live/DOMAIN/` | TLS certs |
| `/var/www/SITENAME/deploy.sh` | Redeploy script |
| `/var/log/nginx/SITENAME.{access,error}.log` | Nginx logs |

---

## Gotchas worth remembering

1. **IAM instance profile is hidden under "Advanced details"** in the EC2 launch wizard. Verify before clicking Launch.
2. **Angle brackets are placeholder syntax only**. Never paste them into a real config or command. Always `grep server_name` after a heredoc to verify.
3. **The default Amazon Linux `nginx.conf` ships its own `server { server_name _; }` block** on port 80. Harmless when your `server_name` is correct (Nginx prefers exact matches), but a red herring while diagnosing.
4. **Heroku coexistence**: this plan touches no application code, only `.github/workflows/build.yml`. Heroku keeps building `main` exactly as before until you disconnect it.

---

## Possible improvements (out of scope)

- Auto-deploy from the Action via GitHub OIDC → AWS IAM role → SSM `send-command` running `deploy.sh`. Removes the manual Step in the daily loop.
- CloudFront in front of the box for global caching.
- Scheduled EBS snapshots for backups.
