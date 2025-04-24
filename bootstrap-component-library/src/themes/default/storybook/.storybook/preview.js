export const parameters = {
  a11y: {
    element: "#storybook-root",
    config: {
      rules: [
        {
          id: "aria-input-field-name",
          selector: ".ss-list",
        },
        {
          id: "landmark-unique",
          selector: ".shadow-sm",
        },
      ],
    },
    options: {
      checks: { "color-contrast": { options: { noScroll: true } } },
      restoreScroll: true,
    },
    manual: false,
  },
  controls: { expanded: true },
  layout: "padded",
  viewport: {
    defaultViewport: "responsive",
    viewports: {
      responsive: {
        name: "responsive",
        styles: {
          width: "100%",
          height: "100%",
          border: 0,
          margin: 0,
          boxShadow: "none",
          borderRadius: 0,
          position: "absolute",
        },
      },
    },
  },
  options: {
    storySort: {
      method: "alphabetical",
      order: ["Components", "Compositions", "Paragraphs", "Features"],
    },
  },
};
export const tags = ["autodocs"];
