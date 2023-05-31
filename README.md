# Plugin Name: Bands Importer

Plugin URI:
Description: Plugin to import bands and genres from a JSON file.
Version: 1.0
Author: Cup O Code
License: GPL2

## Overview

The Bands Importer plugin is a WordPress plugin that allows you to import bands and their genres from a JSON file. It provides a custom admin menu page where you can trigger the import process. The plugin uses the JSON file located in the plugin directory to retrieve the band data and creates corresponding posts in the WordPress database.

## Installation

To install the Bands Importer plugin, follow these steps:

1. Download the plugin ZIP file.
2. In your WordPress admin panel, navigate to "Plugins" > "Add New".
3. Click the "Upload Plugin" button and choose the downloaded ZIP file.
4. Click "Install Now" and then activate the plugin.

## Importing Bands

To import bands and genres using the Bands Importer plugin, follow these steps:

1. Ensure that you have a valid JSON file containing the band data. The file should be named "bands.json" and be located in the plugin directory.
2. In the WordPress admin panel, navigate to "Bands Importer" under the "Bands Importer" menu.
3. Click the "Import Bands" button to initiate the import process.
4. The plugin will read the JSON file, create a post for each band, and store the band genre and website as post meta.

## Customization

The Bands Importer plugin is designed to work with a specific JSON file structure. The JSON file should have an array of objects, where each object represents a band. Each band object should have the following properties:

- "Band Name": The name of the band.
- "Genre": The genre of the band.
- "Website": The website URL of the band.

If your JSON file has a different structure or property names, you can modify the plugin code in the `bands_importer_import_bands` function to match your JSON file structure.

## Support

If you encounter any issues or have questions regarding the Bands Importer plugin, you can reach out to the plugin author, Cup O Code.

## License

The Bands Importer plugin is licensed under GPL2. You are free to modify and distribute this plugin according to the terms of the GPL2 license.
