# _shared — cross-bundle kit

Used by **both** `web/` and `dashboard/`. Keep this tiny. If a file only serves one side, move it to that side.

```
_shared/
├─ theme/preset.ts                # PrimeVue Aura preset + brand tokens
├─ types/                         # TS interfaces mirroring backend Resources
│  ├─ shipment.ts
│  ├─ address.ts
│  ├─ tracker.ts
│  └─ user.ts
├─ api/
│  ├─ client.ts                   # axios instance + XSRF + 401/419 handlers
│  └─ errors.ts                   # typed Laravel error mapping
└─ ui/
   └─ brand-tokens.css            # CSS variables consumed by both bundles
```

## Rules
- No imports from `web/` or `dashboard/` — only flow is **shared → surface**.
- No Vue components here — behavioral code only. UI components belong in their bundle.
- Every file < 150 LOC ideally. Grow a new folder only when a third file needs to sit next to the first two.
