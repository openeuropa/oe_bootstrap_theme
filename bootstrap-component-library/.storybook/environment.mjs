/* .storybook/environment.mjs
 * – Storybook-side Twig environment
 * – Adds create_attribute(), get_dummy_text(),
 *   and re-defines the “empty” test so a plain {} counts as empty.
 */

import path from 'path';
import { fileURLToPath } from 'url';

import { getDummyText } from '@openeuropa/bcl-data-utils';
import {
  TwingEnvironment,
  TwingLoaderFilesystem,
  TwingFunction,
  TwingTest,
} from 'twing';
import DrupalAttribute from 'drupal-attribute';

/* ── paths ──────────────────────────────────────────────────────────── */
const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);

const projComponentsAbsPath   = path.resolve(__dirname, '../src/components');
const projCompositionsAbsPath = path.resolve(__dirname, '../src/compositions');

/* ── filesystem loader ──────────────────────────────────────────────── */
const loader = new TwingLoaderFilesystem(projComponentsAbsPath);

/* In Storybook the loader may be TwingLoaderNull, so test before addPath */
if (typeof loader.addPath === 'function') {
  loader.addPath(projComponentsAbsPath,  'oe-bcl');
  loader.addPath(projCompositionsAbsPath,'oe-bcl');
}

/* ── custom Twig functions ──────────────────────────────────────────── */
const createAttributeFn = new TwingFunction(
  'create_attribute',
  () => new DrupalAttribute()
);

const dummyTextFn = new TwingFunction(
  'get_dummy_text',
  (count = 1, paragraph = false, paragraphs = false, classes = '') =>
    getDummyText(count, paragraph, paragraphs, classes)
);

/* ── build the environment ──────────────────────────────────────────── */
const environment = new TwingEnvironment(loader, { autoescape: false });
environment.addFunction(createAttributeFn);
environment.addFunction(dummyTextFn);

/* ── PATCH: make plain {} evaluate as “empty” ───────────────────────── */
const emptyTest = new TwingTest('empty', (value) => {
  /* original Twig semantics */
  if (
    value === null ||
    value === undefined ||
    (Array.isArray(value) && value.length === 0) ||
    (value instanceof Map && value.size === 0) ||
    (value instanceof Set && value.size === 0) ||
    (typeof value === 'string'  && value === '') ||
    (typeof value === 'boolean' && value === false)
  ) {
    return true;
  }

  /* extra rule for Storybook: a vanilla object with no own keys */
  return (
    typeof value === 'object' &&
    value?.constructor === Object &&
    Object.keys(value).length === 0
  );
});

/* register AFTER the environment exists so we replace the default test */
environment.addTest(emptyTest);

export default environment;   // ← importable everywhere via ESM
