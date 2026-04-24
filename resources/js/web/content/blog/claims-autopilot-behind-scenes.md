---
title: Claims Autopilot — what actually happens when a package goes missing
date: 2026-04-18
author: Dana Okafor
excerpt: "Lost package. The two words every shipping team dreads. Here's what Claims Autopilot does — automatically — from the moment it detects a problem."
ogImage: /og/claims.png
---

"Lost package." The two words every shipping team dreads. Not because losing a package is rare — it isn't — but because filing the claim, chasing the carrier, and crediting the customer is death by a thousand emails.

## What Claims Autopilot detects

We watch every tracker. When a shipment hits any of these signals, Autopilot flags it:

- **No scan for 7 days** after the initial carrier pickup
- Carrier-reported status of `lost`, `damaged`, or `return_to_sender`
- Customer-reported non-delivery via the branded tracking page
- Shipment marked delivered but tracker shows no delivery scan

A flag triggers a review — not an automatic file, because some of these are false positives (rural USPS routes, for example, are notorious for missing intermediate scans).

## What the review looks like

A CS agent on your team gets a card: "Shipment EZ…123 may be lost. Carrier: UPS. Value: $89. Age: 8 days." They can:
1. **Confirm** — Autopilot files the claim
2. **Dismiss** — Autopilot closes the flag with a reason
3. **Request more info** — Autopilot messages the customer

## What Autopilot does on "Confirm"

It files the claim in the carrier's portal. Every carrier has its own dance. USPS wants a PS Form 1000. UPS wants a claim number and supporting photos. FedEx has an online form but you need the tracking number and declared value. DHL wants an email with specific fields.

Autopilot knows each carrier's requirements. It fills the form, uploads supporting docs (label PDF, tracker events, customer email), and submits. It then watches the carrier portal for status changes.

## What happens when the refund comes in

Carrier issues the refund. Autopilot logs it against the shipment. The credit auto-applies to the client's next invoice (or is issued as a refund, depending on the client contract). The shipment record shows the full timeline: lost → filed → approved → credited.

Your team does nothing except click "Confirm" on the flag.

## Pricing

Autopilot is success-fee only: 20% of the recovered amount. If nothing is recovered, you pay nothing. Available on Business tier and above.

To date, our customers have recovered over $2.1M through Autopilot. The average small 3PL recovers $3K–$8K/month — money that used to quietly vanish.
