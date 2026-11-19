# Regional sliders and advertisements

Both create and edit forms use the shared Global / Regions / Countries dropdown. Regions come only from active saved stock regions with country membership. Existing country and Global campaigns keep their scope; no data migration is needed.

A region is a separate campaign scope (`region_code`); it does not aggregate country campaigns. For each placement (top slider, advertisement slider, standalone advertisement), the API selects active banners from the requested region, falling back to Global only when that placement has no regional content. Country requests keep exact-country behavior. Invalid or disabled regions are rejected.

The homepage sends region scope during server rendering and client fallback requests. Changing scope remounts the banner components and cancels older requests. Advertisement sliders take precedence over standalone advertisements, matching the existing placement layout.

## Manual check

1. In local admin, create an active top slider and advertisement for Africa using the grouped dropdown.
2. Open the Africa homepage: confirm Africa campaigns appear, in the saved slider order, and links work.
3. Open another region with no campaigns: confirm Global fallback appears, without Nigeria or other country campaigns.
4. Open Nigeria: confirm its country campaigns remain separate.
5. Edit a regional campaign, save, reopen, and confirm its scope persists. Switch it to a country or Global and check again.
6. Disable the regional campaign and confirm the relevant placement falls back to Global.

Automated checks: `BannerScopesTest.php` (admin create/edit/validation/order/toggle); `TestRegionalBannersStayScopedAndFallbackPerPlacement` (real MongoDB/API scope isolation and fallbacks); `tests/regional-banners.test.cjs` (SSR/client requests and empty-result handling).
