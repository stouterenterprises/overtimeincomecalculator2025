# Release & ZIP Instructions

## Upload as a Folder
1. Download the GitHub repository.
2. Ensure the folder is named `overtime-deduction-calculator-2025`.
3. Upload the folder to `/wp-content/plugins/`.
4. Activate the plugin in **WP Admin → Plugins**.
5. Visit **Pages → 2025 Overtime Deduction Calculator**.

## Upload as a ZIP
1. Zip the entire `overtime-deduction-calculator-2025` folder.
2. In WP Admin, go to **Plugins → Add New → Upload Plugin**.
3. Select the ZIP and activate.

## Verify It’s Working
- The calculator page should render the tool.
- Tools → **2025 Overtime Deduction Calculator** should show links and the recreate button.
- Export buttons should download PDF/CSV/JSON/HTML.

## Troubleshooting
- **Permalinks**: If the page link fails, go to **Settings → Permalinks** and click **Save Changes**.
- **REST/AJAX blocked**: Ensure admin-ajax.php requests are not blocked by security plugins or firewalls.
- **PDF issues**: Verify `vendor/odc-pdf/ODC25_Pdf.php` is present and readable.
- **Deleted page**: Use **Tools → 2025 Overtime Deduction Calculator → Recreate Calculator Page**.
