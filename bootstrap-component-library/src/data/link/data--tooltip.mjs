const drupalAttribute = require("drupal-attribute");

export default {
  label: "A link with tooltip",
  path: "/example.html",
  attributes: new drupalAttribute()
    .setAttribute("title", "This is a tooltip")
    .setAttribute("data-bs-toggle", "tooltip")
    .setAttribute("role", "button"),
};
