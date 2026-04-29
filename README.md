# ShipDesk — what it is

ShipDesk is a shipping operations app for businesses that send a lot of packages. It pulls everything together in one place — buying labels, tracking deliveries, handling returns, dealing with damaged goods, and managing the people who do the work — so the team isn't jumping between five different tools to ship one box.

It's built for 3PL companies (logistics providers who ship on behalf of other brands), but it works just as well for any company shipping their own orders. Each company gets its own workspace, with carrier accounts, billing, and team members kept separate.

## Who uses it

We split users into six roles, so each person sees only what they need:

- **Admin** — runs the company's account. Sets up carriers, manages billing, hires and fires staff, sees the full picture.
- **Manager** — runs day-to-day operations. Approves expensive shipments, assigns work, watches for problems, and signs off on returns or claims.
- **Shipper** — the warehouse team. Picks orders, prints labels, packs boxes, and schedules pickups.
- **CS Agent** — handles customer issues. Processes returns, opens insurance claims, and talks to buyers when something goes wrong.
- **Client** — an outside customer (a brand whose orders we ship). Logs in to a private portal to create shipments and check their own returns. They never see anyone else's data.
- **Viewer** — read-only access for accountants or executives. Can look at reports and numbers but can't change anything.

Each role has its own menu and dashboard, so a warehouse worker doesn't see billing screens and an admin doesn't get bogged down in print queues.

## What you can do with it

- **Create and buy labels** across multiple carriers (UPS, FedEx, DHL, USPS) from one screen
- **Approve high-value shipments** before they're paid for
- **Print labels in batches** and generate scan forms for carrier handoff
- **Schedule pickups** so drivers come to the warehouse
- **Track everything** in real time and get alerts when something gets stuck
- **Handle returns and refunds** without leaving the app
- **File insurance claims** when packages are lost or damaged
- **Run reports** on shipping costs, carrier performance, and team productivity
- **Invite your customers** as clients so they can self-serve
- **Manage your team** with role-based permissions, daily spending caps, and audit logs

The whole thing is built to scale — start with one warehouse and a few people, grow into multiple locations and dozens of customers without changing systems.

## How to log in

Open the login page and type one of the PIN codes below — that's it, no email or password needed. Each PIN signs you in as a different person so you can see exactly what that role looks like.

**Login URL:** https://shipdesk.devflip.io/

| PIN | Name | Role | What you'll see |
|---|---|---|---|
| **9999** | Stan Admin | Admin | Full control — overview, people management, configuration, data |
| **9998** | Alex Admin | Admin | Same as above (second admin account) |
| **8888** | Riley Manager | Manager | Operations, approvals, customer service, data |
| **8887** | Morgan Manager | Manager | Same as Riley |
| **7777** | Pat Shipper | Shipper | Warehouse view — my queue, print, batches, pickups |
| **7776** | Quinn Shipper | Shipper | Same as Pat |
| **7775** | River Shipper | Shipper | Same as Pat |
| **6666** | Maya CS | CS Agent | Returns, claims, insurance, customer comms |
| **6665** | Noah CS | CS Agent | Same as Maya |
| **5555** | Jen Widgets | Client | Customer portal — only their own shipments and returns |
| **5554** | Bob Widgets | Client | Same as Jen |
| **4444** | Jordan Viewer | Viewer | Read-only — reports, analytics, billing snapshot |

Best way to explore: log in as **9999** first to see the full admin view, then try **8888** (Manager) and **7777** (Shipper) to see how the menus change for different roles.

---

# Developer reference

Role-based shipping platform built on Laravel + Vue + EasyPost.

## Stack

- Laravel 12 · PHP 8.3
- Vue 3 · TypeScript · PrimeVue · Tailwind v4
- Postgres · Redis · Horizon · Reverb · Meilisearch

## Quick start

```bash
cp .env.example .env
docker compose -f compose.dev.yaml up -d
docker compose -f compose.dev.yaml exec workspace npm install
docker compose -f compose.dev.yaml exec workspace npm run dev
```

Then open:

- App → http://localhost:8080
- Vite HMR → http://localhost:5173
- Adminer → http://localhost:9091

## Tests

```bash
docker compose -f compose.dev.yaml exec php-fpm php artisan test
```
