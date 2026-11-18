# Google Sheets — quote / contact form integration

**Every** form and popup on the site posts to a single **Google Apps Script
Web App** which appends a row to a Google Sheet. No PHP backend, no API keys
to store. One endpoint powers them all.

Each submission carries a `source` field so you can tell them apart / filter
in the sheet:

| `source`         | Where it comes from                          |
| ---------------- | -------------------------------------------- |
| `home-quote`     | "Request a Free Quote" form on the home page |
| `quote-popup`    | Phone-only popup (home + category pages)     |
| `contact-page`   | Contact page form                            |
| `product-<slug>` | "Request a Quote" on a product page          |
| `newsletter-blog`| Newsletter signup (blog article page)        |

Every submission also includes `timestamp` and `page_url` automatically.

## 1. Create the sheet

1. Go to [sheets.google.com](https://sheets.google.com) → **Blank**.
2. Name it e.g. `Green Divine — Website Leads`.
3. In **Row 1**, add headers in this exact order (extra fields are appended automatically, but these will be picked up reliably):

   `timestamp | source | name | phone | email | message | product | product_slug | area | interest | page_url`

## 2. Add the Apps Script

1. In the sheet: **Extensions → Apps Script**.
2. Replace the default `Code.gs` with the script below.
3. **Save**.

```js
const SHEET_NAME = 'Sheet1'; // change if you renamed the tab

function doPost(e) {
  try {
    const data = JSON.parse(e.postData.contents || '{}');
    const sheet = SpreadsheetApp.getActive().getSheetByName(SHEET_NAME);
    if (!sheet) throw new Error('Sheet tab not found: ' + SHEET_NAME);

    // Use the header row to keep column order stable
    const headers = sheet.getRange(1, 1, 1, sheet.getLastColumn()).getValues()[0];
    const row = headers.map(h => (h in data ? data[h] : ''));

    // Append unknown keys to the end so nothing gets lost
    Object.keys(data).forEach(k => {
      if (headers.indexOf(k) === -1) row.push(k + '=' + data[k]);
    });

    sheet.appendRow(row);
    return ContentService
      .createTextOutput(JSON.stringify({ ok: true }))
      .setMimeType(ContentService.MimeType.JSON);
  } catch (err) {
    return ContentService
      .createTextOutput(JSON.stringify({ ok: false, error: String(err) }))
      .setMimeType(ContentService.MimeType.JSON);
  }
}

// Optional: makes the URL testable in a browser tab
function doGet() {
  return ContentService.createTextOutput('Green Divine endpoint OK');
}
```

## 3. Deploy as a Web App

1. **Deploy → New deployment**.
2. **Type** = `Web app`.
3. Description: `Green Divine forms`.
4. **Execute as**: *Me*.
5. **Who has access**: *Anyone* (required so the public site can POST).
6. Click **Deploy** → authorise the script when prompted.
7. Copy the **Web app URL** — it looks like
   `https://script.google.com/macros/s/AKfycb.../exec`.

## 4. Paste the URL into the site

Open [`data.php`](../data.php) and update:

```php
$DATA['config'] = [
    'google_sheet_endpoint' => 'PASTE_YOUR_WEB_APP_URL_HERE',
    // ...
];
```

Reload the site. Forms now save directly to your sheet — verify by submitting
the home page quote form once.

## 5. Re-deploying after a script change

Apps Script Web Apps are versioned. Each time you edit `Code.gs`:

- **Deploy → Manage deployments → ✏ Edit (pencil) → Version: New version → Deploy**.

The URL stays the same, so no PHP change is required.

## Troubleshooting

| Symptom                                  | Cause / fix                                                                                          |
| ---------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| Form shows the red error message         | The endpoint URL is wrong, the deployment access isn't `Anyone`, or the script threw — check **Executions** in Apps Script. |
| Rows have empty cells                    | Header text in Row 1 doesn't match the JSON keys exactly (case-sensitive).                            |
| CORS error in browser console            | Apps Script Web Apps need `Content-Type: text/plain` (the site already uses this). Confirm you didn't add custom headers. |
| Success message but no row appears       | You may have re-deployed without bumping the version — repeat step 5.                                 |
