<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        $plans = Plan::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q')->toString();
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.planes.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.planes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'description'    => ['nullable', 'string'],
            'slug'           => ['nullable', 'string', 'max:120', 'unique:plans,slug'],
            'sessions_total' => ['required', 'integer', 'min:0'],
            'duration_months'=> ['required', 'integer', 'min:1', 'max:60'],
            'price'          => ['required', 'numeric', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'is_active'      => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        Plan::create($data);

        return redirect()->route('admin.planes.index')->with('ok', 'Plan creado correctamente.');
    }

    public function edit(Plan $plan)
    {
        return view('admin.planes.edit', compact('plan'));
    }

    public function update(Request $request, Plan $plan)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:120'],
            'description'    => ['nullable', 'string'],
            'slug'           => ['nullable', 'string', 'max:120', 'unique:plans,slug,' . $plan->id],
            'sessions_total' => ['required', 'integer', 'min:0'],
            'duration_months'=> ['required', 'integer', 'min:1', 'max:60'],
            'price'          => ['required', 'numeric', 'min:0'],
            'currency'       => ['required', 'string', 'size:3'],
            'is_active'      => ['boolean'],
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $plan->update($data);

        return redirect()->route('admin.planes.index')->with('ok', 'Plan actualizado correctamente.');
    }

    public function destroy(Plan $plan)
    {
        $plan->delete();

        return back()->with('ok', 'Plan eliminado.');
    }
}
