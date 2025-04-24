/* .storybook/main.js (excerpt) */
import fs   from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';
import twig from 'vite-plugin-twig-drupal';
import { viteCommonjs } from '@originjs/vite-plugin-commonjs';

const __filename = fileURLToPath(import.meta.url);
const __dirname  = path.dirname(__filename);

function fileExists(base) {
  // try “base.{js,mjs,ts,jsx}”  and “base/index.{…}”
  const exts = ['.js', '.mjs', '.ts', '.jsx'];
  return exts.some(ext => fs.existsSync(base + ext)) ||
         exts.some(ext => fs.existsSync(path.join(base, 'index' + ext)));
}

export default {
  /* ─────────────── Storybook core ─────────────── */
  core:      { builder: '@storybook/builder-vite' },
  framework: { name: '@storybook/html-vite', options: {} },

  stories:    ['../src/*/*/*.story.js'],
  staticDirs: ['../assets'],
  addons:     ['@storybook/addon-controls'],
  features:   { postcss: false },
  docs:       {},

  /* ──────────────── Vite config ──────────────── */
  async viteFinal(config) {
    /* 1 · plugins — identical to what you already have … */
    config.plugins = [
      twig({
        namespaces: { 'oe-bcl': path.resolve(__dirname, '../src/components') },
        paths: [
          path.resolve(__dirname, '../src/components'),
          path.resolve(__dirname, '../src/compositions'),
        ],
        environmentPath: path.resolve(__dirname, 'environment.mjs'),
      }),
      viteCommonjs({ include: ['src/data/**/data.js'] }),
      ...(config.plugins ?? []),
    ];

    /* 2 · dev-server FS allow-list — unchanged … */
    config.server = {
      ...(config.server ?? {}),
      fs: { allow: [path.resolve(__dirname, '..')] },
    };

    /* 3 · aliases */
    const smartAlias = {
      find: /^@oe-bcl\/(.*)$/,
      replacement(id) {
        const rel   = id.replace(/^@oe-bcl\//, '');
        const comp  = path.resolve(__dirname, `../src/components/${rel}`);
        const compo = path.resolve(__dirname, `../src/compositions/${rel}`);

        return fileExists(comp) ? comp : compo;
      },
    };

    config.resolve = {
      ...(config.resolve ?? {}),
      alias: [
        smartAlias,
        /*  ───── everything else you already had ─────  */
        { find: /^@openeuropa\/bcl-story-utils$/,   replacement: path.resolve(__dirname, '../tools/story-utils') },
        { find: /^@openeuropa\/bcl-theme-default\/(.*)$/, replacement: path.resolve(__dirname, '../node_modules/@openeuropa/bcl-theme-default/$1') },
        { find: /^@openeuropa\/bcl-data-utils$/,   replacement: path.resolve(__dirname, '../src/data/utils/index.mjs') },
        { find: /^@openeuropa\/bcl-data-([^/]+)(?:\/(.*))?$/, replacement: (_m, pkg, rest = 'data.js') => path.resolve(__dirname, `../src/data/${pkg}/${rest}`) },
        { find: /^@openeuropa\/(bcl-[^/]+)\/data\.js$/,   replacement: path.resolve(__dirname, '../src/compositions/$1/data/data.js') },
        { find: /^@openeuropa\/(bcl-[^/]+)\/data\/(.*)$/, replacement: path.resolve(__dirname, '../src/compositions/$1/data/$2') },
        { find: /^@openeuropa\/(bcl-[^/]+)\/(.*)$/, replacement: path.resolve(__dirname, '../src/components/$1/$2') },
      ],
    };

    return config;
  },
};
