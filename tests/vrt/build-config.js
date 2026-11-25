const fs = require("fs");
const path = require("path");

const base = JSON.parse(fs.readFileSync(path.resolve("backstop.json"), "utf8"));

const scenariosDir = path.join(__dirname, "scenarios");
const scenarioFiles = fs.readdirSync(scenariosDir).filter(f => f.endsWith(".json"));

const scenarios = scenarioFiles.flatMap(f =>
  JSON.parse(fs.readFileSync(path.join(scenariosDir, f), "utf8"))
);

const out = { ...base, scenarios };
fs.writeFileSync(path.join(__dirname, "backstop.generated.json"), JSON.stringify(out, null, 2));
console.log(`Generated backstop.generated.json with ${scenarios.length} scenarios`);
