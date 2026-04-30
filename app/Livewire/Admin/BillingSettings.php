<?php

namespace App\Livewire\Admin;

use App\Models\BillingPlan;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.app', ['title' => 'Billing Settings'])]
class BillingSettings extends Component
{
    public $plans;
    public $name = '';
    public $price = 0;
    public $editingId = null;

    protected $rules = [
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
    ];

    public function mount()
    {
        $this->loadPlans();
    }

    public function loadPlans()
    {
        $this->plans = BillingPlan::all();
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            BillingPlan::find($this->editingId)->update([
                'name' => $this->name,
                'price' => $this->price,
            ]);
        } else {
            BillingPlan::create([
                'name' => $this->name,
                'price' => $this->price,
            ]);
        }

        $this->resetInput();
        $this->loadPlans();
        session()->flash('message', 'Billing plan saved successfully.');
    }

    public function edit($id)
    {
        $plan = BillingPlan::find($id);
        $this->editingId = $id;
        $this->name = $plan->name;
        $this->price = $plan->price;
    }

    public function delete($id)
    {
        BillingPlan::find($id)->delete();
        $this->loadPlans();
    }

    public function resetInput()
    {
        $this->name = '';
        $this->price = 0;
        $this->editingId = null;
    }

    public function render()
    {
        return view('livewire.admin.billing-settings');
    }
}
