import { Buffer } from 'buffer';

global.Buffer = Buffer;

export const parameters = {
  a11y: {
    element: "#storybook-root",
    config: {},
    options: {
      checks: { "color-contrast": { options: { noScroll: true } } },
      restoreScroll: true,
    },
    manual: false,
  },  
  docs: {
    canvas: { sourceState: 'shown' },
  },
  viewMode: 'story',
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
      order: [
        "Components",
        "Compositions",
        "Paragraphs",
        "Features"
      ],
    },
  },
};
export const tags = ["autodocs"];
