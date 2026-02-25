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

**Query DTO (pageable/sortable)**: Add `use OrderableTrait; use PaginableTrait;` and implement `OrderableInterface, PaginationInterface` on Query DTOs that must support pagination and sorting. The handler then injects `PaginatorInterface` and returns `PaginationInterface<int, Entity>`.

**Paginated list controller**:
1. **Query DTO** — implements `OrderableInterface, PaginationInterface`, uses `OrderableTrait, PaginableTrait`. Constructor sets optional filter params; set default sort in controller.
2. **Handler** — injects `PaginatorInterface`, calls `$this->paginator->paginate($repo->getQueryBuilder($query), $query->getPage(), $query->getPageSize())`, returns `PaginationInterface<int, Entity>`.
3. **Repository** — `getQueryBuilder(FindFooQuery $q)` applies filters + `orderBy($q->getOrderBy(), $q->getOrderDirection()->value)`, returns `QueryBuilder`.
4. **Form type** — `data_class = FindFooQuery`, calls `PaginationBuilder::buildForm($builder)` (no extra fields unless filters needed).
5. **Controller** — uses `PaginationFormHandlerTrait`; creates Query DTO with defaults (`->setOrderBy('field')->setOrderDirection(OrderEnum::DESC)`), calls `$this->handlePaginationForm($request, FooSearchType::class, $query)`, dispatches via `$this->messageBus->dispatch($query)`, passes result to template.
6. **Template** — iterates `PaginationInterface` directly (`{% for item in items %}`), renders controls with `{{ knp_pagination_render(items) }}`.

**QueryHandler**: `readonly class FooHandler implements QueryHandlerInterface` — single `__invoke(FooQuery $c): void`, inject `EntityManagerInterface` + repos.

**Live Component search form + Turbo Stream**:
1. **Live Component** — `src/.../Twig/Components/FooSearchForm.php`, extends `AbstractController`, uses `#[AsLiveComponent(template: '...')]` + `ComponentWithFormTrait` + `DefaultActionTrait`. `instantiateForm()` creates the form with `action` pointing to the Turbo search route.
2. **twig_component.yaml** — register each new module namespace: `App\...\Twig\Components\: 'module/.../components'`.
3. **Front search controller** — POST route, uses `TurboResponseTrait`; calls `createForm()->handleRequest($request)` manually (NOT `handlePaginationForm`); returns `renderTurboStream()` with a `*.turbo.stream.html.twig` template.
4. **Turbo Stream template** — extends `shared/turbo/_stream.html.twig`; `<turbo-stream action="update" target="main_body">` includes the `_list.html.twig` partial.
5. **Live Component template** — wraps with `<div {{ attributes }}>`, sets `data-turbo: true` on the form, includes `shared/menu/_search_form_button_actions.html.twig` for submit/reset buttons.

### Validation groups

| Group | When | Tools |
|---|---|---|
| `Default` | Commands + Queries | Standard Symfony constraints |
| `Business` | Commands only | `groups: [ValidationGroupEnum::Business->value]` + custom validators in `Domain/ContextName/Validator/` |

## CRITICAL: Code Style

- **English only** — all code, comments, docblocks, commit messages, and documentation (including this file) must be written in English.
- `declare(strict_types=1);` everywhere · PSR-12 · PHP 8.4 features
- Constructor promotion + `readonly` always · Early return / guard clauses
- Explicit types · `??` and `?->` · PHPDoc only when inference is insufficient
- Imports: global namespace, alphabetically sorted, no function imports
- Doctrine queries: always via repository · `use Doctrine\ORM\Mapping as ORM`
- Throw specific exceptions · `Throwable` catch-all only in controllers

**Enums**: For translatable enum values, always add a `label(): string` method returning a translation key based on `$this->name` (e.g. `'prefix.' . $this->name`). See `EntryTypeEnum::label()` (`src/Domain/Entry/Entity/EntryTypeEnum.php`) as reference.

**EnumType form fields**: Always set `'choice_label' => 'label'` + `'choice_translation_domain' => 'messages'` for enums that have a `label()` method. Optional filter fields use `'required' => false` + `'placeholder' => 'shared.default.placeholders.all'`.

### Naming

| Symbol | Convention |
|---|---|
| Class / Interface / Trait / Enum | PascalCase |
| Method / Property | camelCase |
| Constant | UPPER_CASE |
| Enum case | PascalCase |
| File / Directory | Match class name / PascalCase |
| Controller | Always split into `Controller/Back/` (admin) and `Controller/Front/` (user-facing) |

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

translations/
├── messages.fr.yaml         # global translations
├── module/
│   └── {module}/            # module-scoped translations (same file naming)
│       ├── messages.fr.yaml
│       ├── document.fr.yaml
│       └── ...
```

**Module translations**: Module-specific translations must live under `translations/module/{module}/`, using standard file naming conventions (`messages.fr.yaml`, `validators.fr.yaml`, `forms.fr.yaml`, etc.).

---

## Module Exporter — Key concepts

- **Artifact** states: `PENDING | DONE | FAILED | DISABLED`. API: `getStatus()`, `isPending()`, `isFinished()`, `isDisabled()`, `getParent()`.
- **DocumentFactoryInterface**: `support(string $targetClass, DocumentTypeEnum $type): bool` + `createDocument(RequestExportCommandInterface $cmd): DocumentInterface`. Auto-tagged via `DocumentFactoryResolver`.
- **Export flow**: `RequestExportCommand → createParentEmptyArtifact() [async] → DocumentFactory::createDocument() → AttachDocumentToArtifactCommand`
