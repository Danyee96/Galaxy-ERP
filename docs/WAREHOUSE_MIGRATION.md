# Warehouse activation and reconciliation

The feature is opt-in (`WAREHOUSES_ENABLED=false` by default). Activation must be explicit; deployment still requires its own reviewed backup and mapping.

1. Back up the database and use a maintenance window; pause workers and all stock writers. Test on a restored copy first.
2. Run the additive `2026_11_06_000000_create_warehouse_inventory.php` migration using the deployment's normal migration process.
3. Run `php artisan warehouses:backfill` for a read-only preflight, then explicitly run `php artisan warehouses:backfill --apply` only against the intended database.
4. The deterministic mapping is warehouse code `LEGACY`, no rack, row `Unassigned`, column `Unassigned`. Each physical source record is mapped independently; IDs from different source tables never overlap logically. Air/sea transit and projected/reserved quantities are not copied as physical stock.
5. Products use `total_qty`; raw, semi and finished goods use `current_stock`. Each serialized `FinishedModels` row maps to one unit (including WIP). `UPSModel.qty` and derived capacities are not physical stock. Existing transaction histories remain unchanged.
6. Backfill checks each source value before and after mapping and rolls back the entire operation on mismatch. Negative, malformed or over-precision legacy amounts must be explicitly resolved; they are never silently rounded or reset. Re-running is idempotent.
7. Enable `WAREHOUSES_ENABLED=true` only after reconciliation. Clear configuration cache through the normal deployment process. Run preflight/reconciliation again after activation.

The warehouse balances/ledger become authoritative. Existing physical totals are synchronized compatibility aggregates, never a second independently managed stock source. Projected stock changes only for new external receipts/issues, not internal transfers or arrivals already counted in transit. The existing integer product-transit receipt fields require whole-unit receipts; direct inbound supports four decimals.

Legacy stock requests are wrapped in one transaction under a common lock, with Eloquent stock observers mirroring deltas and rejecting stale totals. Existing forms get warehouse selectors and retry keys. The configured legacy allocation rule is ascending location ID across active physical warehouses unless source warehouses/location are selected; default receipts go to the deterministic legacy location. Disabled warehouses retain balances and history and are excluded from allocation.

Transfers dispatch into a system holding location (`INTERNAL-TRANSIT`); partial receipts release it to the destination. Combined stock remains unchanged and holding stock is excluded from estimation/production availability. Pending transfers do not reserve stock. Receive/dispatch retry keys are unique, and the entire request rolls back on insufficient material or invalid location.

Warehouse-filtered estimation conservatively subtracts active legacy commitments once from the selected pool because historical reservations have no warehouse assignment. It never reserves or deducts inventory. Saved calculation snapshots retain the warehouse breakdown at calculation time.

Corrections use referenced adjustment movements, never edits/deletes of movement history. Warehouse deletion is denied if balances/history/transfers exist; deactivate instead. Rollback refuses to drop tables once movement history exists. No production database has been migrated or modified automatically.

Stock views expose per-warehouse totals and an expandable breakdown. Movement reports contain warehouse/location and before/after balances, support all-row CSV for Excel, and print/PDF through the browser. Existing aggregate reports remain compatible through synchronized legacy fields; use the warehouse ledger for the source-location trace.

The latest local import contains 43 negative-balance products. At the user's request, local activation mapped 1,114 valid source records to Warehouse 1 / no rack / Row 1 / Column 1 using:

```sh
php artisan warehouses:backfill --apply --skip-negative-products --warehouse-name="Warehouse 1" --row=1 --column=1
```

A private database backup was taken before activation. Original physical/projected quantities were verified unchanged. Negative products retain their original balances and are recorded as `unassigned:product:ID` entries in warehouse state, with exclusion audit operations and no warehouse item/balance. The inventory warning links to a permission-protected correction/assignment screen. Enter the actual non-negative stock and destination to create the first balance; correction preserves the existing projected-minus-physical difference, records old/new quantities in the operation result, and clears the warning only after commit. Ordinary stock operations for excluded products are blocked until reconciliation. Reruns must use the exclusion option while these products remain negative. This activation applies only to the local imported database, not Hostinger.
