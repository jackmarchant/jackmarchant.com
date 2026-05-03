# Deploying a static site to EC2 from an iPad

Recipe for hosting a pre-built static site on a small EC2 box without ever installing a build toolchain or SSH client locally. All work happens in Safari: GitHub web UI for code, AWS Console for infra, AWS Systems Manager Session Manager for shell.

## Architecture

- **GitHub Actions** runs your build on every push to `main` and force-pushes the resulting static output to a dedicated `deploy` branch whose root **is** the site.
- **EC2 (Amazon Linux 2023, t3.micro)** clones `deploy` and serves it with Nginx. Nothing else runs on the box.
- **Let's Encrypt HTTPS** terminates on the box, auto-renewing.
- **SSM Session Manager** for browser-based shell — no SSH keys, no port 22.

Daily loop: edit content in GitHub web UI → Action rebuilds (~30s) → SSM in → run `deploy.sh`.

## Placeholders

| Placeholder | Example |
|---|---|
| `OWNER/REPO` | `jackmarchant/jackmarchant.com` |
| `SITENAME` | short slug for IAM role / SG / paths |
| `DOMAIN` | apex domain |
| `EMAIL` | for Let's Encrypt expiry warnings |

⚠️ **Never paste angle brackets into commands or config.** They're documentation syntax. A stray `<DOMAIN>` inside an Nginx `server_name` silently breaks the site and confuses certbot.

## Step 1 — GitHub Action

Add `.github/workflows/build.yml` via the GitHub web editor. Adapt the build step to your stack — anything that produces a static output directory works (Hugo, Jekyll, Eleventy, Astro, custom build scripts, etc.). The job's contract is: leave the static site in a known directory, then force-push that directory's contents to the `deploy` branch.

```yaml
name: build-and-publish-dist
on:
  push: { branches: [main] }
  workflow_dispatch:
permissions: { contents: write }
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      # --- replace with your build steps ---
      - run: ./build.sh   # must produce ./dist
      # -------------------------------------
      - name: Publish to deploy branch
        run: |
          cd dist
          git init -b deploy
          git config user.name  "github-actions[bot]"
          git config user.email "github-actions[bot]@users.noreply.github.com"
          git add -A && git commit -m "build: ${GITHUB_SHA::7}"
          git push --force "https://x-access-token:${{ secrets.GITHUB_TOKEN }}@github.com/${{ github.repository }}.git" deploy
```

After it runs, confirm the `deploy` branch exists and `index.html` is at its root.

## Step 2 — AWS infrastructure

1. **IAM role** `SITENAME-web-ssm` (EC2 trusted entity, attach `AmazonSSMManagedInstanceCore`).
2. **Launch EC2** — Amazon Linux 2023, t3.micro, *no key pair*. ⚠️ The **IAM instance profile** field is hidden under "Advanced details" — easy to miss on mobile. Attach `SITENAME-web-ssm`. Without it, SSM refuses to connect.
3. **Security group** — inbound HTTP (80) + HTTPS (443) from anywhere; no port 22.
4. **Elastic IP** allocated and associated, so the IP survives stop/start.
5. **Connect** via EC2 → Connect → Session Manager. If the instance shows Offline, the IAM profile didn't attach: Actions → Security → Modify IAM role, then reboot.

## Step 3 — Server setup (in the SSM shell, as root)

```bash
dnf install -y nginx git
systemctl enable --now nginx
mkdir -p /var/www && chown ec2-user:ec2-user /var/www
git clone --branch deploy --single-branch \
  https://github.com/OWNER/REPO.git /var/www/SITENAME
```

## Step 4 — Nginx site config

Write `/etc/nginx/conf.d/SITENAME.conf` with a server block listening on port 80, `server_name DOMAIN www.DOMAIN;`, root pointing at `/var/www/SITENAME`, and a `try_files` rule for clean URLs (`$uri $uri/ $uri.html $uri/index.html =404`).

After writing, **always** verify the substitution:
```bash
grep server_name /etc/nginx/conf.d/SITENAME.conf
```
The output must show your bare domain — no angle brackets. Then `nginx -t && systemctl reload nginx`. Hit `http://<elastic-ip>/` to confirm it loads.

## Step 5 — DNS

Point `DOMAIN` and `www.DOMAIN` A records at the Elastic IP. Confirm with `dig +short DOMAIN` before continuing.

## Step 6 — HTTPS

```bash
dnf install -y python3-certbot python3-certbot-nginx
certbot --nginx -d DOMAIN -d www.DOMAIN --redirect --agree-tos -m EMAIL --no-eff-email
certbot renew --dry-run
```

If certbot complains it can't find a matching server block, your `server_name` doesn't exactly match the `-d` argument (often the angle-bracket trap). The cert is still issued — fix `server_name`, reload Nginx, then `certbot install --cert-name DOMAIN` reuses the existing cert without a fresh Let's Encrypt request.

## Step 7 — Redeploy script

Drop a script at `/var/www/SITENAME/deploy.sh` that does:
```bash
cd /var/www/SITENAME && git fetch origin deploy && git reset --hard origin/deploy
```
`git reset --hard` (not `pull`) is correct because the Action force-pushes `deploy` on every build.

## Daily publishing

Edit content in GitHub → wait for the Action → SSM in → `sudo /var/www/SITENAME/deploy.sh`.

## Gotchas worth remembering

1. **IAM instance profile is under "Advanced details"** in the EC2 launch wizard. Verify it before launching.
2. **Angle brackets are documentation syntax only.** After any heredoc, `grep` the result to confirm placeholders were substituted.
3. **Amazon Linux's stock `nginx.conf` ships a default `server { server_name _; }` block.** Harmless once your `server_name` is correct, but a red herring while diagnosing.
4. **Force-push deploys** mean the server uses `git reset --hard`, never `git pull`.

## Possible improvements

- Auto-deploy via GitHub OIDC → AWS IAM role → SSM `send-command` running `deploy.sh`, removing the manual SSM step.
- CloudFront in front of the box for global caching.
- Scheduled EBS snapshots for backups.
