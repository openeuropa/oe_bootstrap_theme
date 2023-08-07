# OpenEuropa Bootstrap theme : Featured Media Field
This module provides functionalities like FieldFormatters dedicated to **Featured media** field (oe_featured_media).

Image styles <sub>/admin/config/media/image-styles</sub> will now be available with this feature.

## Installation
After enabling this module, additional display Plugins will be available.

### As a field in content
If you want to render the field as a traditional field of type Image, use the "Image" (oe_featured_media_image) plugin.

### As an ImageValueObject in pattern
This case happens when you are using "UI Patterns Layouts" module for sample.
If you want to use the field inside a pattern using that is waiting an ImageValueObject object (sample patterns `card` and `content_banner`), use the "ImageValueObject for pattern" (oe_featured_media_imageobjectvalue).
