-- My Login Form — Licensing schema: add product scoping
--
-- Run this AFTER 01_licenses.sql and 02_activations.sql already exist in
-- production. Paste into Supabase Dashboard → SQL Editor → New Query → Run.
--
-- Until now, `licenses` had no record of *which* product a key was sold
-- for, and the Edge Function's activate/validate never checked one either —
-- so a key issued for one product line (e.g. My Login Form) would silently
-- also activate any other product line built against this same backend
-- (e.g. Omega Design). This closes that gap.
--
-- Every existing row predates multi-product support and was sold as My
-- Login Form, so the backfill default keeps every current paying
-- customer's license working exactly as before, with zero action needed
-- on their end.

alter table public.licenses
    add column if not exists product text not null default 'my-login-form';

-- No default going forward — every NEW row must explicitly say which
-- product it's for (handleGenerate() in the Edge Function now requires
-- it). Better to fail loudly on a missing product than silently default a
-- brand-new product line's licenses to 'my-login-form'.
alter table public.licenses
    alter column product drop default;

create index if not exists licenses_product_idx
    on public.licenses (product);
