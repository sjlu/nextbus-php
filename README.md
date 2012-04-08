### index.php/nextbus/get_config/<agency>

This is will get the entire route configuration for a specific agency tag. This will split it up by stops and lines so that way you have an easy way to parse the data.

### index.php/nextbus/get_predictions/<agency>

This will get all prediction times for every single stop, route and direction. It is organized by route->stop_tag.
