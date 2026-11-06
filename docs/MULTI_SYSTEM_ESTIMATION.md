# Multi-system production estimation

## Compatibility boundary

- Legacy estimates use `estimate_values.quantity_in_stock` as component demand and reduce projected `products.temp_qty`; physical `total_qty` is not deducted.
- `temp_qty` also includes incoming transit and clamps shortages to zero. It is not reliable physical availability. Subtracting legacy demand from it again would double-count commitments.
- New plans use `max(total_qty - active legacy demand, 0)`, show projected/transit stock separately, and never update stock. Their component snapshots are kept outside `estimate_values`, so legacy reservation reports and cancellation cannot accidentally treat new plans as reservations.
- Legacy save URLs/JSON envelopes, saved records, shortage sign conventions, and historical exports remain supported. Future legacy saves share the transaction-safe number allocator. No historical estimate is rewritten.

Legacy committed demand is conservative: old estimates have no invoice-fulfillment linkage, so stale active estimates may overstate commitments. Review them rather than automatically repairing historical balances.

## Allocation rules

- Manual: aggregate all requested requirements first. Feasible whole-unit counts are assigned in displayed row order, capped by each request. Allocated/remaining columns describe this executable mix; shortage compares the entire request with usable stock. Missing materials are attributed to unfulfilled systems using the leftover pool once, in row order.
- Priority: produce the maximum whole units of the first model, then the next using only remaining stock. Entered manual quantities are informational until switching back to manual.
- Balanced: normalize integer weights, allocate the maximum complete ratio batches, then at most one additional weighted round, ordered by the next unit's fraction of its weight (ties use displayed order). This limits ratio drift; unused stock is intentional. It is a deterministic heuristic, not a claim of a unique globally optimal mix.
- Components use fixed-point arithmetic with four decimal places. Duplicate recipe lines are summed; duplicate selected models are rejected. Missing/inactive inventory, missing recipes, nonpositive recipe quantities, unsupported precision, and arithmetic overflow fail validation.

## Persistence and safety

- Additive schema only: nullable plan metadata on existing estimates; child system/component snapshots; a locked estimate-number counter. Existing migration history is unchanged.
- Save recomputes recipes, physical stock, and active legacy demand under a transaction/row locks. A changed preview fingerprint returns HTTP 409 with a fresh calculation, requiring review before retry.
- A per-save UUID provides idempotency; reused keys with changed content are rejected. Numbers are assigned by the server, not accepted from a displayed hint.
- Saved details/PDF/Excel exports read immutable snapshots rather than current recipes or stock. New plans are not added together as commitments in aggregate legacy reports.
- Decimal recipe support does not change the existing integer bundle recipe schema; unsupported precision is rejected rather than rounded silently.
- The pre-existing invoice and legacy cancellation implementations still have their previously audited limitations. This feature does not repair historical stock/reservations or introduce production/purchase orders.

## Verification and operational limits

- 20 calculation tests (720 assertions) and 15 isolated persistence/HTTP tests (112 assertions) pass. Coverage includes shared stock, attributed shortages, allocation invariants, stale stock/recipes/legacy commitments, idempotency, roles, immutable exports, old save compatibility, and two concurrent SQLite connections.
- Desktop (1440px), tablet (768px), and phone (390px) browser checks pass without page overflow or JavaScript errors. PDF, CSV, and three-sheet XLSX downloads were verified against a disposable database.
- The additive migration and read-only preview were verified on MySQL. Existing physical/projected stock, legacy estimate fields, and legacy reservation details matched their pre-migration checksums. Concurrent MySQL writes have not been stress-tested.
- Requests allow up to 100 distinct models, manual quantities up to 1,000,000 per model, and integer balance weights up to 1,000. These are validation limits, not changes to recipe storage.
- Native XLSX uses the existing browser XLSX dependency; CSV remains available if that dependency cannot load. Saved plans do not reserve stock, so a saved proposal can cease to be executable as inventory changes.
