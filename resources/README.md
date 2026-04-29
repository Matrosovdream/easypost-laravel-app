# resources/

Two **fully-separated** frontends plus a tiny shared kit. Never import across the boundary except via `_shared`.

```
resources/
├─ css/                           # Tailwind entry (shared between both bundles)
├─ js/
│  ├─ _shared/                    # ONLY cross-bundle code — keep tiny
│  │  ├─ theme/                   # PrimeVue preset + brand tokens
│  │  ├─ types/                   # TS interfaces mirroring backend Resources
│  │  ├─ api/                     # axios base client (CSRF/XSRF interceptors)
│  │  └─ ui/                      # brand-tokens.css variables
│  ├─ web/                        # /, /portal/*, /track/{code}, /blog, /pricing, ...
│  │  ├─ main.ts                  # Vite entry
│  │  ├─ App.vue
│  │  ├─ router/
│  │  │  ├─ index.ts
│  │  │  ├─ routes.marketing.ts
│  │  │  ├─ routes.portal.ts
│  │  │  ├─ routes.tracking.ts
│  │  │  └─ guards.ts
│  │  ├─ layouts/
│  │  │  ├─ MarketingLayout.vue
│  │  │  ├─ PortalLayout.vue
│  │  │  └─ TrackingLayout.vue
│  │  ├─ config/
│  │  │  ├─ theme.ts
│  │  │  └─ seo.ts
│  │  ├─ stores/                  # Pinia (portal auth only)
│  │  ├─ api/                     # auth, contact, tracking
│  │  ├─ types/
│  │  ├─ pages/
│  │  │  ├─ Public/
│  │  │  │  ├─ Home.vue
│  │  │  │  ├─ Pricing.vue
│  │  │  │  ├─ Features.vue
│  │  │  │  ├─ Customers.vue
│  │  │  │  ├─ About.vue
│  │  │  │  ├─ Contact.vue
│  │  │  │  ├─ Tracking.vue       # /track/:code
│  │  │  │  ├─ Blog/
│  │  │  │  │  ├─ Index.vue
│  │  │  │  │  └─ Show.vue
│  │  │  │  └─ Legal/
│  │  │  │     ├─ Terms.vue
│  │  │  │     ├─ Privacy.vue
│  │  │  │     └─ Dpa.vue
│  │  │  └─ Portal/
│  │  │     ├─ Login.vue
│  │  │     ├─ Register.vue
│  │  │     ├─ AcceptInvite.vue
│  │  │     ├─ ForgotPassword.vue
│  │  │     ├─ ResetPassword.vue
│  │  │     ├─ VerifyEmail.vue
│  │  │     ├─ TwoFactor.vue
│  │  │     └─ OauthCallback.vue
│  │  ├─ components/
│  │  │  ├─ marketing/            # Hero, FeatureGrid, Pricing, FAQ, CTA, Footer, ...
│  │  │  ├─ portal/               # login form, register form, 2FA input
│  │  │  ├─ tracking/             # stepper, timeline, brand header
│  │  │  └─ nav/                  # top nav + footer
│  │  ├─ content/                 # Markdown for blog + legal
│  │  │  ├─ blog/
│  │  │  └─ legal/
│  │  └─ assets/                  # hero illustrations, OG images
│  └─ dashboard/                  # /dashboard/* only
│     ├─ main.ts                  # Vite entry
│     ├─ App.vue
│     ├─ router/
│     │  ├─ index.ts
│     │  ├─ routes.ts             # every /dashboard/* route
│     │  └─ guards.ts             # auth + permission guards
│     ├─ layouts/
│     │  ├─ DashboardLayout.vue   # from Sakai — the shell
│     │  └─ PackLayout.vue        # minimal shell for Shipper pack mode
│     ├─ config/
│     │  ├─ nav.ts                # sidebar tree (role-filtered)
│     │  └─ theme.ts              # re-exports _shared preset
│     ├─ stores/                  # auth, team, shipments, trackers, ...
│     ├─ api/                     # shipments, addresses, batches, pickups, returns, claims, reports, ...
│     ├─ types/
│     ├─ pages/
│     │  ├─ Home.vue              # role-aware dispatcher
│     │  ├─ Shipments/
│     │  ├─ Batches/
│     │  ├─ ScanForms/
│     │  ├─ Pickups/
│     │  ├─ Returns/
│     │  ├─ Claims/
│     │  ├─ Addresses/
│     │  ├─ Trackers/
│     │  ├─ Clients/
│     │  ├─ Analytics/
│     │  ├─ Reports/
│     │  ├─ Ops/
│     │  ├─ Settings/
│     │  ├─ Profile.vue
│     │  ├─ Forbidden.vue         # /dashboard/403
│     │  ├─ NotFound.vue
│     │  └─ Locked.vue            # /dashboard/locked
│     ├─ components/
│     │  ├─ shell/                # topbar, sidebar, footer (from Sakai)
│     │  ├─ tables/               # DataTable wrappers
│     │  ├─ forms/                # PrimeVue form primitives w/ vee-validate
│     │  ├─ widgets/              # home page KPIs, charts, feeds
│     │  ├─ wizards/              # Shipment create, Pickup schedule, Return
│     │  └─ kanban/               # Shipments kanban
│     └─ composables/             # useLayout, useCan, useEcho, useToast, useConfirm
└─ views/
   ├─ web/
   │  └─ app.blade.php            # @vite('resources/js/web/main.ts')
   └─ dashboard/
      └─ app.blade.php            # @vite('resources/js/dashboard/main.ts')
```

## Rules

- **No cross-import.** `dashboard/*` cannot import from `web/*` and vice versa. Only `_shared/*` is allowed on both sides. Linted by ESLint `no-restricted-imports`.
- **Two Vite entries, two bundles.** See `vite.config.ts`.
- **Two Blade files, two SPAs.** Routes under `/dashboard/*` serve `views/dashboard/app.blade.php`; everything else serves `views/web/app.blade.php`.
- **Pages mirror specs.** Every Dashboard page matches a section in [../project-plan/pages-detailed.md](../project-plan/pages-detailed.md); every Web page matches [../project-plan/wireframes.md §1 + §8.9](../project-plan/wireframes.md).
- **Templates are in [`../project-plan/templates/`](../project-plan/templates/README.md).** Port HTML from there; do not depend on the template folders at runtime.

## Bundle size targets

| Bundle | First-visit JS (gzipped) |
|--------|-------------------------:|
| Web landing | ≤ 200 KB |
| Tracking page | ≤ 120 KB |
| Dashboard first-visit | ≤ 400 KB |

Enforced in CI via `vite-bundle-analyzer`.
