-- My Login Form — Licensing schema: human-readable product name
--
-- Run this AFTER 01-04 already exist in production. Paste into Supabase
-- Dashboard → SQL Editor → New Query → Run.
--
-- `product` (added by 03_) stays a plain slug ('omega-design' /
-- 'my-login-form') because that's what code compares against - this adds
-- `product_name` as a GENERATED column derived FROM it, purely so the
-- table is readable at a glance in Supabase Studio ("Omega Design Theme"
-- instead of "omega-design") without maintaining a second column by hand.
-- Being generated means it can never drift out of sync with `product` -
-- Postgres recomputes it automatically on every insert/update, for every
-- existing row and every new one, with no application code changes needed
-- anywhere (Edge Function, plugin, or theme).
--
-- Add a new WHEN branch here if a third product line ever joins this
-- backend - until then, anything other than the two known slugs falls
-- back to showing the raw slug itself instead of silently showing nothing.

alter table public.licenses
    add column if not exists product_name text
    generated always as (
        case product
            when 'omega-design'  then 'Omega Design Theme'
            when 'my-login-form' then 'My Login Form Plugin'
            else product
        end
    ) stored;
