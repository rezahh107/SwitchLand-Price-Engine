- `auto_fix_allowed=NO`
- `resolved=false`
- `technical_import_ready=false`

سیستم باید از کاربر بپرسد آیا Rerun همان Source لازم است.

اگر کاربر Rerun را صریحاً تأیید کند:

- همان Issue Event در Audit حفظ می‌شود؛
- `resolved=true` می‌شود؛
- دیگر Run را Block نمی‌کند؛
- Run جدید و Version جدید ایجاد می‌شود؛
- `run_type=RERUN_SAME_SOURCE`
- `run_reason=RERUN_SAME_SOURCE`

اگر کاربر Rerun را تأیید نکند، Run جدید برای همان Source ایجاد/ادامه داده نمی‌شود.

نتیجه قبلی نباید صرفاً reuse شود؛ Rerun تأییدشده با Current Master/State/Rulebook/Discount Decisions جاری محاسبه می‌شود.

این Rule استثناء معنی `WARNING` ایجاد نمی‌کند؛ `WARNING` طبق `GOV-003` و `VAL-204` به‌صورت پیش‌فرض Non-Blocking باقی می‌ماند.

### SRC-108 — Source Completeness State Model

هر Source پس از Extraction Validation دقیقاً یکی از این Stateها را می‌گیرد:

- `COMPLETE`
- `INCOMPLETE`
- `UNRESOLVED`

تصمیم Completeness نباید بر عبارت subjective مانند «Parser confidence بالا» یا «به‌نظر کامل» متکی باشد.

#### Observable Checks

برای هر PDF این Checkها باید Event/Result قابل Audit تولید کنند:

1. `FILE_READABLE`
   - PDF container بدون read error باز شود.
   - page count قابل استخراج باشد.

2. `ALL_PAGES_READABLE`
   - تمام page indexهای `1..page_count` بدون render/extraction error قابل خواندن باشند.

3. `PAGE_SEQUENCE_CHECK`
   - اگر Source دارای Page Number قابل تشخیص است، sequence نباید unexplained gap یا duplicate incompatible داشته باشد.
   - اگر Source Page Number ندارد، این Check برابر `NOT_APPLICABLE` است و به‌تنهایی مانع COMPLETE نیست.
   - اگر شماره‌گذاری وجود دارد ولی interpretation آن deterministic نیست، Result = `UNRESOLVED`.

4. `PAGE_CLASSIFICATION_COVERAGE`
   - هر Page باید دقیقاً به یکی از `PRICE_BEARING` یا `NON_PRICE_BEARING` طبقه‌بندی شود.
   - Page غیرقابل طبقه‌بندی -> `UNRESOLVED`.

5. `PRICE_BEARING_EXTRACTION_COVERAGE`
   - تمام Region/Table/Sectionهای شناسایی‌شده به‌عنوان Price-bearing باید `PARSED` باشند.
   - هر `FAILED` یا `PARTIAL` -> Evidence of incompleteness.
   - هر Region با classification نامشخص -> `UNRESOLVED`.

6. `SOURCE_RECORD_NORMALIZATION_COVERAGE`
   - هر Supplier Record استخراج‌شده باید یکی از Stateهای قطعی `VALID_PRICE`, `INACTIVE_SIGNAL`, `NON_PRICE_RECORD` یا Technical Error مشخص داشته باشد.
   - Technical Error حل‌نشده در price-bearing content اجازه COMPLETE نمی‌دهد.

7. `FILE_DAMAGE_CHECK`
   - هر corruption/truncation/load failure گزارش‌شده توسط parser/renderer -> Fail.

#### State Evaluation

`COMPLETE` فقط وقتی مجاز است که:

- تمام Checkهای applicable دارای `PASS` باشند؛
- هیچ Check برابر `FAIL` یا `UNRESOLVED` نباشد؛
- هیچ Price-bearing record/region unresolved نمانده باشد.

`INCOMPLETE` وقتی است که Evidence مثبت از فقدان/خرابی/عدم استخراج بخشی از Source وجود داشته باشد، از جمله:

- missing page در sequence قابل اثبات؛
- unreadable page؛
- corrupted/truncated file؛
- failed/partial extraction در price-bearing region.

`UNRESOLVED` وقتی است که:

- Evidence کافی برای اعلام COMPLETE وجود ندارد؛
- ولی فقدان/خرابی قطعی نیز قابل اثبات نیست؛
- یا یکی از Checkهای الزامی قابل ارزیابی deterministic نیست.

کاهش تعداد Page یا Product نسبت به Source قبلی به‌تنهایی Evidence ناقص‌بودن نیست.

تأیید شفاهی کلی مانند «فایل کامل است» به‌تنهایی حق تبدیل `UNRESOLVED` یا `INCOMPLETE` به `COMPLETE` را ندارد؛ Evidence مانع باید به‌صورت قابل Audit حل شود.

### SRC-109 — Source Incomplete/Unresolved

اگر:

`source_completeness_status=INCOMPLETE`

باشد:

`UNRESOLVED_SOURCE_INCOMPLETE` -> Blocker.

اگر:

`source_completeness_status=UNRESOLVED`

باشد:

`UNRESOLVED_SOURCE_COMPLETENESS` -> Blocker.

در هر دو حالت:

- رکوردهای قابل پردازش MAY تحلیل شوند.
- Deactivation صرفاً بر اساس Absence/`NOT_FOUND` ممنوع است.
- Final `technical_import_ready` تا رفع Source completeness issue برابر false است.

### SRC-110 — Source Chronology State

کنترل Chronology دارای State مستقل است:

- `VERIFIED`
- `SOURCE_CHRONOLOGY_UNVERIFIED`

`VERIFIED` یعنی آخرین ordering رسمی Sourceها تا یک verified-date anchor قابل اثبات است.

`SOURCE_CHRONOLOGY_UNVERIFIED` یعنی حداقل یک Source رسمی پس از آخرین verified-date anchor وجود دارد که Supplier Date آن تأیید نشده و ordering آن نسبت به Sourceهای بعدی صرفاً با Date Comparison قابل اثبات نیست.

### SRC-111 — Chronology Gate after Unknown-Date Success

اگر `source_chronology_state=SOURCE_CHRONOLOGY_UNVERIFIED` باشد و Source جدید با Hash متفاوت وارد شود، Source جدید نباید صرفاً به‌دلیل داشتن Date معتبر از Old-Source Check عبور کند.

اگر ordering Source جدید نسبت به آخرین accepted unknown-date Source از Evidence deterministic قابل اثبات نباشد:

`SOURCE_CHRONOLOGY_RESOLUTION_REQUIRED` -> Blocker تا تصمیم/مدرک Chronology.

راه‌های مجاز Resolution:

1. Evidence deterministic Version/Sequence/Supersession که Rulebook صریحاً نحوه مقایسه آن را تعریف کرده باشد؛ یا
2. تأیید صریح کاربر درباره ordering Sourceها، با ثبت Hash هر Source و Decision ID در Manifest.

تأیید کاربر نباید یک Date جعلی برای Source بی‌تاریخ ایجاد کند.

کاربر MAY اجازه پردازش Source جدید را بدون Re-anchor بدهد؛ در این حالت Chronology همچنان `SOURCE_CHRONOLOGY_UNVERIFIED` باقی می‌ماند.

### SRC-112 — Chronology Re-Anchor

Chronology فقط وقتی به `VERIFIED` بازمی‌گردد که:

- Source جدید دارای Date معتبر باشد؛
- و ordering آن به‌عنوان superseding تمام Sourceهای accepted بعد از آخرین verified anchor به‌طور deterministic یا با تأیید صریح کاربر اثبات شود؛
- و Run آن Source `IMPORT_SUCCESS + PROMOTED` یا `APPROVED_NO_IMPORT_REQUIRED` شود.

پس از Re-anchor:

- `last_processed_price_list_date` = verified date Source re-anchor؛
- `source_chronology_state=VERIFIED`;
- Evidence و Decision مربوط به Re-anchor در Manifest ثبت می‌شود.

### SRC-IR-001 — Canonical Normalized Source Catalog

پس از Source Validation/Extraction و قبل از Matching/Pricing، Source باید به Artifact Canonical زیر تبدیل شود:

`Normalized_Source_Catalog.json`

Rule Engine نباید برای اجرای همان Run مستقیماً از Interpretation دوباره PDF استفاده کند؛ همه Matching/Pricingهای Run باید به Canonical IR Pin‌شده ارجاع دهند.

### SRC-IR-002 — Canonical IR Metadata

Artifact حداقل شامل:

```json
{
  "source_file_hash": "...",
  "source_filename": "...",
  "source_date_original": "...",
  "source_date_calendar": "...",
  "source_date_normalized": "...",
  "source_completeness_status": "COMPLETE|INCOMPLETE|UNRESOLVED",
  "extractor_version": "...",
  "engine_version": "...",
  "records": []
}
```

Artifact نهایی با `SHA-256` Hash می‌شود.

### SRC-IR-003 — Canonical Source Record

هر Supplier Record حداقل باید شامل این اطلاعات باشد:

- `source_record_identity`
- `page_index`
- `page_label` در صورت وجود
- `section_identity`
