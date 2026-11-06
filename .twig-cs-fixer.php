<?php

use TwigCsFixer\Rules\Literal\HashQuoteRule;

$rules = [
  new HashQuoteRule(),
];

$ruleset = new TwigCsFixer\Ruleset\Ruleset();
foreach ($rules as $rule) {
  $ruleset->addRule($rule);
}
$config = new TwigCsFixer\Config\Config();
$config->setRuleset($ruleset);

return $config;
