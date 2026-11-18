# Regional SEO and Static Pages

Both admin flows use Global / Regions / Countries choices sourced from the existing region configuration, including custom regions and disabled/deleted overrides. The existing named Global market is treated as the Global scope.

## SEO Meta

Choose a region in the scope selector, then choose Homepage, a standalone/static page, or a catalogue entity combination. All existing content, translations, social metadata and robots fields remain available. Regional rows use `region_code`, a null `market_id`, and a namespaced SEO lookup key (`market_code=region:<code>`). This key is only for SEO; it does not change the commerce market or stock filters.

Duplicate detection, AJAX brand eligibility, list filtering and editing respect the selected scope. Regional canonical URLs default to the configured region route when the field is blank. Explicit canonical overrides remain editable. SEO keeps its existing specificity and Global fallback rules. Inherited Global SEO does not make an unauthored regional page indexable.

## Static Pages

Choose a region, copy a source if it has no sections yet, edit sections and save the publish setting. Regional sections and settings have `region_code` and a null `location_id`; country rows retain their existing country ObjectId. Copying replaces only the chosen target scope, preserves stored translation documents, and rejects copying a scope to itself.

Static content uses exact regional sections and its independent publish setting. It does not borrow the SEO record or automatically copy Global sections. Unpublished regional sections are not returned by the public content API. Existing country behavior remains unchanged.

## Website and sitemap

Regional home, catalogue and supported static page routes read the selected region. Canonicals support built-in paths such as `/europe` and custom `/region/<code>` paths. Authored exact regional SEO controls indexing; unauthored regional pages remain noindex. Catalogue filter variants retain their existing indexing restrictions.

The generated sitemap discovers active available regions and includes authored indexable standalone/static regional pages. The existing uploaded sitemap remains authoritative when present; add regional URLs to that upload using the existing sitemap workflow. No uploaded sitemap is silently replaced.

## Local testing

1. Restart the local Go API after these changes, and keep Next.js running with the local API configured.
2. Open the admin SEO Meta Create screen: confirm Global, Regions and Countries groups and one Global choice.
3. Save different Europe and Asia Brands SEO, reopen each and check duplicate selection redirects to its own existing record. Confirm Global and a country record remain unchanged.
4. In Static Pages select Europe, copy from Global, edit a section and save published. Repeat for Asia with different content. Verify copying/saving does not alter Global or the other region.
5. Open `/europe/about-us` and `/asia/about-us`; check regional content. Unpublish Europe and verify its sections are no longer returned.
6. Inspect regional page source for title, description, H1, canonical and robots. A regional page without its own SEO should remain noindex; an exact regional record may opt into indexing. Repeat with a custom configured region.

Automated checks cover regional/country/Global isolation, copying and publishing, duplicate checks, translated section copying, SEO scope/canonicals/robots, and regression behavior. Integration tests create disposable localhost MongoDB databases and never edit existing admin content.
