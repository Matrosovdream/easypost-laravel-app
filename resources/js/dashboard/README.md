# dashboard/ — authenticated Vue SPA

Everything under `/dashboard/*`. Built on **Sakai-Vue** (PrimeVue official starter — see [../../../project-plan/templates/sakai-vue/](../../../project-plan/templates/sakai-vue/)).

## Source template

Copy-in (not submodule, not dependency) from [`project-plan/templates/sakai-vue/`](../../../project-plan/templates/sakai-vue/):

| Sakai path | → destination here |
|------------|---------------------|
| `src/layout/AppLayout.vue` → | `layouts/DashboardLayout.vue` |
| `src/layout/AppTopbar.vue` → | `components/shell/AppTopbar.vue` |
| `src/layout/AppSidebar.vue` → | `components/shell/AppSidebar.vue` |
| `src/layout/AppMenu.vue` + `AppMenuItem.vue` → | `components/shell/` |
| `src/layout/AppFooter.vue` → | `components/shell/AppFooter.vue` |
| `src/layout/AppConfigurator.vue` → | `components/shell/AppConfigurator.vue` |
| `src/layout/composables/layout.js` → | `composables/useLayout.ts` (typed) |
| `src/views/pages/auth/Access.vue` → | `pages/Forbidden.vue` |
| `src/views/pages/auth/Error.vue` → | `pages/ServerError.vue` |
| `src/views/pages/NotFound.vue` → | `pages/NotFound.vue` |
| `src/views/pages/Empty.vue` → | `components/widgets/EmptyState.vue` |
| `src/views/pages/Crud.vue` → | base pattern for list+detail pages |

**Skip** (do not copy into dashboard/):
- `src/views/pages/auth/Login.vue` / `Register.vue` — those belong on the Web side.
- `src/views/pages/Landing.vue` — belongs on Web, and we build our own.
- `src/views/uikit/*` — demo catalogs. Keep a reference copy in `_sakai-seed/` only if useful, behind a dev-only route.
- `src/views/dashboard/*` — Sakai's demo dashboard. We replace with role-aware `pages/Home.vue` driven by widgets.

## Rules
- **No imports from `web/`.** Ever.
- Sidebar driven by [`config/nav.ts`](config/nav.ts) filtered by `useCan()`.
- Every page gates itself via `can:` on the route + `$this->authorize()` on any non-GET API call.
- Real-time via Laravel Echo on `team.{id}.*` channels (see [architecture-rules §6.4](../../../project-plan/architecture-rules.md)).
- DataTables use our shared `<DataTable>` wrapper in `components/tables/` that query-string-syncs filters and sort.
- Forms use `vee-validate` + `yup`.
