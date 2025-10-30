# Disclaimer

This plugin was built specifically for the 84em.com website and its unique functionalities.

It is not intended for use on any other website.

If you chose to use & install it, you do so at your own risk.

**Want a version that you can run on your own site? [Contact 84EM](https://84em.com/contact/)**.

# 84EM Local Pages Generator Plugin

A WordPress plugin that automatically generates SEO-optimized Local Pages for each US state and city using Claude AI and WP-CLI, designed specifically for 84em.com.

## Overview

This plugin creates unique, locally-focused landing pages for WordPress development services in all 50 US states and their major cities. Each page targets location-specific keywords while incorporating geographic relevance and automatic interlinking to avoid duplicate content penalties.

## Features

- **Standard WordPress Pages**: Creates hierarchical pages with parent-child relationships (states → cities)
- **Comprehensive Coverage**: 50 state pages + 300 city pages (6 cities per state) = 350 total pages
- **WP-CLI Integration**: Complete command-line management interface with progress bars
- **Claude AI Content**: Generates unique content using Claude AI
- **Dynamic Model Selection**: Fetches available models from Claude API for interactive selection
- **Fuzzy Keyword Matching**: Intelligent algorithm ensures every service list item gets linked, even when API-generated text varies from keyword list
- **Automatic Interlinking**: City names link to city pages, service keywords link to relevant service pages
- **SEO Optimization**: Built-in SEO meta data and structured LD-JSON schema
- **Geographic Relevance**: Each page focuses on local cities and geographic context
- **Bulk Operations**: Create, update, or delete multiple pages efficiently
- **Call-to-Action Integration**: Automatic CTA placement with contact links
- **WordPress Block Editor**: Content generated in Gutenberg block format
- **Rate Limiting**: Respects API limits with configurable delays and duration tracking
- **Progress Indicators**: Real-time feedback on API requests and processing
- **XML Sitemap Generation**: Generate XML sitemaps for all local pages with WP-CLI
- **Index Page Generation**: Create or update a master index page with alphabetized state list
- **Schema Regeneration**: Fix LD-JSON schema issues without regenerating page content
- **Keyword Link Updates**: Update service keyword links when URLs change without API calls (uses fuzzy matching)

## Requirements

- WordPress 6.8 or higher
- PHP 8.2 or higher
- WP-CLI 2.0 or higher
- Claude API key from Anthropic

## Security

### Automated Security Reviews

This repository uses the official [Anthropic Claude Code Security Review](https://github.com/anthropics/claude-code-security-review) GitHub Action to automatically review all pull requests for security vulnerabilities.

#### Features

- **AI-Powered Analysis**: Claude analyzes code changes for security vulnerabilities
- **Automated PR Comments**: Security findings are posted directly on pull requests
- **Language Agnostic**: Works with PHP, JavaScript, TypeScript, and more
- **False Positive Filtering**: Focuses on high-confidence vulnerabilities
- **Dependency Scanning**: Additional checks for vulnerable dependencies

#### Setup

1. **Add Claude API Key**: Add your Anthropic API key as a GitHub secret named `ANTHROPIC_API_KEY`
   ```bash
   # Using GitHub CLI
   gh secret set ANTHROPIC_API_KEY
   
   # Or add manually in repository Settings → Secrets → Actions
   ```

2. **That's it!** The workflow automatically triggers on all pull requests

#### What It Checks

- SQL injection, XSS, command injection vulnerabilities
- Authentication and authorization flaws
- Hardcoded secrets and API keys
- Insecure cryptographic implementations
- Path traversal and file operation security
- Dependency vulnerabilities (composer and npm)

#### Manual Security Checks

```bash
# Check for dependency vulnerabilities locally
composer audit
npm audit

# Run PHP syntax checks
find . -name "*.php" -not -path "./vendor/*" -exec php -l {} +
```

## Installation

1. **Upload Plugin Files**
   ```bash
   # Upload to your WordPress plugins directory
   /wp-content/plugins/84em-local-pages/
   ```

2. **Activate Plugin**
    - Go to WordPress Admin → Plugins
    - Find "84EM Local Pages Generator"
    - Click "Activate"

3. **Verify WP-CLI Access**
   ```bash
   wp --info
   ```

## Quick Start

### Step 1: Configure API Key
```bash
wp 84em local-pages --set-api-key
# You will be prompted to securely paste your API key
```

### Step 2: Generate Everything (Recommended)
```bash
wp 84em local-pages --generate-all
# Creates 50 state pages + 300 city pages = 350 total pages
```

### Step 3: Generate Supporting Pages
```bash
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap
```

### Step 4: Verify Results
```bash
# Check created pages (using meta query to find local pages)
wp post list --post_type=page --meta_key=_local_page_state --format=count

# Check hierarchical structure
wp post list --post_type=page --meta_key=_local_page_state --format=table

# Check index page
wp post list --post_type=page --name=wordpress-development-services-usa --format=table
```

## Command Reference

### 🚀 Bulk Operations (Recommended)

**Generate/Create Everything:**
```bash
# Generate all states and cities (350 pages)
wp 84em local-pages --generate-all

# Generate states only (50 pages)
wp 84em local-pages --generate-all --states-only
```

**Update Existing Pages:**
```bash
# Update all existing states and cities
wp 84em local-pages --update-all

# Update existing states only
wp 84em local-pages --update-all --states-only
```

### API Key Management

**Set Claude API Key:**
```bash
wp 84em local-pages --set-api-key
# Interactive prompt - paste your key securely without shell history
```

**Validate API Key:**
```bash
wp 84em local-pages --validate-api-key
```

### API Model Configuration

**Set/Update API Model:**
```bash
wp 84em local-pages --set-api-model
# Fetches available models from Claude API
# Interactive selection from numbered list
# Model is validated before being saved
```

**View Current Model:**
```bash
wp 84em local-pages --get-api-model
```

**Validate Current Model:**
```bash
wp 84em local-pages --validate-api-model
```

**Clear Current Model:**
```bash
wp 84em local-pages --reset-api-model
# Clears current model configuration
# You'll need to set a new model before generating content
```

**How It Works:**
- Available models are fetched directly from Claude's Models API
- No hardcoded model list - always up-to-date with latest offerings
- Interactive numbered selection for ease of use
- Every model selection is validated before saving
- Must have both API key and model configured to generate content

### State Operations

**Generate/Update States:**
```bash
# All states (legacy command)
wp 84em local-pages --state=all

# Specific states
wp 84em local-pages --state="California"
wp 84em local-pages --state="California,New York,Texas"
```

**Update Existing States:**
```bash
# All states
wp 84em local-pages --update --state=all

# Specific states
wp 84em local-pages --update --state="California,New York"
```

### City Operations

**Generate/Update Cities:**
```bash
# All cities for a state
wp 84em local-pages --state="California" --city=all

# All cities for a state AND update state page
wp 84em local-pages --state="California" --city=all --complete

# Specific cities
wp 84em local-pages --state="California" --city="Los Angeles"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego,San Francisco"
```

### Delete Operations

**Delete States:**
```bash
# All states
wp 84em local-pages --delete --state=all

# Specific states
wp 84em local-pages --delete --state="California,New York"
```

**Delete Cities:**
```bash
# All cities for a state
wp 84em local-pages --delete --state="California" --city=all

# Specific cities
wp 84em local-pages --delete --state="California" --city="Los Angeles,San Diego"
```

### Supporting Operations

**Generate Index Page:**
```bash
wp 84em local-pages --generate-index
```

**Generate XML Sitemap:**
```bash
wp 84em local-pages --generate-sitemap
```

**Update Keyword Links (Refresh service keyword links without API calls):**
```bash
# Update keyword links in all pages
wp 84em local-pages --update-keyword-links

# Update keyword links in state pages only
wp 84em local-pages --update-keyword-links --states-only
```

**Regenerate LD-JSON Schemas (Fix schema issues without regenerating content):**
```bash
# All pages
wp 84em local-pages --regenerate-schema

# States only
wp 84em local-pages --regenerate-schema --states-only

# Specific state and its cities
wp 84em local-pages --regenerate-schema --state="California"

# Specific state only (no cities)
wp 84em local-pages --regenerate-schema --state="California" --state-only

# Specific city
wp 84em local-pages --regenerate-schema --state="California" --city="Los Angeles"
```

**Show Available Commands:**
```bash
wp 84em local-pages
```

## How It Works

### Hierarchical Structure

The plugin creates a hierarchical structure:

```
State Page (Parent)
├── City 1 Page (Child)
├── City 2 Page (Child)  
├── City 3 Page (Child)
├── City 4 Page (Child)
├── City 5 Page (Child)
└── City 6 Page (Child)
```

### URL Structure
```
# State pages
https://84em.com/wordpress-development-services-california/
https://84em.com/wordpress-development-services-texas/

# City pages (child pages)
https://84em.com/wordpress-development-services-california/los-angeles/
https://84em.com/wordpress-development-services-california/san-diego/
https://84em.com/wordpress-development-services-texas/houston/
https://84em.com/wordpress-development-services-texas/dallas/
```

### Content Generation Process

1. **State Analysis**: Plugin identifies the state and its 6 largest cities
2. **Hierarchical Creation**: Creates state page first, then child city pages
3. **Claude Prompt**: Sends structured prompts to Claude AI API with location-specific context
4. **Content Creation**: Generates unique content for each location
5. **Automatic Interlinking**: Links city names to city pages, service keywords to contact page
6. **CTA Integration**: Adds call-to-action blocks before each H2 heading
7. **SEO Integration**: Adds optimized titles, meta descriptions, and LD-JSON Schema data
8. **Page Creation**: Saves as hierarchical WordPress pages with clean URLs

### Content Strategy

**State Pages (300-400 words):**
- Geographic relevance with state and major city mentions
- Service focus on WordPress development capabilities
- City names automatically linked to their respective city pages
- Service keywords automatically linked to contact page

**City Pages (250-350 words):**
- City-specific benefits and local business context  
- Geographic references to the city and state
- Service keywords automatically linked to contact page
- Parent-child relationship with state page

### Automatic Interlinking

**State Pages:**
- ✅ City names → Link to city pages
- ✅ Service keywords → Link to service pages

**City Pages:**
- ✅ Service keywords → Link to service pages

### SEO Implementation

**State Pages:**
- **SEO Title**: "Expert WordPress Development Services in [State] | 84EM"
- **Meta Description**: State and city-specific description
- **LD-JSON Schema**: LocalBusiness schema with city containment

**City Pages:**
- **SEO Title**: "Expert WordPress Development Services in [City], [State] | 84EM"  
- **Meta Description**: City and state-specific description
- **LD-JSON Schema**: LocalBusiness schema with city focus

### Call-to-Action Features

- **Inline CTAs**: 2-3 contextual links throughout content linking to /contact/
- **Prominent CTA Blocks**: Placed before every H2 heading
- **Styled Buttons**: "Start Your WordPress Project" with custom styling
- **Natural Integration**: CTAs flow naturally within content

## API Configuration

### Claude API Setup

1. **Create Anthropic Account**: Visit [console.anthropic.com](https://console.anthropic.com)
2. **Generate API Key**: Create new API key in dashboard
3. **Configure Billing**: Set up payment method for usage-based pricing
4. **Set Rate Limits**: Configure appropriate limits for your needs

### Cost Estimates

- **Full Generation** (350 pages): $14-28 per complete run
- **State Pages Only** (50 pages): $2-4 per run
- **Individual Updates**: $0.04-0.08 per page
- **Monthly Maintenance**: $20-40 depending on update frequency

### API Settings Used

```php
'model' => 'claude-sonnet-4-20250514'
'max_tokens' => 4000
'timeout' => 600 seconds
'rate_limit' => 1 second delay between requests
```

## WordPress Pages Configuration

### Page Type Details
- **Type**: Standard WordPress pages
- **Hierarchical**: Yes (supports parent-child relationships via post_parent)
- **Public**: Yes
- **REST API**: Enabled
- **Supports**: All standard WordPress page features
- **Note**: Migrated from custom 'local' post type in v3.7.0 for simplified architecture

### Custom Fields Stored

**State Pages:**
- `_local_page_state`: State name (e.g., "California")
- `_local_page_cities`: Comma-separated 6 largest cities
- `_genesis_title`: SEO title
- `_genesis_description`: SEO meta description
- `schema`: LD-JSON structured data

**City Pages:**
- `_local_page_state`: State name (e.g., "California")
- `_local_page_city`: City name (e.g., "Los Angeles")
- `_genesis_title`: SEO title
- `_genesis_description`: SEO meta description
- `schema`: LD-JSON structured data

## Content Features

### WordPress Block Editor Format
- All content generated in Gutenberg block syntax
- Proper block markup for paragraphs, headings, and CTAs
- Bold headings with `<strong>` tags
- Clean, structured HTML output
- Smart block detection prevents duplicate wrapping (v3.0.1+)
- Full compatibility with WordPress Block Editor for editing

### Remote-First Messaging
- Emphasizes 84EM's 100% remote operations
- No mentions of on-site visits or local offices
- Focus on technical expertise and proven remote delivery
- Factual tone without hyperbole

## Index Page Feature

### Overview
The `generate-index` command creates or updates a master index page that serves as a navigation hub for all state pages. This page provides an alphabetized directory of all US states with direct links to their respective state pages.

### Index Page Details
- **Page Slug**: `wordpress-development-services-usa`
- **Page Title**: `WordPress Development Services in USA | 84EM`
- **Page Type**: Standard WordPress page (not custom post type)
- **URL**: `https://84em.com/wordpress-development-services-usa/`

### Features
- **Automatic State Discovery**: Uses WP_Query to find all published state pages
- **Alphabetical Sorting**: States are automatically sorted A-Z for easy navigation
- **Smart Create/Update**: Detects existing page and updates content, or creates new page
- **SEO Optimized**: Includes meta description and SEO title
- **WordPress Block Format**: Content generated in Gutenberg block syntax
- **Professional Content**: Includes service overview and call-to-action

## Workflow Examples

### Complete Setup Workflow
```bash
# 1. Set API key
wp 84em local-pages --set-api-key

# 2. Generate everything (350 pages)
wp 84em local-pages --generate-all

# 3. Generate supporting pages
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap

# 4. Verify results
wp post list --post_type=page --meta_key=_local_page_state --format=count
```

### Monthly Maintenance Workflow
```bash
# Update all existing content
wp 84em local-pages --update-all

# Refresh supporting pages
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap
```

### Selective Operations
```bash
# Test with a few states first
wp 84em local-pages --state="California,New York,Texas"

# Generate cities for specific states
wp 84em local-pages --state="California" --city=all
wp 84em local-pages --state="New York" --city=all

# Update specific locations
wp 84em local-pages --update --state="California"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego"
```

### Troubleshooting Workflow
```bash
# Check for failed pages
wp post list --post_type=page --meta_key=_local_page_state --post_status=draft --format=table

# Check page counts
wp post list --post_type=page --meta_key=_local_page_state --format=count

# Regenerate specific failed locations
wp 84em local-pages --update --state="California"
wp 84em local-pages --state="California" --city="Los Angeles"

# Monitor error logs
tail -f /path/to/wordpress/wp-content/debug.log
```

## Error Handling

### Common Issues and Solutions

**"Claude API key not found"**
```bash
# Solution: Set the API key
wp 84em local-pages --set-api-key
```

**"Failed to generate content for [Location]"**
- Check API key validity with `--validate-api-key`
- Verify internet connection
- Check Anthropic service status
- Review API usage limits

**"Parent state page not found"**
- Create state page first before generating city pages
- Use `--generate-all` to create everything in proper order

**"Invalid state name" or "City not found in [State]"**
- Use full state names (e.g., "California", not "CA")
- Check spelling and capitalization
- City names must match the predefined list in the plugin

**"cURL timeout errors"**
```php
// Add to wp-config.php
define('WP_HTTP_TIMEOUT', 120);
```

**"Memory limit exceeded"**
```php
// Add to wp-config.php  
define('WP_MEMORY_LIMIT', '512M');
```

### Debug Mode

Enable detailed logging:
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

View plugin logs:
```bash
tail -f /wp-content/debug.log | grep "84EM"
```

## Performance Features

### Progress Tracking
- Real-time progress bars for bulk operations
- API request duration tracking
- Individual location processing indicators
- Clear success/failure messaging with emojis
- Comprehensive statistics showing created/updated counts

### Bulk Operation Tips

1. **Monitor Progress**: Built-in progress indicators show real-time status
2. **Hierarchical Processing**: States are created first, then their cities
3. **Rate Limiting**: Plugin includes 1-second delays between API calls
4. **Memory Management**: Increase PHP memory limits for large operations
5. **Error Handling**: Graceful failures with detailed logging

### Caching Considerations

The plugin works with most caching plugins, but consider:
- Clear cache after bulk updates
- Exclude Local Pages from aggressive caching
- Warm cache for new pages automatically

## Security

### API Key Storage
- Keys encrypted using AES-256-CBC encryption with WordPress salts
- Encryption key derived from WordPress AUTH_KEY, SECURE_AUTH_KEY, LOGGED_IN_KEY, and NONCE_KEY
- Only encrypted data stored in database - no plaintext API keys
- Cryptographically secure initialization vector (IV) for each encryption
- Not exposed in frontend or logs

### API Key Security
- **Interactive Entry**: API keys are entered via secure prompt, not command arguments
- **No Shell History**: Keys don't appear in bash/shell command history
- **Hidden Input**: Terminal echo is disabled during key entry for privacy
- **Format Validation**: Warns if key doesn't match expected Claude API format

### Input Validation
- All user inputs sanitized and validated
- WP-CLI commands require appropriate permissions
- Post content properly escaped before display

### Rate Limiting
- Built-in delays prevent API abuse
- Configurable timeout settings
- Graceful handling of API failures

### Monitoring Commands

```bash
# Check total local page count
wp post list --post_type=page --meta_key=_local_page_state --format=count

# List all local pages with hierarchy
wp post list --post_type=page --meta_key=_local_page_state --format=table

# Check for drafts (potential failures)
wp post list --post_type=page --meta_key=_local_page_state --post_status=draft --format=table

# Count city pages specifically
wp post list --post_type=page --meta_key=_local_page_city --format=count

# Count state pages (pages without city meta)
wp post list --post_type=page --meta_key=_local_page_state --format=count

# Export all local pages
wp export --post_type=page --start_date=2025-01-01
```

## Backup and Recovery

### Before Major Operations
```bash
# Backup database
wp db export 84em-local-pages-backup-$(date +%Y%m%d).sql

# Export existing local pages (using meta key to identify)
wp export --post_type=page --start_date=2025-01-01 --dir=/backups/local-pages/
```

### Recovery Process
```bash
# Restore from database backup
wp db import 84em-local-pages-backup-20250130.sql

# Or import specific posts
wp import /backups/local-pages/local-pages-export.xml
```

## Deployment

The plugin uses GitHub Actions for automated multi-environment deployments with comprehensive validation, backup, and rollback capabilities.

### Deployment Environments

#### Production
- **Branch**: `main`
- **Trigger**: PR merge to main or manual workflow dispatch
- **Workflow**: `.github/workflows/deploy-prod.yml`
- **URL**: `https://84em.com`

#### Staging
- **Branch**: `staging`
- **Trigger**: PR merge to staging or manual workflow dispatch
- **Workflow**: `.github/workflows/deploy-staging.yml`
- **URL**: `https://staging.84em.com`

#### Development
- **Branch**: `dev`
- **Trigger**: PR merge to dev or manual workflow dispatch
- **Workflow**: `.github/workflows/deploy-dev.yml`
- **URL**: `https://dev.84em.com`

### GitHub Actions Workflows

The deployment system uses a reusable workflow architecture:

#### Core Features
- ✅ **Pre-deployment validation**: PHP syntax check, security scanning, version verification
- ✅ **Automatic backups**: Timestamped backups before each deployment
- ✅ **Health checks**: REST API endpoint validation after deployment
- ✅ **Automatic rollback**: Restores from backup on deployment failure
- ✅ **Version validation**: Ensures correct plugin version is deployed
- ✅ **Progress notifications**: Real-time status updates during deployment
- ✅ **Deployment summary**: Comprehensive report after deployment

#### Deployment Process
1. **Validation Phase**
   - PHP syntax validation
   - Security vulnerability scanning
   - Composer dependency check
   - Version consistency check

2. **Backup Phase** (Production/Staging only)
   - Creates timestamped backup
   - Verifies backup integrity
   - Stores backup path for rollback

3. **Deployment Phase**
   - Syncs files via rsync
   - Excludes development files (.git, tests, etc.)
   - Preserves server-specific configurations

4. **Verification Phase**
   - Health check endpoint validation
   - Version match verification
   - Plugin activation check

5. **Rollback Phase** (on failure)
   - Automatic restoration from backup
   - Notification of rollback status

### Required GitHub Secrets

#### Core Secrets (Required)

| Secret | Description | Example |
|--------|-------------|--------|
| `DEPLOY_SSH_KEY` | SSH private key for server access | `-----BEGIN RSA PRIVATE KEY-----...` |
| `DEPLOY_HOST` | Server hostname or IP address | `server.example.com` or `192.168.1.1` |
| `DEPLOY_USER` | SSH username for deployment | `deploy` or `www-data` |
| `DEPLOY_PATH` | Remote plugin directory path | `/var/www/html/wp-content/plugins/84em-local-pages` |

#### Optional Secrets

| Secret | Description | Default |
|--------|-------------|--------|
| `DEPLOY_PORT` | Custom SSH port | `22` |
| `BACKUP_PATH` | Backup directory path | `~/backups` |
| `HEALTH_CHECK_URL` | Health check endpoint | Auto-generated |
| `SLACK_WEBHOOK_URL` | Slack notifications | None |
| `SMTP_SERVER` | Email server for notifications | None |
| `SMTP_PORT` | Email server port | `587` |
| `SMTP_USERNAME` | Email username | None |
| `SMTP_PASSWORD` | Email password | None |
| `NOTIFICATION_EMAIL` | Recipient email address | None |

#### Environment-Specific Secrets

For multi-environment deployments, use prefixed secrets:

**Production:**
- `PROD_DEPLOY_HOST`
- `PROD_DEPLOY_USER`
- `PROD_DEPLOY_PATH`
- `HEALTH_CHECK_URL`

**Staging:**
- `STAGING_DEPLOY_HOST`
- `STAGING_DEPLOY_USER`
- `STAGING_DEPLOY_PATH`
- `STAGING_HEALTH_CHECK_URL`

**Development:**
- `DEV_DEPLOY_HOST`
- `DEV_DEPLOY_USER`
- `DEV_DEPLOY_PATH`
- `DEV_HEALTH_CHECK_URL`

### Deployment Commands

#### Automatic Deployment (Recommended)

```bash
# Production deployment
git checkout main
git merge feature-branch
git push origin main
# Deployment triggers automatically on PR merge

# Staging deployment
git checkout staging
git merge feature-branch
git push origin staging
# Deployment triggers automatically on PR merge

# Development deployment
git checkout dev
git merge feature-branch
git push origin dev
# Deployment triggers automatically on PR merge
```

#### Manual Deployment

1. Navigate to repository's **Actions** tab
2. Select the appropriate workflow:
   - `Deploy to Production`
   - `Deploy to Staging`
   - `Deploy to Dev`
3. Click **Run workflow**
4. Configure options:
   - **force_deploy**: Skip validation checks (use with caution)
   - **skip_backup**: Skip backup creation (emergency fixes only)
5. Click **Run workflow** button

#### Monitoring Deployment

```bash
# Watch deployment progress
gh run watch

# View deployment logs
gh run view --log

# Check deployment status
gh run list --workflow=deploy-prod.yml
```

### Security Features

- **Encrypted secrets**: All credentials stored as GitHub secrets
- **Security scanning**: Automated vulnerability detection before deployment
- **Dangerous function detection**: Scans for eval(), exec(), system(), etc.
- **Credential pattern detection**: Prevents hardcoded API keys and passwords
- **File permission validation**: Ensures proper file/directory permissions
- **Deployment hash verification**: Validates file integrity after deployment
- **Automatic rollback**: Restores previous version on deployment failure
- **SSH key authentication**: No password-based authentication
- **Restricted rsync**: Excludes sensitive files (.env, .git, etc.)

### Troubleshooting Deployments

#### Common Issues

**Deployment fails at validation phase:**
- Check PHP syntax: `php -l src/**/*.php`
- Run security scan locally
- Verify composer.json is valid

**Health check fails:**
- Ensure plugin is activated
- Check `HEALTH_CHECK_URL` secret is correct
- Verify REST API is accessible
- Test endpoint: `curl https://yourdomain.com/wp-json/84em-local-pages/v1/health`

**Rollback triggered:**
- Check deployment logs for specific error
- Verify file permissions on server
- Ensure sufficient disk space
- Check WordPress error logs

**SSH connection fails:**
- Verify `DEPLOY_SSH_KEY` format
- Check `DEPLOY_HOST` and `DEPLOY_PORT`
- Ensure `DEPLOY_USER` has correct permissions
- Test SSH connection manually

#### Deployment Logs

Access detailed logs through GitHub Actions:
1. Go to **Actions** tab
2. Click on the workflow run
3. Select the job to view
4. Expand steps for detailed output

#### Emergency Recovery

If automatic rollback fails:

```bash
# Manual SSH to server
ssh user@server

# Navigate to backup directory
cd ~/backups

# Find latest backup
ls -la | grep 84em-local-pages

# Restore manually
rm -rf /path/to/wp-content/plugins/84em-local-pages
cp -r 84em-local-pages-backup-TIMESTAMP /path/to/wp-content/plugins/84em-local-pages

# Verify plugin works
wp plugin list --status=active
```

## Support

### Getting Help

1. **Plugin Issues**: 84EM offers no warranty nor provides any support for this plugin
2. **API Issues**: Check [Anthropic Status](https://status.anthropic.com)
3. **WordPress Issues**: [WordPress.org Support](https://wordpress.org/support/)
4. **WP-CLI Issues**: [WP-CLI Documentation](https://wp-cli.org/)

## Testing

The plugin includes a comprehensive WP-CLI-based integration testing framework with 82 integration tests across 10 test suites. All tests use real WordPress functions and real API calls with complete test data isolation.

For complete testing documentation, including test suite details, configuration, and writing new integration tests, see **[TESTING.md](TESTING.md)**.

## Health Check Endpoint

The plugin provides a simple REST API health check endpoint for deployment verification.

### Endpoint URL

```
GET /wp-json/84em-local-pages/v1/health
```

### Purpose

- Verify plugin is active after deployment
- Simple monitoring for uptime services
- GitHub Actions deployment verification

### Response

Always returns HTTP 200 with minimal JSON response if the plugin is working:

```json
{
    "status": "ok"
}
```

### GitHub Actions Integration

```yaml
- name: Health Check
  run: |
    response=$(curl -s -o /dev/null -w "%{http_code}" "${{ secrets.HEALTH_CHECK_URL }}")
    if [[ "$response" == "200" ]]; then
      echo "✅ Health check passed"
    else
      echo "❌ Health check failed"
      exit 1
    fi
```

### Required GitHub Secrets

- `HEALTH_CHECK_URL`: Production health check URL
- `STAGING_HEALTH_CHECK_URL`: Staging health check URL  
- `DEV_HEALTH_CHECK_URL`: Development health check URL

## License

MIT License

Copyright (c) 2025 84EM

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
