# Local Pages Plugin Update - Implementation Guide

## Overview

This update improves content generation for your local SEO pages with:

1. **Randomized testimonials** - Different testimonials appear on different pages (deterministic, so regenerating produces the same result)
2. **Location-aware content** - Industry and business context fed into prompts for more relevant content
3. **Improved prompts** - Better variation, banned phrase detection, more substantive output
4. **Curated testimonial selection** - Excluded "game changer" and other phrases that conflict with your voice

---

## Files to Update

### New Files (add these)

```
src/
├── Data/
│   ├── TestimonialProvider.php      # Handles testimonial selection/randomization
│   └── LocationContextProvider.php  # Provides state/city context data
└── Config/
    └── TestimonialBlockIds.php      # Maps testimonial keys to WordPress block IDs
```

### Updated Files (replace these)

```
src/Content/
├── StateContentGenerator.php        # Updated with new providers and prompts
└── CityContentGenerator.php         # Updated with new providers and prompts
```

---

## Setup Steps

### 1. Get Testimonial Block IDs

Your testimonial patterns need to be synced patterns in WordPress. For each one, find the block ID:

1. Go to **WordPress Admin → Appearance → Patterns** (or **Editor → Patterns**)
2. Find each testimonial pattern
3. Click to edit - the URL will show: `/wp-admin/post.php?post=XXX&action=edit`
4. The `XXX` is the block ID

### 2. Update TestimonialBlockIds.php

Open `src/Config/TestimonialBlockIds.php` and fill in the block IDs:

```php
public const IDS = [
    'cq-concepts-2'        => 12345, // Replace with actual ID
    'red-lab'              => 12346, // Replace with actual ID
    'followbright'         => 12347, // Replace with actual ID
    'pinnacle-short'       => 12348, // Replace with actual ID
    'panacea'              => 12349, // Replace with actual ID
    'mike-hedding-short'   => 12350, // Replace with actual ID
    'equilibria'           => 12351, // Replace with actual ID
    'red-lab-2'            => 12352, // Replace with actual ID
    'followbright-2-short' => 12353, // Replace with actual ID
];
```

### 3. Update Your Container/DI

If you're using dependency injection, register the new providers:

```php
// In your service container setup
$locationContext = new LocationContextProvider();
$testimonialProvider = new TestimonialProvider(TestimonialBlockIds::getAll());

$stateGenerator = new StateContentGenerator(
    $apiKeyManager,
    $apiClient,
    $statesProvider,
    $schemaGenerator,
    $contentProcessor,
    $metadataGenerator,
    $locationContext,      // New
    $testimonialProvider   // New
);
```

---

## Testimonials Included

These testimonials were selected for local pages (short, punchy, universal appeal):

| Key | Quote | Source |
|-----|-------|--------|
| `cq-concepts-2` | "super easy to work with—always quick to respond..." | CQ Concepts |
| `red-lab` | "detailed, builds for extendability, delivers on time" | Red Lab |
| `followbright` | "breath of fresh air in a world of unreliable vendors" | Followbright |
| `pinnacle-short` | "The difference a highly competent developer makes..." | Pinnacle Group |
| `panacea` | "very lucky to have him on their team" | Panacea Financial |
| `mike-hedding-short` | "great technical knowledge and communication skills" | Mike Hedding |
| `equilibria` | "meaningfully contributed to our extreme growth" | Equilibria |
| `red-lab-2` | "Deadlines were always met..." | Red Lab |
| `followbright-2-short` | "maintaining the website without issues for years" | Followbright |

**Excluded:** CQ Concepts original ("game changer" phrase conflicts with your voice)

---

## Testimonial Distribution

The system uses deterministic randomization based on location name:

- **State pages** use a pool of 5 testimonials (broader appeal)
- **City pages** use a pool of 9 testimonials (more variety)
- Same location always gets same testimonial (won't change on regeneration)
- Different locations get different testimonials

To preview distribution across states:

```php
$provider = new TestimonialProvider(TestimonialBlockIds::getAll());
$states = ['Alabama', 'Alaska', 'Arizona', ...]; // all 50
$distribution = $provider->previewDistribution($states);
print_r($distribution);
```

---

## Location Context Data

### States

All 50 states have context data including:
- Key industries
- Business landscape description
- Target audience angle
- Home state flag (Iowa = true)

### Cities

Major cities have specific context. Cities without specific data fall back to state context.

**Cities with specific data:**
- All 10 Iowa cities (your home state)
- Top metros: NYC, LA, SF, Chicago, Houston, Austin, Dallas, Miami, Atlanta, Boston, Seattle, Denver, Phoenix, etc.

### Adding More City Context

Edit `LocationContextProvider.php` and add to `CITY_CONTEXT`:

```php
'Omaha, Nebraska' => [
    'industries' => ['insurance', 'finance', 'agriculture', 'tech'],
    'context'    => 'Home to Berkshire Hathaway and major insurance companies',
],
```

---

## Banned Phrases

The generators now detect and warn about repetitive phrases:

- "Your WordPress site needs to work"
- "can't afford downtime"
- "straightforward support"
- "actually fixes things"
- "handle WordPress"
- "stop worrying about"
- "doing the heavy lifting"
- Marketing superlatives (game-changing, cutting-edge, etc.)

Warnings appear in WP-CLI output during generation.

---

## Prompt Changes Summary

### State Pages - Before
```
Write a short landing page...
1. Intro (2-3 sentences): Why businesses in {state} need reliable WordPress help
```

### State Pages - After
```
Write a landing page for 84EM's WordPress services targeting {state} businesses.

ABOUT 84EM:
- 30 years programming experience, 13 years in WordPress
- Based in Cedar Rapids, Iowa (remote)
- Serves agencies and direct clients
- Industries: fintech, healthcare, education, non-profits

STATE CONTEXT:
- Key industries: {from LocationContextProvider}
- Business landscape: {context}
- Target audience: {business_angle}

STRUCTURE:
1. Opening hook: Specific observation about {state} business landscape
2. Value proposition: Why remote specialist makes sense (mention 30 years)
3. Who we work with: Industries relevant to {state}
4. City links in prose
5. Services block
6. Testimonial block  <-- NEW
7. CTA block
```

Key improvements:
- More context for better content
- Explicit banned phrases
- Testimonial included
- Longer word count target (150-200 vs ~50)
- Varied opening approaches

---

## Testing

1. **Test single state:**
   ```bash
   wp local-pages generate state --state="Colorado" --dry-run
   ```

2. **Check testimonial distribution:**
   ```bash
   wp eval '
   use EightyFourEM\LocalPages\Data\TestimonialProvider;
   $p = new TestimonialProvider([]);
   foreach (["Iowa", "California", "Texas", "New York", "Florida"] as $s) {
       $t = $p->getForState($s);
       echo "$s: {$t["key"]}\n";
   }
   '
   ```

3. **Regenerate a few pages and compare** to verify:
   - Content varies more between locations
   - Testimonials are different
   - Banned phrases don't appear
   - Industry context is reflected

---

## Questions?

The location context data is pre-generated and static - no runtime API calls needed. If you want to expand the city data, you can either:

1. Manually add entries to `LocationContextProvider::CITY_CONTEXT`
2. Have me generate a batch of city context for specific states
3. Add a one-time generation step that uses Claude to create context for all cities

The deterministic randomization means testimonials won't shift around if you regenerate - useful for consistency.
