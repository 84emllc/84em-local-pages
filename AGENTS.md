# AGENTS.md - 84EM Local Pages Content Generation

This document contains the Claude AI prompt templates and guidelines used by the 84EM Local Pages Generator plugin for creating unique, SEO-optimized content for each US state and city.

## Current Prompt Templates (Updated February 2026 - v3.21.0)

The plugin uses two distinct prompt structures for generating location-specific content with improved readability using list-based formatting and concise sentence structures. **v3.21.0** restored the "Why Choose 84EM" content section to improve LLM discoverability and citation potential.

### Prompt Template History

The prompt templates were rewritten in v3.17.0 and further enhanced in v3.21.0:

**v3.17.0 Changes:**
- Reduced prompt size and removed redundant instructions
- Removed location linking instructions (ContentProcessor handles linking)
- Added opening hook framing to match 84EM's voice
- Block IDs moved from content generators to `src/Config/BlockIds.php`

**v3.21.0 Changes:**
- Restored "Why Choose 84EM" content section for LLM discoverability
- Added experience shortcodes (`[dev_years]`, `[wp_years]`) for dynamic year calculations
- Restored block syntax formatting instructions in prompts
- Prompts now include banned phrases validation

**Block ID Constants** (in `src/Config/BlockIds.php`):
```php
public const SERVICES = 5031;   // Services reusable block
public const CTA = 1219;        // CTA button reusable block
public const SEPARATOR = 5034;  // Separator reusable block
```

## Hierarchical Content Structure

### State Pages (Child of Index Page)
- **Content Length**: 200-300 words
- **Geographic Focus**: State and 10 largest cities
- **Automatic Interlinking**: City names link to child city pages
- **Service Sections**: Reusable blocks with service links
- **URL Format**: `/wordpress-development-services-usa/california/`

### City Pages (Child of State Page)
- **Content Length**: 150-200 words
- **Geographic Focus**: Specific city and state context
- **Parent Relationship**: Child of respective state page
- **Service Sections**: Reusable blocks with service links
- **URL Format**: `/wordpress-development-services-usa/california/los-angeles/`

## Automatic Interlinking System

### State Page Interlinking
The plugin automatically processes state page content after generation:

1. **City Name Detection**: Identifies city names from the state's city list
2. **Link Generation**: Creates URLs in format `/wordpress-development-services-usa/{state}/{city}/`
3. **Content Replacement**: Replaces first occurrence of each city name with link
4. **Service Sections**: Handled via reusable blocks referenced in prompts

### City Page Interlinking
City pages receive automatic state name linking:

1. **State Name Linking**: Links the state name to the parent state page
2. **Service Sections**: Handled via reusable blocks referenced in prompts

### Interlinking Implementation
Content processing is handled by the `ContentProcessor` class:
```php
// ContentProcessor handles location linking (city names, state names)
$contentProcessor = new ContentProcessor();
$processed = $contentProcessor->processContent( $raw_content, $context );
```

## Prompt Variables

The prompts use direct PHP variable interpolation:

### Location Information
- `$state`: Full state name (e.g., "California")
- `$city`: City name (e.g., "Los Angeles") - city pages only
- `$city_list`: Comma-separated list of 10 largest cities - state pages only

### Experience Shortcodes
The prompts use shortcodes that are replaced with calculated values:
- `[dev_years]`: Years of programming experience (calculated from 1995)
- `[wp_years]`: Years of WordPress experience (calculated from 2012)

### Block References
- `$services_block`: Services reusable block ID (from `BlockIds::SERVICES`)
- `$cta_block`: CTA button reusable block ID (from `BlockIds::CTA`)

## Content Structure Guidelines

### WordPress Block Editor Format
All content is generated using proper Gutenberg block syntax with large font size:

```html
<!-- wp:paragraph {"fontSize":"large"} -->
<p class="has-large-font-size">Paragraph content here.</p>
<!-- /wp:paragraph -->

<!-- wp:heading {"level":2,"fontSize":"large"} -->
<h2 class="has-large-font-size"><strong>Bold Heading</strong></h2>
<!-- /wp:heading -->

<!-- wp:list {"className":"is-style-checkmark-list","fontSize":"large"} -->
<ul class="wp-block-list is-style-checkmark-list has-large-font-size">
<!-- wp:list-item --><li>Item</li><!-- /wp:list-item -->
</ul>
<!-- /wp:list -->
```

### Call-to-Action Integration

#### Inline CTAs
- **State Pages**: 2-3 contextual links throughout content
- **City Pages**: 2-3 contextual links throughout content
- **Natural Phrases**: "contact us today", "get started", "reach out", "discuss your project"
- **Link Target**: `/contact/` page

#### CTA Reusable Block
- **Placement**: End of content (single reusable block reference)
- **Block Reference**: `<!-- wp:block {"ref":1219} /-->`
- **Layout**: Centered with proper spacing
- **Target**: `/contact/` page

### Remote-First Messaging
- **Emphasis**: 84EM's 100% remote operations
- **Exclusions**: No mentions of on-site visits, local offices, or physical presence
- **Focus**: Remote expertise and proven delivery capabilities
- **Scope**: Nationwide service coverage

### Tone and Style Guidelines
- **Professional, factual tone** - avoid hyperbole and superlatives
- **Technical focus** - emphasize concrete services and capabilities
- **Local relevance** - mention locations naturally within content
- **Concise content** - appropriate word count for page type
- **Generic approach** - suitable for all business types

## SEO Optimization Strategy

### Keyword Integration

**State Pages:**
- Primary: "WordPress development {STATE}"
- Secondary: "custom plugins {STATE}"
- Location-based: "web development {CITY_LIST}"

**City Pages:**
- Primary: "WordPress development {CITY}"
- Secondary: "custom plugins {CITY}"
- Location-based: "web development {STATE}"

### Meta Data Structure (AI-Generated v3.15.0+)

Starting with v3.15.0, all metadata (page title, SEO title, meta description) is generated by AI using the `MetadataGenerator` class. This ensures unique, contextually relevant metadata for each location page.

**AI Metadata Generation:**
- **API Call**: Separate API request generates metadata JSON
- **Fallback**: Hardcoded templates used if AI generation fails
- **Storage**: Metadata stored in custom 84EM meta fields

**State Pages:**
- **Page Title**: AI-generated (40-60 characters, unique per state)
- **SEO Title**: AI-generated (50-60 characters, ends with " | 84EM")
- **Meta Description**: AI-generated (150-160 characters)
- **Meta Fields**: `_84em_seo_title`, `_84em_seo_description`

**City Pages:**
- **Page Title**: AI-generated (40-70 characters, includes city name)
- **SEO Title**: AI-generated (50-60 characters, ends with " | 84EM")
- **Meta Description**: AI-generated (150-160 characters)
- **Meta Fields**: `_84em_seo_title`, `_84em_seo_description`

**Fallback Templates (used when AI fails):**
- State Title: "WordPress Development Services in {STATE}"
- State SEO Title: "WordPress Development, Plugins, Consulting, Agency Services in {STATE} | 84EM"
- City Title: "WordPress Development Services in {CITY}, {STATE}"
- City SEO Title: "WordPress Development, Plugins, Consulting, Agency Services in {CITY}, {STATE} | 84EM"

### URL Structure
Clean hierarchical URLs with index page as parent:

**Index Page:**
- Format: `/wordpress-development-services-usa/`
- Example: `/wordpress-development-services-usa/`

**State Pages:**
- Format: `/wordpress-development-services-usa/{state}/`
- Example: `/wordpress-development-services-usa/california/`

**City Pages:**
- Format: `/wordpress-development-services-usa/{state}/{city}/`
- Example: `/wordpress-development-services-usa/california/los-angeles/`

### LD-JSON Schema

**State Pages:**
- Top-level Type: WebPage
- Main Entity: LocalBusiness
- Service Area: State with city containment
- Contains Place: Array of major cities

**City Pages:**
- Top-level Type: WebPage
- Main Entity: Service
- Area Served: Specific city
- Contained In Place: Parent state

## API Configuration

### Current Model Settings (v3.2.4+)
```php
// Located in src/Api/ClaudeApiClient.php
private const MAX_TOKENS = 4000;
private const TIMEOUT = 600;  // 10 minutes
private const API_VERSION = '2023-06-01';
private const MAX_RETRIES = 5;  // Retry failed requests with exponential backoff
private const INITIAL_RETRY_DELAY = 1;  // Initial delay between retries
private const MODELS_ENDPOINT = 'https://api.anthropic.com/v1/models';
```

### Model Configuration (v3.2.4+)

The plugin uses dynamic model selection fetched directly from the Claude API:

#### Model Selection Process
1. User runs `--set-api-model` command
2. Plugin fetches available models from Claude API
3. User selects model from interactive numbered list
4. Model is validated with test API call
5. If validation succeeds, model is saved

#### Model Management Commands

```bash
# Set/update model (fetches list from Claude API)
wp 84em local-pages --set-api-model

# View current model configuration
wp 84em local-pages --get-api-model

# Validate current model
wp 84em local-pages --validate-api-model

# Clear current model configuration
wp 84em local-pages --reset-api-model
```

#### Model Validation
All model changes are validated with a test API call before being saved. This ensures:
- The model exists and is accessible
- Your API key has permission to use the model
- The model is functioning correctly

If validation fails, the model will NOT be saved and you'll see a clear error message.

#### Model Storage
- Models are stored in WordPress option: `84em_local_pages_claude_api_model`
- **No default model** - users must select a model before generating content
- Model configuration is separate from API key storage
- Models can be changed at any time via WP-CLI
- Available models are fetched dynamically from Claude's Models API

### Slack Notifications (v3.20.0+)

The plugin can send Slack notifications when bulk operations complete. This is useful for long-running operations like `--generate-all` which can take hours.

#### Slack Webhook Commands

```bash
# Set Slack webhook URL (interactive secure prompt)
wp 84em local-pages --set-slack-webhook

# Test webhook configuration
wp 84em local-pages --test-slack-webhook

# Remove stored webhook URL
wp 84em local-pages --remove-slack-webhook
```

#### Webhook Storage
- Webhook URLs are encrypted with AES-256-CBC (same as API keys)
- Stored in WordPress option: `84em_local_pages_slack_webhook_encrypted`
- Secure input prompt (URL not visible when typing)
- URL validated to match Slack webhook format

#### Notification Events
Notifications are sent on completion of:
- `--generate-all` (with or without `--states-only`)
- `--update-all` (with or without `--states-only`)

#### Notification Content
Each notification includes:
- Operation type (Generate All, Update All, etc.)
- Duration of operation
- Count of pages created/updated
- Total pages processed
- Site URL for identification

#### Graceful Failure
- If webhook is not configured, notifications are silently skipped
- If webhook delivery fails, a warning is logged but operation continues
- Notifications never interrupt the generation process

### Rate Limiting and Error Handling
- **Delay Between Requests**: 2 seconds between API calls
- **Timeout**: 600 seconds (10 minutes) per request
- **Retry Logic**: Up to 5 attempts with exponential backoff for transient errors
- **Retryable Errors**: Network issues, rate limits, server errors (500-503, 529)
- **Progress Tracking**: Real-time duration monitoring
- **Bulk Operations**: Progress bars with comprehensive statistics

## WP-CLI Command Structure

### Bulk Operations
```bash
# Generate everything (550 pages: 50 states + 500 cities)
wp 84em local-pages --generate-all

# Generate states only (50 pages)
wp 84em local-pages --generate-all --states-only

# Update all existing pages
wp 84em local-pages --update-all

# Update existing states only
wp 84em local-pages --update-all --states-only
```

### Import Mode (v3.16.0+)

Bulk operations (`--generate-all` and `--update-all`) automatically enable WordPress import mode by defining the `WP_IMPORTING` constant. This signals to other plugins (caching plugins, SEO plugins, etc.) that a bulk import is in progress and they should skip their post update/insert hooks until processing is complete.

**How It Works:**
- The constant is defined at the very start of bulk operations, before any content generation begins
- A log message confirms when import mode is enabled: "Import mode enabled - plugin hooks suspended during bulk operation."
- Since PHP constants cannot be undefined, this persists for the duration of the WP-CLI request

**Benefits:**
- Prevents caching plugins from clearing/rebuilding cache on every page save
- Prevents SEO plugins from running expensive analysis on each page
- Reduces database writes from other plugin hooks
- Significantly improves bulk operation performance

**Note:** Individual operations (`--state`, `--city`) do not enable import mode as they only process a small number of pages.

### Resume After Errors (v3.12.0+)

All bulk operations (`--generate-all` and `--update-all`) now support checkpoint/resume functionality to recover from non-retryable errors:

```bash
# Resume generation from last checkpoint
wp 84em local-pages --generate-all --resume

# Resume update from last checkpoint
wp 84em local-pages --update-all --resume

# Resume state-only operations
wp 84em local-pages --generate-all --states-only --resume
wp 84em local-pages --update-all --states-only --resume
```

**How It Works:**
- Checkpoints are saved after each successful API call (after each city or state page)
- If a non-retryable error occurs, the operation stops but progress is preserved
- Use `--resume` flag to continue from where you left off
- Checkpoints expire after 24 hours
- Checkpoints are automatically deleted on successful completion

**Use Cases:**
- Recovering from API quota exhaustion
- Resuming after authentication errors (401, 403)
- Continuing after invalid model errors (400, 404)
- Recovering from other non-retryable API errors

**Checkpoint Storage:**
- Stored in WordPress options table
- Operation-specific: `generate-all`, `generate-all-states-only`, `update-all`, `update-all-states-only`
- Includes: progress counters, processed state/city lists, current position

**Notes:**
- Retryable errors (rate limits, timeouts, server errors) are automatically retried up to 5 times with exponential backoff
- Only non-retryable errors (bad requests, auth failures, etc.) require manual resume
- Starting a new operation without `--resume` will clear any existing checkpoint

### Individual Operations
```bash
# State operations
wp 84em local-pages --state="California"
wp 84em local-pages --state=all

# City operations
wp 84em local-pages --state="California" --city=all
wp 84em local-pages --state="California" --city="Los Angeles"
wp 84em local-pages --state="California" --city="Los Angeles,San Diego"

# Generate all cities AND update state page
wp 84em local-pages --state="California" --city=all --complete
```

### Supporting Operations
```bash
# Generate index page (no API key required)
wp 84em local-pages --generate-index

# Generate XML sitemap (no API key required)
wp 84em local-pages --generate-sitemap

# Update location links in existing pages (no API key required)
wp 84em local-pages --update-location-links                    # All pages
wp 84em local-pages --update-location-links --states-only      # States only

# Regenerate LD-JSON schemas without touching content (no API key required)
wp 84em local-pages --regenerate-schema                    # All pages
wp 84em local-pages --regenerate-schema --states-only      # States only
wp 84em local-pages --regenerate-schema --state="California"  # Specific state and its cities
wp 84em local-pages --regenerate-schema --state="California" --state-only  # State only, no cities
wp 84em local-pages --regenerate-schema --state="California" --city="Los Angeles"  # Specific city
```

### Delete Operations
```bash
# Delete state pages
wp 84em local-pages --delete --state=all
wp 84em local-pages --delete --state="California,New York"

# Delete city pages
wp 84em local-pages --delete --state="California" --city=all
wp 84em local-pages --delete --state="California" --city="Los Angeles,San Diego"
```

### URL Migration
```bash
# Migrate pages from old URL structure to new
wp 84em local-pages --migrate-urls
```

### Testimonial Management
```bash
# Find testimonial block IDs
wp 84em local-pages --find-testimonial-ids
```

## Content Quality Assurance

### Automated Checks
- WordPress block syntax validation
- Keyword density monitoring
- Character count verification
- CTA placement verification
- Automatic interlinking processing

### Manual Review Points
1. **Geographic Relevance**: Natural mention of locations
2. **Hierarchical Structure**: Parent-child relationships maintained
3. **Natural Keyword Integration**: No keyword stuffing
4. **Service Focus**: Technical capabilities without industry claims
5. **Local Authenticity**: Location names feel natural
6. **Professional Tone**: Factual without exaggeration
7. **Clear CTAs**: Multiple conversion opportunities
8. **Block Structure**: Proper Gutenberg formatting
9. **Interlinking**: City names link to city pages, state names link to state pages

## Performance Monitoring

### Content Metrics
- Organic search rankings for target keywords
- Page engagement metrics (time on page, bounce rate)
- Conversion rates from CTAs
- Internal link click-through rates
- Hierarchical navigation patterns

### Technical Metrics
- API response times (tracked with duration display)
- Content generation success rates
- WordPress block parsing accuracy
- SEO meta data completeness
- XML sitemap generation and validation
- Automatic interlinking accuracy

## Troubleshooting

### Common Content Issues

#### Block Syntax Errors
**Problem**: Malformed WordPress blocks
**Solution**: Verify exact block markup in prompt templates

#### Missing CTAs
**Problem**: CTA reusable block not appearing in content
**Solution**: Check CTA block reference in prompt template and verify block ID matches BlockIds::CTA

#### Generic Content
**Problem**: Similar content across locations
**Solution**: Strengthen geographic relevance and location mentions

#### Missing Interlinking
**Problem**: City names or keywords not linked
**Solution**: Verify automatic linking functions and content processing

### Hierarchical Issues

#### Parent-Child Relationships
**Problem**: City pages not properly linked to state pages
**Solution**: Ensure state page exists before creating city pages

#### URL Structure
**Problem**: Incorrect hierarchical URLs
**Solution**: Verify rewrite rules and post_parent relationships

### API Issues

#### Timeout Errors
**Problem**: Requests exceeding 600-second (10 minute) limit
**Solution**: Check network connectivity and API status. The plugin will automatically retry up to 5 times with exponential backoff for transient errors

#### Rate Limiting
**Problem**: Too many requests too quickly
**Solution**: Verify 2-second delay between requests

#### Model Errors
**Problem**: Unexpected Claude model responses
**Solution**: Verify API key and model availability

## Commands Not Using Claude AI

### Index Page Generation
The `--generate-index` command creates a master index page with an alphabetized list of states. This command:
- **Does not require Claude API key**: Uses only existing state page data
- **No API calls**: Content is generated programmatically using WordPress block syntax
- **Static content**: Uses predefined template with dynamic state list from WP_Query
- **Page details**: Creates/updates `wordpress-development-services-usa` page
- **State Focus**: Only lists state pages, not city pages

### Sitemap Generation
The `--generate-sitemap` command creates XML sitemaps. This command:
- **Does not require Claude API key**: Uses only existing local page data
- **No API calls**: Generates XML using WordPress permalink data
- **Includes All Pages**: Both state and city pages in sitemap
- **Static output**: Creates `sitemap-local.xml` in WordPress root directory

### Location Link Updates
The `--update-location-links` command refreshes all location links in existing pages. This command:
- **Does not require Claude API key**: Works with existing page content
- **No API calls**: Reprocesses existing content with ContentProcessor
- **Use Case**: Update location links when URL structure changes
- **Process**:
  1. Strips existing auto-generated location links
  2. Reprocesses content with ContentProcessor
  3. Preserves user-added links and content structure
- **Options**:
  - `--update-location-links`: Updates all state and city pages
  - `--update-location-links --states-only`: Updates state pages only
- **Performance**: Uses progress bar and batch processing for efficiency

## Health Check Endpoint

The plugin provides a REST API health check endpoint for deployment verification:

### Endpoint
```
GET /wp-json/84em-local-pages/v1/health
```

### Response
```json
{
    "status": "ok"
}
```

### Purpose
- Verify plugin is active after deployment
- Used by GitHub Actions deployment workflows
- Returns HTTP 200 if plugin is functioning
- Minimal response for security (no system information exposed)

## Plugin Architecture (v3.0.0+)

### Modular Structure
The plugin has been refactored from a monolithic class to a modern modular architecture:

#### Core Components
- **`Plugin`**: Main plugin class handling initialization and service registration
- **`Container`**: Dependency injection container for managing class instances

#### API Layer (`src/Api/`)
- **`ApiKeyManager`**: Handles API key storage and retrieval
- **`ClaudeApiClient`**: Manages communication with Claude API (includes retry logic and error handling)
- **`Encryption`**: Provides AES-256-CBC encryption for API keys
- **`HealthCheckEndpoint`**: REST API endpoint for deployment verification

#### CLI Layer (`src/Cli/`)
- **`CommandHandler`**: Main WP-CLI command registration and routing
- **`Commands/GenerateCommand`**: Handles all content generation commands
- **`Commands/TestCommand`**: Manages the testing framework

#### Content Layer (`src/Content/`)
- **`StateContentGenerator`**: Generates state page content
- **`CityContentGenerator`**: Generates city page content
- **`MetadataGenerator`**: Generates AI-powered SEO metadata (v3.15.0+)

#### Data Layer (`src/Data/`)
- **`StatesProvider`**: Provides US states and cities data
- **`LocationContextProvider`**: Provides state/city-specific context for content generation
- **`TestimonialProvider`**: Provides deterministic testimonial block references per location

#### Schema Layer (`src/Schema/`)
- **`SchemaGenerator`**: Creates LD-JSON structured data

#### Utils Layer (`src/Utils/`)
- **`ContentProcessor`**: Handles content processing, linking, and formatting
- **`CheckpointManager`**: Manages progress checkpoints for bulk operations (v3.12.0+)

#### Config Layer (`src/Config/`)
- **`BlockIds`**: Centralized reusable block ID constants (services, CTA, separator)
- **`TestimonialBlockIds`**: Testimonial block ID mappings per location

#### Core Layer (`src/Core/`)
- **`Activator`**: Plugin activation logic
- **`Deactivator`**: Plugin deactivation logic
- **`Requirements`**: Plugin requirements checking (PHP version, WordPress version)

#### Admin Layer (`src/Admin/`)
- **`PluginLinks`**: Admin plugin action links

#### Redirects Layer (`src/Redirects/`)
- **`LegacyUrlRedirector`**: 301 redirects from old URL format to new hierarchical structure

#### Contracts Layer (`src/Contracts/`)
- **`ApiClientInterface`**: API client contract
- **`ContentGeneratorInterface`**: Content generator contract
- **`SchemaGeneratorInterface`**: Schema generator contract
- **`DataProviderInterface`**: Data provider contract

### Key Classes and Responsibilities

| Class | Responsibility | Location |
|-------|---------------|----------|
| `Plugin` | Main initialization | `src/Plugin.php` |
| `ApiKeyManager` | API key management | `src/Api/ApiKeyManager.php` |
| `ClaudeApiClient` | Claude API communication | `src/Api/ClaudeApiClient.php` |
| `StateContentGenerator` | State page generation | `src/Content/StateContentGenerator.php` |
| `CityContentGenerator` | City page generation | `src/Content/CityContentGenerator.php` |
| `MetadataGenerator` | AI-generated SEO metadata | `src/Content/MetadataGenerator.php` |
| `ContentProcessor` | Content enhancement | `src/Utils/ContentProcessor.php` |
| `CheckpointManager` | Bulk operation checkpoints | `src/Utils/CheckpointManager.php` |
| `SlackWebhookManager` | Encrypted webhook storage | `src/Notifications/SlackWebhookManager.php` |
| `SlackNotifier` | Slack message delivery | `src/Notifications/SlackNotifier.php` |
| `CommandHandler` | CLI command routing | `src/Cli/CommandHandler.php` |
| `BlockIds` | Reusable block ID constants | `src/Config/BlockIds.php` |
| `TestimonialBlockIds` | Testimonial block IDs | `src/Config/TestimonialBlockIds.php` |
| `LocationContextProvider` | Location-specific prompt context | `src/Data/LocationContextProvider.php` |
| `TestimonialProvider` | Testimonial block references | `src/Data/TestimonialProvider.php` |
| `LegacyUrlRedirector` | Old-to-new URL 301 redirects | `src/Redirects/LegacyUrlRedirector.php` |
| `PluginLinks` | Admin plugin action links | `src/Admin/PluginLinks.php` |
| `Activator` | Plugin activation logic | `src/Core/Activator.php` |
| `Requirements` | PHP/WP version checking | `src/Core/Requirements.php` |

### Namespace Structure
All classes use the `EightyFourEM\LocalPages` namespace:
```php
namespace EightyFourEM\LocalPages\Admin;
namespace EightyFourEM\LocalPages\Api;
namespace EightyFourEM\LocalPages\Cli;
namespace EightyFourEM\LocalPages\Config;
namespace EightyFourEM\LocalPages\Content;
namespace EightyFourEM\LocalPages\Contracts;
namespace EightyFourEM\LocalPages\Core;
namespace EightyFourEM\LocalPages\Data;
namespace EightyFourEM\LocalPages\Notifications;
namespace EightyFourEM\LocalPages\Redirects;
namespace EightyFourEM\LocalPages\Schema;
namespace EightyFourEM\LocalPages\Utils;
```

## Testing Framework

The plugin includes a comprehensive WP-CLI-based testing framework that uses **real WordPress functions and API calls** instead of mocks, following WordPress best practices.

### Running Tests
```bash
# Run all test suites
wp 84em local-pages --test --all

# Run specific test suite
wp 84em local-pages --test --suite=api-client
```

### Test Configuration

**Tests always use real WordPress functions, real database operations, and real API calls.** There are no mocks.

The test suite will use the production Claude API key that's already configured in the plugin. If you want to use a different API key for testing (to keep test API usage separate from production), you can set:

```bash
# Optional: Use a different API key for testing
export EIGHTYFOUREM_TEST_API_KEY="your-test-api-key-here"
```

If not set, tests will use the production API key configured in the plugin (stored encrypted in `84em_local_pages_claude_api_key_encrypted`).

### Available Test Suites
- **encryption** - API key encryption and security
- **data-structures** - Service keywords and states data
- **content-processing** - Content processing and linking
- **cli-args** - WP-CLI argument parsing
- **ld-json** - Schema.org structured data
- **api-client** - Claude API client with retry logic (no mocks)
- **content-generators** - State and city content generation (no mocks)
- **error-handling** - Error handling and recovery
- **security** - Security and input sanitization
- **model-management** - Model configuration and validation

**Total**: 10 test suites with 78 tests

**Testing Philosophy**: All tests use real WordPress functions (get_option, update_option, delete_option) and real class instances. No mocks, no anonymous classes, just real integration testing. Tests properly clean up after themselves by restoring original database values.

For detailed testing documentation, see [TESTING.md](TESTING.md).

## Recent Updates

### Version 3.21.0 (2026-02-05)

#### LLM Discoverability Enhancement
- **Problem Solved**: Referral traffic from ChatGPT dropped after v3.17.0's prompt redesign removed substantive, citable content
- **Root Cause**: v3.17.0 reduced prompts from ~450 to ~150 words, removing the "Why Choose 84EM" bullet point section that LLMs could extract and cite
- **Solution**: Restored substantive content sections and enhanced Schema.org markup

#### Content Changes
- **State Pages**: Added "Why {State} Businesses Choose 84EM" H2 section with 5 bullet points:
  - [dev_years] years of web development experience – Programming since 1995, WordPress since 2012
  - Deep WordPress architecture expertise – Custom plugins, theme development, complex integrations
  - Agency partnerships – White-label or client-facing, your choice
  - Direct partnership with businesses – From startups to established companies
  - Remote-first, nationwide service – Based in Cedar Rapids, Iowa, serving all 50 states
- **City Pages**: Added "Why {City} Businesses Choose 84EM" H2 section with 4 bullet points
- **Word Count**: States increased to 200-300 words, Cities to 150-200 words

#### Schema.org Enhancements
- **Organization Schema**: Added foundingDate (2012), founder (Andrew Miller), description, slogan, legalName, extensive knowsAbout array, sameAs social links, contactPoint with business hours
- **State Schema**: Enhanced descriptions, detailed serviceType array, pricing offers ($150/hour)
- **City Schema**: Enhanced descriptions, detailed serviceType array, pricing offers

#### Modified Files
- `src/Content/StateContentGenerator.php` - Restored "Why Choose" section in buildPrompt()
- `src/Content/CityContentGenerator.php` - Added "Why Choose" section in buildPrompt()
- `src/Schema/SchemaGenerator.php` - Enhanced getOrganizationSchema(), generateStateSchemaInternal(), generateCitySchemaInternal()

### Version 3.17.0 (2025-12-29)

#### Prompt Template Redesign
- **Complete Rewrite**: Reduced prompt templates from ~450 words to ~150 words
- **Voice Alignment**: Prompts now match actual 84EM writing style with problem-first framing
- **Configurable Block IDs**: Moved hardcoded block references to class constants
- **Cleaner Instructions**: Removed redundant block syntax instructions (ContentProcessor handles this)
- **Future-Proof Credentials**: Changed from calculated years to "since 2012" / "since 1995"
- **Bug Fixes**: Fixed duplicate section text and typos in CityContentGenerator
- **Modified Files**:
  - `src/Content/StateContentGenerator.php` - Rewrote buildPrompt(), added block constants
  - `src/Content/CityContentGenerator.php` - Rewrote buildPrompt(), added block constants

### Version 3.15.1 (2025-12-15)

#### Critical Bugfix: MetadataGenerator Integration
- **Problem**: v3.15.0 registered MetadataGenerator in the DI container but content generators did not accept or use it
- **Fix**: Completed MetadataGenerator integration in both content generators
  - Added MetadataGenerator as constructor dependency to `StateContentGenerator` and `CityContentGenerator`
  - Both generators now call `generateStateMetadata()` / `generateCityMetadata()` during page creation and updates
  - Added try/catch blocks with automatic fallback to template metadata when AI generation fails
- **Result**: `--generate-all` and `--update-all` commands now properly trigger AI-generated metadata

### Version 3.15.0 (2025-12-15)

#### AI-Generated SEO Metadata
- **New Feature**: Page titles, SEO titles, and meta descriptions are now generated by Claude AI
- **New MetadataGenerator Class** (`src/Content/MetadataGenerator.php`):
  - Generates unique, contextually relevant metadata for each location
  - Separate API call for metadata generation (returns JSON)
  - Fallback templates used when AI generation fails
  - Proper validation of required fields (page_title, seo_title, meta_description)
- **Updated Content Generators**:
  - `StateContentGenerator`: Now uses MetadataGenerator for AI metadata
  - `CityContentGenerator`: Now uses MetadataGenerator for AI metadata
  - Both generators have fallback metadata methods
- **Metadata Specifications**:
  - **Page Title**: 40-70 characters, unique per location
  - **SEO Title**: 50-60 characters, ends with " | 84EM"
  - **Meta Description**: 150-160 characters, includes call-to-action
- **Storage**: Metadata stored in custom 84EM meta fields (`_84em_seo_title`, `_84em_seo_description`)
- **Integration**: Works with all existing commands (`--generate-all`, `--update-all`, individual state/city)
- **Modified Files**:
  - `src/Content/MetadataGenerator.php` - New AI metadata generation class
  - `src/Content/StateContentGenerator.php` - Integrated MetadataGenerator
  - `src/Content/CityContentGenerator.php` - Integrated MetadataGenerator
  - `src/Plugin.php` - Registered MetadataGenerator in DI container
  - `AGENTS.md` - Updated documentation

### Version 3.12.0 (2025-11-17)

#### Checkpoint/Resume System for Bulk Operations
- **Problem Solved**: Non-retryable API errors during bulk operations required starting over from scratch, wasting time and API quota
- **Solution**: Implemented comprehensive checkpoint system to save progress and enable resuming
- **New CheckpointManager Class** (`src/Utils/CheckpointManager.php`):
  - Saves progress after each successful API call
  - Stores checkpoints in WordPress options table
  - Checkpoints expire after 24 hours
  - Operation-specific checkpoints: `generate-all`, `generate-all-states-only`, `update-all`, `update-all-states-only`
- **Enhanced Commands**:
  - `--generate-all --resume` - Resume generation from last checkpoint
  - `--update-all --resume` - Resume update from last checkpoint
  - Works with `--states-only` flag as well
- **Checkpoint Data Includes**:
  - Progress counters (states/cities created/updated)
  - List of processed state/city IDs
  - Current position in processing (for mid-state resumption)
  - Timestamp for expiration tracking
- **Benefits**:
  - Recover from API quota exhaustion without losing progress
  - Resume after authentication errors (401, 403)
  - Continue after invalid model errors (400, 404)
  - Automatic cleanup on successful completion
  - Progress visibility when resuming
- **Modified Files**:
  - `src/Utils/CheckpointManager.php` - New checkpoint management class
  - `src/Cli/Commands/GenerateCommand.php` - Added checkpoint support to handleGenerateAll() and handleUpdateAll()
  - `src/Plugin.php` - Registered CheckpointManager in dependency injection container
  - `AGENTS.md` - Added resume documentation
- **Use Case**: When running `--generate-all` with 550 pages, if an error occurs on page 150, you can resume with `--generate-all --resume` to continue from page 151
- **Note**: Retryable errors (timeouts, rate limits, server errors) are still automatically retried up to 5 times with exponential backoff. Checkpoints are only needed for non-retryable errors.

### Version 3.7.0 (2025-10-30)

#### Post Type Migration to Standard WordPress Pages
- **Major Architectural Change**: Converted custom post type from `local` to standard WordPress `page` type
- **Motivation**: Simplify architecture and improve WordPress integration
- **Implementation Changes**:
  1. **Deleted Custom Post Type Class**: Removed `src/PostTypes/LocalPostType.php` entirely
  2. **Removed Registration**: Eliminated LocalPostType registration from Plugin.php initialization
  3. **Removed Activation Hooks**: No longer calls flush_rewrite_rules() on activation
  4. **Updated Queries**: All WP_Query operations changed from `post_type=local` to `post_type=page`
  5. **Updated Content Generators**: StateContentGenerator and CityContentGenerator now create standard pages
- **Preserved Functionality**:
  - All custom meta fields remain (_local_page_state, _local_page_city, _local_page_type)
  - Hierarchical parent-child structure maintained through post_parent
  - All interlinking functionality preserved
  - SEO meta data and LD-JSON schema unchanged
  - URL structure remains the same
- **Benefits**:
  - Simpler plugin architecture (eliminated custom post type complexity)
  - Better WordPress core integration
  - No custom rewrite rules needed
  - Reduced code maintenance overhead
  - Standard WordPress pages UI/UX for editing
- **Modified Files**:
  - `src/Plugin.php` - Removed LocalPostType registration and activation hooks
  - `src/Cli/Commands/GenerateCommand.php` - Updated all WP_Query calls to use post_type=page
  - `src/Content/StateContentGenerator.php` - Updated post creation to use page type
  - `src/Content/CityContentGenerator.php` - Updated post creation to use page type
- **Deleted Files**:
  - `src/PostTypes/LocalPostType.php` - Custom post type class no longer needed
- **Migration Note**: Existing installations with 'local' post type pages will need manual migration or regeneration

### Version 3.6.1 (2025-10-25)

#### Enhanced Keyword Linking Safeguards
- **Problem Solved**: Automatic keyword linking was adding unwanted links to bolded service titles, creating nested links within href attributes, and double-linking existing content
- **Solution**: Implemented comprehensive protection for service list items
  1. **List Item Protection**: List items containing `<strong>` tags are completely protected from automatic linking via placeholder replacement
  2. **Tag-Aware Splitting**: Content is split by HTML tags before linking to prevent matches inside tag attributes
  3. **Clean Prompt Structure**: Removed `<strong>` tags from "Learn More →" links in prompts (keeping only service category titles bolded)
- **Benefits**:
  - Preserves hardcoded service lists exactly as specified in prompts
  - Prevents nested links and malformed HTML
  - Maintains clean, readable service lists with only the intended "Learn More →" links
  - Works for both state and city pages
- **Updated Files**:
  - `src/Utils/ContentProcessor.php` - Added placeholder-based protection for list items with `<strong>` tags
  - `src/Content/StateContentGenerator.php` - Removed `<strong>` tags from "Learn More →" links
  - `src/Content/CityContentGenerator.php` - Removed `<strong>` tags from "Learn More →" links, fixed HTML typo
  - `AGENTS.md` - Updated documentation with new safeguards
- **Technical Implementation**:
  - Protected list items are replaced with placeholders before keyword linking
  - Content is split by HTML tags to avoid matching text inside attributes
  - Placeholders are restored after all keyword linking is complete
  - Service category titles remain bolded while "Learn More →" links are unbolded for cleaner appearance

### Version 3.3.0 (2025-10-19)

#### Enhanced Content Readability with List-Based Structure
- **Problem Solved**: Previous content was dense "walls of text" with services and benefits crammed into paragraphs
- **New Approach**: List-based formatting for improved scannability and readability
- **Content Structure Changes**:
  - **State Pages**: 8-10 service list items, 4-5 benefit list items
  - **City Pages**: 6-8 service list items, 3-4 benefit list items
  - Short paragraphs (2-3 sentences maximum)
  - Lists use proper WordPress `<!-- wp:list -->` block syntax
- **Benefits**:
  - Improved scannability for users with short attention spans
  - Reduced cognitive load through chunked information
  - Better mobile experience with vertical lists
  - Maintained SEO value with all keywords present
  - Professional service page appearance
- **Updated Files**:
  - `src/Content/CityContentGenerator.php` - Updated buildPrompt() method
  - `src/Content/StateContentGenerator.php` - Updated buildPrompt() method
  - `AGENTS.md` - Updated prompt template documentation

#### Test Classification: Integration Tests
- **Renamed Test Suite**: Changed from "unit tests" to "integration tests" for accuracy
  - Renamed `tests/unit/` directory to `tests/integration/`
  - Updated all test file docblocks to reflect integration testing terminology
  - Updated `TestCommand.php` to reference new directory path
  - Updated `TESTING.md` and `README.md` documentation with correct terminology
- **Why Integration Tests**: Tests use real WordPress database, real API calls to Claude, and real class instances (no mocks)
- **Removed Obsolete Mocks**: Deleted unused `tests/fixtures/mock-api-responses.php` and `tests/wp-mocks.php` files
- **All 82 Integration Tests Pass**: 100% success rate with valid API key configured

### Version 3.2.5 (2025-10-19)

#### Test Suite Refactor: Consistent ApiKeyManager Usage
- **ApiKeyManager Methods**: All tests now use `ApiKeyManager` methods exclusively
  - Replaced all direct `get_option()`, `update_option()`, `delete_option()` calls
  - Tests use `getKey()`, `setKey()`, `deleteKey()`, `getModel()`, `setModel()`, `deleteModel()`
  - Properly respects `getOptionName()` method which handles test prefix logic
- **Real WordPress Integration**: All tests use real WordPress functions, no mocks
- **Real API Calls**: Tests make real API calls to Claude using production API key
- **Test Data Isolation**: Complete separation via `test_` prefixed options
- **Proper Cleanup**: All tests restore original database values in tearDown()
- **All 82 Tests Pass**: 100% success rate with valid API key configured

This refactor ensures all tests follow the global AGENTS.md guideline: "don't use mocks, always use real wordpress functions, api calls, etc." while maintaining proper encapsulation through the ApiKeyManager interface.

### Version 3.2.0 (2025-08-17)

#### Dependency Injection Overhaul
- **Eliminated Service Locator Anti-Pattern**: All classes now use proper constructor injection
- **Singleton Services**: `ClaudeApiClient` registered as singleton for better performance
- **Real WordPress Integration**: Tests use real WordPress functions and API calls, not mocks
- **Clean Architecture**: Following SOLID principles throughout

#### CLI Argument Validation
- **Smart Error Detection**: Catches missing `--` prefixes and suggests corrections
- **Typo Detection**: Uses Levenshtein distance for intelligent suggestions
- **Argument Validation**: Ensures required arguments are present
- **Helpful Error Messages**: Clear examples of correct usage

#### Test Suite Optimization
- **Reduced Test Count**: From 120 to 69 tests (42% reduction)
- **Focused Testing**: Removed tests for PHP built-ins and WordPress core
- **9 Test Suites**: Down from 10 (removed unnecessary container tests)

### Version 3.1.2

### Enhanced Error Handling
- **Retry Logic**: API calls now retry up to 3 times with exponential backoff
- **Smart Error Classification**: Distinguishes between retryable and permanent errors
- **Rate Limit Handling**: Respects Retry-After headers from API
- **Comprehensive Logging**: Multi-level logging (Error, Warning, Info) for debugging

### Health Check Endpoint
- **Simple REST API**: `/wp-json/84em-local-pages/v1/health`
- **Minimal Response**: Returns only `{"status": "ok"}` for security
- **Deployment Verification**: Used by GitHub Actions workflows

### Testing Improvements
- **Full Test Coverage**: All test suites now execute completely
- **Context-Aware Output**: Suppresses expected warnings during tests
- **Extended TestCase**: Added missing assertion methods

## Version History

For a complete list of changes, bug fixes, and new features, see [CHANGELOG.md](CHANGELOG.md).

---

**Last Updated**: February 5, 2026
**Claude Model**: claude-sonnet-4-20250514
**Content Format**: WordPress Block Editor (Gutenberg) with concise sentence-per-line structure
**API Version**: 2023-06-01
**Content Strategy**: Hierarchical location pages with automatic city/state interlinking, enhanced Schema.org for LLM discoverability
**Word Count**: States 200-300 words, Cities 150-200 words
**Total Pages**: 550 (50 states + 500 cities)
**Plugin Version**: 3.21.0
**Post Type**: Standard WordPress pages (migrated from custom 'local' post type in v3.7.0)
**Architecture**: Modular PSR-4 autoloaded classes with dependency injection
**Model Selection**: Dynamic fetching from Claude Models API with interactive selection
**Interlinking**: Automatic city name and state name linking via ContentProcessor
**Metadata Generation**: AI-generated page titles, SEO titles, and meta descriptions (v3.15.0+)
**Testing**: Real WordPress integration, no mocks, 78 integration tests (100% passing with valid API key)

- Always ensure the AGENTS.md is up to date.
- Always ensure the README.md is up to date.
- Always ensure TESTING.md is up to date after any change to the test framework.
- Always ensure CHANGELOG.md is up to date with all changes following Keep a Changelog format.
- **NEVER commit directly to `main` branch** - always use feature branches and pull requests
- **Branch naming conventions**:
  - Releases: `release/v3.10.1`
  - Bugfixes: `bugfix/descriptive-name`
  - Features: `feature/descriptive-name`
- **Release workflow**: Always create a release branch (e.g., `release/v3.10.1`), make changes there, push the branch, create a PR, then merge to main
- When creating release commit messages, always put the version first in the format: "v4.2.1 - this is the release title"
