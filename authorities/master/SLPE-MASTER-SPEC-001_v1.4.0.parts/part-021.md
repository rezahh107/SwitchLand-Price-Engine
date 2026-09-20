- Rial->Toman + Ceiling
- Global Price->Visibility Rule
- Discount Preflight
- Manual Price Override Lifecycle
- Excluded Product Policy
- New Product Workflow و Run-Scoped Skip
- AUTO_FIX/WARNING/BLOCKER Severity Model
- Mutation Allowlist
- Final Summary + Global Approval
- Audit Report/Manifest
- Import Success/Failed/Partial
- NO_CHANGES
- Rollback
- Rulebook/Schema Governance
- Conservative accounting-grade control defaults

---

# ضمیمه A — خلاصه رفتار در یک جمله

**SwitchLand Price Engine یک موتور قیمت‌گذاری نسخه‌دار، قابل حسابرسی و محافظه‌کار است که فقط با Evidence قطعی داده تجاری را تغییر می‌دهد، همه تغییرات را ردیابی می‌کند، هیچ تاریخچه‌ای را بازنویسی نمی‌کند و Current Master را پس از نتیجه موفق یا استثنای دقیق run-scoped با Queue آشکار و تأییدشده ارتقا می‌دهد.**

# ضمیمه B — اصل نهایی تصمیم‌گیری

اگر سیستم بتواند با Rule معتبر و Evidence قطعی تصمیم بگیرد، باید کار را بدون مزاحمت اضافه ادامه دهد و نتیجه را Audit کند. اگر تصمیم نیازمند حدس باشد و حدس بتواند داده تجاری را اشتباه تغییر دهد، باید آن مورد را Fail-Closed کند، سایر رکوردها را ادامه دهد و فقط همان تصمیم ضروری را با توضیح ساده از کاربر بخواهد.
