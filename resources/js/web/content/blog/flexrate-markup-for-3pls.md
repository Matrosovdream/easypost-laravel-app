---
title: FlexRate markup for 3PLs — what, why, how
date: 2026-04-02
author: Priya Nair
excerpt: If you run a small 3PL, your margin lives in the markup between carrier cost and client invoice. FlexRate is how we make that automatic.
ogImage: /og/flexrate.png
---

If you run a small 3PL, your margin lives in the markup between what the carrier charges you and what you invoice your client. Get this right, you make money. Get it wrong — or do it manually — you leak margin on every label.

## The manual way

Before FlexRate, most of our 3PL customers were doing this in spreadsheets. Every Monday morning, someone exported a shipments CSV, applied a formula (base + %, or flat uplift, or whatever the contract said), and generated invoices.

Predictable problems:
- Formulas drift across clients
- Rates change mid-month; nobody updates the sheet
- Contract renegotiation requires re-applying the formula historically
- Disputes happen when clients want line-by-line backup

## What FlexRate does

FlexRate is built into every label purchase. When you buy a label for Client A, ShipDesk applies Client A's markup rule in real time. The label goes out at carrier cost; the invoice goes out at client cost. The margin is captured on the shipment record itself.

Rules can be:
- **Percent** — 10% on top of carrier rate
- **Flat** — $1.25 per label
- **Tiered** — 15% ground, 8% priority, 5% overnight
- **Per-carrier** — different for UPS vs FedEx

## How it shows up at invoice time

At month end, ShipDesk generates a per-client invoice PDF with:
- Every shipment, with carrier cost and client charge
- Line-level markup
- Total owed, broken out by carrier and service level

You send the PDF. Your client sees exactly what they're paying for. There's no "trust me, my spreadsheet says."

## Why it matters

On 15,000 shipments a month, a 10% average markup on a $8 blended rate is $12K/month in margin that FlexRate captures cleanly. Done manually, our customers tell us they were leaking 20–30% of that through rate drift and invoice errors.

FlexRate is included on our 3PL tier ($999/mo). If you're running a small 3PL and still billing from a spreadsheet, call us — we'll show you the math.
