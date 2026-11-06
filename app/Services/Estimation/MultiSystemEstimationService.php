<?php

namespace App\Services\Estimation;

use InvalidArgumentException;

/** Pure, fixed-point planning; never reads or writes inventory. */
class MultiSystemEstimationService
{
    private const SCALE = 10000;
    public const RULES = [
        'manual' => 'Combined demand is checked first. Feasible whole systems are assigned in displayed order, up to each requested quantity.',
        'priority' => 'Each model receives its maximum whole units from the stock remaining after higher-priority models. Manual quantities do not cap this proposal.',
        'balanced' => 'Maximum complete normalized ratio batches, followed by one partial weighted round ordered by the next unit fraction. Ties use displayed order. Stock may remain unused to limit ratio drift; this is not a unique global optimum.',
    ];

    public function calculate(array $items, array $catalog, array $stock, string $mode): array
    {
        if (!isset(self::RULES[$mode]) || !$items || count($items) > 100) {
            throw new InvalidArgumentException('Select 1–100 models and a supported planning mode.');
        }
        $systems = [];
        $seen = [];
        $recipes = [];
        foreach ($items as $item) {
            $id = (int) ($item['product_id'] ?? 0);
            if (isset($seen[$id])) throw new InvalidArgumentException('Each system model may appear only once.');
            $seen[$id] = true;
            if (!isset($catalog[$id])) throw new InvalidArgumentException('A selected model is missing or inactive.');
            $requested = $this->integer($item['quantity'] ?? null, 0, 1000000, 'Requested units');
            $weight = $this->integer($item['weight'] ?? 1, 1, 1000, 'Ratio weight');
            $recipe = [];
            foreach ($catalog[$id]['recipe'] as $line) {
                $component = (int) $line['product_id'];
                if (!isset($stock[$component])) throw new InvalidArgumentException('A recipe references missing or inactive inventory.');
                $quantity = $this->decimal($line['quantity'], 'Recipe quantity');
                if ($quantity <= 0) throw new InvalidArgumentException('Every recipe component must have a positive quantity.');
                $recipe[$component] = $this->add($recipe[$component] ?? 0, $quantity);
            }
            if (!$recipe) throw new InvalidArgumentException('Model '.$catalog[$id]['name'].' has no active recipe.');
            ksort($recipe);
            $recipes[$id] = $recipe;
            $systems[] = ['product_id' => $id, 'name' => $catalog[$id]['name'], 'sku' => $catalog[$id]['sku'] ?? '', 'requested_units' => $requested, 'weight' => $weight];
        }
        if ($mode === 'manual' && array_sum(array_column($systems, 'requested_units')) === 0) {
            throw new InvalidArgumentException('Request at least one system in manual mode.');
        }
        $pool = [];
        $source = [];
        foreach (array_unique(array_merge(...array_map('array_keys', array_values($recipes)))) as $id) {
            $s = $stock[$id];
            $physical = $this->decimal($s['total_qty'] ?? 0, 'Physical stock');
            $committed = $this->decimal($s['legacy_committed'] ?? 0, 'Legacy committed demand');
            if ($committed < 0) throw new InvalidArgumentException('Legacy committed demand contains a negative value and needs review.');
            $pool[$id] = max(0, $physical - $committed);
            $source[$id] = ['physical_stock' => $physical, 'legacy_committed' => $committed,
                'projected_stock' => $this->decimal($s['temp_qty'] ?? 0, 'Projected stock'),
                'in_transit' => $this->add($this->decimal($s['air_transit'] ?? 0, 'Air transit'), $this->decimal($s['sea_transit'] ?? 0, 'Sea transit'))];
        }
        $remaining = $pool;
        $feasible = [];
        if ($mode === 'balanced') {
            $weights = array_column($systems, 'weight');
            $gcd = array_reduce($weights, function ($carry, $value) { return $this->gcd($carry, $value); }, $weights[0]);
            $cycle = [];
            foreach ($systems as $i => $system) {
                $weight = intdiv($system['weight'], $gcd);
                $systems[$i]['normalized_weight'] = $weight;
                foreach ($recipes[$system['product_id']] as $id => $amount) $cycle[$id] = $this->add($cycle[$id] ?? 0, $this->multiply($amount, $weight));
            }
            $batches = $this->capacity($cycle, $pool);
            $round = [];
            foreach ($systems as $i => $system) {
                $id = $system['product_id'];
                $feasible[$id] = $this->multiply($batches, $system['normalized_weight']);
                $this->consume($remaining, $recipes[$id], $feasible[$id]);
                for ($n = 1; $n <= $system['normalized_weight']; $n++) $round[] = ['id' => $id, 'position' => $i, 'n' => $n, 'weight' => $system['normalized_weight']];
            }
            usort($round, function ($a, $b) { return ($a['n'] * $b['weight'] <=> $b['n'] * $a['weight']) ?: ($a['position'] <=> $b['position']); });
            foreach ($round as $step) {
                if ($this->capacity($recipes[$step['id']], $remaining) > 0) {
                    $feasible[$step['id']]++;
                    $this->consume($remaining, $recipes[$step['id']], 1);
                }
            }
        } else {
            foreach ($systems as $system) {
                $id = $system['product_id'];
                $maximum = $this->capacity($recipes[$id], $remaining);
                $feasible[$id] = $mode === 'manual' ? min($maximum, $system['requested_units']) : $maximum;
                $this->consume($remaining, $recipes[$id], $feasible[$id]);
            }
        }
        $required = array_fill_keys(array_keys($pool), 0);
        $consumers = array_fill_keys(array_keys($pool), []);
        foreach ($systems as &$system) {
            $id = $system['product_id'];
            $system['planned_units'] = $mode === 'manual' ? $system['requested_units'] : $feasible[$id];
            $system['feasible_units'] = $feasible[$id];
            $system['standalone_maximum'] = $this->capacity($recipes[$id], $pool);
            $system['bottlenecks'] = [];
            $system['recipe_snapshot'] = [];
            foreach ($recipes[$id] as $component => $perUnit) {
                $demand = $this->multiply($perUnit, $system['planned_units']);
                $required[$component] = $this->add($required[$component], $demand);
                $system['recipe_snapshot'][] = ['product_id' => $component, 'quantity' => $this->format($perUnit)];
                $consumers[$component][] = ['product_id' => $id, 'name' => $system['name'], 'per_unit' => $this->format($perUnit), 'units' => $system['planned_units'], 'required' => $this->format($demand)];
                if (intdiv($pool[$component], $perUnit) === $system['standalone_maximum']) $system['bottlenecks'][] = $stock[$component]['name'];
            }
        }
        unset($system);
        $components = [];
        foreach ($pool as $id => $available) {
            $row = ['product_id' => (int) $id, 'name' => $stock[$id]['name'], 'sku' => $stock[$id]['sku'] ?? '', 'consumers' => $consumers[$id]];
            foreach ($source[$id] as $key => $value) $row[$key] = $this->format($value);
            $row += ['available' => $this->format($available), 'required' => $this->format($required[$id]), 'allocated' => $this->format($available - $remaining[$id]), 'remaining' => $this->format($remaining[$id]), 'shortage' => $this->format(max(0, $required[$id] - $available))];
            $components[] = $row;
        }
        // Attribute missing materials against the remaining pool, once, in row order.
        $shortageBudget = $remaining;
        foreach ($systems as &$system) {
            $system['unfulfilled_units'] = $system['planned_units'] - $system['feasible_units'];
            $system['shortage_requirements'] = [];
            foreach ($recipes[$system['product_id']] as $component => $perUnit) {
                $unfilledDemand = $this->multiply($perUnit, $system['unfulfilled_units']);
                $missing = max(0, $unfilledDemand - $shortageBudget[$component]);
                $shortageBudget[$component] = max(0, $shortageBudget[$component] - $unfilledDemand);
                if ($missing) $system['shortage_requirements'][] = ['product_id' => $component, 'name' => $stock[$component]['name'], 'quantity' => $this->format($missing)];
            }
            $system['shortage_components'] = array_column($system['shortage_requirements'], 'name');
        }
        unset($system);
        foreach ($components as &$component) {
            $component['affected_models'] = [];
            foreach ($systems as $system) if (in_array($component['product_id'], array_column($system['shortage_requirements'], 'product_id'), true)) $component['affected_models'][] = $system['name'];
        }
        unset($component);
        $bottlenecks = [];
        foreach ($recipes as $recipe) foreach ($recipe as $id => $amount) if ($remaining[$id] < $amount) $bottlenecks[$id] = $stock[$id]['name'];
        return ['version' => 1, 'allocation_method' => $mode, 'allocation_rule' => self::RULES[$mode], 'systems' => $systems, 'components' => $components,
            'summary' => ['requested_units' => array_sum(array_column($systems, 'requested_units')), 'planned_units' => array_sum(array_column($systems, 'planned_units')), 'feasible_units' => array_sum($feasible), 'shortage_components' => count(array_filter($components, fn ($row) => $row['shortage'] !== '0')), 'feasible' => !count(array_filter($components, fn ($row) => $row['shortage'] !== '0')), 'bottlenecks' => array_values($bottlenecks)],
            'stock_policy' => 'Usable stock = max(physical stock − active legacy committed demand, 0). Transit is excluded. Projected stock is informational and is not subtracted again. This plan does not reserve or deduct inventory.'];
    }

    private function capacity(array $recipe, array $stock): int
    {
        $capacity = PHP_INT_MAX;
        foreach ($recipe as $id => $amount) $capacity = min($capacity, intdiv($stock[$id], $amount));
        return $capacity;
    }

    private function consume(array &$stock, array $recipe, int $units): void
    {
        foreach ($recipe as $id => $amount) $stock[$id] -= $this->multiply($amount, $units);
    }

    private function integer($value, int $min, int $max, string $label): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || $value < $min || $value > $max) throw new InvalidArgumentException($label.' is outside the supported integer range.');
        return (int) $value;
    }

    private function decimal($value, string $label): int
    {
        $value = $value === null || $value === '' ? '0' : (string) $value;
        if (!preg_match('/^(-?)(\d{1,10})(?:\.(\d{1,4}))?$/D', $value, $match) || abs((float) $value) > 1000000000) throw new InvalidArgumentException($label.' must be numeric, within one billion, and have at most four decimal places.');
        return (($match[1] ?? '') === '-' ? -1 : 1) * ((int) $match[2] * self::SCALE + (int) str_pad($match[3] ?? '', 4, '0'));
    }

    private function format(int $value): string
    {
        $negative = $value < 0;
        $value = abs($value);
        $fraction = rtrim(str_pad((string) ($value % self::SCALE), 4, '0', STR_PAD_LEFT), '0');
        return ($negative ? '-' : '').intdiv($value, self::SCALE).($fraction !== '' ? '.'.$fraction : '');
    }

    private function add(int $a, int $b): int
    {
        if ($b > 0 && $a > PHP_INT_MAX - $b) throw new InvalidArgumentException('The combined plan exceeds the supported quantity range.');
        return $a + $b;
    }

    private function multiply(int $a, int $b): int
    {
        if ($a < 0 || $b < 0 || ($a !== 0 && $b > intdiv(PHP_INT_MAX, $a))) throw new InvalidArgumentException('The combined plan exceeds the supported quantity range.');
        return $a * $b;
    }

    private function gcd(int $a, int $b): int
    {
        while ($b) { [$a, $b] = [$b, $a % $b]; }
        return $a;
    }
}
