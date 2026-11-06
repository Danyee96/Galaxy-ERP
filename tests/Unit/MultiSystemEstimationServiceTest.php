<?php

namespace Tests\Unit;

use App\Services\Estimation\MultiSystemEstimationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class MultiSystemEstimationServiceTest extends TestCase
{
    private function calculate(array $items, string $mode = 'manual', array $stock = [], array $catalog = []): array
    {
        return (new MultiSystemEstimationService)->calculate($items, $catalog ?: [1 => ['name' => 'System A', 'recipe' => [['product_id' => 10, 'quantity' => 2]]], 2 => ['name' => 'System B', 'recipe' => [['product_id' => 10, 'quantity' => 4]]]], $stock ?: [10 => ['name' => 'Battery', 'total_qty' => 40, 'legacy_committed' => 0]], $mode);
    }

    public function test_single_model_requirement_and_capacity(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 10]]);
        $this->assertSame('20', $plan['components'][0]['required']);
        $this->assertSame(20, $plan['systems'][0]['standalone_maximum']);
        $this->assertSame(10, $plan['systems'][0]['feasible_units']);
        $this->assertTrue($plan['summary']['feasible']);
    }

    public function test_shared_materials_are_combined_and_not_double_allocated(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 10], ['product_id' => 2, 'quantity' => 5]], 'manual', [10 => ['name' => 'Battery', 'total_qty' => 35]]);
        $row = $plan['components'][0];
        $this->assertSame('40', $row['required']);
        $this->assertSame('5', $row['shortage']);
        $this->assertSame('32', $row['allocated']);
        $this->assertSame('3', $row['remaining']);
        $this->assertSame([10, 3], array_column($plan['systems'], 'feasible_units'));
        $this->assertCount(2, $row['consumers']);
        $this->assertSame([], $plan['systems'][0]['shortage_requirements']);
        $this->assertSame('5', $plan['systems'][1]['shortage_requirements'][0]['quantity']);
        $this->assertFalse($plan['summary']['feasible']);
    }

    public function test_priority_uses_remaining_stock_in_displayed_order(): void
    {
        $plan = $this->calculate([['product_id' => 2, 'quantity' => 1], ['product_id' => 1, 'quantity' => 1]], 'priority');
        $this->assertSame([10, 0], array_column($plan['systems'], 'planned_units'));
        $this->assertSame('0', $plan['components'][0]['remaining']);
        $this->assertSame(20, $plan['systems'][1]['standalone_maximum']);
    }

    public function test_balanced_ratio_is_integer_and_feasible(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 1, 'weight' => 1], ['product_id' => 2, 'quantity' => 1, 'weight' => 2]], 'balanced');
        $this->assertSame([4, 8], array_column($plan['systems'], 'planned_units'));
        $this->assertSame('40', $plan['components'][0]['allocated']);
        $this->assertTrue($plan['summary']['feasible']);
    }

    public function test_balanced_partial_round_is_deterministic(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 1], ['product_id' => 2, 'quantity' => 1]], 'balanced');
        $this->assertSame([7, 6], array_column($plan['systems'], 'planned_units'));
        $this->assertSame('2', $plan['components'][0]['remaining']);
    }

    public function test_balanced_retains_unused_stock_instead_of_claiming_global_maximum(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 1], ['product_id' => 2, 'quantity' => 1]], 'balanced', [10 => ['name' => 'A part', 'total_qty' => 1], 11 => ['name' => 'B part', 'total_qty' => 100]], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => 1]]], 2 => ['name' => 'B', 'recipe' => [['product_id' => 11, 'quantity' => 1]]]]);
        $this->assertSame([1, 2], array_column($plan['systems'], 'planned_units'));
        $this->assertSame('98', $plan['components'][1]['remaining']);
    }

    public function test_decimal_components_use_exact_fixed_point_arithmetic(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 7]], 'manual', [10 => ['name' => 'Material', 'total_qty' => '1.1000']], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => '0.125']]]]);
        $this->assertSame('0.875', $plan['components'][0]['required']);
        $this->assertSame('0.225', $plan['components'][0]['remaining']);
    }

    public function test_duplicate_recipe_lines_are_summed(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 2]], 'manual', [], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => 2], ['product_id' => 10, 'quantity' => 3]]]]);
        $this->assertSame('10', $plan['components'][0]['required']);
    }

    public function test_legacy_commitment_is_subtracted_once_and_transit_is_not_physical(): void
    {
        $stock = [10 => ['name' => 'Battery', 'total_qty' => 100, 'legacy_committed' => 20, 'temp_qty' => 130, 'air_transit' => 50]];
        $original = $stock;
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 10]], 'manual', $stock);
        $this->assertSame('80', $plan['components'][0]['available']);
        $this->assertSame('130', $plan['components'][0]['projected_stock']);
        $this->assertSame($original, $stock);
    }

    public function test_zero_stock_has_zero_capacity(): void
    {
        $plan = $this->calculate([['product_id' => 1, 'quantity' => 1]], 'manual', [10 => ['name' => 'Battery', 'total_qty' => 0]]);
        $this->assertSame(0, $plan['systems'][0]['standalone_maximum']);
        $this->assertSame('2', $plan['components'][0]['shortage']);
    }

    public function test_all_modes_conserve_shared_materials_across_varied_mixes(): void
    {
        mt_srand(116);
        for ($case = 0; $case < 20; $case++) {
            $catalog = []; $items = [];
            foreach ([1, 2, 3] as $id) {
                $catalog[$id] = ['name' => 'System '.$id, 'recipe' => [['product_id' => 10, 'quantity' => mt_rand(1, 4)], ['product_id' => 11, 'quantity' => mt_rand(1, 4)]]];
                $items[] = ['product_id' => $id, 'quantity' => mt_rand(1, 10), 'weight' => mt_rand(1, 4)];
            }
            $stock = [10 => ['name' => 'Shared A', 'total_qty' => mt_rand(0, 40)], 11 => ['name' => 'Shared B', 'total_qty' => mt_rand(0, 40)]];
            foreach (['manual', 'priority', 'balanced'] as $mode) {
                $plan = $this->calculate($items, $mode, $stock, $catalog);
                foreach ($plan['components'] as $component) {
                    $this->assertSame((int) $component['available'], (int) $component['allocated'] + (int) $component['remaining']);
                    $this->assertSame(max(0, (int) $component['required'] - (int) $component['available']), (int) $component['shortage']);
                    $attributed = 0;
                    foreach ($plan['systems'] as $system) foreach ($system['shortage_requirements'] as $missing) if ($missing['product_id'] === $component['product_id']) $attributed += (int) $missing['quantity'];
                    $this->assertSame((int) $component['shortage'], $attributed);
                    $this->assertGreaterThanOrEqual(0, (int) $component['remaining']);
                    if ($mode !== 'manual') $this->assertSame('0', $component['shortage']);
                }
            }
        }
    }

    /** @dataProvider invalidInputs */
    public function test_invalid_inputs_fail(array $items, array $stock, array $catalog): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->calculate($items, 'manual', $stock, $catalog);
    }

    public function invalidInputs(): array
    {
        return [
            'duplicate model' => [[['product_id' => 1, 'quantity' => 1], ['product_id' => 1, 'quantity' => 1]], [], []],
            'invalid model' => [[['product_id' => 99, 'quantity' => 1]], [], []],
            'negative quantity' => [[['product_id' => 1, 'quantity' => -1]], [], []],
            'fractional systems' => [[['product_id' => 1, 'quantity' => '1.5']], [], []],
            'missing recipe' => [[['product_id' => 1, 'quantity' => 1]], [], [1 => ['name' => 'A', 'recipe' => []]]],
            'missing inventory' => [[['product_id' => 1, 'quantity' => 1]], [11 => ['name' => 'Wrong', 'total_qty' => 10]], []],
            'zero recipe' => [[['product_id' => 1, 'quantity' => 1]], [], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => 0]]]]],
            'arithmetic overflow' => [[['product_id' => 1, 'quantity' => 1000000]], [], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => 1000000000]]]]],
            'unsupported precision' => [[['product_id' => 1, 'quantity' => 1]], [], [1 => ['name' => 'A', 'recipe' => [['product_id' => 10, 'quantity' => '0.00001']]]]],
        ];
    }
}
