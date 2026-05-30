<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::query()
            ->orderBy('type')
            ->orderBy('billing_cycle')
            ->get();

        return view('admin.subscription-plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.subscription-plans.form', [
            'plan' => new SubscriptionPlan([
                'is_active' => true,
                'duration_days' => 30,
                'billing_cycle' => 'monthly',
                'type' => 'silver',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        SubscriptionPlan::create($data);

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan created.');
    }

    public function edit(SubscriptionPlan $plan)
    {
        return view('admin.subscription-plans.form', ['plan' => $plan]);
    }

    public function update(Request $request, SubscriptionPlan $plan)
    {
        $data = $this->validated($request, $plan);
        $plan->update($data);

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan updated.');
    }

    public function destroy(SubscriptionPlan $plan)
    {
        if ($plan->subscriptions()->exists()) {
            return back()->withErrors([
                'error' => 'Cannot delete a plan that has subscriptions. Deactivate it instead.',
            ]);
        }

        $plan->delete();

        return redirect()
            ->route('admin.subscriptions.plans.index')
            ->with('success', 'Subscription plan deleted.');
    }

    private function validated(Request $request, ?SubscriptionPlan $plan = null): array
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:silver,gold,diamond',
            'billing_cycle' => 'required|string|in:monthly,yearly',
            'price' => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'is_active' => 'sometimes|boolean',
            'is_popular' => 'sometimes|boolean',
            'max_listings' => 'nullable|integer|min:0',
            'max_users' => 'nullable|integer|min:0',
            'priority_support' => 'sometimes|boolean',
            'advanced_reports' => 'sometimes|boolean',
        ]);

        $features = array_filter([
            'max_listings' => $validated['max_listings'] ?? null,
            'max_users' => $validated['max_users'] ?? null,
            'priority_support' => $request->boolean('priority_support'),
            'advanced_reports' => $request->boolean('advanced_reports'),
        ], fn ($v) => $v !== null);

        return [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'billing_cycle' => $validated['billing_cycle'],
            'price' => $validated['price'],
            'discount_price' => $validated['discount_price'] ?? null,
            'duration_days' => $validated['duration_days'],
            'is_trial' => false,
            'features' => $features,
            'is_active' => $request->boolean('is_active'),
            'is_popular' => $request->boolean('is_popular'),
        ];
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $counter = 1;

        while (
            SubscriptionPlan::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
