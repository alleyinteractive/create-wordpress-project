# Theme Navigation
Allows users to display a menu that is attached to a specific menu location.

## Usage
1. Register menu location.
2. Create a menu and assign it to a menu location.
3. Add the block to a page and select the menu location to display via the block settings.

## Styling
Each block is rendered with a data attribute of the location that is being targeted. This allows
for custom styling of each menu location.

For example, targeting the menu location `header` would look like this:

```css
.wp-block-create-wordpress-plugin-theme-navigation[data-location="header"] {
	background-color: #0a4b78;
}
```

## Props
| Name         | Type   | Default | Required | Description                  |
|--------------|--------|---------|----------|------------------------------|
| menuLocation | string |         | No       | The menu location to target. |
