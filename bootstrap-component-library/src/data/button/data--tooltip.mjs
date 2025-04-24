const drupalAttribute = require("drupal-attribute");

export default {
  label: "Button with tooltip",
  attributes: new drupalAttribute()
    .setAttribute("autocomplete", "off")
    .setAttribute("data-bs-toggle", "tooltip")
    .setAttribute("title", "This is a tooltip"),
};
