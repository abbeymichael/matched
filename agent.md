
# AGENTS.md — "MatchLock" (Preference-First Dating App for Ghanaian Youth)

This document is a **complete build specification** for any AI coding agent (Claude Code, Cursor, Windsurf, etc.) working on this repo. It defines the product concept, data model, matching logic, security posture, testing strategy, and implementation plan for an MVP.

**Read this entire document before writing any code.** Sections reference each other extensively — the data model (§5) depends on the field library (§2), scoring (§3) depends on field types (§2.3), moderation (§12) shapes the data model, and Ghana-specific constraints (§13) affect auth, deployment, and compliance. Skipping ahead will produce code that has to be rewritten.

**Target audience**: young adults (18+) in Ghana looking to form meaningful, serious relationships — not a casual hookup app. This shapes several decisions below: auth method (phone+OTP, §8), data-consciousness (image compression, low-bandwidth mode, §13), tag vocabulary (culturally relevant options, §13), verification (selfie check, §13), and age gating (hard legal requirement, §13). See §13 for the full list of Ghana-specific adaptations.

**Conventions used in this document**: "viewer" means the user whose match list is being computed; "candidate" or "target" means a potential match being scored against the viewer's preferences. "Active field" means a `field_definitions` row with `is_active = true`. "Lock" or "locked" means `profile_locked = true` on the user — their profile and preferences are immutable.

---

## 0. Guiding Principles

These principles resolve ambiguity when a section doesn't cover a specific edge case:

**Intentionality over engagement.** Every design decision should favor deliberate, one-time choices over feed-scrolling habits. If a feature would encourage compulsive checking or endless browsing, it is wrong for this product.

**Safety is not a feature — it is infrastructure.** Moderation, reporting, age gating, and content checks (§12, §13) ship before public launch, not as "nice-to-have" follow-ups. The build order (§10) reflects this.

**Configuration over code changes.** Field activation, weights, hard-filter flags, match thresholds, and option lists are all admin-configurable at runtime. Deploying new code should never be required to change what questions users answer or how matches are scored.

**API-first, not API-later.** Every piece of business logic lives in `app/Actions` or `app/Services`, consumed by both Livewire components (web) and API controllers (mobile). The two interfaces must never diverge in behavior. If a bug fix only touches one consumer, the logic has leaked out of the shared layer and must be moved back.

**Shared-library, per-market config.** The field and option library is global/market-agnostic. Which fields are active, which options are shown, and how they're weighted is a per-deployment admin decision — never hardcoded.

---

## 1. Concept

MatchLock is not a swipe app. There is no endless feed. Instead:

1. An admin configures which profile fields are active from a shared **field library** (see §2) — a curated subset, not the whole library at once. This gives a guided, not overwhelming, onboarding wizard.
2. Every user fills out their profile (who they are) across the active fields, in a guided step-by-step wizard — one step per active field.
3. Every user then sets **preferences** (who they want) across the same active fields.
4. Once submitted, the profile + preferences are **LOCKED**. No editing (see §7 for the one exception: a limited "reset").
5. The system continuously computes a **weighted match score** between every pair of locked users, using each active field's admin-configured weight (or hard-filter flag).
6. If User A meets User B's preferences above a threshold, **A appears on B's Matches list with a visible score** (and vice versa — matching is symmetric in structure, but asymmetric in results: appearing on someone's list doesn't mean you appear on theirs unless you *also* meet their preferences).
7. B can tap **"Interested"** on A.
8. If A has *also* independently tapped "Interested" on B, it becomes a **Mutual Match** → a private contact/chat channel unlocks.
9. Interest is one-directional and invisible to the other person until it's mutual — no rejection notifications, no awkwardness, no "someone liked you" teasers behind a paywall.

The core hook: you don't build a shopping-feed habit, you make one honest, permanent decision about who you are and what you want, and the system does the filtering for you.

---

## 2. Field Library & Admin Configuration

There is no hardcoded field list in the schema. Instead, there's a **field library** — a superset of possible profile fields, grouped by category — and an admin picks which ones are active for a given launch/market. This mirrors how reference dating-platform builders (e.g. Luvora) do it: a `selectField()`-style admin picker, not a fixed onboarding form baked into the codebase.

### 2.1 Core fields (always active, not admin-togglable)
A small set of fields is structurally required regardless of admin config — auth, hard filtering, and distance calc all depend on them. These live directly on the `profiles` and `preferences` tables (§5), not in the EAV `profile_field_values`/`preference_field_values` tables:

| Field | Type | Matching role | Validation |
|---|---|---|---|
| Display name | text | not matched | Required, 2–50 chars, no URLs/emails (prevents contact-sharing before match) |
| Date of birth / age | date→number | weighted (range decay) | Required, must be 18+ at signup (hard block, not just a warning — see §13) |
| Gender identity | single_select | **hard filter** | Required, from `profile_field_options` where `field_key = 'gender'` |
| Location (city + lat/lng) | geo | weighted (distance decay) | Required; city is user-selected from a predefined Ghana cities list for MVP; lat/lng auto-populated from city selection, not raw GPS (privacy, and many users won't grant location permission) |
| Profile photo | image | not scored | Required (at least 1); max 4; server-compressed on upload (§13); used for selfie verification (§13) |

**Implementation note on location**: For MVP, pre-seed a `cities` table with Ghana's major cities and their lat/lng coordinates (Accra, Kumasi, Tamale, Takoradi, Cape Coast, Sunyani, Ho, Koforidua, Wa, Bolgatanga, and ~20 more). The user picks a city from a searchable dropdown; the app stores the city name and its canonical lat/lng. This is simpler and more privacy-respecting than asking for GPS permission, and works for a country-specific launch. The `geo` field-type scoring (§3.2) still uses lat/lng for distance calculation.

### 2.2 Configurable field library (admin activates a subset)
Everything else lives in the library below. This is backed by real seeders (`database/seeders/*OptionsSeeder.php`) rather than a sketch — categories match those seeders exactly, and every field is **inactive by default** until an admin turns it on for this deployment:

| Category | Fields |
|---|---|
| Physical Attribute | Body Type, Ethnicity, Hair Color, Eye Color |
| Relationship | Relationship Goal, Relationship Status |
| Career/Education | Education Level, Industry, Income Range, Work Schedule |
| Lifestyle | Living Situation, Exercise Frequency, Diet, Smoking, Drinking, Cannabis Use, Pets, Travel Frequency |
| Personality/Values | Personality Type (MBTI), Love Language, Politics, Religion, Conflict Style, Core Values |
| Interests | Interests & Hobbies, Music Genres, Media/TV Genres |
| Communication/Matching | Communication Style, Dealbreakers, Must-Haves |

For the initial Ghana-youth launch (see §13), a sensible starting subset would be: Relationship Goal, Religion, Core Values, Love Language, Exercise Frequency, Smoking, Drinking, Interests & Hobbies, Dealbreakers — but this is an **admin decision made in the field-library UI**, not a hardcoded list in code. Treat that as a seed/starting configuration, not a constraint the code enforces.

### 2.3 Field types and how each is matched
Every field (core or library) has a `fieldType` that determines both its input UI and its scoring behavior (see §3):

| Type | Example fields | Profile input UI | Profile value (stored) | Preference input UI | Preference value (stored) | Default scoring |
|---|---|---|---|---|---|---|
| `single_select` | Relationship Goal, Religion, Smoking | Radio buttons or single-choice dropdown | One string value | Checkbox group ("which do you accept?") | Array of accepted string values | 1.0 if profile value ∈ accepted set, else 0.0 (or hard filter if flagged) |
| `multi_select` | Core Values, Interests & Hobbies, Music Genres, Dealbreakers, Must-Haves | Checkbox group or tag picker (capped, e.g. top 3–5) | Array of string values | Checkbox group ("which matter to you?") or empty = no preference | Array of desired string values (or `[]` = no preference) | Overlap ratio: `|intersection| / |viewer.desired|`; if viewer's set is empty, contributes 1.0 |
| `scale` | Exercise Frequency, Drinking | Labeled slider or stepped radio (e.g. "Never / Rarely / Sometimes / Often / Daily") | Integer ordinal position (0-based) | Range slider with two handles ("min acceptable" to "max acceptable") | `{min: int, max: int}` ordinal range | 1.0 within range, linear decay per ordinal step outside |
| `range`/`number` | Age, Income Range | Number input or slider | Integer value | Min–max number inputs | `{min: int, max: int}` | 1.0 within range, linear decay outside with configurable buffer |
| `geo` | Location | City picker (see §2.1 note) | `{lat: float, lng: float}` | Distance slider in km | Integer (max distance in km) | 1.0 within radius, linear decay to 0.0 at 1.5× radius |
| `text` | Display Name | Text input | String | — (not shown in preferences wizard) | — | Not scored; display-only |

### 2.4 Per-field admin controls
For each library field the admin activates, they set:

- **Active** (on/off) — determines whether it appears in onboarding at all. Deactivating a field hides it from the wizard for new users but does NOT delete existing `profile_field_values` / `preference_field_values` rows for that `field_key` — those rows are simply excluded from scoring. This prevents data loss if an admin temporarily deactivates a field.
- **Hard filter vs. weighted** — a hard filter excludes non-matching candidates entirely, like gender; a weighted field contributes to the score. A field cannot be both.
- **Weight** (decimal, default 1.0) — only relevant if weighted, not hard filter. Normalized against other active weighted fields at score time (see §3), so an admin can use any positive number as a relative importance signal.
- **Sort order** (integer) — controls onboarding step sequence. Lower numbers appear first.
- **Required vs. optional** (boolean, default true) — whether the user must answer this field to proceed in the wizard. If optional and left blank, the profile value is `null` and the preference value is `[]` (no preference), both of which contribute 1.0 to scoring (benefit of the doubt).

This keeps the *code* generic (one dynamic wizard renderer, one generic scoring function per `fieldType`) while the *product* stays configurable without a redeploy.

### 2.5 Real field-key vocabulary (from `database/seeders/`)
The option seeders already exist and define the real `field_key` values and their selectable options — treat these as the source of truth over any earlier draft naming in this doc:

`body_type`, `ethnicity`, `hair_color`, `eye_color`, `relationship_goal`, `relationship_status`, `education_level`, `industry`, `income_range`, `work_schedule`, `living_situation`, `exercise_frequency`, `diet`, `smoking`, `drinking`, `cannabis_use`, `pets`, `travel_frequency`, `personality_type`, `love_language`, `politics`, `religion`, `conflict_style`, `core_values`, `interests`, `music_genres`, `media_genres`, `communication_style`, `dealbreakers`, `must_haves`.

**Additionally, `gender` is a core field (§2.1) that also has its options in `profile_field_options`** — seed it with at minimum: `male`, `female`, `non_binary`, `prefer_not_to_say`. The gender field is always active and always a hard filter; it does not appear in `field_definitions` as a togglable row.

**Resolved decision** (was an open question in an earlier draft): `dealbreakers` and `must_haves` are `multi_select` fields with a real option list, not free text — so they're scored like any other `multi_select` field (§3.2), not just displayed post-match. If a genuinely free-text personal dealbreaker note is still wanted alongside this structured list, that would be a *new*, separate `text` field, not a replacement for these.

**Two seeder edits made vs. the uploaded versions**, both for the same reason — the option *library* is shared across markets even though launch is Ghana-first, so it shouldn't hardcode one market's assumptions:

- `income_range`: was £-denominated UK bands, now currency-agnostic ordinal tiers (`tier_1`...`tier_6`). If a market ever wants to *display* real numeric brackets, do that with a per-market formatter reading the ordinal value — don't put a currency symbol back into the shared option label.
- `ethnicity`: was UK-centric (`white_british` etc.), broadened to a global set. Whether to activate this field at all, and how granular to make it, stays a deliberate per-deployment decision (§14) — this only fixes the *library* being one market's default.

---

## 3. Match Score Algorithm (weighted, generic over active fields)

Weights are no longer hardcoded per named field — they live on each `FieldDefinition` row, set by the admin (§2.4). At score time:

```
activeWeightedFields = FieldDefinition.where(isActive: true, isHardFilter: false)
normalizedWeight(field) = field.weight / sum(f.weight for f in activeWeightedFields)
```

This means admin weight inputs don't need to sum to exactly 1.0 — they're normalized automatically, so an admin can just say "Relationship Goal matters more, give it a 3" without doing the math themselves.

**Edge case**: if there are zero active weighted fields (all active fields are hard filters or none are active), the score is trivially 100% for anyone who passes all hard filters. This shouldn't happen in practice but the scoring function must not divide by zero — handle it explicitly.

### 3.1 Hard filters
Any field marked `isHardFilter = true` (gender is always one; admin can mark others, e.g. Relationship Goal) acts as a **gate**: if the candidate's profile value isn't in the viewer's accepted set/range for that field, the candidate is excluded entirely — never appears on the list, regardless of score. Hard filters are checked **before** scoring runs, not blended into the percentage.

**Hard-filter logic by field type:**
- `single_select`: candidate excluded if their value ∉ viewer's accepted set.
- `multi_select`: candidate excluded if `|intersection| == 0` (no overlap at all).
- `scale` / `range` / `number`: candidate excluded if their value is outside viewer's accepted range (no buffer — hard filter means hard cutoff).
- `geo`: candidate excluded if distance > viewer's max distance (no 1.5× buffer — that's only for weighted decay).

**Missing preference data on a hard-filter field**: if the viewer has no preference set (empty accepted set / null) for a hard-filter field, treat it as "accept all" — don't accidentally exclude everyone. If the *candidate* has no profile value for a hard-filter field, they ARE excluded (incomplete profile on a mandatory filter = fail).

### 3.2 Per-field-type scoring (0.0–1.0, before weighting)
Generic functions keyed by `fieldType` (§2.3), not by field name — this is what makes the scoring engine reusable across whatever fields the admin activates:

- **`single_select`**: 1.0 if candidate's value ∈ viewer's accepted set, else 0.0.
- **`multi_select`**: `|candidate.values ∩ viewer.desiredValues| / |viewer.desiredValues|` (capped at 1.0); if viewer's desired set is empty ("no preference"), contributes 1.0 automatically. If candidate's value set is empty (they skipped an optional field), score is 0.0 unless viewer also has no preference (then 1.0).
- **`scale`**: 1.0 if candidate's ordinal is within viewer's accepted range `[min, max]`, else decays linearly: `score = max(0, 1 - abs(distance_to_nearest_bound) / total_scale_length)`. Example: scale has 5 positions (0–4), viewer accepts [1,3], candidate is at 0 → distance is 1, score = 1 - 1/4 = 0.75.
- **`range`/`number`** (e.g. age): 1.0 within range, linear decay to 0.0 over a configurable tolerance buffer (default: 20% of range width, minimum 2 units) outside the range. Example: viewer wants age 22–28 (width 6), buffer = max(6×0.2, 2) = 2, so age 30 scores 1 - (30-28)/2 = 0.0, age 29 scores 0.5.
- **`geo`** (location): calculate distance using the **Haversine formula** (`Services/GeoService.php`). Score = 1.0 if distance ≤ viewer's `max_distance_km`; linear decay from 1.0 to 0.0 between `max_distance_km` and `1.5 × max_distance_km`; 0.0 beyond 1.5×. Example: max distance 50km, candidate is 60km away → score = 1 - (60-50)/(75-50) = 0.6.
- **`text`**: not scored (returns null, excluded from weighting).

### 3.3 Final score

```
score(viewer→candidate) = Σ over active weighted fields (
    normalizedWeight(field) × fieldScore(field, viewer.preferences, candidate.profile)
) × 100   // produces 0–100 integer
```

Computed **twice per pair** — `score(A→B)` and `score(B→A)` — since A's preferences and B's preferences are different. A only appears on B's match list if:
1. A passes ALL of B's hard filters, AND
2. `score(B→A)` ≥ B's `match_threshold` (default 60)

Each user's match list shows their own score of the other person. The scores are **not** shown to the other party (A sees their score of B; B sees their score of A; neither sees the other's number).

### 3.4 Score storage and display
Scores are stored as integers 0–100 in `match_scores`. Displayed to the user as a percentage (e.g. "87% match"). Do not display scores below the threshold — users only see candidates who meet their threshold, and every visible candidate has a visible score.

---

## 4. Core User Flow

```
Sign up (phone + OTP verification, age gate enforced)
  → Upload profile photo(s) (1 required, up to 4)
  → Selfie verification (photo captured → queued for manual review, see §13)
  → Onboarding wizard: Profile step 1 of N ... step N of N
      (N = count of admin's active fields + core fields)
      (one field per screen, ordered by sort_order)
      (progress bar shown: "Step 3 of 12")
  → Preferences wizard: Preference step 1 of N ... step N of N
      (reuses same active fields, but in "what do you accept?" mode)
      (each step shows the field label + preference input variant from §2.3)
  → Review screen: summary of all profile answers and preference settings
      with clear warning: "Once you submit, your profile and preferences are
      locked. You cannot edit them afterward (you get one lifetime reset — see below)."
  → Confirm & Lock: user taps "Lock In" → profile_locked = true, locked_at = now()
  → Match computation dispatched (queued job, §6)
  → Redirect to Matches tab (may show "Computing your matches..." briefly)
  → Matches tab: list of people who meet MY preferences above MY threshold,
      each showing: photo, display name, age, city, match score %
      sorted by score descending
  → Tap a card → Profile detail view (shows their profile answers for active fields)
      + "I'm Interested" button
      + "Report" button
  → Tap "I'm Interested" → confirmation recorded, button changes to "Interest Sent ✓"
      (no indication to the other person that interest was expressed)
  → If they've also independently marked interest in me:
      → Both see a "It's a Match!" screen
      → MutualMatch row created → Chat thread unlocked
      → Both can now exchange messages
```

**What the user does NOT see**: other people's scores of them, who has expressed interest in them (until mutual), a count of "people who liked you", any feed or discovery mechanism beyond the computed match list.

---

## 5. Data Model (Laravel migrations / Eloquent)

Use UUID primary keys (`HasUuids` trait) on all user-facing tables so IDs aren't sequentially guessable. Store enum-like values as strings with app-layer enum classes (`app/Enums/...`), not DB-native enums, so field options stay admin-configurable (§2) without a migration.

**Index strategy**: add database indexes on every foreign key column and every column used in `WHERE`/`ORDER BY` clauses in the queries described in this doc. Specifically: `match_scores(viewer_id, score)` composite index for the match list query; `interests(from_id, to_id)` for mutual-match detection; `reports(reported_id, status)` for moderation queue; `profile_field_values(user_id, field_key)` and `preference_field_values(user_id, field_key)` for scoring lookups.

```
users
  id (uuid, pk)
  phone (string, unique)           // primary identifier, E.164 format (e.g. +233XXXXXXXXX)
  phone_verified_at (timestamp, nullable)
  email (string, unique, nullable) // optional, for account recovery only
  password (string, nullable)      // unused if phone+OTP is the only auth path (§8)
  profile_locked (boolean, default false)
  locked_at (timestamp, nullable)
  reset_used (boolean, default false)  // tracks the one-time unlock (§7)
  match_threshold (integer, default 60) // 0-100
  status (string, default 'active')    // active | pending_verification | under_review | suspended | banned
  verification_status (string, default 'pending') // pending | approved | rejected — selfie verification (§13)
  banned_at (timestamp, nullable)
  ban_reason (string, nullable)
  suspension_ends_at (timestamp, nullable) // for temporary suspensions
  strike_count (integer, default 0)
  is_admin (boolean, default false)    // gates /admin/* Livewire routes and /api/v1/admin/* endpoints
  last_active_at (timestamp, nullable) // updated on each authenticated request, used for stale-account cleanup
  timestamps
  softDeletes                          // never hard-delete user data — needed for moderation evidence trail

profiles
  id (uuid, pk)
  user_id (uuid, fk -> users, unique, onDelete cascade)
  display_name (string, max 50)
  date_of_birth (date)                 // validated 18+ at creation time
  gender (string)                      // from profile_field_options where field_key='gender'
  city (string)
  lat (decimal 10,7)
  lng (decimal 10,7)
  timestamps

profile_photos
  id (uuid, pk)
  user_id (uuid, fk -> users, onDelete cascade)
  path (string)                        // storage path (local disk or S3-compatible)
  is_primary (boolean, default false)  // the main photo shown on match cards
  sort_order (integer, default 0)
  original_size_kb (integer)           // stored for analytics on compression effectiveness
  timestamps

preferences
  id (uuid, pk)
  user_id (uuid, fk -> users, unique, onDelete cascade)
  age_min (integer)
  age_max (integer)
  accepted_genders (json)              // string[] e.g. ["male","female"]
  max_distance_km (integer)
  timestamps

field_definitions
  id (uuid, pk)
  key (string, unique)                 // e.g. "smoking", "love_language" — see §2.5
  label (string)                       // human-readable, e.g. "Smoking Habits"
  description (string, nullable)       // optional help text shown in onboarding wizard
  category (string)                    // Physical Attribute | Relationship | Career/Education | Lifestyle | Personality/Values | Interests | Communication/Matching
  field_type (string)                  // single_select | multi_select | scale | range | text
  is_active (boolean, default false)
  is_hard_filter (boolean, default false)
  is_required (boolean, default true)  // whether user must answer (§2.4)
  weight (decimal 5,2, default 1.00)
  sort_order (integer, default 0)
  config (json, nullable)              // field-type-specific config: {max_selections: 5} for multi_select, {buffer_percent: 20} for range, etc.
  timestamps

profile_field_options
  id (uuid, pk)
  field_key (string)                   // matches field_definitions.key (string match, not FK — see note below)
  value (string)                       // stored value, e.g. "vegan", "quality_time"
  label (string)                       // display label, e.g. "Vegan", "Quality Time"
  sort_order (integer, default 0)
  is_active (boolean, default true)    // retire a single option without touching the whole field
  timestamps
  unique(field_key, value)

  // NOTE on why field_key is a string match, not an FK to field_definitions.id:
  // Options are seeded by key (e.g. "smoking") not by UUID. The seeders reference
  // field_key strings. An FK to a UUID pk would require the field_definitions rows
  // to exist first and would couple seeder ordering. String match is intentional.

profile_field_values
  id (uuid, pk)
  user_id (uuid, fk -> users, onDelete cascade)
  field_key (string)                   // matches field_definitions.key
  value (json)                         // shape depends on field_type:
                                       //   single_select: "string"
                                       //   multi_select: ["string", ...]
                                       //   scale: integer (ordinal position)
                                       //   range/number: integer
                                       //   text: "string"
  timestamps
  unique(user_id, field_key)

preference_field_values
  id (uuid, pk)
  user_id (uuid, fk -> users, onDelete cascade)
  field_key (string)
  value (json)                         // shape depends on field_type:
                                       //   single_select: ["accepted", "values"] (array)
                                       //   multi_select: ["desired", "values"] or [] (no preference)
                                       //   scale: {min: int, max: int}
                                       //   range/number: {min: int, max: int}
                                       //   geo: integer (max_distance_km) — but this is on preferences table for core geo
                                       //   text: not stored (text fields have no preference)
  timestamps
  unique(user_id, field_key)

match_scores
  id (uuid, pk)
  viewer_id (uuid, fk -> users, onDelete cascade)
  target_id (uuid, fk -> users, onDelete cascade)
  score (integer)                      // 0-100
  passed_hard_filters (boolean)        // cached so the match list query doesn't re-check
  updated_at (timestamp)
  unique(viewer_id, target_id)
  index(viewer_id, score DESC)         // the match list query

interests
  id (uuid, pk)
  from_id (uuid, fk -> users, onDelete cascade)
  to_id (uuid, fk -> users, onDelete cascade)
  created_at (timestamp)
  unique(from_id, to_id)

matches                                // Eloquent model: MutualMatch (avoids PHP reserved word conflicts)
  id (uuid, pk)
  user_a_id (uuid, fk -> users, onDelete cascade)
  user_b_id (uuid, fk -> users, onDelete cascade)
  matched_at (timestamp)
  is_active (boolean, default true)    // can be deactivated if one user blocks the other post-match
  unique(user_a_id, user_b_id)         // always store with lower UUID as user_a to prevent duplicate pairs

messages
  id (uuid, pk)
  match_id (uuid, fk -> matches, onDelete cascade)
  sender_id (uuid, fk -> users)
  body (text)                          // max 2000 chars, enforced at validation layer
  flagged (boolean, default false)     // set by ModerationService (§12.4)
  flag_reason (string, nullable)       // e.g. "keyword_match", "pattern_match"
  delivered (boolean, default true)    // false if held for review (§12.4 decision)
  read_at (timestamp, nullable)        // set when recipient views the message
  sent_at (timestamp)

otp_codes                              // for phone+OTP auth (§8)
  id (uuid, pk)
  phone (string)                       // E.164 format
  code (string)                        // 6-digit, hashed (bcrypt or similar — never store plain)
  purpose (string)                     // login | signup | password_reset
  attempts (integer, default 0)        // failed verification attempts against this code
  expires_at (timestamp)               // 5 minutes from creation
  verified_at (timestamp, nullable)
  created_at (timestamp)
  index(phone, purpose, created_at)

reports
  id (uuid, pk)
  reporter_id (uuid, fk -> users)
  reported_id (uuid, fk -> users)
  reason (string)                      // harassment | threats | fake_profile | explicit_content | hate_speech | underage | other
  details (text, nullable)             // reporter's description, max 1000 chars
  message_id (uuid, nullable, fk -> messages)  // if reporting a specific message
  match_id (uuid, nullable, fk -> matches)     // context
  status (string, default 'pending')   // pending | reviewed_dismissed | reviewed_actioned
  severity (string, default 'standard') // standard | zero_tolerance — auto-set based on reason (§12.2)
  admin_notes (text, nullable)         // moderator's notes on review
  action_taken (string, nullable)      // dismissed | warned | suspended | banned
  created_at (timestamp)
  reviewed_at (timestamp, nullable)
  reviewed_by (uuid, nullable, fk -> users)  // the admin who reviewed
  index(reported_id, status)
  index(status, severity, created_at)

cities                                 // pre-seeded Ghana cities for location picker (§2.1)
  id (uuid, pk)
  name (string)                        // e.g. "Accra", "Kumasi"
  region (string)                      // e.g. "Greater Accra", "Ashanti"
  lat (decimal 10,7)
  lng (decimal 10,7)
  is_active (boolean, default true)
  sort_order (integer, default 0)      // popular cities first
  unique(name, region)
```

### 5.1 Eloquent Relationships

```php
// User
hasOne(Profile::class)
hasOne(Preferences::class)
hasMany(ProfilePhoto::class)
hasMany(ProfileFieldValue::class)
hasMany(PreferenceFieldValue::class)
hasMany(Interest::class, 'from_id')          // interests I've expressed
hasMany(Interest::class, 'to_id')            // interests expressed toward me
hasMany(MatchScore::class, 'viewer_id')      // my scores of others
hasMany(MatchScore::class, 'target_id')      // others' scores of me
hasMany(Report::class, 'reporter_id')        // reports I've filed
hasMany(Report::class, 'reported_id')        // reports filed against me
hasMany(Message::class, 'sender_id')
// MutualMatches: custom scope/accessor since user could be user_a or user_b

// Profile: belongsTo(User::class)
// Preferences: belongsTo(User::class)
// ProfilePhoto: belongsTo(User::class)
// ProfileFieldValue: belongsTo(User::class)
// PreferenceFieldValue: belongsTo(User::class)
// FieldDefinition: hasMany(ProfileFieldOption::class, 'field_key', 'key')
// ProfileFieldOption: (no explicit belongsTo — linked by string field_key)
// MatchScore: belongsTo(User::class, 'viewer_id'), belongsTo(User::class, 'target_id')
// Interest: belongsTo(User::class, 'from_id'), belongsTo(User::class, 'to_id')
// MutualMatch: belongsTo(User::class, 'user_a_id'), belongsTo(User::class, 'user_b_id')
//   hasMany(Message::class, 'match_id')
// Message: belongsTo(MutualMatch::class, 'match_id'), belongsTo(User::class, 'sender_id')
// Report: belongsTo(User::class, 'reporter_id'), belongsTo(User::class, 'reported_id')
//   belongsTo(Message::class, 'message_id'), belongsTo(MutualMatch::class, 'match_id')
```

### 5.2 Eloquent Casts

```php
// On all models with json columns:
protected $casts = [
    'value' => 'array',            // ProfileFieldValue, PreferenceFieldValue
    'accepted_genders' => 'array', // Preferences
    'config' => 'array',           // FieldDefinition
];

// Date casts:
'date_of_birth' => 'date',        // Profile
'locked_at' => 'datetime',        // User
'matched_at' => 'datetime',       // MutualMatch
'sent_at' => 'datetime',          // Message
'expires_at' => 'datetime',       // OtpCode
// etc.
```

### 5.3 MutualMatch pair ordering convention
To prevent duplicate pairs (A↔B stored twice), always store the pair with the **lexicographically smaller UUID as `user_a_id`**. Enforce this in `Actions/RegisterInterest.php` when creating the `MutualMatch` row. Query mutual matches for a user with: `where('user_a_id', $userId)->orWhere('user_b_id', $userId)`.

---

## 6. When Matching Recomputes

Keep this simple for MVP — no real-time streaming infrastructure needed at small scale:

**Trigger 1: User lock-in.** On a user's lock-in (profile + preferences submitted): dispatch a queued job (`app/Jobs/ComputeMatchScoresForUser.php`) that:
1. Loads the newly locked user's profile and preferences.
2. Loads all other locked, active, non-banned/suspended users.
3. For each other user, computes both directions: `score(newUser→other)` and `score(other→newUser)`.
4. Upserts `match_scores` rows (both directions).
5. Uses chunked queries (e.g. `User::where('profile_locked', true)->where('status', 'active')->chunk(200, ...)`) to avoid loading the entire user table into memory.

Queued (not synchronous) so the lock-in request returns fast. Use Laravel's `database` queue driver for MVP; switch to Redis if queue depth becomes a problem.

**Trigger 2: Viewing the Matches tab.** Just query precomputed data — no live scoring:
```sql
SELECT ms.*, p.display_name, p.city, pp.path as photo
FROM match_scores ms
JOIN profiles p ON p.user_id = ms.target_id
JOIN users u ON u.id = ms.target_id
LEFT JOIN profile_photos pp ON pp.user_id = ms.target_id AND pp.is_primary = true
WHERE ms.viewer_id = :me
  AND ms.score >= :my_threshold
  AND ms.passed_hard_filters = true
  AND u.status = 'active'
  AND u.verification_status = 'approved'  -- only show verified users
ORDER BY ms.score DESC
```

**Trigger 3: Admin field-config change.** If an admin activates/deactivates a `field_definitions` row, changes a weight, or toggles a hard-filter flag: run `php artisan matches:recompute` (a custom Artisan command) that dispatches `ComputeMatchScoresForUser` for every locked, active user. A field-config change invalidates the **entire** score matrix — don't attempt incremental patches.

**Trigger 4: User status change.** When a user is suspended/banned (§12), immediately delete or mark as stale all `match_scores` rows where that user is either viewer or target. When a user is restored from suspension, dispatch recomputation for them.

**Performance note**: for MVP scale (< 10,000 users), full-matrix recomputation is fine. At larger scale, consider: (a) background batch jobs that process the matrix in chunks, (b) only recomputing for users who haven't been recomputed since the config change, (c) a `scores_stale_since` timestamp on `field_definitions` to track when the last invalidating change happened. Do not pre-optimize — the current approach will handle the first 12+ months of growth.

---

## 7. Locking Rules (the "no traditional swiping" part)

- Profile and preferences become **immutable** once submitted (`profile_locked = true`, `locked_at` = now).
- Rationale: forces genuine, considered answers instead of feed-optimized, constantly-tweaked bait profiles. Users know their answers matter because they can't change them — this is the core product differentiator.
- **No partial edits.** There is no "edit one field" UI. The only mutation is a full reset (below).

**One-time reset:**
- Every user gets **one free "unlock and redo"** in their lifetime (tracked by `users.reset_used`).
- Triggered from a settings screen, behind a confirmation modal with explicit consequences: *"This will: (1) clear all your current match scores, (2) clear all your expressed interests, (3) take you back through the full onboarding wizard. Your existing mutual matches and chat history will be preserved. This action cannot be undone, and you cannot reset again. Are you sure?"*
- On confirm: `profile_locked = false`, `reset_used = true`. Delete all `match_scores` where user is viewer or target. Delete all `interests` where user is from or to. Do NOT delete `MutualMatch` rows or `Message` rows — preserve existing relationships.
- The user then re-does the full onboarding wizard (both profile and preferences). On re-lock, match computation runs again from scratch.
- A second reset is never available. The UI should not show the reset option once `reset_used = true`.

---

## 8. Tech Stack (MVP)

### 8.1 Core Framework
- **Framework**: Laravel 11+, PHP 8.3+
- **Web frontend**: Livewire 3 (+ Volt for single-file components where it keeps things simpler) + Blade + Tailwind CSS
- **DB**: MySQL 8+ via Eloquent (Postgres also acceptable; SQLite for local dev/tests)
- **Queue**: `database` driver for MVP (Redis if needed later)
- **Cache**: `file` driver for MVP (Redis if needed later)
- **Deployment**: Laravel Forge + VPS (DigitalOcean or similar with a region near Ghana — e.g. London or Amsterdam), or Laravel Cloud. NOT Vercel/edge-function platforms — this is a full Laravel app, not a Node/edge stack.

### 8.2 Authentication (Phone + OTP)
Phone number + SMS OTP is the **only** auth path for MVP. No email/password, no social login.

Flow:
1. User enters phone number (validated as Ghanaian: `+233XXXXXXXXX` or local format `0XXXXXXXXX`, normalized to E.164 on the server).
2. Server generates a 6-digit OTP, hashes it, stores it in `otp_codes` with 5-minute expiry.
3. Server sends the OTP via SMS through `Services/OtpService.php`, which wraps the provider SDK.
4. User enters the 6-digit code. Server verifies against the hashed stored code.
5. On verification: if the phone number already exists in `users`, log them in. If not, create a new `users` row.
6. **For web (Livewire)**: issue a standard Laravel session.
7. **For API (mobile)**: issue a Sanctum personal access token, returned in the response body. Client stores it and sends as `Authorization: Bearer {token}` on subsequent requests.

**Rate limiting (critical for SMS cost control):**
- Max 3 OTP requests per phone number per 15 minutes (`throttle:3,15` keyed on phone).
- Max 5 OTP verification attempts per code (tracked in `otp_codes.attempts`; after 5 failures, code is invalidated).
- Max 10 OTP requests per IP per hour (defense against enumeration from a single source).
- Return generic error messages ("Invalid or expired code") — never confirm whether a phone number exists in the system.

**Provider**: Africa's Talking (primary recommendation — strong Ghana/West Africa coverage, MTN/Telecel/AirtelTigo support, reasonable pricing) or Hubtel (Ghana-native alternative). `OtpService.php` should use an interface (`Contracts/SmsProvider.php`) so the provider can be swapped via config without touching business logic. See §14 for the provider decision.

### 8.3 Authorization
- Laravel Policies/Gates for admin-only routes.
- Gate definition: `Gate::define('admin', fn (User $user) => $user->is_admin)`.
- Applied to all `/admin/*` Livewire routes and all `/api/v1/admin/*` API routes.
- Middleware: `auth` (Sanctum for API, session for web) on all app routes; `verified` (phone verified) on all post-auth routes; `can:admin` on admin routes.

### 8.4 Realtime Chat
- MVP: `wire:poll.5s` on the Livewire chat component (polls every 5 seconds while the chat view is open). The API equivalent: mobile clients poll `GET /api/v1/matches/{match}/messages?since={last_message_id}` on a similar interval.
- Upgrade path: Laravel Reverb (Laravel's WebSocket server) for real-time push. Reverb broadcasts to both web (via Echo) and mobile (via any WebSocket client) from the same event classes. This is a v2 upgrade — do not build it now, but do fire a `MessageSent` event (via `ShouldBroadcast` interface, with `broadcastOn()` returning the match channel) when a message is created, even if nothing is listening yet. This makes the Reverb upgrade a config change, not a code rewrite.

### 8.5 Image Handling
- Use **Intervention Image** (v3, `intervention/image` package) for server-side processing on upload.
- Resize to max 1200px on longest edge, compress to JPEG quality 75 (or WebP if browser support is confirmed — check Accept header).
- Store original and compressed versions. Serve compressed by default. Store in `storage/app/photos/` for MVP; migrate to S3-compatible storage (e.g. DigitalOcean Spaces) before scale.
- Lazy-load all images in Blade/Livewire templates (`loading="lazy"`).

### 8.6 PWA Configuration
Serve the web app as a PWA for an app-like experience with no app-store download:
- `public/manifest.json`: app name, icons, `display: standalone`, `theme_color`, `start_url: /`.
- `public/sw.js`: minimal service worker that caches the app shell (CSS/JS assets, layout Blade) and serves cached content when offline, with a "You're offline" fallback page for dynamic routes.
- Use the `erag/laravel-pwa` package or hand-write the manifest + service worker (it's < 50 lines for the MVP scope).
- PWA rationale for Ghana market: no app-store download size (critical for data-cost-conscious users), works across low-end Android devices (the dominant device type), no iOS/Android codebase split at launch.

### 8.7 Non-Goals for MVP (explicitly out of scope — do NOT build)
Video profiles, payments/subscriptions, push notifications (beyond PWA's basic capability), infinite swipe feed, social login, email/password auth, native mobile app (§11 covers the API path that makes this easy later), AI-powered matching or recommendations, user analytics dashboard, admin analytics beyond the moderation queue, dark mode (can be added trivially later with Tailwind's `dark:` classes).

**IN scope** (do not defer): light selfie-based identity verification at signup (§13), age gating (§13), moderation + reporting (§12), outgoing message content checks (§12.4).

---

## 9. File Structure

```
app/
  Contracts/                            // interfaces for swappable services
    SmsProviderInterface.php            // send(phone, message): void
    ModerationProviderInterface.php     // check(text): ModerationResult
  Enums/
    UserStatus.php                      // Active, PendingVerification, UnderReview, Suspended, Banned
    VerificationStatus.php              // Pending, Approved, Rejected
    ReportReason.php                    // Harassment, Threats, FakeProfile, ExplicitContent, HateSpeech, Underage, Other
    ReportSeverity.php                  // Standard, ZeroTolerance
    ReportStatus.php                    // Pending, ReviewedDismissed, ReviewedActioned
    ModerationAction.php               // Dismissed, Warned, Suspended, Banned
    FieldType.php                       // SingleSelect, MultiSelect, Scale, Range, Text
    OtpPurpose.php                      // Login, Signup
  Models/
    User.php
    Profile.php
    ProfilePhoto.php
    Preferences.php
    FieldDefinition.php
    ProfileFieldOption.php
    ProfileFieldValue.php
    PreferenceFieldValue.php
    MatchScore.php
    Interest.php
    MutualMatch.php                     // table name: 'matches'
    Message.php
    OtpCode.php
    Report.php
    City.php
  Actions/                              // framework-agnostic business logic, shared by Livewire + API (§11)
    Auth/
      SendOtp.php                       // validates phone, rate-limits, creates OtpCode, dispatches SMS
      VerifyOtp.php                     // verifies code, creates/finds user, issues session or token
    Onboarding/
      SaveProfileStep.php              // saves one profile_field_value (or core profile field)
      SavePreferenceStep.php           // saves one preference_field_value (or core preference field)
      LockUserProfile.php             // validates completeness, sets profile_locked, dispatches score job
      ResetUserProfile.php            // the one-time unlock (§7)
    Matching/
      ComputeMatchScore.php            // generic per-field-type scoring (§3.2) + hard-filter gate (§3.1)
      ComputePairScore.php             // scores one viewer→target pair, upserts match_score
    Social/
      RegisterInterest.php             // creates Interest, checks mutual → creates MutualMatch
      SendMessage.php                  // creates Message, runs moderation check, fires event
    Moderation/
      FileReport.php                   // creates Report, auto-sets severity, triggers auto-suspend for zero-tolerance (§12.2)
      ReviewReport.php                 // moderator actions: dismiss/warn/suspend/ban (§12.3)
      SuspendUser.php                  // sets status, clears match_scores, preserves evidence
      BanUser.php                      // permanent ban logic
      RestoreUser.php                  // un-suspend, re-trigger match computation
    Admin/
      UpdateFieldDefinition.php        // activate/deactivate, change weight/hard-filter/order
      UpdateFieldOption.php            // activate/deactivate individual options
  Jobs/
    ComputeMatchScoresForUser.php      // queued, runs on lock-in and via the recompute command (§6)
  Livewire/
    Auth/
      PhoneEntry.php                   // enter phone number, request OTP
      OtpVerification.php              // enter OTP code
    Onboarding/
      ProfileWizard.php                // multi-step wizard controller (tracks current step, validates, saves)
      PreferenceWizard.php
      ReviewAndLock.php                // summary + lock-in confirmation
    Dashboard/
      MatchList.php                    // paginated match list sorted by score
      ProfileDetail.php                // view another user's profile + interest/report buttons
      InterestButton.php               // extracted component for the interest action
    Chat/
      ThreadList.php                   // list of mutual matches with last message preview
      Thread.php                       // wire:poll-based message thread, gated on existing MutualMatch
    Settings/
      AccountSettings.php              // reset trigger (§7), match threshold adjustment
    Admin/
      Fields/
        FieldManager.php               // field-library manager: activate/deactivate, weight/hard-filter/order (§2.4)
        OptionManager.php              // manage options for a specific field
      Reports/
        ReportQueue.php                // moderator queue (§12.3)
        ReportDetail.php               // single report review + action buttons
      Users/
        UserList.php                   // search/browse users, view status, manual verification
      Verification/
        VerificationQueue.php          // selfie verification review queue (§13)
  Http/
    Controllers/Api/V1/                // mirrors Livewire functionality for mobile clients (§11)
      AuthController.php               // phone+OTP → Sanctum token
      ProfileController.php            // GET profile, GET active fields + options
      PreferenceController.php
      LockController.php               // POST lock-in
      MatchController.php              // GET matches list, GET match detail
      InterestController.php           // POST interest
      ChatController.php               // GET messages, POST message
      ReportController.php             // POST report
      SettingsController.php           // POST reset, PATCH threshold
      Admin/
        FieldController.php
        ReportController.php
        UserController.php
        VerificationController.php
    Resources/                         // API JSON shape, versioned (§11)
      UserResource.php
      ProfileResource.php
      MatchResource.php
      MatchListResource.php
      MessageResource.php
      FieldDefinitionResource.php
      FieldOptionResource.php
      ReportResource.php
    Middleware/
      EnsurePhoneVerified.php          // blocks unverified users from app routes
      EnsureProfileLocked.php          // blocks unlocked users from matches/chat routes
      EnsureNotBanned.php              // returns 403 with ban reason for banned users
      EnsureVerifiedIdentity.php       // blocks unverified-selfie users from appearing in matches
      TrackLastActive.php              // updates users.last_active_at
    Requests/                          // form request validation classes
      SendOtpRequest.php
      VerifyOtpRequest.php
      SaveProfileStepRequest.php
      SavePreferenceStepRequest.php
      SendMessageRequest.php
      FileReportRequest.php
      // etc.
  Services/
    OtpService.php                     // wraps SmsProviderInterface, handles code generation/hashing/verification
    GeoService.php                     // Haversine distance calculation
    ModerationService.php              // outgoing-message content check (§12.4)
    ImageService.php                   // compression, resizing via Intervention Image
    ScoringService.php                 // orchestrates per-field-type scoring functions (§3.2)
  Providers/
    AfricasTalkingSmsProvider.php      // implements SmsProviderInterface
    HubtelSmsProvider.php              // alternative implementation
    AppServiceProvider.php             // binds interfaces to implementations based on config
routes/
  web.php                              // Livewire routes, grouped by middleware
  api.php                              // /api/v1/... routes, Sanctum-protected (§11)
  console.php                          // Artisan command registrations
database/
  migrations/
    xxxx_create_users_table.php
    xxxx_create_profiles_table.php
    xxxx_create_profile_photos_table.php
    xxxx_create_preferences_table.php
    xxxx_create_field_definitions_table.php
    xxxx_create_profile_field_options_table.php
    xxxx_create_profile_field_values_table.php
    xxxx_create_preference_field_values_table.php
    xxxx_create_match_scores_table.php
    xxxx_create_interests_table.php
    xxxx_create_matches_table.php
    xxxx_create_messages_table.php
    xxxx_create_otp_codes_table.php
    xxxx_create_reports_table.php
    xxxx_create_cities_table.php
  seeders/
    DatabaseSeeder.php                          // calls all below in order
    CitySeeder.php                              // Ghana cities with lat/lng
    GenderOptionsSeeder.php                     // core gender options
    FieldDefinitionSeeder.php                   // seeds field_definitions rows (§14 — STILL NEEDED)
    ProfileFieldOptionsSeeder.php               // orchestrator — calls the 7 category seeders below
    PhysicalAttributeOptionsSeeder.php
    RelationshipOptionsSeeder.php
    CareerEducationOptionsSeeder.php
    LifestyleOptionsSeeder.php
    PersonalityValuesOptionsSeeder.php
    InterestsOptionsSeeder.php
    CommunicationMatchingOptionsSeeder.php
    AdminUserSeeder.php                         // creates a default admin user for local dev (DO NOT run in production)
  factories/                                    // for testing (§15)
    UserFactory.php
    ProfileFactory.php
    FieldDefinitionFactory.php
    // etc.
resources/
  views/
    layouts/
      app.blade.php                             // main app layout (authenticated)
      auth.blade.php                            // auth flow layout
      admin.blade.php                           // admin layout
    components/                                 // reusable Blade components
      field-input.blade.php                     // renders profile input for any field_type
      preference-input.blade.php                // renders preference input for any field_type
      match-card.blade.php                      // card for match list
      score-badge.blade.php                     // displays match percentage
    livewire/                                   // Livewire component views (mirroring app/Livewire structure)
      auth/
      onboarding/
      dashboard/
      chat/
      settings/
      admin/
    pages/
      offline.blade.php                         // PWA offline fallback
      banned.blade.php                          // shown to banned users
      under-review.blade.php                    // shown to suspended users
  css/
    app.css                                     // Tailwind directives
  js/
    app.js                                      // minimal — Alpine.js (via Livewire), no heavy SPA framework
config/
  matchlock.php                                 // app-specific config: otp_expiry_minutes, max_otp_attempts,
                                                // default_match_threshold, image_max_width, image_quality,
                                                // sms_provider (africastalking|hubtel), etc.
  services.php                                  // Africa's Talking / Hubtel API keys (via .env)
public/
  manifest.json                                 // PWA manifest
  sw.js                                         // service worker
tests/                                          // see §15
  Unit/
    Actions/
    Services/
  Feature/
    Api/
    Livewire/
console/
  Commands/
    RecomputeMatchScores.php                    // php artisan matches:recompute (§6)
    PruneExpiredOtps.php                        // php artisan otp:prune — clean up expired OTP rows (run via scheduler)
    PruneStaleMatchScores.php                   // clean up scores for banned/deleted users
```

---

## 10. Build Order (recommended agent task sequence)

Each step should result in committed, tested, working code before moving to the next. Do not skip ahead. Steps are ordered by dependency (each step depends on the ones before it) and by the principle that safety features ship before social features.

**Phase 1: Foundation**

1. **Scaffold.** `laravel new matchlock`, install Livewire 3 + Volt, Tailwind CSS, Sanctum. Create `config/matchlock.php`. Set up the `admin` Gate in `AuthServiceProvider`. Configure `database` queue driver. Set timezone to `Africa/Accra` in `config/app.php`.

2. **Migrations + Models.** Create every migration from §5 in the order listed (respecting FK dependencies). Create all Eloquent models with relationships (§5.1), casts (§5.2), and `HasUuids` trait. Add soft deletes to `User`. Run `php artisan migrate` and verify the schema.

3. **Seeders.** Build `CitySeeder` (Ghana cities), `GenderOptionsSeeder`, `FieldDefinitionSeeder` (every field from §2.2 with correct `field_type`, `category`, all inactive by default), and all 7 category option seeders. Build `AdminUserSeeder` for local dev. Run `php artisan db:seed` and verify data.

4. **Admin field manager.** Build `Admin/Fields/FieldManager` Livewire component + `Api/V1/Admin/FieldController`. This is needed early because everything else depends on having active fields. Implement: list all `field_definitions`, toggle `is_active`, edit `weight`/`is_hard_filter`/`is_required`/`sort_order`. Activate the suggested starting subset (§2.2) via the UI or a test/seed.

**Phase 2: Auth + Onboarding**

5. **OTP auth.** Build `Contracts/SmsProviderInterface`, `AfricasTalkingSmsProvider` (or a `LogSmsProvider` for local dev that logs OTPs to `storage/logs`), `Services/OtpService`, `Actions/Auth/SendOtp` + `VerifyOtp`. Build the Livewire auth flow (`PhoneEntry` + `OtpVerification`) and `Api/V1/AuthController`. Implement all rate limiting (§8.2). Write feature tests covering: successful signup, successful login, expired OTP, too many attempts, rate limiting.

6. **Onboarding wizard.** Build `Actions/Onboarding/SaveProfileStep` + `SavePreferenceStep`. Build the Livewire `ProfileWizard` + `PreferenceWizard` + `ReviewAndLock`. These must be **dynamic**: they read active `field_definitions` (ordered by `sort_order`) and render the correct input UI for each `field_type` (§2.3) using a shared `field-input.blade.php` / `preference-input.blade.php` component. Build the equivalent API controllers. Write tests covering: completing all steps, validation per field type, skipping optional fields, the lock-in action.

7. **Lock-in.** Build `Actions/Onboarding/LockUserProfile`: validates all required fields are filled, sets `profile_locked = true` and `locked_at`, dispatches `ComputeMatchScoresForUser` job. Build `Http/Middleware/EnsureProfileLocked` to gate post-onboarding routes.

**Phase 3: Matching Engine**

8. **Scoring engine.** Build `Services/ScoringService` with per-field-type scoring functions (§3.2) — each is a pure function taking `(fieldDefinition, profileValue, preferenceValue)` → `float 0.0–1.0`. Build `Services/GeoService` with Haversine distance calculation. Build `Actions/Matching/ComputeMatchScore` (orchestrates hard-filter check + weighted score for one viewer→target pair). **Write thorough unit tests** for every field type (§3.2) including edge cases: empty preference = 1.0, missing profile value on required field = 0.0, division by zero when no weighted fields exist, geo scoring at exact boundary distances.

9. **Match computation job.** Build `Jobs/ComputeMatchScoresForUser` (§6) — loads all other locked/active users, computes both directions, upserts `match_scores`. Build `console/Commands/RecomputeMatchScores.php`. Test with seeded data: create 5+ test users with varying profiles/preferences, verify scores are correct and symmetric-in-structure (both directions computed, possibly different values).

**Phase 4: Core Social Loop**

10. **Match list.** Build `Livewire/Dashboard/MatchList` + `Api/V1/MatchController`. Query per §6 Trigger 2. Paginate (20 per page). Show match card: photo, name, age, city, score. Build `Livewire/Dashboard/ProfileDetail` for viewing a match's full profile.

11. **Interest + mutual match.** Build `Actions/Social/RegisterInterest`: creates `Interest` row, checks if reciprocal `Interest` exists, if so creates `MutualMatch` (with UUID ordering per §5.3). The Livewire `InterestButton` component and `Api/V1/InterestController` both call this Action. Test: one-way interest (no match created), mutual interest (match created), duplicate interest (idempotent, no error).

12. **Chat.** Build `Actions/Social/SendMessage` (creates `Message`, fires `MessageSent` event). Build `Livewire/Chat/ThreadList` (list of mutual matches with last message preview) and `Livewire/Chat/Thread` (`wire:poll.5s`). Build `Api/V1/ChatController`. Gate all chat routes on existing `MutualMatch` — `EnsureProfileLocked` middleware + explicit match-membership check in the Action/controller. Test: can only message mutual matches, messages appear for both parties.

**Phase 5: Safety & Moderation (ship before public launch)**

13. **Reporting.** Build `Actions/Moderation/FileReport`: creates `Report`, auto-sets `severity` based on `reason` (§12.2), auto-suspends for zero-tolerance categories. Build report buttons on match cards, profile detail, and individual messages. Build `Api/V1/ReportController`. Test: zero-tolerance report → immediate suspension, standard report → queued, repeated standard reports → auto-escalation.

14. **Admin moderation queue.** Build `Admin/Reports/ReportQueue` + `ReportDetail` Livewire components and `Api/V1/Admin/ReportController`. Implement dismiss/warn/suspend/ban actions via `Actions/Moderation/ReviewReport`, `SuspendUser`, `BanUser`, `RestoreUser`. Test: all action flows, that banned users are excluded from match lists, that suspended users see the under-review screen.

15. **Outgoing message moderation.** Build `Services/ModerationService` (§12.4): keyword/pattern list for slurs, threats, explicit sexual content. Integrate into `Actions/Social/SendMessage`. Flag matching messages. Surface flagged messages in the admin report queue. Test: flagged words are caught, clean messages pass through.

16. **Selfie verification.** Build `Admin/Verification/VerificationQueue` Livewire component. Build `profile_photos` upload flow into onboarding (before the field wizard). For MVP, verification is manual: admin compares selfie to profile photo and approves/rejects. Users with `verification_status != 'approved'` do not appear in anyone's match list (enforced in the match list query, §6 Trigger 2). Build `Api/V1/Admin/VerificationController`.

**Phase 6: Polish**

17. **One-time reset.** Build `Actions/Onboarding/ResetUserProfile` (§7). Add the reset button to `Settings/AccountSettings`, gated on `!reset_used`. Test: reset clears scores/interests, preserves matches/messages, blocks second reset.

18. **PWA.** Add `manifest.json`, `sw.js`, offline fallback page. Test: app is installable on Android Chrome, offline page shows when disconnected.

19. **Image optimization.** Build `Services/ImageService` (Intervention Image compression/resizing, §8.5). Integrate into photo upload flow. Add `loading="lazy"` to all `<img>` tags. Test: uploaded images are compressed, originals preserved.

20. **Scheduled tasks.** Register in `console/Kernel.php` (or `routes/console.php` in Laravel 11+): `otp:prune` daily (delete expired OTP rows), `matches:prune-stale` weekly (clean up scores for deleted/banned users).

21. **API documentation.** Once the API is stable, generate an OpenAPI spec (via `dedoc/scramble` or hand-written) so a React Native/Flutter developer can build against it. Include example requests/responses for every endpoint.

---

## 11. API Layer for Future Mobile Apps (React Native or Flutter)

The point of putting business logic in `app/Actions`/`app/Services` (§9) rather than directly in Livewire components is that the mobile apps built later shouldn't require re-implementing locking, scoring, matching, or moderation — they just call the same API the Livewire frontend's controllers ultimately call.

### 11.1 Versioning
Everything under `/api/v1/...` from the start. Bumping to `/api/v2` later is far cheaper than retrofitting versioning onto an unversioned API.

### 11.2 Auth
Phone+OTP (shared `OtpService`, §8) issues a Sanctum personal access token instead of a session cookie. Token is returned in the response body after successful OTP verification:
```json
{
  "token": "1|abc123...",
  "user": { "id": "uuid", "phone": "+233...", "profile_locked": false, ... }
}
```
Mobile clients store this token securely (Keychain on iOS, EncryptedSharedPreferences on Android) and send it as `Authorization: Bearer {token}` on every request. Tokens do not expire (Sanctum default) but can be revoked by the user (logout) or by admin action (ban).

### 11.3 Response shape
Use Laravel API Resources (`Http/Resources/*`) for every response — never return raw Eloquent models. This decouples the API's JSON shape from the DB schema. Every resource includes only the fields the client needs; internal fields (`strike_count`, `ban_reason`, etc.) are excluded from non-admin resources.

### 11.4 Server-driven onboarding
The mobile onboarding wizard is driven by `GET /api/v1/fields` (returns active `field_definitions` with their options, ordered by `sort_order`), NOT hardcoded into the app binary. This means an admin can activate a new field and it appears in the mobile wizard without an app store update. The response shape:
```json
[
  {
    "key": "religion",
    "label": "Religion",
    "description": "What is your religious background?",
    "field_type": "single_select",
    "is_required": true,
    "sort_order": 1,
    "config": {},
    "options": [
      { "value": "christianity", "label": "Christianity" },
      { "value": "islam", "label": "Islam" },
      ...
    ]
  },
  ...
]
```

### 11.5 Parity, not duplication
For each feature, the Action/Service class is written once; the Livewire component and the API controller are both thin wrappers. **Litmus test**: if a bug fix only touches the Livewire component, the logic leaked out of the Action layer and must be moved back in.

### 11.6 What mobile apps will need on launch day
Auth (OTP + token), onboarding wizard (server-driven from `/api/v1/fields`), matches list + profile detail, interest/match actions, chat (poll-based), reporting, settings (reset, threshold). That's it.

### 11.7 What NOT to build yet
Push notifications, offline sync, native chat SDK, token refresh (Sanctum tokens don't expire by default — add expiry only if needed). These are mobile-specific concerns to design once there's an actual mobile client.

### 11.8 API documentation
Once the API stabilizes (build order §10, step 21), generate an OpenAPI spec so a Flutter/React Native developer can build against a contract instead of reading Laravel source.

---

## 12. Moderation, Reporting & Zero-Tolerance Enforcement

Harassment tolerance is **zero**, not "three strikes for everything." This section defines what that means concretely so it doesn't stay a slogan.

### 12.1 Reporting surfaces
A "Report" action must be reachable from every place a user can see another user:
- On a match-list card (before any interest is expressed)
- On a full profile view
- On every message in a chat thread (report a specific message, not just the person — the `message_id` FK in §5 supports this)

Reporting is always available — you don't need to be mutually matched to report someone whose profile you can see. The report form collects: reason (dropdown from `ReportReason` enum), optional details (text, max 1000 chars), and auto-attaches the `message_id` and/or `match_id` context if applicable.

### 12.2 Report reasons and severity
Reason enum: `harassment`, `threats`, `fake_profile`, `explicit_content`, `hate_speech`, `underage`, `other`.

Two severity tiers, auto-assigned based on reason:

**Zero-tolerance** (severity = `zero_tolerance`): `threats`, `hate_speech`, `underage`. A single report with any of these reasons triggers **immediate auto-suspension** of the reported account:
- `status` → `under_review`
- User is hidden from all match lists (excluded from `match_scores` queries)
- User cannot send messages
- User sees an "Your account is under review" screen on login
- This happens automatically in `Actions/Moderation/FileReport` — no moderator action required to trigger the suspension, only to resolve it.

**`harassment`** is a special case: it could be zero-tolerance (sexual harassment, threats) or standard (mildly inappropriate behavior). For MVP, treat all `harassment` reports as **zero-tolerance** to err on the side of safety. A moderator can dismiss if the report is unsubstantiated.

**Standard** (severity = `standard`): `fake_profile`, `explicit_content` (non-threatening), `other`. Account stays active unless/until a moderator acts. However, **3+ pending standard reports** against the same user (configurable threshold in `config/matchlock.php`) auto-escalate to `under_review` status as well.

### 12.3 Moderator review & actions
Build a minimal internal admin view, gated by the `admin` Gate:

**Report queue** (`Admin/Reports/ReportQueue`): list of pending reports, sorted by severity (zero-tolerance first) then recency. Shows: reported user's name + photo, reporter's name, reason, severity badge, time since report.

**Report detail** (`Admin/Reports/ReportDetail`): the reported user's full profile, the flagged message/context if applicable, reporter's stated reason and details, the reported user's complete report history (all past reports + outcomes), and the reported user's current `strike_count`.

**Actions** (each implemented as an Action class called by both Livewire and API):
- **Dismiss** → `status: reviewed_dismissed`, account restored to `active` if it was auto-suspended, no strike added. Use when the report is unsubstantiated.
- **Warn** → `status: reviewed_actioned`, account restored to `active`, `strike_count += 1`, warning logged. Use for minor first-time violations.
- **Suspend** → `status: reviewed_actioned`, account `status = suspended`, `suspension_ends_at` set (1 day / 7 days / 30 days — moderator selects duration). Use for serious violations that don't warrant a permanent ban.
- **Ban** → `status: reviewed_actioned`, account `status = banned`, `banned_at` + `ban_reason` set, account permanently excluded from matching, login blocked. Use for confirmed zero-tolerance violations.

Zero-tolerance categories that are confirmed by a moderator should default to **ban** as the pre-selected action in the UI, with the moderator able to override (with a reason logged in `admin_notes`).

### 12.4 Automated content checks (message-level)
Don't rely on user reports alone to catch harassment in real time:

**`Services/ModerationService.php`** runs on every outgoing message (called from `Actions/Social/SendMessage`):
- Checks against a keyword/pattern list for: slurs, racial/ethnic slurs, death threats, explicit sexual content, phone numbers/emails/social media handles (contact-sharing before mutual match is already blocked by the chat gate, but catching it in messages adds defense-in-depth).
- The keyword list lives in `config/matchlock.php` (or a separate `config/moderation.php`), not hardcoded in the service class, so it can be updated without a code deploy.
- Flagged messages: set `Message.flagged = true` and `Message.flag_reason`.

**Delivery decision** (§14 — flag to human team): flagged messages can either be:
- (a) **Held before delivery** (`delivered = false`): safer, but adds latency and requires moderator action to release or reject. Better for zero-tolerance keywords (threats, slurs).
- (b) **Delivered but flagged** (`delivered = true`, `flagged = true`): faster UX, but the harmful message reaches the recipient. Flagged messages appear in the moderation queue for review.
- **Recommended MVP approach**: hold for delivery only messages matching the most severe patterns (threats, extreme slurs); deliver-and-flag for softer matches (e.g. mild profanity, possible contact info). Implement both paths and make the severity-to-action mapping configurable.

**Escalation**: a user whose messages have been flagged 3+ times (configurable) should be auto-suspended (`status = under_review`) even without a user-filed report.

### 12.5 Banned/suspended user effects
- **Immediately excluded** from `match_scores` generation (both as viewer and target) — run `Actions/Moderation/SuspendUser` or `BanUser` which handles this cleanup.
- **Existing `MutualMatch` threads** with a banned/suspended user: preserved for the *other* party (evidence trail + their own message history). The banned/suspended user loses write access to `Message` (enforced in `Actions/Social/SendMessage`). The other party sees a notice: "This user's account has been suspended" (not detailed reason).
- **Login**: `status = banned` → blocked entirely, shown `banned.blade.php`. `status = suspended` → allowed to reach `under-review.blade.php` only, no other app access. `status = under_review` → same as suspended (shown review screen). Enforced via `EnsureNotBanned` middleware.

### 12.6 What NOT to build for MVP (explicitly deferred)
- Automated ML-based image moderation (use manual review queue for reported photos)
- User-facing appeal process (a simple "Your account was suspended for violating our community guidelines" notice is enough; formal appeals is a v2 feature)
- Detailed "why was I banned" transparency reports
- Reporter feedback ("your report was reviewed and action was taken") — nice to have but not MVP

---

## 13. Ghana-Specific Considerations

These adaptations exist because the target audience is Ghanaian youth seeking meaningful relationships, not a generic global user base.

### 13.1 Age Gate
**Hard legal and safety requirement.** Enforce 18+ at signup: the `date_of_birth` field is validated server-side (`before_or_equal:` today minus 18 years). Users under 18 are blocked from creating an account — not just warned, not soft-gated, fully blocked. The validation runs on both the Livewire form and the API endpoint. Do not rely on client-side validation alone.

### 13.2 Phone-First Auth
SMS OTP via a provider with strong Ghana/West Africa coverage. Africa's Talking is the primary recommendation: reliable delivery to MTN (dominant carrier), Telecel, and AirtelTigo numbers. Hubtel is a Ghana-native alternative. See §8.2 for the full auth flow and rate limiting. See §14 for the final provider decision.

Phone number validation: accept `+233XXXXXXXXX` (E.164, 12 digits total) or local format `0XXXXXXXXX` (10 digits). Normalize to E.164 server-side before storage. Reject non-Ghanaian numbers for MVP (prefix validation: `+233` only).

### 13.3 Data-Conscious Design
Mobile data is a real cost for this user base. Every page and asset must be built with bandwidth awareness:

- **Images**: server-side compression (§8.5). Serve WebP where supported. Lazy-load all images (`loading="lazy"`). On match list, load only primary photos; secondary photos load on tap into profile detail.
- **JS bundle**: Livewire + Alpine.js is already lightweight compared to a React SPA. Confirm the total JS payload is under 100KB gzipped. Do not add heavy JS libraries.
- **CSS**: Tailwind with PurgeCSS (built into Tailwind's JIT mode) — only ship used classes.
- **Low-bandwidth mode** (optional, can be v1.1): a user setting that loads text-only profile cards (name, city, score — photo loaded on tap). Store this as a cookie/localStorage preference so it persists without a DB write.
- **PWA caching**: the service worker caches the app shell so repeat visits don't re-download CSS/JS.

### 13.4 PWA Over Native for MVP
No app-store download size (critical), works across low-end Android devices (dominant in Ghana), no iOS/Android codebase split at launch. The API (§11) is already designed to support React Native/Flutter later — revisit building a native app only once product-market fit is validated.

### 13.5 Light Identity Verification
A selfie-match-to-profile-photo check at signup meaningfully reduces catfishing risk. For MVP:

- During onboarding (after photo upload, before the field wizard), the user is prompted to take a selfie.
- The selfie is stored alongside their profile photos (but not displayed publicly).
- The selfie is queued for manual admin review (`Admin/Verification/VerificationQueue`).
- Admin compares the selfie to the profile photo and approves or rejects (`verification_status` on `users`).
- Users with `verification_status = 'pending'` can complete onboarding and lock in, but **do not appear in anyone's match list** until approved (enforced in the match list query, §6).
- Users with `verification_status = 'rejected'` are prompted to re-upload their selfie.
- Do NOT build automated liveness detection or facial recognition for MVP — the manual queue is sufficient at launch scale.

### 13.6 Culturally Relevant Field Choices
Whichever library fields the admin activates (§2.2), and the option lists for each field, should be reviewed with actual Ghanaian users before launch. Things likely to need adjustment from generic Western defaults:

- **Religion**: Christianity (with denominations — Pentecostal, Catholic, Methodist, Presbyterian, etc. are culturally significant distinctions in Ghana), Islam, Traditional African Religion, other.
- **Core Values**: family involvement, community, faith, respect for elders, tradition — these may weight differently than in Western dating-app defaults.
- **Interests**: include locally relevant activities (e.g. football/soccer, fufu joints, church/mosque events, highlife/afrobeats, Ghanaian cinema).
- **Ethnicity** (if activated — see §14): Akan, Ga-Adangbe, Ewe, Dagomba, Fante, Ashanti, etc. This is culturally sensitive; the §14 decision on whether to activate it at all should be made by the human team, not defaulted by an engineer.

### 13.7 Mobile Money (future monetization)
If monetization is ever added: MTN MoMo and Telecel Cash are the dominant payment rails. Do not default to Stripe/credit-card-only checkout. Use a payment provider with MoMo integration (Paystack, Flutterwave, or Hubtel Payments all support this in Ghana).

### 13.8 Language / i18n
English is fine for MVP copy. However, **do not hardcode user-facing strings directly in Blade templates**. Use Laravel's `__()` / `@lang()` helpers and store all strings in `resources/lang/en/*.php` files. This makes future Twi/Ga/Ewe/Dagbani localization a translation task, not a codebase refactor. For field labels and option labels: these already live in the database (`field_definitions.label`, `profile_field_options.label`), so localization would mean adding a `translations` JSON column or a separate `translations` table — flag this as a v2 architecture decision, don't build it now, but don't make it impossible.

### 13.9 Ghana Data Protection Act 2012 (Act 843) Compliance
Ghana has a data protection law (Data Protection Act, 2012) enforced by the Data Protection Commission. Key requirements for MatchLock:

- **Registration**: any entity processing personal data in Ghana must register with the Data Protection Commission. The business team must do this before public launch.
- **Consent**: collect explicit consent at signup for processing personal data (a clear terms/privacy-policy acceptance checkbox, not pre-checked). Store the consent timestamp.
- **Purpose limitation**: personal data collected for matching must not be used for other purposes (e.g. marketing, selling to third parties) without separate consent.
- **Data subject rights**: users have the right to access, correct, and request deletion of their personal data. The `softDeletes` on `User` and the ability to export one's own data (a v2 feature, but don't make it architecturally impossible) support this.
- **Security**: implement reasonable security measures for personal data (HTTPS everywhere, hashed OTPs, encrypted database at rest if the host supports it, no PII in logs).
- **Breach notification**: if a data breach occurs, the Commission and affected users must be notified. This is a process/ops requirement, not a code feature, but the team should have a breach-response plan.
- **Special personal data**: Section 37 of the Act restricts processing of data relating to race, ethnicity, religion, political opinions, health, and sexual life. MatchLock collects some of these (religion, potentially ethnicity). Ensure the privacy policy explicitly covers these fields, and that consent is specific to each category of special data collected. This is another reason the Ethnicity field activation (§14) should be a deliberate human decision.

---

## 14. Open Product Questions (flag to human before deciding)

These are decisions that require human judgment, user research, or business context that a coding agent cannot resolve. **Do not make assumptions about these — ask the human team.**

1. **Which fields to activate for launch.** The starting subset suggested in §2.2 (Relationship Goal, Religion, Core Values, Love Language, Exercise Frequency, Smoking, Drinking, Interests & Hobbies, Dealbreakers) is a suggestion, not a spec. Needs real input from target users.

2. **Default match threshold.** 60% is suggested but may need tuning. Too high → empty match lists frustrating new users. Too low → noisy lists undermining the "quality over quantity" pitch. Consider starting at 50% and raising once there's a critical mass of users.

3. **`FieldDefinitionSeeder.php` needs to be written.** The uploaded seeders cover options (§2.5) but not the field-level metadata (`category`, `field_type`, default `is_active`/`weight`/`is_required`/`sort_order`). Without it, the option library has nothing to attach to. See §10 step 3 — this is early in the build.

4. **SMS OTP provider.** Africa's Talking vs. Hubtel vs. Twilio. Decision depends on: pricing per SMS in Ghana, delivery reliability to MTN/Telecel/AirtelTigo, ease of integration, sandbox/test mode availability. The code is provider-agnostic via `SmsProviderInterface` (§8.2), so this is a config decision.

5. **Selfie verification flow.** Manual admin review (recommended for MVP) vs. automated facial comparison (more expensive, complex, but scales). At what user volume does manual review become unviable? Who staffs the review queue?

6. **Ethnicity field activation.** Present in the library (§2.2/§2.5), but whether to activate it, and at what granularity, is culturally sensitive. In Ghana, ethnic identity intersects with regional identity, language, and sometimes politics. This must be a deliberate human decision with community input, not an engineering default.

7. **Who staffs moderation.** Zero-tolerance auto-suspension (§12.2) only works if reviews happen promptly. If reports sit for days, innocent users are wrongly locked out. A real person or team must own this queue at launch. Define an SLA (e.g. all zero-tolerance reports reviewed within 4 hours, standard reports within 24 hours).

8. **Message moderation delivery strategy.** Hold-before-delivery vs. deliver-and-flag (§12.4). Real tradeoff between safety and chat latency. The recommended hybrid approach (hold severe, flag mild) is a starting point — the human team should decide the severity-to-action mapping.

9. **Whether a formal appeals process is needed at launch.** Can it genuinely wait for v2? If auto-suspension produces frequent false positives (e.g. from troll reports), the lack of an appeal path may drive away legitimate users. At minimum, consider an email-based manual appeal channel even if it's not built into the app.

10. **Monetization model.** The spec deliberately defers this, but it affects architecture: if premium features are planned (e.g. extra resets, seeing who's interested before mutual match, boosted visibility), the data model may need a `subscriptions` table. Don't build it now, but confirm the business model direction so the schema doesn't need a major rework later.

11. **Content moderation keyword list.** The initial list of flagged words/patterns (§12.4) should include both English and Twi/Ga terms. This requires cultural/linguistic input, not just a generic English profanity list.

---

## 15. Testing Strategy

Every Action, Service, and critical flow must have tests before the feature is considered complete. Use Laravel's built-in testing (PHPUnit + Laravel's test helpers).

### 15.1 Unit Tests (`tests/Unit/`)
Test individual Actions and Services in isolation, with mocked dependencies where needed:

- `Actions/Matching/ComputeMatchScore` — one test per field type (§3.2), including edge cases (empty preferences, missing values, boundary scores, zero weighted fields).
- `Services/ScoringService` — per-field-type scoring functions, each with at least 3 cases (within range, at boundary, outside range).
- `Services/GeoService` — Haversine distance for known city pairs (e.g. Accra→Kumasi ≈ 252km).
- `Services/ModerationService` — flagged keywords caught, clean text passes, edge cases (partial matches, case insensitivity, Unicode).
- `Services/OtpService` — code generation, hashing, verification, expiry.
- `Actions/Social/RegisterInterest` — one-way interest, mutual match creation, duplicate interest idempotency.
- `Actions/Moderation/FileReport` — severity auto-assignment, auto-suspension for zero-tolerance.

### 15.2 Feature Tests (`tests/Feature/`)
Test full HTTP request→response flows through both Livewire and API endpoints:

- **Auth flow**: OTP request, verification, session/token creation, rate limiting.
- **Onboarding**: step-by-step wizard completion, validation errors, lock-in, redirect to matches.
- **Match list**: correct candidates shown (above threshold, passes hard filters, active + verified only), correct scores, correct sort order.
- **Interest/match**: express interest via button/API, mutual match creation, chat unlock.
- **Chat**: send message, receive message, moderation flag, cannot message non-match.
- **Reporting**: file report, zero-tolerance auto-suspension, admin review actions.
- **Admin**: field activation/deactivation, weight changes trigger recomputation.
- **Authorization**: non-admin cannot access admin routes, non-locked users cannot access match/chat routes, banned users get 403.

### 15.3 Database Factories
Create factories for all models (`database/factories/`) to support both feature tests and local development seeding. Key factory: `UserFactory` with states for `locked`, `verified`, `suspended`, `banned`, `admin`. `FieldDefinitionFactory` with states for each `field_type`.

### 15.4 Test Database
Use SQLite in-memory (`phpunit.xml` config: `DB_CONNECTION=sqlite`, `DB_DATABASE=:memory:`) for fast test runs. Run the full migration + seed in `setUp()` via `RefreshDatabase` trait.

---

## 16. Environment Variables & Configuration

Ensure the following are in `.env.example` with placeholder values and comments:

```env
# App
APP_NAME=MatchLock
APP_TIMEZONE=Africa/Accra

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=matchlock
DB_USERNAME=root
DB_PASSWORD=

# Queue
QUEUE_CONNECTION=database

# SMS Provider (africastalking | hubtel | log)
SMS_PROVIDER=log
AFRICASTALKING_USERNAME=sandbox
AFRICASTALKING_API_KEY=
AFRICASTALKING_FROM=        # sender ID, if registered
HUBTEL_CLIENT_ID=
HUBTEL_CLIENT_SECRET=
HUBTEL_FROM=

# OTP Config
OTP_EXPIRY_MINUTES=5
OTP_MAX_ATTEMPTS=5
OTP_LENGTH=6

# Matchlock Config
MATCH_DEFAULT_THRESHOLD=60
MATCH_STANDARD_REPORT_ESCALATION_COUNT=3
MATCH_MESSAGE_FLAG_ESCALATION_COUNT=3

# Image Processing
IMAGE_MAX_WIDTH=1200
IMAGE_QUALITY=75
IMAGE_DISK=local            # local | s3

# Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

---

## 17. Deployment Checklist (pre-launch)

Before the first public user touches the app:

1. **SSL/HTTPS**: enforced on all routes (`APP_URL=https://...`, `FORCE_HTTPS=true` in `AppServiceProvider`).
2. **SMS provider**: switched from `log` to real provider, delivery tested to all 3 Ghanaian carriers.
3. **Admin account**: created via `AdminUserSeeder` or manual DB insert — NOT a guessable phone number.
4. **Field configuration**: admin has activated the launch field set and configured weights via the admin UI.
5. **Moderation staffing**: at least one person is assigned to check the report queue daily (ideally within the SLAs in §14.7).
6. **Verification queue**: at least one person is assigned to review selfie verifications within 24 hours.
7. **Queue worker**: `php artisan queue:work` running as a supervised process (e.g. via Supervisor or Laravel Forge's queue management).
8. **Scheduled tasks**: `php artisan schedule:run` running via cron.
9. **Backups**: database backup configured (daily).
10. **Error monitoring**: a service like Sentry or Laravel Telescope (remove Telescope in production if it's resource-heavy — use Sentry instead).
11. **Rate limiting**: verify OTP rate limits are active and working.
12. **Privacy policy / Terms of Service**: pages exist and are linked from the signup flow. Must cover Ghana DPA 2012 requirements (§13.9).
13. **Data Protection Commission registration**: completed by the business team (§13.9).
14. **Keyword moderation list**: populated with English + relevant local-language terms (§14.11).
15. **Load test**: verify the app handles 100 concurrent users without degradation (should be trivial at MVP scale, but confirm).

---

## 18. Glossary

| Term | Definition |
|---|---|
| **Active field** | A `field_definitions` row with `is_active = true` — appears in the onboarding wizard and is used in scoring. |
| **Candidate / Target** | A user being evaluated against the viewer's preferences. |
| **Core field** | A field that is always active and not admin-togglable (display name, DOB, gender, location, photo). Lives on the `profiles`/`preferences` tables directly. |
| **EAV** | Entity-Attribute-Value — the pattern used by `profile_field_values` and `preference_field_values` to store arbitrary field data without per-field columns. |
| **Hard filter** | A field that excludes non-matching candidates entirely, before scoring runs. |
| **Library field** | A field from the configurable field library (§2.2) — stored in `field_definitions`, activated by an admin. |
| **Lock / Locked** | `users.profile_locked = true` — the user's profile and preferences are immutable. |
| **MutualMatch** | Both users have expressed interest in each other. Creates a row in the `matches` table and unlocks chat. |
| **Viewer** | The user whose match list is being computed — their preferences are used to score candidates. |
| **Weighted field** | A field that contributes to the match score (as opposed to a hard filter that gates inclusion). |

---

*This document is the single source of truth for the MatchLock MVP. If the code contradicts this spec, the code is wrong (unless a §14 decision has been made and documented by the human team). Update this document when product decisions change — don't let it drift from reality.*
