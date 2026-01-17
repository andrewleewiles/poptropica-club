# CLAUDE.md - Critical Project Instructions

## CRITICAL: Docker Volume Preservation

**NEVER recreate or remove Docker volumes.** The following volumes contain persistent data that cannot be recovered:

| Volume Name | Contains | CRITICAL |
|-------------|----------|----------|
| `poptropica_wordpress_html` | WordPress installation, uploads, plugins | YES |
| `poptropica_mariadb_data` | All databases (WordPress, MediaWiki, Auth) | YES |
| `server_mediawiki_html` | MediaWiki installation, LocalSettings.php | YES |

### When modifying docker-compose.yml:

1. **NEVER run `docker compose down -v`** - This deletes volumes
2. **NEVER remove the `external: true` declarations** from volumes
3. **ALWAYS verify volume names match existing volumes** before restarting
4. **If adding new mounts**, use bind mounts (`./path:/container/path`) not new volumes
5. **Before running `docker compose up -d`**, check that no volume conflicts will occur

### Required nginx mounts (do not remove):

```yaml
nginx:
  volumes:
    - ./nginx/nginx.conf:/etc/nginx/nginx.conf:ro
    - ./nginx/conf.d:/etc/nginx/conf.d:ro
    - wordpress_html:/var/www/html:ro
    - mediawiki_html:/var/www/mediawiki:ro
    - ./wordpress-theme/poptropica-club:/var/www/html/wp-content/themes/poptropica-club:ro
    - ./files:/var/www/files:ro
```

### Required WordPress mounts:

```yaml
wordpress:
  volumes:
    - wordpress_html:/var/www/html
    - ./wordpress-theme/poptropica-club:/var/www/html/wp-content/themes/poptropica-club
```

### Required MediaWiki volume:

```yaml
mediawiki:
  volumes:
    - mediawiki_html:/var/www/html  # Maps to server_mediawiki_html
```

## Server Architecture

```
VPS: 178.156.210.210
├── cloudflared (Cloudflare Tunnel)
├── nginx (reverse proxy)
│   ├── blog.poptropica.club → wordpress
│   ├── poptropica.wiki → mediawiki
│   ├── auth.poptropica.club → auth-service
│   └── files.poptropica.club → /var/www/files
├── wordpress (PHP-FPM)
├── mediawiki
├── auth-service (custom PHP)
├── mariadb (databases)
└── mail-server
```

## Deployment Commands

### WordPress Theme (safe):
```bash
rsync -avz server/wordpress-theme/ root@178.156.210.210:/opt/poptropica/wordpress-theme/
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose restart wordpress nginx"
```

**IMPORTANT: Cache Busting for CSS Changes**

When updating CSS, you MUST bump the version number in `functions.php` to bust browser caches:

```php
// In server/wordpress-theme/poptropica-club/functions.php
wp_enqueue_style('poptropica-club-style', get_stylesheet_uri(), array(), '3.5');  // <-- Increment this version
```

Always increment the version number (e.g., 3.5 → 3.6) when deploying CSS changes, otherwise users will see cached old styles.

### Auth Service (safe):
```bash
rsync -avz server/auth-service/ root@178.156.210.210:/opt/poptropica/auth-service/
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose build auth-service && docker compose up -d auth-service"
```

### Static Site (safe):
```bash
cd website && npx wrangler pages deploy . --project-name=poptropica-club
```

### Docker Compose changes (DANGEROUS - be careful):
```bash
# First, verify changes won't affect volumes
rsync -avz server/docker-compose.yml root@178.156.210.210:/opt/poptropica/
# Then restart ONLY the specific service that changed
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose up -d <service-name>"
```

## What NOT to do

1. **DO NOT** run `docker compose down` without the user's explicit permission
2. **DO NOT** modify volume declarations without checking existing volumes first
3. **DO NOT** assume volumes can be recreated - they contain user data
4. **DO NOT** remove bind mounts from nginx without understanding what they serve
5. **DO NOT** change the `external: true` or `name:` properties of volumes

## MediaWiki API Authentication

To authenticate with the MediaWiki API using bot credentials from `.credentials`:

```bash
# Step 1: Get login token
curl -s -c /tmp/wiki_cookies.txt \
  "https://poptropica.wiki/api.php?action=query&meta=tokens&type=login&format=json" > /tmp/login_token.json

# Step 2: Extract token (must use Python to properly unescape JSON)
LOGIN_TOKEN=$(python3 -c "import json; print(json.load(open('/tmp/login_token.json'))['query']['tokens']['logintoken'])")

# Step 3: Login with bot credentials (use --data-urlencode for proper encoding)
curl -s -b /tmp/wiki_cookies.txt -c /tmp/wiki_cookies.txt \
  --data-urlencode "action=login" \
  --data-urlencode "lgname=Puterpop@rabbot" \
  --data-urlencode "lgpassword=<from .credentials MW_BOT_PASSWORD>" \
  --data-urlencode "lgtoken=$LOGIN_TOKEN" \
  --data-urlencode "format=json" \
  "https://poptropica.wiki/api.php"

# Step 4: For subsequent API calls requiring authentication, get CSRF token
CSRF_TOKEN=$(curl -s -b /tmp/wiki_cookies.txt \
  "https://poptropica.wiki/api.php?action=query&meta=tokens&type=csrf&format=json" | \
  python3 -c "import json,sys; print(json.load(sys.stdin)['query']['tokens']['csrftoken'])")
```

**Important:** The login token contains `+\` which requires proper JSON unescaping. Using `grep`/`sed` will fail - always use Python's `json` module.

## MediaWiki Customization

### CSS Locations (in order of precedence)

1. **`MediaWiki:Common.css`** - Wiki page editable via API or web interface
   - Contains the clickable logo styling (`#wiki-logo-link`)
   - Edit via: `https://poptropica.wiki/wiki/MediaWiki:Common.css`

2. **`LocalSettings.php`** - Server-side CSS injection via `$wgHooks["BeforePageDisplay"]`
   - Contains sidebar background, header styling, search box positioning
   - Edit via: `docker exec mediawiki vi /var/www/html/LocalSettings.php`

3. **Vector skin custom.css** - `/skins/Vector/resources/skins.vector.styles/custom.css`
   - Contains sidebar background pseudo-element
   - Edit via: `docker exec mediawiki vi /var/www/html/skins/Vector/resources/skins.vector.styles/custom.css`

### Custom Logo Implementation

The wiki logo is implemented via JavaScript (not the standard Vector skin logo):

- **Source**: `MediaWiki:Common.js` adds a clickable `<a id="wiki-logo-link">` element
- **Styling**: `MediaWiki:Common.css` positions the logo
- **Image**: `/images/8/85/PoptropicaWikiLogo.png`

To change logo positioning:
```bash
# Edit MediaWiki:Common.css via API or directly edit the wiki page
# Key CSS properties:
#wiki-logo-link {
    position: absolute;  /* Use 'absolute' to scroll with page, 'fixed' to stay in place */
    left: 180px;
    top: 30px;
}
```

### Wiki Templates

Custom templates created for guides:
- `{{Notice}}` - Blue info box with exclamation mark (Grilled Cheese font)
- `{{Tip}}` - Green tip box with "TIP" label
- `{{Note}}` - Orange note box with "FYI" label
- `{{clear}}` - Clears floating elements between sections

### Cache Busting for MediaWiki CSS

After changing CSS, bump the cache epoch:
```bash
ssh root@178.156.210.210 "docker exec mediawiki sed -i 's/wgCacheEpoch = \"[0-9]*\"/wgCacheEpoch = \"$(date +%Y%m%d%H%M%S)\"/' /var/www/html/LocalSettings.php"
```

Users may also need to hard refresh (Ctrl+Shift+R) to see CSS changes.

## Recovery Notes

If MediaWiki shows setup page:
- Check if it's using the wrong volume
- The correct volume is `server_mediawiki_html` (contains LocalSettings.php)

If files.poptropica.club is down:
- Ensure `./files:/var/www/files:ro` is mounted in nginx

If WordPress theme changes don't appear:
- The theme must be bind-mounted into BOTH wordpress AND nginx containers

## Game Profile Integration (play.poptropica.club)

The browser game at `play.poptropica.club` integrates with Poptropica Passport for cloud saves.

### Key Components

| Component | Location | Purpose |
|-----------|----------|---------|
| `auth-manager.js` | browser/js/ | Token validation, login redirect |
| `profile-sync.js` | browser/js/ | Server sync, auto-save every 60s |
| `GameProfile.php` | auth-service/includes/ | Profile CRUD operations |
| Import page | website/passport/import/ | Desktop profile import |

### Database: pc_game_profiles

Profile data stored in individual columns (not JSON blob):
- Character info: login, first_name, last_name, age, gender, etc.
- Appearance: skin_color, hair_color, *_frame columns
- Location: last_island, last_room, last_char_x, last_char_y
- Progress: inventory, completed_events, etc. (JSON columns)
- Raw backup: raw_so (full SharedObject data)

### Deployment

**Browser files:**
```bash
rsync -avz browser/ root@178.156.210.210:/opt/poptropica/play/
curl -X POST "https://api.cloudflare.com/client/v4/zones/b4d0c309983c1c4ec56a7119bee48d3d/purge_cache" \
  -H "X-Auth-Email: andrewlwiles@icloud.com" \
  -H "X-Auth-Key: <CF_GLOBAL_API_KEY>" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'
```

**WordPress plugin (SSO sync):**
```bash
rsync -avz server/wordpress-plugin/poptropica-club-auth/ root@178.156.210.210:/opt/poptropica/wordpress-plugin/poptropica-club-auth/
ssh root@178.156.210.210 "docker cp /opt/poptropica/wordpress-plugin/poptropica-club-auth/. wordpress:/var/www/html/wp-content/plugins/poptropica-club-auth/"
```

### Authentication Flow

1. User clicks "Log In" on play.poptropica.club
2. Redirect to login page with `redirect=play.poptropica.club&platform=game`
3. After login, auth_token appended to redirect URL
4. SSO chain: WordPress → MediaWiki → final redirect with token
5. auth-manager.js validates token, profile-sync.js loads/creates profile

### Troubleshooting

**Profile not loading after login:**
- Check browser console for `[AuthManager]` and `[ProfileSync]` logs
- Verify auth_token is in URL after redirect
- Check sessionStorage for `pc_auth_token`

**Import page not working:**
- Verify profile JSON format (supports v1, v2, nested formats)
- Check browser console for parsing errors
- Test API directly: `curl -X GET "https://auth.poptropica.club/api/auth/game/profile/load" -H "Authorization: Bearer TOKEN"`
