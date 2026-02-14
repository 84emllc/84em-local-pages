# Changelog

All notable changes to the 84EM Local Pages Generator plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.22.0] - 2026-02-14

### Changed
- **Broader Service Positioning**: Updated local page titles from "WordPress Development Services in [Location]" to "Web Development & WordPress Services in [Location]" across all 350 location pages
- **AI Prompt Templates**: Revised state and city content generator prompts to reference broader service offerings including web development, AI integrations, and consulting
- **Metadata Generator**: Updated SEO title templates, meta descriptions, and fallback metadata to reflect expanded service positioning
- **Schema.org Markup**: Updated WebPage schema `name` fields for both state and city pages
- **Index Page**: Updated index page title and CLI output messaging

### Fixed
- **Content Layout**: Moved spacer block from before "Why Businesses Choose" heading to after the bullet list for correct spacing before testimonial section

## [3.21.0] - 2026-02-05

### Added
- **LLM Discoverability Enhancement**: Restored substantive content for improved LLM citation potential
  - **State Pages**: Added "Why {State} Businesses Choose 84EM" H2 section with 5 bullet points:
    - [dev_years] years of web development experience
    - Deep WordPress architecture expertise
    - Agency partnerships (white-label or client-facing)
    - Direct partnership with businesses
    - Remote-first, nationwide service
  - **City Pages**: Added "Why {City} Businesses Choose 84EM" H2 section with 4 bullet points
  - Increased word count targets: States 200-300 words, Cities 150-200 words

### Changed
- **Schema.org Organization Markup**: Significantly enhanced for better structured data
  - Added `foundingDate`, `foundingLocation`, `legalName`
  - Added `description` and `slogan` fields
  - Added extensive `knowsAbout` array with 10 specializations
  - Added `sameAs` array with social/professional links (GitHub, LinkedIn, Facebook)
  - Added `contactPoint` with email, hours, and availability
  - Added `founder` Person schema with LinkedIn URL
  - Added `areaServed` Country for United States
- **State Page Schema**: Enhanced with detailed service descriptions, expanded `serviceType` array, and pricing `offers` specification
- **City Page Schema**: Enhanced with detailed descriptions, service types, and pricing specification

### Fixed
- **Agency Services Wording**: Changed "White-label services for agencies" to "Agency partnerships (white-label or client-facing)" to accurately distinguish between the two service models

### Purpose
These changes aim to restore LLM referral traffic that dropped after v3.17.0's prompt template redesign removed substantive, citable content. The enhanced schema and restored "Why Choose" sections provide specific, factual information that LLMs can extract and cite when recommending WordPress development services.

## [3.20.2] - 2026-01-12

### Changed
- **Schema Logo**: Updated organization logo URL to correct image path
- **Composer Metadata**: Updated author email to andrew@84em.com

## [3.20.1] - 2026-01-11

### Added
- **Individual Operation Notifications**: Slack alerts for single state/city operations
  - `--state="California"`: Notifies on state page generation
  - `--state="California" --city="Los Angeles"`: Notifies on city page generation
  - `--state="California" --city=all`: Notifies on all-cities-for-state generation
  - Includes operation type, location, created/updated counts, and duration

## [3.20.0] - 2026-01-11

### Added
- **Slack Notifications**: Get notified when bulk operations complete
  - `--set-slack-webhook`: Configure Slack webhook URL (encrypted storage)
  - `--test-slack-webhook`: Send test notification to verify configuration
  - `--remove-slack-webhook`: Remove stored webhook URL
  - Notifications sent on `--generate-all` and `--update-all` completion
  - Includes operation type, duration, and page counts
  - Graceful failure (never interrupts generation process)
- **New Classes**:
  - `SlackWebhookManager`: Encrypted webhook URL storage (AES-256-CBC)
  - `SlackNotifier`: Slack Block Kit message delivery

## [3.19.8] - 2026-01-11

### Changed
- **Content Prompts**: Simplified "remote" messaging to focus on Cedar Rapids headquarters
- **Banned Phrases**: Added AI-related terms (AI-powered, artificial intelligence, machine learning)
- **City Content**: Changed "Why remote works" section to "Why 84EM" for clearer positioning
- **State Content**: Added "doing the heavy lifting" to banned phrases

## [3.19.7] - 2026-01-10

### Changed
- **PHP Syntax Workflow**: Removed PHP 7.4, 8.0, 8.1 (plugin requires PHP 8.2+)
- **README Badges**: Added shields.io Security badge to tested-on section

## [3.19.6] - 2026-01-10

### Added
- **PHP 8.5 Support**: Added PHP 8.5 to syntax check workflow and README badges

## [3.19.5] - 2026-01-10

### Added
- **PHP Syntax Check Workflow**: Multi-version PHP syntax validation
  - Tests against PHP 7.4, 8.0, 8.1, 8.2, 8.3, 8.4
  - Matrix strategy for parallel testing
  - Shields.io status badges for each PHP version in README

## [3.19.4] - 2026-01-10

### Added
- **GitHub Actions Link Checking Workflows**: Added automated link validation
  - `check-links.yml`: Internal link validation on push/PR to markdown files
  - `check-external-links.yml`: Weekly external link check with issue creation
  - Status badges added to README for both workflows

## [3.19.3] - 2026-01-10

### Added
- **GitHub Actions Status Badges**: Added workflow status badges to README
  - Tests, Security Review, Deploy Production, Deploy Staging, Deploy Dev
  - Badges link to respective GitHub Actions workflow pages

## [3.19.2] - 2026-01-08

### Fixed
- **Dynamic Years Shortcode Misuse (Complete Fix)**: Removed all conflicting date references from prompts
  - v3.19.1 fix was insufficient - AI was still combining "since 1995" with shortcodes
  - Removed "Programming since 1995, WordPress specialist since 2012" text that confused AI
  - Removed "Mention experience since 1995" instruction from structure section
  - Created dedicated "EXPERIENCE SHORTCODES (CRITICAL)" section with clear rules
  - Added explicit NEVER rules: no "since [shortcode]", no combining with year dates
  - Affects both `CityContentGenerator.php` and `StateContentGenerator.php`

## [3.19.1] - 2026-01-08

### Fixed
- **Dynamic Years Shortcode Misuse**: Fixed AI prompt that caused incorrect shortcode usage
  - AI was generating "since [wp_years]" which renders as "since 14" (wrong)
  - Updated prompts to enforce correct pattern: "[wp_years] years" renders as "14 years" (correct)
  - Added explicit MUST/IMPORTANT instructions with correct/wrong examples
  - Affects both `CityContentGenerator.php` and `StateContentGenerator.php`

## [3.19.0] - 2026-01-08

### Changed
- **Dynamic Years in AI Prompts**: Updated content generation prompts to use dynamic year shortcodes
  - `CityContentGenerator.php` - Prompts now instruct AI to output `[dev_years]` and `[wp_years]` shortcodes
  - `StateContentGenerator.php` - Same shortcode instructions added to state page prompts
  - Generated pages will auto-update experience years annually instead of hardcoding values

## [3.18.3] - 2026-01-07

### Fixed
- **Placeholder Link Fix (Complete)**: Fixed two issues preventing placeholder link replacement
  - `handleUpdateLocationLinks` now passes cities array in context for state pages
  - Updated regex pattern to match `href="#"` regardless of attribute order
  - Fixes `<a class="x" href="#">City</a>` and other variations

## [3.18.2] - 2026-01-07

### Fixed
- **Placeholder City Links**: Fixed bug where city names showed `href="#"` instead of real URLs
  - Claude sometimes generates placeholder links like `[Phoenix](#)` in city mentions
  - Added `fixPlaceholderLinks()` method to ContentProcessor to detect and replace these
  - Updated link detection to skip only REAL links, not placeholder `#` links

## [3.18.1] - 2026-01-07

### Fixed
- **Critical Bug in --update-all**: Fixed query that was returning ALL pages instead of only local pages
  - `handleUpdateAll` now always includes `_local_page_state EXISTS` in the meta query
  - Bug caused non-local pages (e.g., AI Policy) to fail validation during bulk updates

## [3.18.0] - 2026-01-07

### Added
- **Randomized Testimonials**: Different testimonial blocks appear on different location pages
  - Deterministic selection based on location name (consistent on regeneration)
  - 5 testimonials for state pages, 9 for city pages
  - New `TestimonialProvider` class with `getStateBlockReference()` and `getCityBlockReference()` methods
  - New `TestimonialBlockIds` config class mapping testimonial keys to WordPress block IDs
- **Location Context Provider**: Industry and business context for all 50 states and major cities
  - State-specific industries, business landscape, and target audience data
  - City-specific context for Iowa cities and major metros
  - Falls back to state context for cities without specific data
- **WP-CLI Testimonial Finder**: `--find-testimonial-ids` command to discover testimonial pattern block IDs

### Changed
- **Prompt Improvements**: Enhanced content generation prompts
  - Added location context (industries, business landscape) for more relevant content
  - Updated experience language to use years (1995, 2012) instead of calculated durations
  - Clarified agency/business relationships: "Partners with digital agencies (white-label or client-facing) and works directly with businesses"
  - Added banned phrase detection with WP-CLI warnings
  - City pages no longer link to state pages (breadcrumbs provide navigation)

## [3.17.3] - 2025-12-29

### Changed
- **Index Page Content**: Reduced marketing speak and simplified structure
  - Removed superlatives from meta description ("Professional", "Expert")
  - Moved "What We Do" section above state list
  - Shortened heading to "WordPress Services by State"
  - Removed "Why Choose 84EM" section and closing CTA paragraph
  - Kept content lean to match city/state page updates

## [3.17.2] - 2025-12-29

### Changed
- **Simplified Prompt Structure**: Reduced landing pages to 3 sections for cleaner output
  - Intro (2-3 paragraphs)
  - Services heading + reusable block
  - CTA block
- **Removed "Why Choose Us" Section**: Credibility is already in site header; keeps pages lean
- **Removed Closing Paragraphs**: CTA block is now the sole call-to-action
- **Removed Separator Blocks**: Simpler structure doesn't need visual breaks

## [3.17.1] - 2025-12-29

### Fixed
- **Prompt Template Syntax**: Fixed double curly braces `{{` in prompt templates causing invalid WordPress block JSON
  - Changed `{{"ref":5031}}` to `{"ref":5031}` in block references
  - PHP does not treat `{{` as an escape sequence; it outputs literally
- **Large Font Size**: Added `has-large-font-size` class to all paragraphs and headings in generated content
  - Paragraphs now use `{"fontSize":"large"}` attribute
  - Headings now include `{"fontSize":"large"}` in addition to level
- **ApiKeyManager False Positive**: Fixed `setKey()` and `setModel()` methods incorrectly reporting failure
  - WordPress `update_option()` returns `false` both on failure AND when value is unchanged
  - Now verifies success by reading back the option value instead of trusting return value

## [3.17.0] - 2025-12-29

### Added
- **Shared BlockIds Config Class**: New `src/Config/BlockIds.php` centralizes reusable block IDs
  - `BlockIds::SERVICES` (5031): Services section reusable block
  - `BlockIds::CTA` (1219): CTA button reusable block
  - `BlockIds::SEPARATOR` (5034): Separator reusable block

### Changed
- **Prompt Template Redesign**: Complete rewrite of AI prompt templates for better output quality
  - Reduced prompt length from ~450 words to ~150 words
  - Aligned prompts with actual 84EM writing style (problem-first framing, direct tone)
  - Removed redundant block syntax instructions (ContentProcessor handles formatting)
  - Removed location linking instructions (ContentProcessor handles linking)
  - Changed from calculated years to "since 2012" / "since 1995" for future-proofing
- **ContentProcessor Cleanup**: Removed dead code and modernized PHP
  - Removed unused KeywordsProvider dependency (service linking was disabled)
  - Removed ~180 lines of dead service linking code
  - Extracted duplicated linking logic into `linkFirstOccurrence()` helper method
  - Replaced `strpos()` with `str_contains()` (PHP 8.0+)
- **CLI Command Rename**: Renamed `--update-keyword-links` to `--update-location-links`
  - Better reflects actual functionality (updates location links only, not service keywords)
  - Service keyword linking was removed as part of ContentProcessor cleanup
- **KeywordsProvider Removal**: Completely removed KeywordsProvider class and all references
  - Removed from StateContentGenerator, CityContentGenerator, GenerateCommand, CommandHandler
  - Simplified `stripExistingKeywordLinks()` method (no longer needs keyword data)
  - Deleted `src/Data/KeywordsProvider.php` file
  - Updated all test files to remove KeywordsProvider usage
- **Dead Code Removal**: Removed unused properties and methods across multiple classes
  - `SchemaGenerator`: Removed unused `$statesProvider` property and constructor parameter
  - `StateContentGenerator`: Removed unused `generateStateUrl()` private method and unnecessary import
  - `CityContentGenerator`: Removed unnecessary import
  - `CommandHandler`: Removed unused `$statesProvider` property and constructor parameter
  - `GenerateCommand`: Removed unused `$apiKeyManager` property and constructor parameter
  - Updated `Plugin.php` DI registrations to match simplified constructors
- **Test Suite Cleanup**: Removed obsolete tests and fixed test file issues
  - Removed `test_service_keyword_list` test that referenced removed KeywordsProvider
  - Removed non-existent `keyword-link-updates` test suite from TestCommand
  - Fixed `test-wp-cli-args.php` GenerateCommand constructor call (6 params, not 7)
  - Test count reduced from 82 to 78 tests across 10 suites
- **Documentation Rename**: Renamed `CLAUDE.md` to `AGENTS.md`
  - Updated all references in TESTING.md, deploy-reusable.yml, and planning docs
  - Added `CLAUDE.md` redirect file pointing to `AGENTS.md`

### Fixed
- **CityContentGenerator**: Fixed duplicate "Opening Section" text in prompt template
- **CityContentGenerator**: Fixed typo "paragrph" in prompt template
- **Both Generators**: Fixed missing forward slash in `<!-- wp:list -->` closing tag (was causing invalid block syntax)

### Technical Details
**Modified Files**:
- `src/Config/BlockIds.php`: New shared block ID constants class
- `src/Content/StateContentGenerator.php`: Rewrote buildPrompt(), uses BlockIds, removed KeywordsProvider dependency, removed unused generateStateUrl() method
- `src/Content/CityContentGenerator.php`: Rewrote buildPrompt(), uses BlockIds, removed KeywordsProvider dependency
- `src/Schema/SchemaGenerator.php`: Removed unused statesProvider property and constructor parameter
- `src/Utils/ContentProcessor.php`: Removed dead code, modernized PHP, extracted helper
- `src/Plugin.php`: Updated dependency injection (removed KeywordsProvider, simplified SchemaGenerator/CommandHandler/GenerateCommand registrations)
- `src/Cli/Commands/GenerateCommand.php`: Renamed handleUpdateKeywordLinks(), removed KeywordsProvider and apiKeyManager dependencies, simplified stripExistingKeywordLinks()
- `src/Cli/Commands/TestCommand.php`: Removed non-existent keyword-link-updates test suite
- `src/Cli/CommandHandler.php`: Updated command routing, removed KeywordsProvider and statesProvider dependencies
- `CLAUDE.md`: Updated prompt template documentation and command references
- `tests/integration/test-wp-cli-args.php`: Fixed GenerateCommand constructor call
- `tests/integration/test-content-generators.php`: Removed obsolete test_service_keyword_list test
- `tests/integration/*`: Updated all test files to remove KeywordsProvider usage

**Deleted Files**:
- `src/Data/KeywordsProvider.php`: Service keyword provider class (no longer needed)

## [3.16.0] - 2025-12-15

### Added
- **WP_IMPORTING Support for Bulk Operations**: Bulk operations now define the `WP_IMPORTING` constant
  - Signals to other plugins (caching, SEO, etc.) that a bulk import is in progress
  - Prevents expensive hooks from running on every page save during bulk operations
  - Applies to `--generate-all` and `--update-all` commands
  - Log message confirms when import mode is enabled
  - Significantly improves performance during bulk content generation/updates

### Technical Details
**Modified Files**:
- `src/Cli/Commands/GenerateCommand.php`: Added WP_IMPORTING constant definition to `handleGenerateAll()` and `handleUpdateAll()` methods
- `CLAUDE.md`: Added documentation for Import Mode feature

## [3.15.1] - 2025-12-15

### Fixed
- **Critical Bug**: Completed MetadataGenerator integration that was incomplete in v3.15.0
  - `StateContentGenerator` now properly accepts and uses MetadataGenerator dependency
  - `CityContentGenerator` now properly accepts and uses MetadataGenerator dependency
  - Both generators now call `generateStateMetadata()` / `generateCityMetadata()` during page creation and updates
  - Added try/catch blocks with automatic fallback to template metadata when AI generation fails
  - `--generate-all` and `--update-all` commands now trigger AI-generated metadata as intended

### Technical Details
**Problem**: v3.15.0 registered MetadataGenerator in the DI container and passed it to content generators, but the generators did not have the constructor parameter or property to accept it. The MetadataGenerator methods were never called.

**Solution**: Added MetadataGenerator as 7th constructor parameter to both generators with proper property assignment and method calls in `generateStatePage()`, `updateStatePage()`, `generateCityPage()`, and `updateCityPage()`.

**Modified Files**:
- `src/Content/StateContentGenerator.php`: Added MetadataGenerator property, constructor parameter, and method calls
- `src/Content/CityContentGenerator.php`: Added MetadataGenerator property, constructor parameter, and method calls

## [3.15.0] - 2025-12-15

### Added
- **AI-Generated SEO Metadata**: Page titles, SEO titles, and meta descriptions are now generated by Claude AI
  - New `MetadataGenerator` class (`src/Content/MetadataGenerator.php`) handles AI metadata generation
  - Generates unique, contextually relevant metadata for each location page
  - Separate API call for metadata generation (returns structured JSON)
  - Fallback templates used automatically when AI generation fails
- **Enhanced Content Generators**: Both generators now integrate with MetadataGenerator
  - `StateContentGenerator`: Uses AI-generated metadata with fallback support
  - `CityContentGenerator`: Uses AI-generated metadata with fallback support

### Changed
- **Metadata Storage**: All metadata now stored in custom 84EM meta fields
  - `_84em_seo_title`: SEO title (appears in browser tab and search results)
  - `_84em_seo_description`: Meta description (appears in search results)
  - Page title stored in WordPress `post_title` field
  - Migrated from Genesis Framework meta fields (`_genesis_*`) to custom keys
- **Dependency Injection**: Updated Plugin.php to register MetadataGenerator service
  - MetadataGenerator injected into StateContentGenerator and CityContentGenerator
  - Follows existing DI patterns with proper constructor injection

### Technical Details
**New Files**:
- `src/Content/MetadataGenerator.php`: AI metadata generation with prompts and fallbacks

**Modified Files**:
- `src/Content/StateContentGenerator.php`: Added MetadataGenerator dependency and integration
- `src/Content/CityContentGenerator.php`: Added MetadataGenerator dependency and integration
- `src/Plugin.php`: Registered MetadataGenerator in DI container
- `CLAUDE.md`: Updated documentation with AI metadata feature details

**Metadata Specifications**:
- Page Title: 40-70 characters, unique per location
- SEO Title: 50-60 characters, must end with " | 84EM"
- Meta Description: 150-160 characters, includes call-to-action

## [3.14.0] - 2025-11-30

### Added
- **Expanded City Coverage**: Increased from 6 cities to 10 cities per state
  - Total pages increased from 350 to 550 (50 states + 500 cities)
  - 200 new city pages added across all 50 states
  - All existing 6 cities per state preserved (no data loss)
  - New cities selected based on US Census population data (2024)
- **Enhanced State Content**: State pages now mention all 10 major cities
  - Updated `StateContentGenerator.php` prompts to reference 10 cities
  - City list in content generation expanded from 6 to 10

### Changed
- **StatesProvider Data**: Expanded city arrays from 6 to 10 entries per state
  - Research-based additions using US Census 2024 population data
  - Alaska: Added Kenai, Palmer, Kodiak, Bethel (kept existing Sitka, Ketchikan)
  - California: Added Long Beach, Oakland, Bakersfield, Anaheim
  - Texas: Added Arlington, Corpus Christi, Plano, Laredo
  - (All 50 states updated similarly)
- **CLI Output**: Updated `GenerateCommand.php` progress messages
  - "500 city pages (10 per state)" instead of "300 city pages (6 per state)"
  - "550 pages" total instead of "350 pages"
- **Help Text**: Updated `CommandHandler.php` usage information
- **Documentation**: Updated all references in README.md, CLAUDE.md
- **Cost Estimates**: Updated API cost estimates for 550 pages

### Technical Details
**Modified Files**:
- `src/Data/StatesProvider.php`: Expanded all 50 state city arrays to 10 entries
- `src/Content/StateContentGenerator.php`: Updated city slice from 6 to 10, updated prompt text
- `src/Cli/Commands/GenerateCommand.php`: Updated CLI output messages
- `src/Cli/CommandHandler.php`: Updated help text
- `tests/integration/test-data-structures.php`: Updated assertions to expect 10 cities
- `tests/integration/test-content-generators.php`: Updated assertions to expect 10 cities
- `tests/integration/test-wp-cli-args.php`: Updated assertions to expect 10 cities
- `README.md`: Updated all city count and total page references
- `CLAUDE.md`: Updated all city count and total page references

**Migration Notes**:
- Existing state and city pages are preserved
- New city pages can be generated incrementally using `--state="State" --city=all`
- Or generate all new pages at once using `--generate-all`

## [3.13.0] - 2025-11-29

### Changed
- **Rebranding**: Changed "White-Label" terminology to "Agency Services" throughout the plugin
  - Updated post titles in `StateContentGenerator.php` and `CityContentGenerator.php`
  - Updated meta descriptions in both content generators
  - Updated LD-JSON schema descriptions in `SchemaGenerator.php`
  - Updated service keyword mapping in `KeywordsProvider.php`
  - Changed service URL from `/services/white-label-wordpress-development-for-agencies/` to `/services/wordpress-development-for-agencies/`
- **Index Page Improvement**: Replaced hardcoded service list with WordPress reusable block
  - Index page now uses `<!-- wp:block {"ref":5031} /-->` for consistent service listing
  - Ensures design changes propagate automatically to index page
- **Documentation Cleanup**: Removed outdated prompt templates from CLAUDE.md
  - Prompt templates are now maintained only in source code files
  - Reduced documentation maintenance overhead

### Added
- **New Keyword Mapping**: Added "Agency Services" keyword to `KeywordsProvider.php`

### Fixed
- **Content Generator**: Removed unnecessary `<strong>` tag from first benefit list item in prompts
  - Ensures consistent formatting across all benefit list items
  - Fixed in both `StateContentGenerator.php` and `CityContentGenerator.php`

## [3.12.0] - 2025-11-17

### Added
- **Checkpoint/Resume System**: Comprehensive checkpoint system for bulk operations to recover from non-retryable errors
  - New `CheckpointManager` class (`src/Utils/CheckpointManager.php`) manages progress tracking
  - Checkpoints saved after each successful API call (after each state/city page)
  - Supports resuming with `--resume` flag on `--generate-all` and `--update-all` commands
  - Works with `--states-only` flag as well
  - Checkpoints automatically expire after 24 hours
  - Automatic cleanup on successful completion
- **New WP-CLI Flags**:
  - `--resume` flag for all bulk operations
  - Examples: `wp 84em local-pages --generate-all --resume`
  - Examples: `wp 84em local-pages --update-all --states-only --resume`

### Changed
- **Enhanced GenerateCommand**: Updated `handleGenerateAll()` and `handleUpdateAll()` methods with checkpoint support
  - Saves progress after each page generation/update
  - Skips already-processed states/cities on resume
  - Shows progress summary when resuming from checkpoint
  - Handles mid-state resumption (can resume in the middle of processing a state's cities)
- **Plugin Architecture**: Registered `CheckpointManager` in dependency injection container
  - Added to Utils layer in plugin architecture
  - Injected into `GenerateCommand` constructor

### Fixed
- **Recovery from Non-Retryable Errors**: No longer need to start bulk operations from scratch after errors
  - Recover from API quota exhaustion
  - Resume after authentication errors (401, 403)
  - Continue after invalid model errors (400, 404)
  - Resume after any non-retryable API error

### Technical Details
**New Files**:
- `src/Utils/CheckpointManager.php`: Checkpoint management class with save/load/delete methods

**Modified Files**:
- `src/Cli/Commands/GenerateCommand.php`: Added checkpoint support to bulk operations (350+ lines of changes)
- `src/Plugin.php`: Registered CheckpointManager in container and injected into GenerateCommand
- `CLAUDE.md`: Added comprehensive resume functionality documentation
- `README.md`: Updated with resume feature documentation

**Checkpoint Data Structure**:
- Progress counters: `state_created_count`, `state_updated_count`, `city_created_count`, `city_updated_count`
- Processing state: `processed_states`, `current_state`, `current_city_index`
- Metadata: `timestamp` for expiration tracking

**Storage**:
- WordPress options table with operation-specific keys
- Keys: `84em_local_pages_checkpoint_generate-all`, `84em_local_pages_checkpoint_generate-all-states-only`, `84em_local_pages_checkpoint_update-all`, `84em_local_pages_checkpoint_update-all-states-only`

**Use Case**: When running `--generate-all` with 350 pages, if error occurs on page 150, resume with `--generate-all --resume` to continue from page 151.

**Note**: Retryable errors (timeouts, rate limits, server errors) still automatically retry up to 5 times with exponential backoff. Checkpoints only needed for non-retryable errors.

## [3.11.0] - 2025-11-17

### Changed
- **SEO Metadata Update**: Revised page titles and meta descriptions to emphasize core services
  - Removed "AI-Enhanced" prefix from titles and descriptions
  - Reordered service emphasis: "WordPress Development, Plugins, Consulting, White-Label"
  - State page titles: "WordPress Development, Plugins, Consulting, White-Label in {State} | 84EM"
  - City page titles: "WordPress Development, Plugins, Consulting, White-Label in {City}, {State} | 84EM"
  - Updated in both `StateContentGenerator.php` and `CityContentGenerator.php`
  - Better aligns with current business positioning and market demands

### Removed
- **Automatic Service Keyword Linking**: Disabled automatic keyword linking in content processor
  - Commented out `addServiceLinks()` call in `ContentProcessor.php`
  - Reduces over-optimization and potential SEO penalties
  - Focuses link equity on location-based links instead
  - Service links in hardcoded reusable blocks remain active

### Technical Details
- Modified `src/Content/StateContentGenerator.php` getPostTitle() and getMetaDescription() methods
- Modified `src/Content/CityContentGenerator.php` getPostTitle() and getMetaDescription() methods
- Modified `src/Utils/ContentProcessor.php` processContent() method to skip service keyword linking

## [3.10.2] - 2025-11-02

### Fixed
- **Critical Bug**: Fixed nested anchor tags in location links causing invalid HTML
  - Nested links were created when old URL format links existed and new linking logic tried to link the same text again
  - Example: `<a href="OLD_URL"><a href="NEW_URL">Wisconsin</a></a>`
  - Added nested link detection and removal in `stripExistingKeywordLinks()` method
  - Pattern now collapses nested links before processing: `/<a\s+href=["\'][^"\']*["\']>\s*<a\s+href=["\'][^"\']*["\']>([^<]+)<\/a>\s*<\/a>/i`
  - Enhanced `addLocationLinks()` to prevent creating nested links in the first place
  - Now checks if location name is already inside ANY link before attempting to link
  - Uses tag-aware content splitting to avoid matching text inside HTML tags
  - Fixed in `src/Cli/Commands/GenerateCommand.php` (lines 1380-1386) and `src/Utils/ContentProcessor.php` (lines 316-422)

### Changed
- **Improved Link Prevention**: Enhanced location linking logic to be more robust
  - Checks for existing links with ANY URL, not just specific URLs
  - Splits content by HTML tags before pattern matching to avoid tag attribute matches
  - Early returns if URL or linked text already exists in content

## [3.10.1] - 2025-11-02

### Fixed
- **Critical Bug**: Fixed `--update-keyword-links` command processing ALL published pages instead of only local pages
  - Added required `meta_query` filter for `_local_page_state` EXISTS to ensure command only affects the 350 local pages
  - Previously missing meta_query caused command to update every published page on the WordPress site
  - Command now correctly processes exactly 350 pages (all local pages) or 50 pages with `--states-only` flag
  - Fixed in `src/Cli/Commands/GenerateCommand.php` handleUpdateKeywordLinks() method (lines 1123-1143)

### Added
- **Integration Tests**: New test suite for keyword link updates functionality
  - Created `tests/integration/test-keyword-link-updates.php` with 3 comprehensive tests
  - Test verifies command only processes pages with `_local_page_state` meta key
  - Test verifies `--states-only` flag correctly excludes city pages
  - Test verifies both state and city pages are included without flag
  - Registered new test suite in TestCommand.php
  - Total test count increased from 82 to 85 tests

### Technical Details
**Files Modified**:
- `src/Cli/Commands/GenerateCommand.php`: Added meta_query to filter for local pages only (line 1126-1131)
- `src/Cli/Commands/TestCommand.php`: Registered new keyword-link-updates test suite (line 37)
- `tests/integration/test-keyword-link-updates.php` (NEW): 3 integration tests validating the fix

**Testing Results**: 84/85 tests passing (98.82%) - single failing test is pre-existing bug unrelated to this fix

## [3.10.0] - 2025-11-02

### Added
- **URL Migration System**: Complete migration infrastructure for simplifying URL structure
  - New `LegacyUrlRedirector` class (`src/Redirects/LegacyUrlRedirector.php`) handles 301 redirects from old to new URLs
  - New WP-CLI command `--migrate-urls` for automated URL migration of all 350 pages
  - Automatic parent-child relationship updates during migration
  - Progress tracking and comprehensive migration reporting
- **Redirects Layer**: New namespace and architecture component for URL management
  - Registered in dependency injection container as singleton service
  - Initialized on plugin load to handle all legacy URL requests
  - Supports both state and city URL pattern redirects

### Changed
- **URL Structure Simplified**: Cleaner, more user-friendly URLs for all local pages
  - **State URLs**: Changed from `/wordpress-development-services-usa/wordpress-development-services-{state}/` to `/wordpress-development-services-usa/{state}/`
  - **City URLs**: Changed from `/wordpress-development-services-usa/wordpress-development-services-{state}/{city}/` to `/wordpress-development-services-usa/{state}/{city}/`
  - State page slugs now use just the state name instead of `wordpress-development-services-{state}`
  - All state pages set index page as parent for proper hierarchical structure
- **URL Generation Updated**: All URL generation methods updated to use new format
  - `StateContentGenerator::generateStateUrl()` updated in `src/Content/StateContentGenerator.php`
  - `StateContentGenerator::setupStateUrl()` updated to use new slug format and set parent relationship
  - `ContentProcessor::generateStateUrl()` updated in `src/Utils/ContentProcessor.php`
  - `ContentProcessor::generateCityUrl()` updated to use new format
  - Schema URL generation updated in 3 locations in `src/Schema/SchemaGenerator.php`:
    - State schema URL fallback
    - City schema URL fallback
    - Breadcrumb schema state URL
- **Link Processing Updated**: Content processor handles both legacy and new URL formats
  - Link stripping pattern updated in `src/Cli/Commands/GenerateCommand.php` to support migration period
  - Pattern matches both old and new URL structures during transition
- **Test Suite Updated**: All tests updated for new URL structure
  - Updated assertions in `tests/integration/test-content-processing.php` to expect new URLs
  - Tests for city URL generation, state linking, and city linking all updated
  - Maintains 98.78% test pass rate (81/82 tests passing)

### Fixed
- **Meta Query Bug**: Fixed URL migration query to use correct meta keys
  - State pages identified by presence of `_local_page_state` AND absence of `_local_page_city`
  - City pages identified by presence of both `_local_page_state` AND `_local_page_city`
  - Replaced incorrect `_local_page_type` meta query with correct meta field detection
- **Container Registration**: Fixed `LegacyUrlRedirector` registration using `register()` instead of `singleton()`
  - Resolved "Call to undefined method Closure::initialize()" error
  - Added proper `use` statement for `LegacyUrlRedirector` class

### Migration Guide

#### Automated Migration (Recommended)

Run the following commands in sequence:

```bash
# 1. Migrate all URLs (updates 50 states + 300 cities)
wp 84em local-pages --migrate-urls

# 2. Update all internal links to use new URLs
wp 84em local-pages --update-keyword-links

# 3. Regenerate XML sitemap with new URLs
wp 84em local-pages --generate-sitemap

# 4. Flush WordPress rewrite rules
wp rewrite flush
```

#### What the Migration Does

- Updates all state page slugs from `wordpress-development-services-{state}` to `{state}`
- Sets state pages' parent to the index page (wordpress-development-services-usa)
- City pages automatically inherit new URLs from updated parent slugs
- All old URLs automatically redirect (301) to new URLs via `LegacyUrlRedirector`

#### Backward Compatibility

- **100% Backward Compatible**: All old URLs automatically redirect to new URLs with 301 status
- `LegacyUrlRedirector` permanently active to handle cached links and external references
- No broken links - all existing URLs continue to work

### Technical Details

**Files Modified**:
- `src/Redirects/LegacyUrlRedirector.php` (NEW): Handles 301 redirects from legacy URLs
- `src/Content/StateContentGenerator.php`: Updated URL generation and slug format (lines 406-436)
- `src/Utils/ContentProcessor.php`: Updated both URL generation methods (lines 434-451)
- `src/Schema/SchemaGenerator.php`: Updated 3 URL references (lines 81, 159, 185)
- `src/Cli/Commands/GenerateCommand.php`: Added migration command and updated link stripping (lines 1250-1356, 1284)
- `src/Plugin.php`: Registered and initialized `LegacyUrlRedirector` (lines 28, 91-93, 150-154)
- `src/Cli/CommandHandler.php`: Added `--migrate-urls` flag to all command lists and help text (lines 155-157, 680, 745, 818, 1007)
- `tests/integration/test-content-processing.php`: Updated URL assertions for new format (lines 84-89, 106, 172-177)
- `84em-local-pages.php`: Version bump to 3.10.0
- `CHANGELOG.md`: Documented all URL migration changes

**Testing Results**: 81/82 tests passing (98.78%) - single failing test is pre-existing bug unrelated to URL migration

**Migration Statistics**: Successfully migrated 50 state pages + 300 city pages (350 total), updated 415 pages with new internal links, regenerated sitemap with 415 pages

## [3.9.0] - 2025-11-02

### Changed
- **SEO Metadata**: Updated page titles and meta descriptions to emphasize AI-enhanced capabilities
  - State page titles: Changed from "Custom WordPress Plugin Development, Consulting, and White-Label services" to "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting"
  - City page titles: Changed from "Custom WordPress Plugin Development, Consulting, and White-Label services" to "AI-Enhanced WordPress Development, White-Label Services, Plugins, Consulting"
  - State meta descriptions: Updated to match new title format
  - City meta descriptions: Updated to match new title format
  - Reflects 84EM's integration of AI technology in WordPress development workflow

### Added
- **URL Migration Planning**: Comprehensive planning document for future URL structure simplification
  - Document location: `planning/URL_MIGRATION.md`
  - Plans to simplify state URLs from `/wordpress-development-services-usa/wordpress-development-services-{state}/` to `/wordpress-development-services-usa/{state}/`
  - Plans to simplify city URLs from `/wordpress-development-services-usa/wordpress-development-services-{state}/{city}/` to `/wordpress-development-services-usa/{state}/{city}/`
  - Includes complete implementation plan with LegacyUrlRedirector class following plugin's DI/container architecture
  - Outlines required code changes across StateContentGenerator, CityContentGenerator, ContentProcessor, SchemaGenerator
  - Provides migration workflow, testing plan, and rollback procedures
  - Includes 301 redirect strategy to preserve SEO value

### Technical Details
- Modified `src/Content/StateContentGenerator.php` getPostTitle() and getMetaDescription() methods
- Modified `src/Content/CityContentGenerator.php` getPostTitle() and getMetaDescription() methods
- Created `planning/URL_MIGRATION.md` comprehensive migration planning document

## [3.8.0] - 2025-11-02

### Changed
- **Content Generation Prompts**: Completely revamped prompts for more direct, authentic voice
  - Added comprehensive voice and tone guidelines emphasizing matter-of-fact, no-fluff communication
  - Explicitly banned marketing superlatives ("game-changing," "cutting-edge," "industry-leading," "best-in-class")
  - Removed soft benefit language in favor of concrete deliverables
  - Added natural contractions guidance ("won't," "you're," "we'll") for conversational tone
  - Reduced opening section from 3-4 sentences to 2-3 sentences
  - Changed section structure to 1 sentence per paragraph for better readability
  - Added explicit instruction to never use em-dashes or hyphens
  - Enhanced with tone examples showing "Not this" vs "Do this" patterns
- **Service Lists**: Migrated to reusable WordPress blocks for consistency
  - State services now use `<!-- wp:block {"ref":5031} /-->` (reusable block)
  - City services now use `<!-- wp:block {"ref":5031} /-->` (reusable block)
  - Eliminates AI-generated service lists in favor of consistent, controlled content
  - Ensures all pages use identical service formatting
- **Benefits Lists**: Enhanced with custom styling and structured HTML
  - Added checkmark list style class: `is-style-checkmark-list`
  - Added large font size class for better readability
  - Structured as proper list-item blocks with specific benefits
  - Varies content naturally while maintaining consistency
- **CTA Blocks**: Simplified to reusable blocks
  - End-of-content CTA now uses `<!-- wp:block {"ref":1219} /-->` (Free Consult button)
  - Section separator now uses `<!-- wp:block {"ref":5034} /-->` (code icon separator)
  - Reduces prompt complexity and ensures design consistency
- **Critical Instructions**: Added safeguards for keyword linking
  - Explicit instruction to avoid linking substrings within words (e.g., "AI" in "retail")
  - Focus all service-related linking to main `/services/` page
  - Lists 6 core service categories to emphasize: AI-Enhanced Development, White-Label Agency Services, Custom Plugin Development, Code Cleanup and Refactoring, Consulting & Strategy, Maintenance & Support

### Improved
- **Content Authenticity**: Generated content now reads more like genuine developer communication
- **Prompt Clarity**: Removed ambiguity about tone, style, and formatting expectations
- **Maintenance**: Using reusable blocks means design changes update across all pages instantly
- **Readability**: One sentence per paragraph in opening/closing sections improves scannability

### Technical Details
- Modified `src/Content/StateContentGenerator.php` buildPrompt() method
- Modified `src/Content/CityContentGenerator.php` buildPrompt() method
- Prompts now reference specific WordPress reusable block IDs (5031, 1219, 5034)
- All block references use `<!-- wp:block {"ref":XXXX} /-->` syntax

## [3.7.0] - 2025-10-30

### ⚠️ BREAKING CHANGES

This release contains **major architectural changes** that require action when upgrading:

**IMPORTANT**: This version migrates from a custom 'local' post type to standard WordPress 'page' type. Existing local pages will no longer be visible after upgrading until you complete the migration steps below.

#### Required Migration Steps

**Option 1: Regenerate All Pages (Recommended)**
```bash
# Delete existing local pages
wp post delete $(wp post list --post_type=local --format=ids) --force

# Regenerate all pages with new structure
wp 84em local-pages --generate-all
wp 84em local-pages --generate-index
wp 84em local-pages --generate-sitemap
```

**Option 2: Manual Post Type Migration**
```bash
# Convert existing local posts to pages
wp post list --post_type=local --format=ids | xargs -I % wp db query "UPDATE wp_posts SET post_type='page' WHERE ID=%"

# Flush permalinks
wp rewrite flush
```

**Why This Change?**
- Eliminates custom post type complexity and maintenance overhead
- Provides better WordPress core integration
- Standard WordPress pages UI/UX for editing
- No custom rewrite rules needed
- Simpler, more maintainable codebase

### Changed
- **Post Type Migration**: Converted from custom 'local' post type to standard WordPress 'page' type
  - Updated all WP_Query operations in `GenerateCommand.php` to use `post_type=page` instead of `post_type=local`
  - Modified `StateContentGenerator.php` to create standard WordPress pages
  - Modified `CityContentGenerator.php` to create standard WordPress pages
  - Removed custom post type registration from `Plugin.php` initialization
  - Removed custom post type activation hooks from `Plugin.php`
  - All custom meta fields preserved (_local_page_state, _local_page_city, _local_page_type)
  - Hierarchical parent-child relationships maintained via post_parent
  - URL structure remains unchanged after migration
  - All interlinking functionality preserved

### Removed
- **Custom Post Type Infrastructure**: Eliminated custom post type complexity
  - Deleted `src/PostTypes/LocalPostType.php` file entirely
  - Removed LocalPostType class registration from dependency injection container
  - Removed flush_rewrite_rules() calls from activation process
  - No custom rewrite rules for post type needed

### Technical Details
- WP-CLI commands now query pages using `--post_type=page --meta_key=_local_page_state`
- All monitoring and troubleshooting commands updated in documentation
- Custom meta fields serve as identifiers for local pages among all WordPress pages
- Parent-child hierarchy maintained through standard post_parent relationships

## [3.6.1] - 2025-10-25

### Fixed
- **Keyword Linking Safeguards**: Fixed automatic keyword linking creating malformed HTML and unwanted links in service lists
  - Fixed nested links within href attributes (e.g., "ai" being linked inside `href="...ai-enhanced-wordpress-development/"`)
  - Prevented automatic linking of text inside service list items that contain `<strong>` tags
  - Implemented placeholder-based protection for service category lists
  - Added tag-aware content splitting to prevent matching text inside HTML attributes
- **Prompt Structure Cleanup**: Removed `<strong>` tags from "Learn More →" links in both state and city page prompts
  - Only service category titles remain bolded (e.g., `<strong>AI Services</strong>`)
  - "Learn More →" links are now unbolded for cleaner appearance
  - Fixed HTML typo in `CityContentGenerator.php` where `</strong>` was misplaced
- **Service List Protection**: Service list items now have ONLY the hardcoded "Learn More →" links
  - No automatic keyword linking occurs within service descriptions
  - Preserves clean, readable service lists as specified in prompts
  - Words like "Plugins", "Themes", "Custom Solutions" are no longer auto-linked

### Changed
- **ContentProcessor Enhancement**: Improved `addServiceLinks()` method with comprehensive protection
  - Extracts and protects list items containing `<strong>` tags using placeholder replacement
  - Splits content by HTML tags before keyword matching to avoid attribute matches
  - Restores protected content after all keyword linking is complete

### Technical Details
- **Files Modified**:
  - `src/Utils/ContentProcessor.php`: Added placeholder-based list item protection and tag-aware splitting
  - `src/Content/StateContentGenerator.php`: Removed `<strong>` tags from Learn More links
  - `src/Content/CityContentGenerator.php`: Removed `<strong>` tags from Learn More links, fixed HTML structure
  - `CLAUDE.md`: Updated documentation with version 3.6.1 and new safeguards
  - `84em-local-pages.php`: Version bump to 3.6.1

## [3.6.0] - 2025-10-25

### Added
- **AI Services Listing**: Added "AI Services" as the first item in service lists for both state and city pages
  - New service line: "AI Services: Development, Research, Troubleshooting, Security, Code Review"
  - Positions 84EM's AI capabilities prominently in all local pages
  - Reflects the company's expanded AI service offerings

### Changed
- **Enhanced Keywords Provider**: Expanded and reorganized keyword mapping with AI-focused services
  - Added new AI-related keywords: "AI Enhanced WordPress Development", "AI", "AI WordPress", "AI WordPress development", "AI Plugins", "AI WordPress Plugins"
  - Added "Consulting" and "WordPress Consulting" keywords
  - Reorganized keyword URLs to point to specific service pages (AI, Consulting, Maintenance)
  - Updated "WordPress development" to link to AI services page instead of work page
  - Created shorter, cleaner URL variable names for better code readability
- **Code Cleanup**: Removed unused `$service_keywords_list` variable from content generators
  - Variable was declared but never used in `CityContentGenerator.php` and `StateContentGenerator.php`
  - Simplifies code and reduces memory footprint

### Improved
- **Service Page Routing**: More specific keyword-to-page mappings for better user navigation
  - Consulting-related keywords → `/services/wordpress-consulting-strategy/`
  - Maintenance-related keywords → `/services/wordpress-maintenance-support/`
  - AI development keywords → `/services/ai-enhanced-wordpress-development/`

## [3.5.1] - 2025-10-23

### Changed
- **SEO Meta Updates**: Refined page titles and meta descriptions for improved focus
  - Post titles updated to emphasize "Custom WordPress Plugin Development, Consulting, and White-Label services"
  - Meta descriptions updated to match new title structure
  - City meta descriptions now properly include city and state names
  - More concise and focused messaging aligned with core service offerings

### Fixed
- **Documentation**: Fixed typo in docblock comments (descirption → description)
  - Corrected in both StateContentGenerator.php and CityContentGenerator.php

## [3.5.0] - 2025-10-23

### Changed
- **Service List Standardization**: Updated content generation prompts to use fixed, consistent service list format
  - State pages now use 3-item standardized list: Development, Support, Consulting
  - City pages now use 3-item standardized list: Development, Support, Consulting
  - Removed dynamic service selection from AI prompts to ensure consistency across all pages
  - Service descriptions now use colons format: "Service Category: Brief description"
  - Updated both `StateContentGenerator.php` and `CityContentGenerator.php` prompts
- **CTA Button Text**: Changed call-to-action button text from "Start Your WordPress Project" to "Free Consult"
  - More concise and action-oriented
  - Lower barrier to entry for potential clients
  - Updated in both state and city page prompts
- **Visual Separators**: Added code-style SVG separator elements before all H2 headings
  - Uses `wp-block-uagb-separator` block with code bracket icon
  - Provides visual separation between content sections
  - Consistent with 84EM's developer-focused brand identity
- **CTA Positioning**: Moved primary CTA button block from before H2 headings to end of content
  - Reduces visual clutter in content body
  - Places conversion opportunity after value proposition
  - Separator now appears before H2s instead of CTA block

### Updated
- **Keywords Provider**: Refined service keyword list
  - Added: "Plugins", "Custom Solutions", "Agency Partnership"
  - Removed: "custom theme development" (redundant with other theme keywords)
  - Reordered keywords for better logical grouping
  - All keywords link to appropriate service pages

## [3.4.0] - 2025-10-20

### Added
- **Fuzzy Keyword Matching**: Intelligent algorithm ensures every service list item gets linked, even when API-generated text doesn't exactly match keyword list
  - Scans all list items and searches for matching keywords using case-insensitive substring matching
  - Selects the longest/most specific matching keyword when multiple matches found
  - Preserves original text casing from generated content
  - Works with any variation in capitalization and phrasing
  - New methods in `ContentProcessor`: `addServiceLinksInListItems()`, `findBestKeywordMatch()`, `linkKeywordInText()`
- **Expanded Keyword List**: Added 12+ new keyword variations in `KeywordsProvider` to improve matching coverage
  - "Custom WordPress development"
  - "Data migration and platform transfers"
  - "WordPress security audits and hardening"
  - "WordPress Maintenance and Support"
  - "White Label Development"
  - "Platform Migrations"
  - "WordPress maintenance and ongoing support"
  - "White-label development services for agencies"
  - Multiple capitalization variations for better fuzzy matching

### Changed
- **Content Structure**: Updated prompt templates for more concise, scannable content
  - **State Pages**: Word count reduced from 300-400 to 200-300 words
  - **City Pages**: Word count reduced from 250-350 to 200-300 words
  - **Opening Section**: Changed from "1-2 short paragraphs" to "3-4 short sentences, one per line"
  - **Closing Section**: Changed from "2-3 sentences in paragraph" to "2 sentences, each on their own line"
  - Improved readability with sentence-per-line format for better scanning
- **Keyword Routing**: Updated several keywords to point to more specific service pages
  - "digital agency services" now links to white-label development page
  - "API integrations" now links to services page instead of work page
  - "WordPress security audits" links to services page
- **Service Link Processing**: Refactored to prioritize list items first, then process regular paragraph content
  - Ensures service list items always get linked before paragraph occurrences
  - Prevents duplicate linking attempts

### Improved
- **Case-Insensitive Matching**: Enhanced link detection to use regex patterns instead of string position checks
  - Prevents missed matches due to case variations
  - More reliable detection of existing links in content
- **Link Coverage**: Fuzzy matching algorithm now achieves nearly 100% link coverage in service lists
  - Tested on all 350 pages with successful links in every service list item
  - Handles both formatted lists ("Service: description") and unformatted lists ("Service description...")

### Documentation
- **CLAUDE.md**: Comprehensive updates reflecting all changes
  - Updated version number to 3.4.0
  - Added "Fuzzy Keyword Matching" section with detailed explanation and examples
  - Updated word counts throughout (200-300 for all pages)
  - Updated prompt templates to match code implementation
  - Expanded keyword list documentation
  - Updated interlinking system documentation
  - Updated "Last Updated" footer with new features

## [3.3.3] - 2025-10-19

### Changed
- **Code Quality**: Refactored `GenerateCommand.php` to standardize database query methods
  - Replaced all 12 instances of `get_posts()` with consistent `WP_Query` usage
  - Created three reusable helper methods: `findStatePage()`, `findCityPage()`, `findLocalPages()`
  - Reduced code duplication by 47 lines (201 lines removed, 154 added)
  - Improved memory management with proper `wp_reset_postdata()` calls in all queries
  - Enhanced maintainability by centralizing query logic in helper methods
  - Updated `buildIndexPageContent()` to accept array of posts instead of `WP_Query` object
  - Updated `handleSitemapGeneration()` and `handleIndexGeneration()` to use helper methods
  - All database queries now follow WordPress best practices using `WP_Query` class

### Fixed
- **Help Documentation**: Updated WP-CLI help text to list all 10 available test suites
  - Added missing test suites: `cli-args`, `ld-json`, `api-client`, `content-generators`, `error-handling`, `security`, `model-management`
  - Previously only showed 3 of 10 test suites (`encryption`, `data-structures`, `content-processing`)
  - Help output now matches actual available test suites defined in `TestCommand.php`

## [3.3.2] - 2025-10-19

### Fixed
- **Update Command**: Fixed `--states-only` flag support in `--update-all` command
  - Added proper filtering to skip city pages when `--states-only` is specified
  - Added skipped count to summary output
  - Prevents unnecessary API calls and delays when updating only state pages
  - Now consistent with `--generate-all --states-only` behavior
  - Improves performance when only state pages need updating

## [3.3.1] - 2025-10-19

### Changed
- **Plugin Metadata**: Updated plugin header information
  - Updated version to 3.3.1
  - Added Author URI: https://84em.com/
  - Added Plugin URI: https://github.com/84em/84em-local-pages/
  - Updated plugin constant `EIGHTYFOUREM_LOCAL_PAGES_VERSION` to 3.3.1

### Fixed
- **Encryption Security**: Added IV length validation in `Encryption::decrypt()` method
  - Prevents OpenSSL warnings when attempting to decrypt corrupted data
  - Validates IV is exactly 16 bytes before calling `openssl_decrypt()`
  - Returns `false` gracefully when data is corrupted instead of generating PHP warnings
  - Adds debug logging when WP-CLI is available to help diagnose encryption issues
  - Fixes Sentry issues #6806189599 and #6814063379 (52 total events)

## [3.3.0] - 2025-10-19

### Changed
- **Content Readability**: Enhanced content structure with list-based formatting for improved scannability
  - Updated `StateContentGenerator.php` prompt to use unordered lists for services and benefits
  - Updated `CityContentGenerator.php` prompt to use unordered lists for services and benefits
  - State pages: 8-10 service list items, 4-5 benefit list items
  - City pages: 6-8 service list items, 3-4 benefit list items
  - Short paragraphs (2-3 sentences maximum) for better readability
  - Lists use proper WordPress `<!-- wp:list -->` block syntax
  - Updated `CLAUDE.md` to document new list-based prompt templates
  - Benefits: Improved mobile experience, reduced cognitive load, better scannability, maintained SEO value
- **Test Classification**: Renamed test suite from "unit tests" to "integration tests"
  - Renamed `tests/unit/` directory to `tests/integration/`
  - Updated all test file docblocks to reflect integration testing terminology
  - Updated `TestCommand.php` to reference new directory path
  - Updated `TESTING.md` and `README.md` documentation with correct terminology
  - Tests are integration tests because they use real WordPress database, real API calls, and real class instances

### Removed
- **Obsolete Mock Files**: Removed unused mock files from test suite
  - Deleted `tests/fixtures/mock-api-responses.php` (completely unused)
  - Deleted `tests/wp-mocks.php` (completely unused)
  - Removed `tests/fixtures/` directory
  - Updated `.gitignore` to remove mock file references
  - All tests use real WordPress functions and real API calls (no mocks)

## [3.2.5] - 2025-10-19

### Added
- **License**: Changed from proprietary to MIT License
  - Created LICENSE file with MIT License text
  - Updated all PHP file headers with MIT License docblock tags
  - Updated composer.json license field
  - Updated README.md with full MIT License text
- **Test Configuration Prompting**: Added API configuration check before running tests
  - TestCommand now checks for API key and model before executing tests
  - Displays clear error message with setup instructions if configuration is missing
  - Shows step-by-step commands for configuring missing API key or model
  - Prevents test failures due to missing API configuration

### Changed
- **Option Names**: Updated all WordPress option names to be plugin-specific
  - Changed prefix from `84em_` to `84em_local_pages_` for better namespacing
  - API key: `84em_claude_api_key_encrypted` → `84em_local_pages_claude_api_key_encrypted`
  - API key IV: `84em_claude_api_key_iv` → `84em_local_pages_claude_api_key_iv`
  - Model: `84em_claude_api_model` → `84em_local_pages_claude_api_model`
  - Test options also updated: `test_84em_local_pages_*`
  - **Note**: Existing installations will need to reconfigure API key and model after update
- **Test Data Isolation**: Complete isolation between test and production data
  - All test data now stored in `test_` prefixed WordPress options
  - `ApiKeyManager` uses `getOptionName()` helper to prepend `test_` when `RUNNING_TESTS` constant is defined
  - Production options (`84em_local_pages_claude_api_key_encrypted`, `84em_local_pages_claude_api_model`) never modified during tests
  - Tests read production API key for authentication but store test data separately
  - `TestCase::setUp()` automatically defines `RUNNING_TESTS` constant
  - All test tearDown() methods delete `test_` prefixed options for cleanup
- **TestConfig Updates**: Simplified test configuration
  - `TestConfig::getTestApiKey()` reads production API key directly (not through ApiKeyManager)
  - Removed environment variable support (always use database options)
  - Tests always use real WordPress options for configuration
- **Test Suite**: Complete refactor to use ApiKeyManager methods consistently
  - All tests now use `ApiKeyManager` methods (`getKey()`, `setKey()`, `deleteKey()`, `getModel()`, `setModel()`, `deleteModel()`) instead of calling `update_option()` and `delete_option()` directly
  - Removed all direct `get_option()`, `update_option()`, and `delete_option()` calls from test files
  - Tests properly respect `ApiKeyManager::getOptionName()` method which handles test prefix logic
  - All test setUp() methods now unconditionally set test data
  - Tests always execute (no skipping based on API key availability)
  - **All 82 tests now pass** (100% success rate with valid API key)

### Fixed
- **Production Safety**: Tests can no longer accidentally modify production data
  - Previously tests could delete production options during cleanup
  - Now all test operations use isolated `test_` prefixed options
  - Production WordPress installation remains completely unaffected by test runs
- **Model Validation**: Fixed model validation workflow in ClaudeApiClient
  - `validateModel()` now checks `hasKey()` instead of `isConfigured()`
  - Allows model validation when setting up a new model (before it's saved)
  - Fixes error "API client is not properly configured" during model setup
  - Model validation only requires API key, not full configuration
- **Test Data Isolation**: Fixed test state contamination issues
  - `test_send_request_invalid_config` and `test_send_request_error_scenarios` now properly delete keys before testing empty state
  - `test_is_configured` now properly cleans state before testing unconfigured client
  - `test_validate_credentials` creates fresh manager instance to avoid state pollution
  - `test_validate_model_without_api_key` now deletes both key and model for proper isolation
- **Test Path Bug**: Fixed TestCommand test file path construction
  - Added missing trailing slash to test directory path in `getTestDirectory()`
  - Prevented incorrect paths like `/tests/unittest-encryption.php`
  - Now correctly constructs paths like `/tests/unit/test-encryption.php`

## [3.2.4] - 2025-10-19

### Added
- **Dynamic Model Selection**: Model fetching from Claude Models API
  - New `getAvailableModels()` method in `ClaudeApiClient`
  - Fetches model list from `https://api.anthropic.com/v1/models`
  - Returns structured data with model ID, display name, created date, and type
  - Interactive numbered selection menu for choosing models
  - Model selection highlights current model if already configured

### Changed
- **Model Configuration**: Removed all hardcoded model defaults
  - Removed `DEFAULT_MODEL` constant from `ClaudeApiClient` and `ApiKeyManager`
  - `ApiKeyManager::getModel()` now returns `string|false` instead of defaulting to hardcoded model
  - Removed `ApiKeyManager::getDefaultModel()` method
  - Users must explicitly select a model before generating content
- **API Client Configuration**: Enhanced `isConfigured()` validation
  - Now requires both API key AND model to be configured
  - Returns `false` if either is missing
  - Prevents content generation attempts without proper configuration
- **Model Management Commands**: Updated all model-related WP-CLI commands
  - `--set-api-model`: Fetches models from API and presents interactive selection
  - `--get-api-model`: Shows current model or prompts to set one if not configured
  - `--validate-api-model`: Requires model to be configured before validation
  - `--reset-api-model`: Clears current model (no longer resets to "default")
- **Help Text**: Updated CLI help to reflect new model fetching behavior
  - Changed description from "Set/update Claude API model (interactive prompt)"
  - To "Set/update Claude API model (fetches list from API)"
  - Updated reset command description to "Clear current model configuration"

### Fixed
- **Model Validation**: Improved validation workflow
  - Better error messages when model not configured
  - Clear prompts directing users to `--set-api-model` command
  - Validation only proceeds if both API key and model are set

### Testing
- **Test Suite Updates**: Updated all model management tests for new behavior
  - Renamed `test_get_default_model` → `test_get_model_returns_false_when_not_set`
  - Renamed `test_delete_model_reverts_to_default` → `test_delete_model_returns_false`
  - Replaced `test_get_default_model_constant` with `test_has_key_integration`
  - Added `test_is_configured_requires_both_key_and_model`
  - Added `test_get_available_models_without_api_key`
  - Added `test_get_available_models_structure`
  - All 13 model management tests passing

### Documentation
- **CLAUDE.md**: Updated for v3.2.4
  - Documented new Model Selection Process
  - Removed references to default models
  - Added Models API endpoint documentation
  - Clarified that no default model exists
- **README.md**: Updated API Model Configuration section
  - Updated command descriptions and examples
  - Added "How It Works" section explaining dynamic model fetching
  - Removed hardcoded model list

## [3.2.3] - 2025-09-16

### Changed
- **API Client**: increased retry values and added 529 as an acceptable HTTP retry status

## [3.2.2] - 2025-08-18

### Changed
- **Content Generation Prompts**: Updated AI prompts for both state and city pages
  - Added instruction to mention that 84EM is headquartered in Cedar Rapids, Iowa
  - Removed requirement to use the specific phrase "remote-first"
  - Maintains emphasis on 100% fully remote operations
  - Helps establish company credibility with headquarters location

## [3.2.1] - 2025-08-18

### Added
- **Keyword Link Update Command**: New `--update-keyword-links` command to refresh service keyword links without API calls
  - Updates keyword links in all existing pages when `KeywordsProvider` URLs change
  - Strips existing keyword links and reprocesses with current URLs
  - Supports `--states-only` flag to update only state pages
  - Shows progress bar with detailed statistics (updated/skipped/errors)
  - Preserves all other content including city links and WordPress block structure
  - No Claude API key required - works entirely with existing content

### Changed
- **KeywordsProvider URLs**: Updated service keyword mappings to use more specific URLs (Commit ae1f988)
  - `custom plugin development` → `/services/custom-wordpress-plugin-development/`
  - `white-label development` → `/services/white-label-wordpress-development-for-agencies/`
  - Added new multi-word keywords with proper URL mappings
  - All keywords now point to appropriate service-specific pages

## [3.2.0] - 2025-08-17

### Added
- **CLI Argument Validation**: Comprehensive error checking for WP-CLI commands
  - Detects missing `--` prefixes (e.g., `state="California"` → suggests `--state="California"`)
  - Identifies unrecognized arguments with smart suggestions using Levenshtein distance
  - Validates mutually exclusive argument combinations
  - Checks for incomplete argument sets (e.g., `--city` requires `--state`)
  - Provides helpful error messages with correct usage examples
  - Prevents silent failures from typos or incorrect syntax

### Changed
- **Dependency Injection Refactoring**: Complete overhaul of DI implementation
  - Eliminated service locator anti-pattern throughout the codebase
  - All classes now use proper constructor injection
  - `GenerateCommand` no longer instantiates dependencies in constructor
  - `StateContentGenerator` and `CityContentGenerator` receive `ClaudeApiClient` via injection
  - `CommandHandler` receives command instances via constructor injection
  - `ClaudeApiClient` now registered as singleton service for better performance
  - Improved testability with proper dependency mocking support

- **Test Suite Optimization**: Removed 51 unnecessary tests (42% reduction)
  - Eliminated tests for PHP built-in functions
  - Removed tests for WordPress core functions
  - Deleted trivial validation tests
  - Removed test-container.php entirely (all tests were unnecessary)
  - Test suite now focuses exclusively on plugin functionality
  - Reduced from 120 tests to 69 focused tests across 9 suites

### Fixed
- **City Page Update Bug**: Fixed query in `CityContentGenerator` that could incorrectly identify parent pages
  - Added `NOT EXISTS` check for `_local_page_city` meta to properly identify state pages
  - Prevents city pages from being incorrectly selected as parent pages

### Developer Experience
- **Better Error Messages**: CLI now provides clear, actionable feedback for command errors
- **Improved Architecture**: Clean dependency injection patterns following SOLID principles
- **Enhanced Testability**: All classes can now be easily unit tested with mock dependencies
- **Reduced Coupling**: Components are loosely coupled through constructor injection

## [3.1.3] - 2025-08-16

### Added
- **Health Check Endpoint**: REST API endpoint for deployment verification
  - New endpoint at `/wp-json/84em-local-pages/v1/health`
  - Returns minimal `{"status": "ok"}` response for security
  - Used by GitHub Actions workflows to verify successful deployments
  - Implemented in new `HealthCheckEndpoint` class

### Fixed
- **Critical Bug**: Fixed missing SchemaGenerator parameter in content generator registration
  - StateContentGenerator and CityContentGenerator were missing SchemaGenerator in dependency injection
  - Could have caused fatal errors when generating content
  - Both generators now properly receive all 5 required dependencies

### Documentation
- **CLAUDE.md Updates**: Comprehensive documentation update
  - Updated plugin version references to 3.1.3
  - Corrected API timeout value from 60 to 600 seconds
  - Added MAX_RETRIES and INITIAL_RETRY_DELAY constants documentation
  - Added Health Check Endpoint section with REST API details
  - Added Testing Framework section with all 10 test suites
  - Added Recent Updates section highlighting v3.1.2 improvements
  - Updated architecture documentation to include HealthCheckEndpoint
  - Enhanced error handling documentation with retry logic details

- **README.md Updates**: Health check endpoint documentation
  - Simplified health check description to reflect minimal implementation
  - Updated response format to show only `{"status": "ok"}`
  - Clarified security-focused minimal response approach

## [3.1.2] - 2025-08-16

### Added
- **Robust Error Handling**: Enhanced ClaudeApiClient with comprehensive retry logic
  - Implemented exponential backoff with 3 retry attempts for transient failures
  - Added intelligent error classification (retryable vs non-retryable errors)
  - Added rate limiting support with Retry-After header handling
  - Implemented detailed error logging at multiple levels (Error, Warning, Info)

- **Test Framework Enhancement**: Extended TestCase with missing assertion method
  - Added `assertLessThanOrEqual()` method to fix test execution issues
  - Enabled full execution of all 12 API client tests

### Fixed
- **Test Runner Issues**: Resolved test suite execution stopping after 2 tests
  - Fixed missing TestCase assertion method causing silent test failures
  - All test suites now execute completely without interruption

- **Test Output Clarity**: Suppressed expected warning messages during test execution
  - Added test context detection to prevent confusion from intentional error testing
  - Test output now shows only pass/fail status without noise from error handling tests
  - Production warning behavior remains unchanged

### Changed
- **API Timeout Configuration**: Replaced WordPress constant with hardcoded value
  - Changed `TIMEOUT` from `10 * MINUTE_IN_SECONDS` to `600` for better portability
  - Ensures compatibility in environments where WordPress constants aren't loaded

- **Composer Configuration**: Removed version field from composer.json
  - Eliminates version conflicts between composer.json and plugin header
  - Enables proper deployment pipeline versioning

### Removed
- **Unused API Method**: Removed `getUsageStats()` method from ClaudeApiClient
  - Method was not used anywhere in the codebase
  - Simplifies API client interface

## [3.1.1] - 2025-08-16

### Fixed
- **GitHub Actions**: Fixed deprecated Composer flag causing deployment failures
  - Removed `--no-suggest` flag from composer install commands (deprecated in Composer 2.0)
  - Workflow now compatible with Composer v2 used in deployment environments

### Improved
- **Deployment Workflows**: Split deployment workflows for better environment separation
  - Created separate workflows for dev, staging, and production environments
  - Implemented reusable workflow pattern for DRY principles
  - Added comprehensive backup and rollback mechanisms
  - Enhanced validation and verification steps

## [3.1.0] - 2025-08-16

### Changed
- **Test Architecture Modernization**: Migrated test code from legacy procedural to object-oriented design
  - Refactored ld-json and cli-args tests to use proper namespacing and dependency injection
  - Updated test architecture to align with plugin's modular structure
  - Simplified data loading by removing external config file dependency
  - Re-enabled previously disabled test suites after fixing fatal errors

- **Major Test Suite Refactoring**: Comprehensive overhaul of all test suites to focus on actual plugin functionality
  - Rewrote content-processing tests to test actual ContentProcessor methods instead of mock implementations
  - Rewrote error-handling tests to test real error conditions in the plugin
  - Rewrote security tests to test actual security features (API key encryption, input sanitization)
  - Fixed data-structures tests by removing tests for non-existent helper methods
  - Removed database-operations test suite (tested static conventions, not actual functionality)
  - Removed basic-functions test suite (only tested WordPress core functions)
  - Removed simple test suite (duplicate of basic-functions)
  - Removed url-generation test suite (outdated and no longer relevant)

### Added
- **New Test Suites**: Implemented comprehensive test coverage for core functionality
  - Container tests for dependency injection (12 tests)
  - API client tests for Claude API integration (9 tests)
  - Content generators tests for state/city generation (12 tests)
  - Error handling tests for real error conditions (12 tests)
  - Security tests for encryption and sanitization (10 tests)

### Improved
- **Test Coverage Quality**: All remaining test suites now test actual plugin classes and methods
  - 10 focused test suites with 106 tests total
  - No more testing of imaginary features or WordPress core functions
  - Tests now validate real business logic used in production
  - Better alignment between tests and actual plugin architecture
  - Comprehensive schema generation tests with proper validation

### Fixed
- **Container Tests**: Fixed assertion methods and exception handling
- **API Client Tests**: Fixed mock ApiKeyManager implementation
- **Content Generator Tests**: Fixed method signatures to match actual implementation
- **CLI Args Tests**: Fixed to use GenerateCommand class with proper dependency injection
- **LD-JSON Tests**: Fixed to use SchemaGenerator class directly

## [3.0.6] - 2025-08-16

### Added
- **Index Page Generation**: Implemented `--generate-index` command functionality
  - Creates a master index page listing all 50 states alphabetically
  - Generates WordPress Development Services USA page with proper block editor syntax
  - Includes automated state links to respective local pages
  - Generates comprehensive LD-JSON schema for the index page
  - Supports both creation and updates of existing index page

### Fixed
- Completed missing `handleIndexGeneration` method in `GenerateCommand` class
- Added `buildIndexPageContent` helper method for content generation
- Fixed SchemaGenerator property injection in GenerateCommand constructor

## [3.0.5] - 2025-08-15

### Fixed
- **Critical**: Fixed --generate-all command updating wrong post IDs for state pages
  - State page queries now check that `_local_page_city` does NOT exist
  - Prevents city pages from being mistakenly updated as state pages
- **City Page Titles**: Fixed city pages not having their titles updated during updates
  - Added `post_title` to the update array in CityContentGenerator::updateCityPage()
  - City pages now properly show "WordPress Development Services in {City}, {State} | 84EM"
- **Schema Generation**: Fixed missing schema generation in StateContentGenerator
  - State pages now generate schema on creation (generateStatePage)
  - State pages now regenerate schema on update (updateStatePage)
  - Ensures consistency with city pages which were already generating schema

### Changed
- Standardized schema meta key to use 'schema' instead of '_local_page_schema'
- SEO meta fields changed from Yoast (_yoast_wpseo_*) to Genesis Framework (_genesis_*)
  - Changed _yoast_wpseo_metadesc to _genesis_description
  - Changed _yoast_wpseo_title to _genesis_title
  - Removed _yoast_wpseo_canonical field

## [3.0.4] - 2025-08-15

### Fixed
- **Critical**: Fixed PHP syntax errors in content generator prompt strings
  - Escaped quotes in grammar rule examples to prevent parse errors (exit code 255)
  - Fixed both StateContentGenerator.php and CityContentGenerator.php
  - All PHP files now pass syntax validation checks

## [3.0.3] - 2025-08-15

### Fixed
- **Content Generation**: State pages now properly mention ALL 6 cities instead of only 4
  - Updated prompt to explicitly require mentioning all cities in the list
  - Changed from "cities like" to "ALL of these cities" with explicit instruction
- **Grammar Issues**: Fixed grammatical errors in generated content
  - Added proper preposition usage (in, for) with location names
  - Prevented awkward constructions like "Hoover businesses seeking Hoover solutions"
  - Added explicit grammar rules to both state and city content prompts
  - Keywords now use proper format: "WordPress development in {city}" instead of "WordPress development {city}"

### Changed
- Improved content generation prompts for better grammatical accuracy
- Enhanced location keyword formatting for more natural reading

### Documentation
- Updated prompt templates to reflect new grammar rules and city mention requirements

## [3.0.2] - 2025-08-15

### Fixed
- **Critical**: Fixed GitHub Actions deployment workflow triggering on dev branch pushes
  - Changed trigger from `push` events to `pull_request` with `types: [closed]`
  - Added job condition to check if PR was actually merged (`github.event.pull_request.merged == true`)
  - Removed tag-based deployment triggers that were causing unintended deployments
  - Deployment now ONLY occurs when PRs are merged to main branch or via manual dispatch from main

### Security
- Improved deployment security by preventing accidental production deployments from non-main branches
- Ensured deployment workflow cannot be triggered by pushing tags on any branch

## [3.0.1] - 2025-08-15

### Added
- New `--complete` flag for `--city=all` command to generate all cities AND update state page in one operation
- Comprehensive tests for block structure handling to prevent regression
- Tests for keyword linking with proper case preservation

### Fixed
- **Critical**: Fixed invalid WordPress block structure that prevented editing in Block Editor
  - ContentProcessor now detects existing block markup to prevent double-wrapping
  - Eliminated nested paragraph blocks and malformed HTML structures
- Fixed state page query bug where state commands incorrectly updated city pages
  - Added proper meta_query checking for `_local_page_city` NOT EXISTS
- Fixed city name interlinking in state pages
  - City names now properly link to their respective city pages
  - Processing order changed to handle location links before service keywords
  - Removed hardcoded location-specific keywords that interfered with dynamic linking
- Fixed service keyword linking for multi-word keywords
  - Improved regex pattern for better word boundary detection
  - Keywords like "API integrations" and "security audits" now link correctly
  - Preserves original case from content when creating links
- Implemented missing `--regenerate-schema` command functionality
  - Command was introduced in v2.2.2 but never implemented
  - Now properly regenerates LD-JSON schema for all pages without touching content
  - Supports all documented filtering options (states-only, specific state, specific city)

### Changed
- KeywordsProvider now uses `/services/` URL instead of `/wordpress-development-services/`
- Updated test expectations to match new service URLs
- Improved ContentProcessor to handle existing block content intelligently

### Removed
- Removed unused `getCities` method from StatesProvider (using `get()` method instead)

## [3.0.0] - 2025-08-12

### Changed
- **BREAKING CHANGE**: Complete architectural overhaul from monolithic class to modular architecture
- Migrated from single 2,954-line class to 20+ focused classes following SOLID principles
- Implemented PSR-4 autoloading with proper PHP namespaces (`EightyFourEM\LocalPages\*`)
- Restructured codebase into logical modules: Api, Cli, Content, Data, Schema, Utils
- Introduced dependency injection container pattern
- All 30 tests rewritten to work with new architecture
- Removed legacy monolithic class entirely

### Added
- Modern PHP 8.2 features including typed properties and union types
- Contracts/interfaces for better abstraction and testability
- Container class for dependency injection
- Dedicated command classes for CLI operations
- Proper separation of concerns with single responsibility per class
- Comprehensive error handling and logging

### Fixed
- Claude API model updated to correct version (`claude-sonnet-4-20250514`)
- Max tokens setting corrected to 4000
- Constructor parameter dependencies properly resolved
- CLI command routing for state/city parameters
- API key management method compatibility

### Improved
- Code maintainability and readability
- Test isolation and reliability
- Memory efficiency with lazy loading
- Plugin initialization flow
- Overall code organization

## [2.4.2] - 2025-08-12

### Fixed
- Fixed heredoc syntax errors throughout deploy.yml workflow causing YAML validation failures
- Corrected Slack notification to use environment variable instead of invalid webhook_url parameter
- Fixed all notification configurations to use secrets instead of repository variables
- Added proper handling for optional health check URL with graceful skip if not configured
- Removed all unnecessary export statements from SSH commands

### Changed
- All sensitive configuration (webhooks, emails, URLs) now properly stored as GitHub secrets
- SSH commands now pass variables as positional parameters for better security
- Added continue-on-error for optional notification steps
- Health check now properly skips when URL not configured instead of failing
- SSH_PORT now has default fallback value of 22

### Improved
- Cleaner, more maintainable workflow with consistent variable handling
- Better error handling for optional features (notifications, health checks)
- All heredocs now use unique delimiter names to prevent conflicts

## [2.4.1] - 2025-08-12

### Changed
- Replaced custom security review implementation with official Anthropic Claude Code Security Review action
- Simplified security workflow from 600+ lines to ~75 lines
- Removed unnecessary configuration files and setup scripts
- Updated documentation to reflect simpler setup process

### Fixed
- Fixed PHP syntax error in test-url-generation.php (invalid array syntax)
- Fixed deployment workflow to properly check validation job success before proceeding
- Updated PHP syntax check to include test files (previously excluded)
- Added explicit validation result checks to all dependent jobs in deploy workflow
- Ensured deployment stops immediately if any PHP file has syntax errors

### Improved
- Better error handling in deployment workflow
- Job dependencies now properly enforce validation success
- Cleaner, more maintainable security review implementation

## [2.4.0] - 2025-08-12

### Added
- Automated security reviews using Claude AI for all pull requests
- Security review GitHub Actions workflow (`security-review.yml`)
- Configurable security review settings (`security-review-config.yml`)
- Support for multiple Claude models (Sonnet 4, Opus 4.1, etc.)
- Automated vulnerability detection for SQL injection, XSS, command injection, and more
- PR comment integration with detailed security findings
- Dependency security checks with composer and npm audit
- Static code analysis integration
- Setup script for easy API key configuration
- Security report artifacts saved for 30 days

### Changed
- Updated GitHub Actions workflows to use secure heredoc patterns for SSH commands
- Improved file handling with null-terminated input in PHP syntax checks
- Added file locking to prevent race conditions in backup cleanup
- Health check failures now trigger automatic rollback
- Replaced hardcoded values with environment variables throughout workflows
- Job dependencies now use explicit result checks instead of success()

### Fixed
- Fixed redundant PR check logic in deployment workflow
- Corrected PHP syntax check to properly detect and report errors
- Added proper escaping for SSH commands with secrets
- Fixed confusing job dependency conditions
- Secured legacy deploy.sh script by ensuring it's gitignored

### Security
- All SSH commands now use single-quoted heredocs to prevent variable expansion
- Environment variables properly exported before SSH execution
- Added flock mechanism for concurrent backup operations
- Removed SSH port fallback to prevent information disclosure

## [2.3.2] - 2025-08-12

### Security
- Added explicit blocking of deployments from pull requests in GitHub Actions workflow
- Enhanced deployment safety checks to prevent premature production deployments
- Added PR context verification even for main branch pushes
- Added debug output for blocked deployment attempts

### Fixed
- Deployment workflow now properly blocks all PR-related events
- Added multiple layers of safety checks to prevent accidental deployments before merge

### Changed
- Deployment decision logic now explicitly checks for pull_request events
- Added clearer error messages when deployment is blocked

## [2.3.1] - 2025-08-12

### Fixed
- LD-JSON schema URLs now correctly use actual page permalinks instead of hardcoded URL structure
- Schema generation functions now accept optional post_id parameter to retrieve real permalinks
- City page lookup improved to use meta_query for more reliable results
- Schema URLs now properly match the page URLs for both state and city pages

### Changed
- Updated `generate_ld_json_schema()` and `generate_city_ld_json_schema()` functions to use `get_permalink()` when post_id is available
- All schema generation calls now pass post_id parameter where available
- Improved city page search logic using meta_query instead of title search

## [2.3.0] - 2025-08-07

### Added
- GitHub Actions deployment workflow with comprehensive security features
- All sensitive deployment data (host, port, paths) stored as GitHub secrets
- Enhanced security scanning for dangerous PHP functions and credential patterns
- Automatic backup and rollback capabilities on deployment failure
- Optional deployment parameters (skip_backup, force_deploy, environment selection)
- Deployment hash verification for integrity checking
- Health check endpoint testing after deployment
- Multiple notification channels (Slack and email)
- Deployment info file with commit hash and metadata
- Support for custom SSH ports via secrets
- Configurable backup retention (keeps last 10 backups)

### Changed
- Migrated from rsync bash script (deploy.sh) to GitHub Actions workflow
- Deployment paths now stored as secrets instead of hardcoded values
- Enhanced pre-deployment validation with more comprehensive checks
- Improved error handling and deployment status reporting

### Security
- SSH private key stored encrypted in GitHub secrets
- All server credentials and paths moved to secure storage
- Enhanced security scanning for eval(), exec(), shell_exec() and other dangerous functions
- Credential pattern detection in code before deployment
- File permission validation to prevent world-writable PHP files

### Removed
- deploy.sh bash script (replaced by GitHub Actions)
- Hardcoded deployment paths from workflow files

## [2.2.3] - 2025-08-04

### Fixed
- Removed invalid `position` property from Offer type in LD-JSON schemas
- Position property is only valid for ListItem types (BreadcrumbList, ItemList), not Offer
- OfferCatalog schema now fully compliant with schema.org specifications

## [2.2.2] - 2025-08-04

### Added
- New `--regenerate-schema` WP-CLI command to fix schema issues without regenerating content
- Schema regeneration supports all pages, states-only, specific states, and specific cities
- Progress tracking for bulk schema regeneration operations
- Documentation for schema regeneration in README.md and TESTING.md

### Fixed
- Removed invalid `addressRegion` property from City type in LD-JSON schemas
- City types in schemas now properly use `containedInPlace` instead of `addressRegion`
- Cleaned up schema structure to use only valid schema.org properties
- LD-JSON validation errors reported by SEO tools like Ahrefs

### Changed
- Schema regeneration doesn't require Claude API key since it only updates metadata
- Updated plugin version to 2.2.2

## [2.2.1] - 2025-08-01

### Fixed
- GitHub Actions workflow simplified to basic syntax checks
- Resolved '84em is not a registered wp command' error in CI
- CI environment issues with WP-CLI command registration

### Changed
- GitHub Actions now only performs PHP syntax and composer.json validation
- Full test suite must be run locally due to CI limitations
- Updated TESTING.md to document CI restrictions

## [2.2.0] - 2025-08-01

### Added
- Comprehensive WP-CLI-based testing framework
- Custom TestCase class for WP-CLI testing without external dependencies
- Test command as subcommand: `wp 84em local-pages --test`
- 30 unit tests across 5 test suites:
  - encryption: API key encryption/decryption tests
  - data-structures: Service keywords and US states validation
  - content-processing: Content processing and title case tests
  - simple: Basic functionality tests
  - basic: WordPress environment tests
- TESTING.md documentation for testing procedures
- Composer configuration for WP-CLI testing dependencies

### Changed
- Test command structure to be subcommand of local-pages to avoid conflicts
- Updated README.md with testing section

### Fixed
- Fatal errors when running tests in WordPress environment
- Test compatibility with actual WordPress site URLs instead of example.com

## [2.1.1] - 2025-08-01

### Fixed
- PHP TypeError when generating LD-JSON schema due to associative service keywords array
- array_map position calculation now uses numeric indices instead of string keys
- Service keywords list generation now uses array_keys() to extract keyword names

## [2.1.0] - 2025-08-01

### Added
- Smart service keyword linking - keywords now link to contextually relevant pages
- Dynamic URL mapping for service keywords (work, services, projects, local pages)
- Special case handling for "84EM" as all caps in title case function

### Changed
- State page prompt updated with "30 years experience" and "diverse client industries"
- Service keywords structure from simple array to associative array with URL mappings
- Title case function now properly handles "84EM" as uppercase

## [2.0.1] - 2025-08-01

### Added
- `process_headings()` function to clean up heading formatting
- `convert_to_title_case()` function following standard title case rules
- Smart title case conversion (keeps articles/prepositions lowercase)

### Fixed
- H2 and H3 headings now properly formatted with title case
- Removed hyperlinks from within H2 and H3 headings

### Changed
- Content processing pipeline now includes heading cleanup step before interlinking
- Regex-based heading detection for WordPress block format
- Maintains WordPress block structure and `<strong>` tags in headings

## [2.0.0] - 2025-07-31

### Added
- Complete city page generation system (300 city pages, 6 per state)
- Hierarchical post type support with parent-child relationships (states → cities)
- Automatic interlinking system for city names and service keywords
- Clean hierarchical URL structure (`/wordpress-development-services-state/city/`)
- Separate Claude AI prompts for state pages (300-400 words) and city pages (250-350 words)
- Bulk operations: `--generate-all` and `--update-all` commands
- City-specific WP-CLI commands with `--city` parameter
- Comprehensive progress tracking with detailed statistics
- Real-time feedback during bulk operations
- Next steps guidance after command completion
- Enhanced error handling for hierarchical operations
- Validation for parent-child relationships
- Custom fields for city pages (`_local_page_city`)
- Hierarchical rewrite rules for clean city URLs
- Enhanced SEO with separate LD-JSON schemas for states and cities
- Progress bars for bulk operations with ETA tracking
- Hierarchical processing order (states first, then cities)
- Parent page validation for city creation
- Automatic link processing functions with collision avoidance

### Changed
- Total page capacity increased from 50 to 350 pages
- API cost estimates updated to reflect new scale ($14-28 for full generation)
- Post type now supports hierarchical structure (`'hierarchical' => true`)
- Index page generation now filters for state pages only
- Enhanced sitemap generation to include all city pages

## [1.0.0] - 2025-07-30

### Added
- WordPress plugin for generating SEO-optimized local pages
- Claude AI integration using Sonnet 4 model for content generation
- Custom "local" post type for WordPress development service pages
- WP-CLI integration with comprehensive command structure
- Support for all 50 US states with 6 largest cities per state
- State-specific landing pages (300-400 words each)
- WordPress Block Editor (Gutenberg) format support
- Automated CTA placement before H2 headings
- Clean URL structure without post type slug
- SEO optimization with titles, meta descriptions, and LD-JSON schema
- XML sitemap generation for all local pages
- Master index page with alphabetized state directory
- AES-256-CBC encryption for API key storage with WordPress salts
- Rate limiting with 1-second delays between API requests
- Progress tracking with real-time duration monitoring
- Comprehensive error handling and logging
- Professional, factual tone without industry specialization
- Geographic relevance through city mentions and remote-first messaging
