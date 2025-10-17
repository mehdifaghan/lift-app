<?php
namespace App\Http\Controllers\Api\v1\Manager;
use App\Domain\Buildings\Building;
use Illuminate\Http\Request;
class BuildingController {
    public function index(Request $r){ return Building::paginate($r->input('limit',15)); }
    public function store(Request $r){ $d=$r->validate(['name'=>'required|max:255','address'=>'nullable','lat'=>'nullable|numeric','lng'=>'nullable|numeric']); return response()->json(Building::create($d),201); }
    public function show(Building $building){ return $building; }
    public function update(Request $r, Building $building){ $d=$r->validate(['name'=>'sometimes|max:255','address'=>'nullable','lat'=>'nullable|numeric','lng'=>'nullable|numeric']); $building->update($d); return $building; }
    public function destroy(Building $building){ $building->delete(); return response()->noContent(); }
}
