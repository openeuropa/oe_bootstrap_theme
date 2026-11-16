/**
 * @file
 * Message API.
 */
((Drupal, drupalSettings) => {
  /**
   * Overrides message theme function.
   *
   * @param {object} message
   *   The message object.
   * @param {string} message.text
   *   The message text.
   * @param {string} [message.heading]
   *   The optional heading for the message.
   * @param {object} options
   *   The message context.
   * @param {string} [options.type]
   *   The message type.
   * @param {string} [options.id]
   *   ID of the message, for reference.
   * @param {boolean} [options.dismissible=true]
   *   Whether the message can be dismissed by the user.
   * @param {boolean} [options.animatedDismiss=true]
   *   Whether to apply animation when the message is dismissed.
   * @param {string} [options.iconName]
   *   The icon name.
   * @param {object} [options.attributes={}]
   *   Additional attributes to apply to the message element.
   *
   * @return {HTMLElement}
   *   A DOM element containing the message.
   */
  Drupal.theme.message = ({ text, heading = "" }, { type = "", id, dismissible = true, animatedDismiss = true, iconName = "", attributes = {} }) => {
    const messagesTypes = {
      status: "success",
      warning: "warning",
      error: "danger"
    };

    const iconNames = {
      success: "check-circle-fill",
      warning: "exclamation-triangle-fill",
      danger: "dash-circle-fill",
      light: "info-circle-fill",
      info: "info-circle-fill",
      dark: "info-circle-fill"
    };

    const iconPath = drupalSettings.bcl_icon_path;
    const variant = messagesTypes[type] || type;

    // Classes for the alert container
    const messageClasses = ["alert", `alert-${variant}`, "d-flex", "align-items-center", "text-dark", dismissible && "alert-dismissible", animatedDismiss && "fade show"].filter(
      Boolean
    );

    // Classes for the icon
    const iconClasses = ["flex-shrink-0", "me-3", "mt-1", "align-self-start", "bi", "icon--s", variant !== "light" ? `text-${variant}` : ""].filter(Boolean);

    // Create the main alert div
    const messageWrapper = document.createElement("div");
    messageWrapper.className = messageClasses.join(" ");

    messageWrapper.setAttribute("role", type === "error" || type === "warning" ? "alert" : "status");
    messageWrapper.setAttribute("aria-labelledby", `${id}-title`);
    messageWrapper.setAttribute("data-drupal-message-id", id);
    messageWrapper.setAttribute("data-drupal-message-type", type);
    if (attributes) {
      Object.keys(attributes).forEach(key => {
        messageWrapper.setAttribute(key, attributes[key]);
      });
    }

    if (iconPath) {
      const finalIconName = iconName || iconNames[variant];
      const svgIconHtml = `
            <svg class="${iconClasses.join(" ")}">
             <use xlink:href="${iconPath}#${finalIconName}"></use>
            </svg>
           `;
      messageWrapper.innerHTML += svgIconHtml;
    }

    const messageContent = document.createElement("div");
    messageContent.className = "alert-content flex-grow-1";
    messageContent.innerHTML = text;
    messageWrapper.appendChild(messageContent);

    if (heading) {
      const messageHeader = document.createElement("div");
      messageHeader.className = "alert-heading h4";
      messageHeader.innerHTML = heading;
      messageContent.appendChild(messageHeader);
    }

    if (dismissible) {
      const closeButton = document.createElement("button");
      closeButton.type = "button";
      closeButton.className = "btn-close";
      closeButton.setAttribute("data-bs-dismiss", "alert");
      closeButton.setAttribute("aria-label", Drupal.t("Close message"));

      closeButton.addEventListener("click", () => {
        messageWrapper.classList.add("hidden");
      });
      messageWrapper.appendChild(closeButton);
    }

    return messageWrapper;
  };
})(Drupal, drupalSettings);
