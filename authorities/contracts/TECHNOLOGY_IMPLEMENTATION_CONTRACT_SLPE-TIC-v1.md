TECHNOLOGY_IMPLEMENTATION_CONTRACT
Contract ID: SLPE-TIC-v1
Status: FROZEN_FOR_IMPLEMENTATION
Target: Windows x86-64 desktop
Architecture: IMPLEMENTATION_ARCHITECTURE_CONTRACT v1

1. PYTHON
   - CPython >=3.13,<3.14
   - Initial build/test baseline: CPython 3.13.14

2. PROJECT / DEPENDENCY MANAGEMENT
   - uv
   - pyproject.toml
   - uv.lock committed
   - .python-version committed
   - development sync must honor lockfile

3. DIRECT RUNTIME DEPENDENCIES
   - PySide6 == 6.11.1
   - python-docx == 1.2.0
   - PyMuPDF == 1.28.0
   - pydantic == 2.13.4
   - XlsxWriter == 3.2.9

4. GUI
   - Qt Widgets, not QML/Qt Quick
   - one QMainWindow
   - stage-oriented persistent workspace
   - QStackedWidget for main stage content
   - Persian-first RTL presentation
   - technical tokens rendered explicitly LTR
   - QTableView + QAbstractTableModel for review tables
   - QSortFilterProxyModel for simple filtering/sorting
   - progressive disclosure for diagnostics
   - business logic prohibited in widgets

5. GUI APPLICATION METHOD
   - Thin View
   - explicit Controller/Presenter
   - Application Services / Run Coordinator behind presenter
   - no full MVVM framework
   - no DI container

6. LONG-RUNNING OPERATIONS
   - QObject worker moved to QThread
   - one worker/thread per active long operation
   - at most one active mutation operation
   - signals for stage/progress/result/error
   - GUI mutation only on GUI thread
   - cooperative cancellation only
   - QThread.terminate prohibited for business operations

7. DOCX EXTRACTION
   - python-docx is semantic parser
   - stdlib zipfile + xml.etree.ElementTree performs bounded OOXML preflight
   - preflight enumerates unsupported price-bearing structural content
   - unknown material structure fails closed
   - no second generic DOCX parser

8. PDF VERIFICATION
   - PyMuPDF
   - candidate-led word/bounding-box evidence lookup
   - NOT a general PDF-to-table extractor
   - missing/ambiguous/conflicting evidence => UNRESOLVED
   - OCR prohibited in normal v1

9. MODELS
   - Pydantic models at persisted/external boundaries
   - strict=True for correctness-critical models
   - extra="forbid"
   - frozen models for immutable snapshots
   - business rules must not be hidden inside structural validators

10. DOMAIN IMPLEMENTATION
    - deterministic side-effect-light functions
    - no filesystem/network/GUI/clock/random access
    - IDs/timestamps supplied by orchestration
    - input snapshots treated as immutable
    - output = candidate results + proposed state + structured issues

11. MONEY
    - supplier Rial: int
    - regular/sale Toman: int
    - percentages/intermediate decimal arithmetic: decimal.Decimal
    - Decimal constructed from string/integer, never binary float
    - explicit rounding modes
    - float prohibited in commercial arithmetic

12. CANONICAL JSON
    - Python stdlib json
    - explicit conversion to canonical primitives
    - ensure_ascii=False
    - sort_keys=True
    - separators=(",", ":")
    - allow_nan=False
    - UTF-8, no BOM
    - no trailing newline for hash-bound canonical bytes
    - Decimal, if persisted, converted by explicit canonical rule
    - generic default=str prohibited

13. CANONICAL CSV
    - custom small deterministic serializer
    - exact active 95-column order
    - delimiter ";"
    - quote char '"'
    - quote fields containing delimiter, quote, CR/LF,
      or leading/trailing whitespace
    - internal quotes doubled
    - LF newline
    - UTF-8 BOM
    - no index column
    - blank remains empty
    - no incidental trim/Unicode normalization
    - parse-back verification with stdlib csv.reader

14. AUDIT XLSX
    - XlsxWriter
    - generate-only workbook
    - RTL worksheets
    - LTR formatting for technical fields
    - minimal header styling/filter/freeze/column sizing
    - no requirement for byte-stable XLSX

15. FILESYSTEM
    - pathlib/os/tempfile/shutil
    - all filesystem layout hidden behind concrete repositories
    - Domain Engine never receives paths
    - closed Run artifacts never overwritten

16. MUTABLE RUN FILE WRITES
    - temp file in target directory
    - complete write
    - flush
    - os.fsync
    - close
    - os.replace

17. CURRENT COMMIT
    - new versioned artifacts prepared and validated first
    - CURRENT.next written and validated
    - Windows ReplaceFileW invoked through a small stdlib ctypes wrapper
    - previous CURRENT retained as backup
    - CURRENT is the only authoritative current commit point
    - startup validates CURRENT and referenced artifact hashes

18. RUN LOCK
    - Win32 named mutex through stdlib ctypes
    - single local application/mutation instance
    - abandoned mutex triggers Current/Run consistency validation
    - no distributed locking

19. STALE-PARENT GUARD
    - expected Current commit ID
    - expected Master version
    - expected Master hash/fingerprint
    - rechecked before Final Approval and Promotion

20. HASHING
    - hashlib SHA-256
    - streamed hashing for files
    - hashes used for correctness/replay/artifact binding

21. DIAGNOSTICS
    - stdlib logging
    - per-Run developer diagnostic log
    - structured DiagnosticIssue model
    - UI levels:
      PRIMARY SUMMARY
      DETAIL
      ADVANCED DIAGNOSTICS
    - tracebacks never shown as normal operator message

22. RESULT / EXCEPTION POLICY
    - expected data/business conditions => DiagnosticIssue
    - anticipated operational failures => structured AppError
    - programming/invariant failures => Python exception + traceback log
    - unexpected exception must not silently advance Run state

23. REPOSITORIES
    - small concrete repositories
    - explicit dependency passing
    - Protocol only where test substitution needs it
    - no generic Repository framework
    - no service locator/DI container

24. RUN COORDINATOR
    - explicit Enum/state definitions
    - explicit allowed-transition table
    - explicit application commands
    - no workflow engine

25. CONFIGURATION
    - business configuration remains versioned authoritative artifacts
    - application constants use small explicit config module
    - QSettings allowed only for non-authoritative UI preferences
    - QSettings prohibited for Run/commercial state

26. TESTING
    - pytest == 8.4.2
    - pytest-qt == 4.5.0
    - qt_api forced to pyside6
    - golden fixtures for PDF/DOCX/Canonical IR/CSV
    - corruption fixtures
    - deterministic replay tests
    - temporary-filesystem promotion tests
    - failure/partial/rollback tests
    - targeted Qt component tests
    - packaged Windows smoke test required before release

27. STATIC QUALITY
    - Ruff == 0.15.22 for lint/format
    - mypy == 2.3.0
    - strong typing required in core/application/repositories
    - narrowly scoped third-party/UI typing exceptions allowed
    - Black/isort/flake8/Pyright not part of v1 toolchain

28. PACKAGING
    - PyInstaller == 6.21.0
    - Windows build
    - onedir
    - windowed
    - persistent Workspace stored separately from executable bundle
    - packaged smoke test under Persian filesystem path
    - onefile deferred

29. PROHIBITED v1 TECHNOLOGIES
    - database/ORM
    - server/backend/REST
    - Electron/browser shell
    - multiprocessing architecture
    - background worker service/queue
    - event sourcing/CQRS
    - generic workflow engine
    - dependency-injection framework
    - generic plugin architecture
    - pandas as Domain/State representation
    - LLM SDK/runtime dependency
    - OCR stack
    - second overlapping PDF/DOCX parser
    - multiple Qt bindings

30. BOUNDED FALLBACK RULES
    - unfamiliar DOCX structure:
        FAIL CLOSED / Human or implementation update
    - insufficient PDF text evidence:
        FAIL CLOSED; do not activate OCR automatically
    - PDF/DOCX disagreement:
        Human Review
    - invalid CURRENT:
        no commercial processing/promotion;
        recovery from last proven Current descriptor
    - packaged dependency failure:
        release fails packaged smoke test
    - future need for OCR/PDF-native extraction/database:
        separate versioned technology decision;
        Canonical/Repository boundaries remain unchanged

END TECHNOLOGY_IMPLEMENTATION_CONTRACT
