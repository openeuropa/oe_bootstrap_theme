#!/usr/bin/env node

// Switched from Lerna to PNPM workspace commands
// ------------------------------------------------
// Every previous call like:
//   lerna --scope @openeuropa/bcl-theme-${theme} run <script>
// is now:
//   pnpm -w --filter @openeuropa/bcl-theme-${theme} run <script>
// "-w" (or "--workspace-root") guarantees the command executes from the
// workspace root where package.json & pnpm-workspace.yaml live.
// ------------------------------------------------

const { spawn } = require("child_process");
const browsersync = require("browser-sync");
const path = require("path");

// instance comes from `npm run watch <instance>` (default, joinup, ucpkn, dev)
const instance = process.argv.slice(3)[0] || "default";

const ports = {
  dev: "5000",
  default: "5001",
  joinup: "5002",
  ucpkn: "5003",
};

// When instance === "dev" we still want to build the default theme assets
const theme = instance === "dev" ? "default" : instance;
const port = ports[instance];

// ------------------------------------------------
// Utility: spawn a child process and reload BrowserSync when done
// ------------------------------------------------
function runCommand({ cmd, args, name, message, reload }) {
  const subprocess = spawn(cmd, args, { stdio: "inherit" });
  subprocess.on("error", (err) => bs.notify(`${name}: ${err.message}`));
  subprocess.on("exit", (code) => {
    const status = code === 0 ? message : `${name} failed with code ${code}`;
    bs.notify(status);
    if (code === 0 && reload) {
      bs.reload(reload);
    }
  });
}

// ------------------------------------------------
// File‑watch handlers
// ------------------------------------------------
const handlers = [
  // ---------- SCSS ----------
  {
    pattern: `${path.resolve(__dirname, "..")}/src/themes/*/src/scss/**/*.scss`,
    events: [
      {
        on: "change",
        name: "scss change",
        command: `pnpm -w --filter @openeuropa/bcl-theme-${theme} run update:styles`,
        message: "New styles ready",
        reload: "*.css",
      },
      {
        on: "change",
        name: "color-scheme update",
        command: `pnpm -w --filter @openeuropa/bcl-theme-${theme} run update:color-scheme`,
        message: "Color&nbsp;scheme rebuilt",
        reload: "*.css",
      },
    ],
  },
  // ---------- JS ----------
  {
    pattern: `${path.resolve(__dirname, "..")}/src/themes/*/src/js/**/*.js`,
    events: [
      {
        on: "change",
        name: "javascript change",
        command: `pnpm -w --filter @openeuropa/bcl-theme-${theme} run update:scripts`,
        message: "New scripts ready",
        reload: true,
      },
    ],
  },
  // ---------- Global Twig templates ----------
  {
    pattern: `${path.resolve(__dirname, "..")}/src/(components|compositions)/*/*.twig`,
    events: [
      {
        on: "change",
        name: "twig template change",
        command: `pnpm -w --filter @openeuropa/bcl-theme-${theme} run build:copy`,
        message: "Templates rebuilt",
        reload: true,
      },
    ],
  },
  // ---------- Theme‑specific Twig overrides ----------
  {
    pattern: `${path.resolve(__dirname, "..")}/src/themes/*/src/templates/*.twig`,
    events: [
      {
        on: "change",
        name: "twig template override change",
        command: `pnpm -w --filter @openeuropa/bcl-theme-${theme} run build:copy`,
        message: "Templates rebuilt",
        reload: true,
      },
    ],
  },
];

// ------------------------------------------------
// BrowserSync initialisation
// ------------------------------------------------
const bs = browsersync.create();

handlers.forEach((handler) => {
  bs.watch(handler.pattern, (event, file) => {
    handler.events.forEach(({ on, name, command, message, reload }) => {
      if (on === event) {
        bs.notify(`${event}: ${file}`);
        const [cmd, ...args] = command.split(" ");
        runCommand({ cmd, args, name, message, reload });
      }
    });
  });
});

bs.init({ open: true, proxy: `localhost:${port}` });
