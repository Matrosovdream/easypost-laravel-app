# web/ — public Vue SPA

Covers everything that is **not** `/dashboard/*`:

- Marketing: `/`, `/pricing`, `/features`, `/customers`, `/about`, `/contact`, `/blog`, `/blog/{slug}`
- Portal (auth): `/portal/login`, `/register`, `/accept-invite/{token}`, `/forgot-password`, `/reset-password/{token}`, `/verify-email`, `/two-factor`, `/oauth/{provider}/callback`
- Legal: `/portal/terms`, `/portal/privacy`, `/portal/dpa`
- Public branded tracking: `/track/{code}`

## Source templates

Ports HTML from [../../../project-plan/templates/](../../../project-plan/templates/README.md):
- **Meraki UI** for heroes, pricing, FAQ, testimonials, CTAs, footers, and **every portal auth card**
- **HyperUI** for feature grids, logo clouds, sections
- **Preline** for layout inspiration + full template cross-reference

## Three layouts
| Route range | Layout | Components |
|-------------|--------|------------|
| `/`, `/pricing`, `/features`, `/blog`, `/contact` | `layouts/MarketingLayout.vue` | `Menubar`, `Button`, `Divider` |
| `/portal/*` | `layouts/PortalLayout.vue` | `Card`, `InputText`, `Password`, `Button`, `InputOtp`, `Message` |
| `/track/:code` | `layouts/TrackingLayout.vue` | `Stepper`, `Timeline`, `Card`, `Tag` |

## Rules
- **No imports from `dashboard/`.** Ever.
- **No PrimeVue modules beyond the tree-shaken subset** (see [templates-plan.md §6](../../../project-plan/templates-plan.md)).
- **No Laravel Echo / Reverb** (public side has no realtime).
- **No chart.js** (public side has no analytics).
- Forms use plain HTML5 + minimal hand-rolled validation — **no** `vee-validate` here (keeps bundle small).
- Markdown for blog/legal via `vite-plugin-md` or `markdown-it` — see `content/`.
