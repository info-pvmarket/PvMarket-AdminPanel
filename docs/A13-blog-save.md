# A13: reliable blog saves and duplicate protection

Implemented locally. Production review, deployment and verification remain pending.

## Exact A13 file list

All paths below are under `C:\dev\pv-market\PvMarket-AdminPanel`:

- `app/Http/Controllers/Admin/BlogController.php` (also contains the earlier A16 validation change)
- `app/Models/Blog.php`
- `app/Traits/HandlesSeoMeta.php`
- `app/Jobs/TranslateBlogJob.php` (new)
- `config/queue.php`
- `database/migrations/2026_10_24_000001_add_unique_index_to_blogs_slug.php` (new, revised from the initial unverified draft)
- `public/js/blog-save-state.js` (new)
- `resources/views/admin/knowledge-hub/blogs/blogs.blade.php` (also contains the earlier A16 loading change)
- `tests/Feature/BlogSaveTest.php` (new)
- `tests/blog-save-browser.cjs` (new)
- `docs/A13-blog-save.md` (new)

Local environment files and the separate A16 setup helpers are not part of this list.

## Recorded verification

- Targeted A13/A16/SEO tests: **25 tests, 125 assertions, passed**. This includes 16 A13 feature/integration tests using disposable local MongoDB databases.
- Existing Unit suite: **142 tests, 766 assertions, passed**. Some of its tests are also included in the targeted run; do not add these counts as independent tests.
- Offline save-state browser checks: invalid form remains usable, second submit blocked, delayed-save guidance, and browser Back restoration all passed.
- A16 real Quill/browser regression: unchanged baseline still reproduces chart loss; patched 1/2/6-chart references retain all nine images, links, dimensions and alt edits after serialize/reopen. New clipboard base64 remains blocked.
- A16 matcher tests: **3 passed, 0 failed**.
- PHP syntax, JavaScript syntax and `git diff --check` passed.
- The migration was executed only against disposable test databases. It has not been run against the user's normal local database or production. The production worker, AWS latency, production collision inventory, and live UI remain unverified.

## Behavior

- Create and update normalize the requested slug, reject empty/oversized results, and check for conflicts before writing. Retrying a successful Create returns a validation error instead of creating another record. Slugs are not automatically suffixed.
- A partial unique index on `blogs.slug_normalized` enforces the same rule when requests race. Existing published `slug` values remain unchanged.
- The migration first checks all existing slugs with the same normalization used by the controller. If any collide, it stops before writing anything. It neither deletes nor chooses between existing records. Resolve such records with owner approval before deployment can proceed.
- Only duplicate-key errors from this particular slug index become a slug validation error. Unrelated write errors remain real errors.
- Save disables the button and shows an accessible Saving message. Repeated submits are blocked. A slow-response message advises checking Blogs before retrying; it never automatically unlocks Save while a request may still be running. Browser Back restores the controls.
- Successful saves show success. Validation failures retain the entered form content and show the error. If queueing fails after the source was saved, the response explicitly says that the blog is saved and warns against creating another copy.
- Existing cover/social image files are deleted only after a successful blog update, so a concurrent slug conflict cannot remove files still referenced by the record. The shared SEO helper retains its existing default behavior for other controllers.

## Save delay and translations

Previously both source fields were translated synchronously for every non-English locale before the database save. HTML translation can invoke AWS once per text fragment, so this can involve more requests than simply counting locales and fields. This code structure is confirmed; production AWS latency was not measured and no live translation requests were made during testing.

Create now saves the source first and enqueues one durable job per locale. Update queues translation only when title or body changes. Metadata-only edits do not retranslate the article. The explicit MongoDB `blog-translations` connection prevents a default sync queue configuration from putting translation back into the HTTP request.

Each source edit gets a new revision token. Jobs read and conditionally update only that revision; an older job cannot overwrite a newer edit. Only translated title/body fields are written. A translation failure throws for queue retry and never replaces a locale with a partial result. Existing translated content remains until the new job completes.

## Deployment requirements for the reviewer

1. Back up the production blogs collection and deploy during a write-free maintenance window. New writes must not be accepted before the unique index is installed.
2. Deploy all A13 files together, including the queue connection and job class. Refresh cached configuration through the team's normal deployment procedure.
3. Run only the reviewed targeted migration, not unrelated pending migrations:

   ```sh
   php artisan migrate --path=database/migrations/2026_10_24_000001_add_unique_index_to_blogs_slug.php --force
   ```

   If the collision preflight fails, stop and obtain approval for data reconciliation. Do not automatically remove duplicate articles or change published URLs. If an external writer exists beyond BlogController, it must maintain `slug_normalized` as well; the Go API's inspected blog endpoints are read-only.

4. Configure a supervised worker for the new connection and named queue:

   ```sh
   php artisan queue:work blog-translations --queue=blog-translations --timeout=1800 --tries=3
   ```

   Its connection retry interval is 1860 seconds, longer than the 1800-second job timeout. The process supervisor's stop grace must also allow the job to finish. A worker listening only to the default queue will not process these jobs. Verify failed-job monitoring/retry through the team's existing operational process.

5. Verify a saved source returns promptly while jobs are pending, then verify translations finish. Do not assume translation is operational from the source-save success alone. AWS credentials, availability and actual production timing remain deployment verification items.
6. In a non-public test, retry a Create and attempt two requests for the same normalized slug. Verify only one record exists and the other attempt reports the conflict. Verify update/self-slug and a different-record conflict as well.
7. Repeat A16's open/save/reopen check for all nine charts. Preserve the latest article text, six corrected links, slugs, cover images and metadata. A16 restoration/deployment remains a separate pending step.

## Local checks

PHPUnit integration tests are opt-in and create randomly named `pvmarket_a13_test_*` databases on `127.0.0.1:27017`. They drop only their own databases afterward. They never use the application's normal database or call AWS. Tests cover real concurrent inserts, application requests, migration collision preflight, queued job persistence, stale revisions, queue/translation failures and preservation of existing charts/images.

```powershell
$env:A13_MONGODB_TESTS = '1'
php vendor/bin/phpunit --filter 'BlogSaveTest|RichTextImagePreservationTest|BlogManagementPresentationTest|SeoMetaPresentationTest'
```

Offline browser tests require an existing Playwright installation and Edge:

```powershell
node tests/blog-save-browser.cjs
node --test tests/admin-quill-preservation.test.cjs
node tests/admin-quill-browser.cjs "PATH_TO_EXTRACTED_PVmarket_EOD_2026-10-24"
```

These browser tests block page network requests. Production code, data and deployment have not been changed by these tests. No commits, pushes or production migration runs are implied by this document.
