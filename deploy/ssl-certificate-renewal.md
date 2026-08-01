# SSL certificate renewal

jackmarchant.com is served from an EC2 instance running nginx, with the TLS
certificate issued by Let's Encrypt via certbot. None of this is tracked in
this repo (no Terraform/Ansible for this box) — this doc is the only record
of how it's configured.

- OS: Amazon Linux 2023
- certbot binary: `/usr/bin/certbot`
- Web server: nginx (see `deploy/nginx.conf.example` for the caching rules
  that should live in its `server {}` block — unrelated to TLS)

## Why the cert expired (2026-08)

Let's Encrypt certs are valid for 90 days and rely on an automated renewal
job. On this box, no such job existed — no `certbot.timer` (AL2023's
minimal install doesn't ship a systemd timer for certbot) and no cron job
(`cronie` isn't installed by default either, so `crontab` wasn't even
available). The cert was issued once, manually, and never renewed.

## Auto-renewal setup

Since cron isn't installed on this box, renewal runs via a systemd timer
instead (systemd is available by default, no extra package needed).

`/etc/systemd/system/certbot-renew.service`:
```ini
[Unit]
Description=Certbot renewal

[Service]
Type=oneshot
ExecStart=/usr/bin/certbot renew --quiet --deploy-hook "systemctl reload nginx"
```

`/etc/systemd/system/certbot-renew.timer`:
```ini
[Unit]
Description=Run certbot renewal twice daily

[Timer]
OnCalendar=*-*-* 03,15:00:00
RandomizedDelaySec=3600
Persistent=true

[Install]
WantedBy=timers.target
```

Enabled with:
```bash
sudo systemctl daemon-reload
sudo systemctl enable --now certbot-renew.timer
```

The `--deploy-hook` reloads nginx after a real renewal so it picks up the
new cert without downtime. `certbot renew` itself is a no-op unless the
cert is within 30 days of expiry, so running it twice a day is safe.

## Verifying it's still working

```bash
systemctl list-timers | grep certbot-renew   # should show a NEXT run time
sudo systemctl status certbot-renew.timer
sudo certbot renew --dry-run                 # simulates a renewal, no changes made
```

If the cert ever expires again, start by checking whether
`certbot-renew.timer` is still enabled and firing (`systemctl status`,
`journalctl -u certbot-renew.service`) before assuming a new root cause.
