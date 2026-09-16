-- My Login Form — Licensing schema: update plan default
--
-- Run this AFTER 01-03 already exist in production. Paste into Supabase
-- Dashboard → SQL Editor → New Query → Run.
--
-- Only two plans are actually sold now: '1-year' and 'lifetime' (6-month
-- and 3-year were dropped - see the Edge Function's handleGenerate()).
-- '01_licenses.sql' being re-run never touches the live column default,
-- since that file only ever CREATEs the table if it doesn't already exist -
-- this is the actual fix for the already-deployed table.
--
-- Purely a correctness/tidiness fix, not a functional one: the Edge
-- Function always sets `plan` explicitly on every insert, so this default
-- was never actually used in practice. No existing rows are touched -
-- already-issued licenses keep whatever plan/expires_at they were sold
-- with, regardless of this change.

alter table public.licenses
    alter column plan set default '1-year';
