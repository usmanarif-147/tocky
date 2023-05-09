<?php

namespace App\Http\Livewire\Admin\Card;

use App\Models\Card;
use Livewire\Component;

use Illuminate\Support\Str;


class Create extends Component
{

    public $heading;

    public $description, $quantity;


    protected function rules()
    {
        return [
            'description' => 'sometimes',
            'quantity' => 'numeric|min:1'
        ];
    }

    public function updated($fields)
    {
        $this->validateOnly($fields);
    }

    public function store()
    {
        $data = $this->validate();
        for ($i = 0; $i < $this->quantity; $i++) {
            
            $permitted_chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            
            Card::create([
                'uuid' => Str::uuid(),
                'activation_code' => generate_string($permitted_chars, 8),
                'description' => $this->description,
                'status' => 0,
            ]);
        }

        $this->dispatchBrowserEvent('swal:modal', [
            'type' => 'success',
            'message' => 'Card created successfully!',
        ]);
    }

    public function render()
    {

        $this->heading = "Create";

        return view('livewire.admin.card.create')
            ->layout('layouts.app');
    }
}
