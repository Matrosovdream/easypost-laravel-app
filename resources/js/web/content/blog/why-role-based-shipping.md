---
title: Why role-based shipping beats one big inbox
date: 2026-03-12
author: Stan Matrosov
excerpt: Every shipping team we talk to has the same problem. One shared inbox, one shared spreadsheet, and one very tired person.
ogImage: /og/role-based.png
---

Every shipping team we talk to has the same problem. One shared inbox, one shared spreadsheet, and one very tired person trying to keep track of what's shipped, what's approved, what's stuck in customs.

## The shared-inbox trap

It starts small. A founder + two warehouse folks + a carrier login. Everyone sees everything. Everyone can do everything. It feels nimble.

Then you grow. You hire a CS agent. They need to see shipments, but shouldn't be able to cancel labels. You add a finance person. They need cost reports, not tracking events. A client asks to see their own inventory — so you start emailing them CSVs.

At some point, the shared inbox becomes the bottleneck. Every "did this ship?" question lands on one person. Every edit is an opportunity for error. Every new hire needs a two-hour onboarding just to know what *not* to touch.

## What roles actually solve

Roles aren't really about permissions. They're about **attention**. When a Shipper opens ShipDesk, they see the print queue. Not approvals. Not invoices. Not cost reports. Just: here are the labels to print.

When a Manager opens ShipDesk, they see approvals. When a CS agent opens it, they see exceptions — delayed packages, return requests, tracker alerts. Each role's home page is built around their actual job.

This is why we shipped 5 built-in roles on day one:

- **Admin** — system-level config, billing, users
- **Manager** — shipment approvals, cost governance, team assignments
- **Shipper** — print queue, labels, pickups
- **CS Agent** — exceptions, returns, claims
- **Client** — their own shipments (scoped)

## Roles in practice

The unlock is that each person stops needing to filter. The CS agent doesn't have to mentally skip past the print queue to find an exception. The Shipper doesn't see approval-pending items that aren't theirs to approve. Everyone's home page is already curated for their job.

This isn't about locking people out. Every role can click "All shipments" and see the team view. But the default is the thing you opened the app to do.

## Why not just use permissions?

Permissions are necessary but not sufficient. Giving a Shipper "view shipment" + "create shipment" + "print label" rights doesn't build their home page. Building a role-based product means **every page** knows who's looking at it.

This is why we built ShipDesk around roles first, and let rights be the finely-tuned knob on top.
