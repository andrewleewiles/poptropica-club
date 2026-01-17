# Poptropica Club - Technical Documentation

## Project Overview

Poptropica Club is a fan community platform consisting of:
- **Landing Page** (poptropica.club) - Main entry point
- **Blog** (blog.poptropica.club) - WordPress-powered blog
- **Wiki** (poptropica.wiki) - MediaWiki-powered wiki
- **Passport** - Central authentication system (SSO)

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     Cloudflare Pages                            │
│  poptropica.club (static site)                                  │
│  - /landing/     - Landing page                                 │
│  - /login/       - Login page                                   │
│  - /join/        - Registration page                            │
│  - /passport/    - User dashboard                               │
│  - /emailcheck/  - Email verification confirmation              │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                     VPS (178.156.210.210)                       │
│  Docker Compose Services:                                       │
│  - auth-service  (auth.poptropica.club)                        │
│  - wordpress     (blog.poptropica.club)                        │
│  - mediawiki     (poptropica.wiki)                             │
│  - mariadb       (database)                                     │
│  - nginx         (reverse proxy)                                │
└─────────────────────────────────────────────────────────────────┘
```

## Directory Structure

```
/poptropicaClub/
├── website/                    # Cloudflare Pages static site
│   ├── landing/               # Landing page
│   │   └── index.html
│   ├── login/                 # Login page
│   │   └── index.html
│   ├── join/                  # Registration page
│   │   └── index.html
│   ├── passport/              # User dashboard
│   │   └── index.html
│   ├── emailcheck/            # Email verification confirmation
│   │   └── index.html
│   ├── email-preview/         # Email template previews
│   │   ├── index.html
│   │   ├── verification.html
│   │   ├── welcome.html
│   │   ├── password-reset.html
│   │   └── newsletter.html
│   └── shared/                # Shared assets
│       ├── fonts/
│       │   ├── GrilledCheeseBTNRegular.TTF
│       │   └── CREABBRG.TTF
│       ├── email/
│       │   └── welcome-text.png
│       ├── logo.png
│       ├── pcLogo2.svg
│       ├── pageBg.png
│       ├── popPassportLogo@3x.png
│       ├── cloudPath.svg
│       └── siteIcon.png
│
├── server/                     # Server-side code
│   ├── auth-service/          # Central authentication API
│   │   ├── api/
│   │   │   └── index.php      # Main API router
│   │   ├── includes/
│   │   │   ├── Auth.php       # Authentication logic
│   │   │   ├── Database.php   # Database wrapper
│   │   │   ├── Email.php      # Email templates & sending
│   │   │   └── PlatformSync.php # SSO sync logic
│   │   ├── sso/
│   │   │   └── sync.php       # SSO sync endpoint
│   │   ├── Dockerfile
│   │   ├── nginx.conf
│   │   └── supervisord.conf
│   │
│   ├── wordpress-plugin/      # Poptropica Club Auth plugin
│   │   └── poptropica-club-auth/
│   │       ├── poptropica-club-auth.php
│   │       └── includes/
│   │           └── class-avatar.php
│   │
│   ├── wordpress-theme/       # Custom WordPress theme
│   │   └── poptropica-club/
│   │
│   ├── mediawiki-extension/   # MediaWiki SSO extension
│   │   └── PoptropicaSSO/
│   │       ├── extension.json
│   │       └── includes/
│   │           └── Hooks.php
│   │
│   ├── nginx/                 # Nginx configuration
│   │   └── conf.d/
│   │
│   └── docker-compose.yml     # Docker services definition
│
└── DOCUMENTATION.md           # This file
```

---

## Authentication System (Poptropica Passport)

### Overview
The Passport system provides centralized authentication across all Poptropica Club platforms using JWT tokens and session-based SSO.

### API Endpoints (auth.poptropica.club)

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/auth/register` | POST | Create new account |
| `/api/auth/login` | POST | Authenticate user |
| `/api/auth/logout` | POST | End session |
| `/api/auth/verify` | GET | Verify email (token in URL) |
| `/api/auth/validate` | POST | Validate JWT token |
| `/api/auth/user` | GET | Get current user data |
| `/api/auth/avatar` | POST | Upload avatar image |
| `/sso/sync` | GET | SSO sync chain for platforms |

### Registration Flow
1. User fills form on `/join/`
2. Cloudflare Turnstile validates human
3. POST to `/api/auth/register` with:
   - email, username, password, birthdate, newsletter opt-in, turnstile_token
4. Auth service creates pending user in database
5. Verification email sent via MailerSend API
6. User redirected to `/emailcheck/?email=...`

### Email Verification Flow
1. User clicks link in verification email
2. GET `/api/auth/verify?token=...`
3. Auth service:
   - Validates token
   - Activates account
   - Creates WordPress account via REST API
   - Creates MediaWiki account via API
   - Generates login JWT token
   - Sends welcome email
4. Redirects through SSO sync chain
5. Lands on `/passport/?verified=1&auth_token=...`
6. Passport page validates token and stores in localStorage

### Login Flow
1. User enters credentials on `/login/`
2. POST to `/api/auth/login`
3. JWT token returned
4. Token stored in localStorage (`pc_auth_token`)
5. User data stored in localStorage (`pc_auth_user`)
6. If `redirect` param present, goes through SSO sync

### SSO Sync Chain
The SSO sync ensures users are logged into all platforms:
1. `/sso/sync?redirect=...` sets PHP session
2. Redirects to WordPress SSO endpoint
3. WordPress plugin logs user in, redirects to MediaWiki
4. MediaWiki extension logs user in
5. Final redirect to original destination

### JWT Token Structure
```json
{
  "user_id": 123,
  "username": "example",
  "email": "user@example.com",
  "iat": 1234567890,
  "exp": 1234567890
}
```

---

## Website Pages

### Landing Page (`/landing/`)
- Animated logo with mouse avoidance effect
- Cloud border background
- "Join the Club" button → `/join/`
- "Login" button → `/login/`
- Links to Blog, Wiki, Discord

### Login Page (`/login/`)
- Animated logo with mouse avoidance
- Dynamic cloud border
- Login form (username/email + password)
- "Forgot password" and "Create account" links
- Handles `redirect` param for SSO flow

### Join Page (`/join/`)
- Animated logo with mouse avoidance
- Dynamic cloud border
- Registration form:
  - Email
  - Username
  - Password (with confirmation)
  - Birthdate
  - Newsletter opt-in checkbox
- Cloudflare Turnstile captcha
- Redirects to `/emailcheck/` on success

### Email Check Page (`/emailcheck/`)
- Shows after successful registration
- Displays user's email from URL param
- Instructions to check email/spam folder
- Link back to registration

### Passport Page (`/passport/`)
- Requires authentication (checks localStorage)
- Displays user profile:
  - Avatar (click to change)
  - Username
  - Member since date
- Quick links to Blog, Wiki, Discord
- Recent blog posts section
- Recent wiki activity section
- Logout button → redirects to `/landing/`

### Handling Auth Token from URL
The passport page checks for `auth_token` in URL params (from email verification redirect):
```javascript
const authTokenFromUrl = urlParams.get('auth_token');
if (authTokenFromUrl) {
    // Validate with API, store in localStorage
    // Clean URL to remove token
}
```

---

## Email Templates

### Location
Templates are defined in `/server/auth-service/includes/Email.php`

Preview pages at `/email-preview/`:
- `verification.html` - Email verification
- `welcome.html` - Welcome after verification
- `password-reset.html` - Password reset
- `newsletter.html` - Newsletter template

### Sending
Emails sent via MailerSend API:
- API Token: `PC_MAILERSEND_API_TOKEN` env var
- From: `noreply@poptropica.club`
- From Name: `Poptropica Passport`

### Template Design
All emails use:
- Background: `pageBg.png` with `#0098f5` fallback
- Content: White rounded container with shadow
- Header: Main logo (`logo.png`)
- Buttons: Green gradient with shadow
- Footer: "Poptropica Passport" + poptropica.club link

### Verification Email
- Subject: "Verify your Poptropica Passport"
- Heading: "Verify Your Email"
- CTA: "Verify My Account" button
- Fallback link text below

### Welcome Email
- Subject: "Your Poptropica Passport is ready!"
- Heading: "Welcome to the Club!" (rendered PNG image)
- Text: "Your account is now active on the blog, wiki, and more."
- CTAs: Visit Blog, Visit Wiki, Join Discord

### Password Reset Email
- Subject: "Reset your Poptropica Passport password"
- Heading: "Reset Your Password"
- CTA: "Reset Password" button
- Expiry: 1 hour

---

## Visual Components

### Animated Logo
The main "Messy Sinker's Poptropica Club" logo (`pcLogo2.svg`) features:
- Individual letter animations (floating effect)
- Mouse avoidance behavior (letters push away from cursor)
- 8 different float animation keyframes
- Smooth easing on push/return

Implementation:
```javascript
// Mouse avoidance parameters
const logoAvoidRadius = 300;  // Detection radius in pixels
const logoMaxPush = 120;      // Maximum push distance
const ease = 0.15;            // Smooth return easing
```

### Dynamic Cloud Border
Cloud borders use SVG with dynamically generated circles:
- Path loaded from `cloudPath.svg`
- 200 circles placed along the path with sine wave offset for organic look
- Circles have random size variation and subtle jitter animation
- Corners have larger circles for visual weight (1.4x boost)

Three-zone stretching (used on landing page and blog):
- Top 20%: Scales uniformly with X (maintains rounded corners)
- Middle 60%: Stretches vertically to fill available height
- Bottom 20%: Scales uniformly with X, anchored to bottom

Implementation details:
```javascript
// Transform function: 3-zone vertical scaling
function transformPoint(x, y) {
    const newX = (x - origViewBox.x) * scaleX;
    const localY = y - origViewBox.y;

    if (localY < topSliceHeight) {
        // Top 20%: scale uniformly with X
        newY = localY * scaleX;
    } else if (localY > origViewBox.height - bottomSliceHeight) {
        // Bottom 20%: scale uniformly, anchor to bottom
        const fromBottom = origViewBox.height - localY;
        newY = svgHeight - (fromBottom * scaleX);
    } else {
        // Middle 60%: stretch to fill remaining space
        const middleLocalY = localY - topSliceHeight;
        const middleRatio = middleLocalY / origMiddleHeight;
        newY = fixedTopHeight + (middleRatio * stretchedMiddleHeight);
    }
    return { x: newX, y: newY };
}
```

### Fonts
- **GrilledCheese BTN**: Used for headings, buttons
- **Creabbrg**: Used for body text

---

## Deployment

### Static Site (Cloudflare Pages)
```bash
cd /Users/andrewwiles/Desktop/poptropicaClub/website
npx wrangler pages deploy . --project-name=poptropica-club
```

### Auth Service
```bash
# Sync files to server
rsync -avz /Users/andrewwiles/Desktop/poptropicaClub/server/auth-service/ \
    root@178.156.210.210:/opt/poptropica/auth-service/

# Rebuild and restart
ssh root@178.156.210.210 "cd /opt/poptropica && \
    docker compose build auth-service && \
    docker compose up -d auth-service"
```

### WordPress Plugin
```bash
rsync -avz /Users/andrewwiles/Desktop/poptropicaClub/server/wordpress-plugin/ \
    root@178.156.210.210:/opt/poptropica/wordpress-plugin/

ssh root@178.156.210.210 "cd /opt/poptropica && \
    docker compose restart wordpress"
```

### MediaWiki Extension
```bash
rsync -avz /Users/andrewwiles/Desktop/poptropicaClub/server/mediawiki-extension/ \
    root@178.156.210.210:/opt/poptropica/mediawiki-extension/

ssh root@178.156.210.210 "cd /opt/poptropica && \
    docker compose restart mediawiki"
```

---

## Database Schema

### Main Tables (pc_ prefix)

**pc_users**
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| email | VARCHAR | Unique email |
| username | VARCHAR | Unique username |
| password | VARCHAR | Hashed password |
| birthdate | DATE | User's birthdate |
| newsletter_opt_in | TINYINT | Newsletter preference |
| email_verified | TINYINT | Verification status |
| verification_token | VARCHAR | Email verification token |
| created_at | TIMESTAMP | Registration date |
| avatar_url | VARCHAR | Profile picture URL |

**pc_sessions**
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to pc_users |
| token | VARCHAR | JWT token |
| platform | VARCHAR | Origin platform |
| ip_address | VARCHAR | Login IP |
| user_agent | TEXT | Browser info |
| created_at | TIMESTAMP | Session start |
| expires_at | TIMESTAMP | Session expiry |

**pc_platform_links**
| Column | Type | Description |
|--------|------|-------------|
| id | INT | Primary key |
| user_id | INT | Foreign key to pc_users |
| platform | VARCHAR | 'wordpress' or 'mediawiki' |
| platform_user_id | INT | User ID on that platform |

---

## Environment Variables

### Auth Service (.env)
```
PC_DB_HOST=mariadb
PC_DB_NAME=poptropica_auth
PC_DB_USER=poptropica
PC_DB_PASS=<password>
PC_JWT_SECRET=<secret>
PC_MAILERSEND_API_TOKEN=<token>
PC_FROM_EMAIL=noreply@poptropica.club
PC_FROM_NAME=Poptropica Passport
PC_TURNSTILE_SECRET=<secret>
PC_WP_API_URL=http://wordpress/wp-json
PC_WP_API_USER=<user>
PC_WP_API_PASS=<password>
PC_MW_API_URL=http://mediawiki/api.php
PC_MW_BOT_USER=<user>
PC_MW_BOT_PASS=<password>
```

---

## External Services

### Cloudflare
- **Pages**: Hosts static website
- **Turnstile**: Captcha for registration form
- **DNS**: Domain management

### MailerSend
- Transactional email delivery
- API-based sending
- Domain: poptropica.club

### Discord
- Community server: https://discord.gg/XkQ5ww8BhE

---

## Common Tasks

### Add New User Manually
```sql
INSERT INTO pc_users (email, username, password, email_verified, created_at)
VALUES ('user@example.com', 'username', '<hashed_password>', 1, NOW());
```

### Reset User Password
Use the password reset flow or update directly:
```sql
UPDATE pc_users SET password = '<new_hashed_password>' WHERE email = 'user@example.com';
```

### Check Docker Logs
```bash
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose logs -f auth-service"
```

### Database Access
```bash
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose exec mariadb mariadb -u root -p"
```

---

## Troubleshooting

### "Security check failed" on registration
- Check Turnstile secret key in auth-service .env
- Verify field name matches: `turnstile_token`

### SSO not working
- Check PHP session cookies are being set
- Verify redirect URLs are correct
- Check platform API credentials

### Emails not sending
- Verify MailerSend API token
- Check auth-service logs for curl errors
- Ensure from email domain is verified in MailerSend

### Avatar not syncing
- Check PlatformSync.php for errors
- Verify platform_links table has correct mappings
- Ensure avatar URL is accessible

---

## Game Profile Integration (play.poptropica.club)

### Overview
The Poptropica Legacy browser game at `play.poptropica.club` integrates with Poptropica Passport to allow users to save their game progress to the cloud. Logged-in users' profiles are stored server-side and synced across devices.

### Architecture
```
┌─────────────────────────────────────────────────────────────────┐
│                  play.poptropica.club                           │
│  Browser-based Flash game (Ruffle WebAssembly)                  │
│                                                                 │
│  Key JS files:                                                  │
│  - auth-manager.js     (authentication state)                   │
│  - profile-sync.js     (server sync logic)                      │
│  - profile-manager-lite.js (local profile management)           │
│  - ruffle-bridge.js    (Flash ExternalInterface callbacks)      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                  auth.poptropica.club                           │
│  Game Profile API Endpoints:                                    │
│  - /api/auth/game/profile/load    (GET)                        │
│  - /api/auth/game/profile/save    (POST)                       │
│  - /api/auth/game/profile/migrate (POST)                       │
└─────────────────────────────────────────────────────────────────┘
```

### Authentication Flow from Game
1. User clicks "Log In" on play.poptropica.club
2. Redirect to: `poptropica.club/login/?redirect=https://play.poptropica.club/&platform=game`
3. User authenticates on login page
4. Login page appends `auth_token` to redirect URL
5. SSO sync chain: WordPress → MediaWiki → final redirect
6. Redirect back: `play.poptropica.club/?auth_token=JWT_TOKEN`
7. `auth-manager.js` validates token with `/api/auth/validate`
8. Token stored in sessionStorage
9. `profile-sync.js` checks for server profiles
10. If no server profile exists, redirect to import page

### Profile Import Page
Location: `poptropica.club/passport/import/`

Allows users to import profile JSON files exported from the desktop version:
- Supports multiple profile formats (v1, v2, nested, flat)
- Parses and flattens nested v2 format (appearance/user/progress/rawSO)
- Uploads profile data to server via `/api/auth/game/profile/save`

### Game Profile API Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/auth/game/profile/load` | GET | Load user's game profiles |
| `/api/auth/game/profile/save` | POST | Save/update a game profile |
| `/api/auth/game/profile/migrate` | POST | Migrate guest profile to account |
| `/api/auth/game/profile/delete` | POST | Delete a game profile |

#### Load Profile Response
```json
{
  "success": true,
  "profiles": [{
    "id": 123,
    "profile_name": "Default",
    "profile_data": { /* full profile object */ },
    "sync_version": 6,
    "last_island": "Early",
    "last_room": "City2",
    "credits": 500,
    "updated_at": "2026-01-17T12:00:00Z"
  }]
}
```

#### Save Profile Request
```json
{
  "profile_name": "Default",
  "profile_data": { /* full profile object */ },
  "sync_version": 5
}
```

#### Sync Conflict Response (409)
```json
{
  "success": false,
  "error": "sync_conflict",
  "server_version": 7,
  "server_data": { /* server's profile data */ }
}
```

### Database Schema: pc_game_profiles

Profile data is stored in individual columns for better querying:

**Character Info**
| Column | Type | JSON Field |
|--------|------|------------|
| login | VARCHAR(60) | login |
| poptropica_dbid | VARCHAR(50) | dbid |
| first_name | VARCHAR(50) | firstName |
| last_name | VARCHAR(50) | lastName |
| age | TINYINT | age |
| gender | TINYINT | gender |
| mem_status | VARCHAR(20) | mem_status |
| registered | TINYINT(1) | Registred |

**Appearance**
| Column | Type | JSON Field |
|--------|------|------------|
| skin_color | INT | skinColor |
| hair_color | INT | hairColor |
| line_color | INT | lineColor |
| line_width | TINYINT | lineWidth |
| eyelid_pos | TINYINT | eyelidPos |
| eyes_frame | VARCHAR(20) | eyesFrame |
| marks_frame | VARCHAR(20) | marksFrame |
| pants_frame | VARCHAR(20) | pantsFrame |
| shirt_frame | VARCHAR(20) | shirtFrame |
| hair_frame | VARCHAR(20) | hairFrame |
| mouth_frame | VARCHAR(20) | mouthFrame |
| item_frame | VARCHAR(20) | itemFrame |
| pack_frame | VARCHAR(20) | packFrame |
| facial_frame | VARCHAR(20) | facialFrame |
| overshirt_frame | VARCHAR(20) | overshirtFrame |
| overpants_frame | VARCHAR(20) | overpantsFrame |
| special_ability | VARCHAR(50) | specialAbility |
| special_ability_params | JSON | specialAbilityParams |

**Location**
| Column | Type | JSON Field |
|--------|------|------------|
| last_island | VARCHAR(50) | lastIsland |
| last_room | VARCHAR(50) | lastRoom |
| last_char_x | INT | lastCharX |
| last_char_y | INT | lastCharY |

**Progress (JSON columns)**
| Column | Type | JSON Field |
|--------|------|------------|
| visited | TEXT | visited |
| games | TEXT | games |
| inventory | JSON | inventory |
| completed_events | JSON | completedEvents |
| removed_items | JSON | removedItems |
| island_completions | JSON | islandCompletions |
| island_times | JSON | islandTimes |
| updated_islands | JSON | updatedIslands |
| user_data | JSON | userData |
| raw_so | JSON | rawSO |

**Economy**
| Column | Type | JSON Field |
|--------|------|------------|
| credits | INT | credits |

### Browser-Side Components

#### auth-manager.js
Handles authentication state:
- `init()` - Check for callback token or restore session
- `isLoggedIn()` - Check auth state
- `getToken()` - Get JWT token for API calls
- `login()` - Redirect to login page
- `logout()` - Clear session
- `apiRequest(endpoint, options)` - Make authenticated API calls

#### profile-sync.js
Handles bidirectional sync:
- `init()` - Initialize sync manager, check for server profiles
- `loadProfile()` - Load profile from server (or localStorage if offline)
- `saveProfile(data)` - Save to server and localStorage
- `migrateGuestProfile()` - Transfer local profile to account
- `checkForServerProfiles()` - Check if user has server profiles
- Auto-sync every 60 seconds when logged in

#### ruffle-bridge.js
Flash ExternalInterface integration:
- `window.LoadProfileData` - Callback registered by Flash, called by JS to load profile into game
- `window.SaveProfileData` - Called by Flash to save profile data
- `loadProfileIntoGame()` - Calls Flash's LoadProfileData with retry logic

### Deployment

#### Browser Files (play.poptropica.club)
```bash
rsync -avz browser/ root@178.156.210.210:/opt/poptropica/play/
```

#### Auth Service (game profile endpoints)
```bash
rsync -avz server/auth-service/ root@178.156.210.210:/opt/poptropica/auth-service/
ssh root@178.156.210.210 "cd /opt/poptropica && docker compose build auth-service && docker compose up -d auth-service"
```

#### Import Page (Cloudflare Pages)
```bash
cd website
git add passport/import/
git commit -m "Update import page"
git push  # Auto-deploys to Cloudflare Pages
```

#### Cache Purge
After deploying JS files, purge Cloudflare cache:
```bash
curl -X POST "https://api.cloudflare.com/client/v4/zones/b4d0c309983c1c4ec56a7119bee48d3d/purge_cache" \
  -H "X-Auth-Email: andrewlwiles@icloud.com" \
  -H "X-Auth-Key: <CF_GLOBAL_API_KEY>" \
  -H "Content-Type: application/json" \
  --data '{"purge_everything":true}'
```

---

## Version History

- **January 2026**: Initial documentation
  - Central auth system with SSO
  - Email verification flow
  - Avatar sync across platforms
  - Custom email templates
  - Dynamic cloud borders
  - Animated logo with mouse avoidance
- **January 2026**: Game Profile Integration
  - play.poptropica.club authentication flow
  - Server-side game profile storage
  - Profile import from desktop version
  - Individual database columns for profile data
  - Auto-sync between browser and server
