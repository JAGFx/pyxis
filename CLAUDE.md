# AGENTS.md

## CRITICAL: Terminology

> **"command"** = CQS `CommandInterface` DTO — never a Symfony CLI command.
> Only create a Symfony CLI command if explicitly asked for a **"console command"** or **"CLI command"**.

## CRITICAL: No Exploration

**Never** glob/grep/find files. **Never** read files for context. Infer paths from naming conventions below. Only read a file immediately before modifying it.

## CRITICAL: CQS Pattern

**Command DTO**: `readonly class FooCommand implements CommandInterface` — promoted private props + Symfony constraint attrs, one getter each, no logic.

**CommandHandler**: `readonly class FooHandler implements CommandHandlerInterface` — single `__invoke(FooCommand $c): void`, inject `EntityManagerInterface` + repos.

**Query DTO**: `readonly class FindFooQuery implements QueryInterface` — optional params only.

**QueryHandler**: `readonly class FooHandler implements QueryHandlerInterface` — single `__invoke(FooQuery $c): void`, inject `EntityManagerInterface` + repos.

### Validation groups

| Group | When | Tools |
|---|---|---|
| `Default` | Commands + Queries | Standard Symfony constraints |
| `Business` | Commands only | `groups: [ValidationGroupEnum::Business->value]` + custom validators in `Domain/ContextName/Validator/` |

## CRITICAL: Code Style

- `declare(strict_types=1);` everywhere · PSR-12 · PHP 8.4 features · English only
- Constructor promotion + `readonly` always · Early return / guard clauses
- Explicit types · `??` and `?->` · PHPDoc only when inference is insufficient
- Imports: global namespace, alphabetically sorted, no function imports
- Doctrine queries: always via repository · `use Doctrine\ORM\Mapping as ORM`
- Throw specific exceptions · `Throwable` catch-all only in controllers

### Naming

| Symbol | Convention |
|---|---|
| Class / Interface / Trait / Enum | PascalCase |
| Method / Property | camelCase |
| Constant | UPPER_CASE |
| Enum case | PascalCase |
| File / Directory | Match class name / PascalCase |

---

## Execution Mode

- Default: execution-only. Use Plan Mode ONLY when the user explicitly asks to "plan" or "propose".
- Generate one plan, wait for approval, then execute in a single pass.

## Output Format

- Output diffs or only the modified code sections — never repeat unchanged code.
- No explanations, summaries, or "what I just did" recaps unless explicitly asked.
- PHPDoc only when type inference is insufficient.

## When Stuck

- If correctness is blocked: state the issue in one sentence, do NOT propose alternative solutions.
- If a required file is missing: ask once and wait.
- Do NOT speculate, do NOT attempt workarounds without confirmation.

## Context Management

- If context grows large (multi-file tasks), use a Markdown checklist/scratchpad file to track progress.
- Do NOT re-read files already processed. Reference previously stated conventions.
- If a task spans >3 files, propose splitting into sub-tasks before executing.

---

## Stack

- Symfony 7.4 · PHP 8.4+ · PHPUnit 12.3
- Namespace: `App\` · ORM: Doctrine (attributes) · Messenger: CQS via `MessageBus`
- Static analysis: PHPStan (max) + PHP CS Fixer + Rector · Fixtures: HautelookAliceBundle

---

## Commands

- Dockerised environemnt.
- PHP binary: `bin/php`. Ex `bin/php bin/console about`
- Prefer using makefile as possible 

```bash
make lint       # CS Fixer + PHPStan + Rector
make db:test    # required before integration tests
make test       # Run all tests
make ci         # lint + tests
```

---

## Architecture

```
src/
├── Domain/{Account,Assignment,Budget,Entry,PeriodicEntry}/
│   ├── Entity/ Repository/ Validator/ Controller/ Form/ Twig/ Security/
│   └── Message/
│       ├── Command/OperationName/  ← OperationNameCommand.php + Handler.php
│       └── Query/OperationName/   ← OperationNameQuery.php + Handler.php
├── Module/Exporter/               # ExporterBundle
│   ├── Domain/
│   │   ├── Artifact/  # Entity, Validator, Message/Command, Message/Query, Repository, Mailer
│   │   └── Account/   # Factory, Message/Command, Controller
│   └── Infrastructure/
│       ├── Document/Factory/  # DocumentFactoryInterface + DocumentFactoryResolver
│       ├── Document/Model/    # Document, DocumentTypeEnum
│       ├── Storage/           # StorageEnum (FILE_SYSTEM='local', S3='s3')
│       └── RequestExport/     # AbstractRequestExportCommand
└── Shared/
```

---

## Module Exporter — Key concepts

- **Artifact** states: `PENDING | DONE | FAILED | DISABLED`. API: `getStatus()`, `isPending()`, `isFinished()`, `isDisabled()`, `getParent()`.
- **DocumentFactoryInterface**: `support(string $targetClass, DocumentTypeEnum $type): bool` + `createDocument(RequestExportCommandInterface $cmd): DocumentInterface`. Auto-tagged via `DocumentFactoryResolver`.
- **Export flow**: `RequestExportCommand → createParentEmptyArtifact() [async] → DocumentFactory::createDocument() → AttachDocumentToArtifactCommand`
