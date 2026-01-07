# Product Bulk Edit Plugin for Botble CMS

This plugin adds Excel-like bulk editing functionality to your Botble eCommerce products.

## Features

- **Excel-like Interface**: Edit multiple products simultaneously using a spreadsheet interface
- **Bulk Operations**: Update prices, quantities, SKUs, and other product attributes in bulk
- **Real-time Changes Tracking**: See exactly what has been modified before saving
- **Filter & Search**: Filter products by category, brand, or search term
- **Pagination**: Handle large product catalogs efficiently
- **Undo/Redo**: Revert changes with built-in undo/redo functionality
- **Bulk Delete**: Delete multiple products at once
- **Dropdown Selectors**: Easy selection for brands, categories, taxes, and statuses

## Installation

### 1. Copy Plugin Files

Copy the `product-bulk-edit` folder to your Botble CMS plugins directory:
```
platform/plugins/product-bulk-edit/
```

### 2. Download Handsontable Library

Download the Handsontable CE (Community Edition) library:

1. Visit: https://github.com/handsontable/handsontable/releases
2. Download the latest release (version 12.x or higher recommended)
3. Extract the files and copy the following to `platform/plugins/product-bulk-edit/public/`:
   - `handsontable.full.min.js`
   - `handsontable.full.min.css`

Or use CDN by modifying the controller to load from CDN:
```javascript
// In ProductBulkEditController.php, replace local files with:
->addScriptsDirectly([
    'https://cdn.jsdelivr.net/npm/handsontable@12.3.1/dist/handsontable.full.min.js',
    'vendor/core/plugins/product-bulk-edit/js/product-bulk-edit.js',
])
->addStylesDirectly([
    'https://cdn.jsdelivr.net/npm/handsontable@12.3.1/dist/handsontable.full.min.css',
    'vendor/core/plugins/product-bulk-edit/css/product-bulk-edit.css',
])
```

### 3. Publish Assets

Run the following command to publish plugin assets:
```bash
php artisan vendor:publish --tag=cms-public --force
```

### 4. Activate Plugin

1. Go to Admin Panel → Plugins
2. Find "Product Bulk Edit" plugin
3. Click "Activate"

### 5. Set Permissions

1. Go to Admin Panel → System → Users → Roles
2. Edit the roles that should have access
3. Enable permissions under "Product Bulk Edit"

## Usage

### Accessing the Bulk Editor

1. Navigate to Admin Panel → E-Commerce → Product Bulk Edit
2. Use filters to narrow down products (optional):
   - Search by name or SKU
   - Filter by category
   - Filter by brand
3. Click "Load Products"

### Editing Products

1. Click on any cell to edit its value
2. Use keyboard shortcuts:
   - `Tab` or `Arrow keys` to navigate
   - `Ctrl+Z` to undo
   - `Ctrl+Y` to redo
   - `Ctrl+C`, `Ctrl+V` to copy and paste
3. Select multiple cells and drag to fill down
4. Check the checkbox column to select products for deletion

### Saving Changes

1. Make your edits in the spreadsheet
2. The "Changes" badge shows how many products have been modified
3. Click "Save Changes" to commit all modifications
4. Changes are saved in a single transaction

### Editable Fields

- **Name**: Product name
- **SKU**: Stock Keeping Unit
- **Price**: Regular price
- **Sale Price**: Discounted price
- **Quantity**: Stock quantity
- **Manage Stock**: Enable/disable stock management
- **Stock Status**: in_stock, out_of_stock, on_backorder
- **Status**: published, draft, pending
- **Brand**: Select from available brands
- **Tax**: Select applicable tax
- **Dimensions**: Weight, Length, Width, Height

## Tips & Tricks

### Keyboard Shortcuts
- `Ctrl+C` / `Ctrl+V` - Copy and paste
- `Ctrl+Z` / `Ctrl+Y` - Undo and redo
- `Tab` / `Shift+Tab` - Move between cells
- `Enter` - Move to cell below
- `Arrow keys` - Navigate cells

### Bulk Operations
1. **Copy Down**: Select a cell, copy it, select multiple cells below, and paste
2. **Fill Handle**: Click and drag the small square in the bottom-right corner of a cell
3. **Column Operations**: Click column header to select entire column

### Performance Tips
- Load products in smaller batches using filters
- Save changes frequently to avoid losing work
- Use search to focus on specific product groups

## Troubleshooting

### Spreadsheet Not Loading
- Ensure Handsontable library files are properly installed
- Check browser console for JavaScript errors
- Verify assets are published correctly

### Changes Not Saving
- Check user permissions
- Verify CSRF token is valid
- Check server logs for errors

### Slow Performance
- Reduce products per page in controller (`$perPage` variable)
- Apply filters to load fewer products
- Check database indexes on products table

## Security

- All operations require proper permissions
- CSRF protection enabled
- Input validation on server-side
- Supports Laravel's authorization system

## Compatibility

- Botble CMS 7.x and above
- Laravel 10.x and above
- PHP 8.1 and above
- Modern browsers (Chrome, Firefox, Safari, Edge)

## Support

For issues, feature requests, or questions:
1. Check Botble documentation
2. Contact support at your Botble purchase channel

## License

This plugin follows the same license as your Botble CMS installation.

---

**Note**: This plugin requires the Ecommerce plugin to be installed and activated.
