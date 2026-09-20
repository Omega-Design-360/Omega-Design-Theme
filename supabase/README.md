# Omega Design — Licensing

This theme is gated behind a license key, checked against a shared Supabase
Edge Function that also serves a separate product (My Login Form). See
`includes/core/license.php` for the actual client-side logic — this folder
is just the schema this theme's licensing depends on, kept here for
reference since this theme's own repo is public.

**What's deliberately NOT here:** the Edge Function's source, the
`generate`/`renew` actions, `LICENSE_GENERATE_SECRET`, and the WooCommerce
store-integration script that mints keys on purchase. Those live in a
separate, private repo (the licensing backend), never in a theme
distributed publicly — see the comments at the top of that backend's own
`woocommerce-license-generator.php` for why.

## Schema (`sql/`)

`01_licenses.sql`, `02_activations.sql`, `05_add_product_column.sql`,
`06_update_plan_default.sql`, `07_add_product_name_column.sql` — run in
that order, once, against the shared Supabase project (the 03/04 slots
belong to a separate, unrelated My Login Form table pair - users/
subscriptions - not needed here and not kept in this repo). `licenses.product`
(added by `05_`) is what keeps a My Login Form key and an Omega Design key
from being able to activate each other, despite sharing one table.
`licenses.product_name` (added by `07_`) is a generated column derived
from `product` - just a readable label for Supabase Studio, always in
sync automatically, never written to directly. Only two plans are ever
sold — `1-year` and `lifetime` — enforced by the Edge Function's
`generate`/`renew` actions, not by a database constraint.

## How this theme talks to it

`includes/core/license.php`:

- `API_URL` — the Edge Function's public URL. Safe to hardcode (it's an
  endpoint address, not a secret) — the actual protection is the license
  key itself plus server-side product-scoping.
- `PRODUCT` — sent as `'omega-design'` on every `activate`/`validate` call,
  so a key sold for the other product is correctly rejected.
- Only ever calls `activate`, `validate`, `deactivate` — never `generate`/
  `renew`, which require a secret this theme never holds.
- A failed *request* (network error, the function briefly down) never
  changes the stored license status — only a definitive `valid:true`/
  `valid:false` *response* does. See the class docblock for the full
  reasoning.

## If you need to change something on the backend

That's a separate, private project — talk to whoever maintains the
licensing backend (Edge Function + store integration), not this repo.
