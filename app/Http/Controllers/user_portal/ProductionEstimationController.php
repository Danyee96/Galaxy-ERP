<?php

namespace App\Http\Controllers\user_portal;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductionEstimationRequest;
use App\Models\ProductEstimate;
use App\Services\Estimation\PreviewChangedException;
use App\Services\Estimation\ProductionEstimationPlanner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PDF;

class ProductionEstimationController extends Controller
{
    public function preview(ProductionEstimationRequest $request, ProductionEstimationPlanner $planner)
    {
        try { return response()->json(['plan' => $planner->preview($request->planningInput())]); }
        catch (InvalidArgumentException $e) { throw ValidationException::withMessages(['models' => $e->getMessage()]); }
    }

    public function store(ProductionEstimationRequest $request, ProductionEstimationPlanner $planner)
    {
        try {
            $estimate = $planner->save($request->planningInput(), (int) $request->user()->id);
            return response()->json(['id' => $estimate->id, 'number' => $estimate->number, 'url' => route('productionEstimation.show', $estimate->id)], 201);
        } catch (PreviewChangedException $e) {
            return response()->json(['message' => $e->getMessage(), 'plan' => $e->snapshot], 409);
        } catch (InvalidArgumentException $e) { throw ValidationException::withMessages(['models' => $e->getMessage()]); }
    }

    public function show($id)
    {
        $estimate = $this->estimate($id);
        return view('user_portal.estimation.show', ['estimate' => $estimate, 'plan' => $estimate->planningSnapshot()]);
    }

    public function pdf($id)
    {
        $estimate = $this->estimate($id);
        return PDF::loadView('user_portal.estimation.print', ['estimate' => $estimate, 'plan' => $estimate->planningSnapshot()])->setPaper('a4', 'landscape')->download('Production_Plan_'.$estimate->number.'.pdf');
    }

    public function csv($id)
    {
        $estimate = $this->estimate($id);
        $plan = $estimate->planningSnapshot();
        return response()->streamDownload(function () use ($estimate, $plan) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            $write = function (array $values) use ($out) {
                fputcsv($out, array_map(function ($v) { $v = (string) $v; return preg_match('/^[\x00-\x20]*[=+@\-]|^[\t\r]/', $v) && !is_numeric($v) ? "'".$v : $v; }, $values));
            };
            $write(['Production plan', $estimate->number, $estimate->title, $estimate->plan_date]);
            $write(['Mode', $plan['allocation_method'], $plan['allocation_rule']]);
            $write(['System', 'SKU', 'Requested', 'Planned', 'Feasible', 'Maximum alone', 'Weight', 'Bottleneck', 'Shortage materials']);
            foreach ($plan['systems'] as $system) $write([$system['name'], $system['sku'], $system['requested_units'], $system['planned_units'], $system['feasible_units'], $system['standalone_maximum'], $system['weight'], implode('; ', $system['bottlenecks']), implode('; ', array_map(fn ($row) => $row['name'].': '.$row['quantity'], $system['shortage_requirements'] ?? []))]);
            $write([]);
            $write(['Component', 'SKU', 'Physical', 'Legacy committed', 'Projected', 'In transit', 'Usable', 'Required', 'Allocated', 'Remaining', 'Shortage', 'Consuming systems']);
            foreach ($plan['components'] as $row) $write([$row['name'], $row['sku'], $row['physical_stock'], $row['legacy_committed'], $row['projected_stock'], $row['in_transit'], $row['available'], $row['required'], $row['allocated'], $row['remaining'], $row['shortage'], implode('; ', array_column($row['consumers'], 'name'))]);
            if (!empty($plan['warehouse_ids']) || !empty($plan['components'][0]['warehouses'])) {
                $write([]); $write(['Component','Warehouse','Physical','Available','Allocated']);
                foreach($plan['components'] as $component) foreach($component['warehouses'] ?? [] as $warehouse) $write([$component['name'],$warehouse['name'],$warehouse['physical'],$warehouse['available'],$warehouse['allocated']]);
            }
            fclose($out);
        }, 'Production_Plan_'.$estimate->number.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function archive($id)
    {
        $this->estimate($id)->update(['status' => 0]);
        return redirect()->route('productEstimates')->with('message', 'Production plan archived. Inventory was not changed.');
    }

    private function estimate($id): ProductEstimate
    {
        abort_unless(Auth::check() && in_array((int) Auth::user()->role_id, [1, 4, 5], true), 403);
        return ProductEstimate::whereNotNull('planning_version')->findOrFail($id);
    }
}
