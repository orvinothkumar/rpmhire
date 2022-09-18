<?php

namespace App\Http\Livewire;

use App\Models\Contract;
use Livewire\Component;
use Livewire\WithPagination;

class Contracts extends Component
{
    use WithPagination;
    public $q = '';
    public $sortBy = 'id';
    public $sortAsc = true;
    public $contract = array();

    protected $queryString = [
        'q' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortAsc' => ['except' => true],
    ];

    protected $rules = [
        'contracts.contractId' => 'required',
        'contracts.contractData' => 'required',
        'contracts.status' => 'required',
    ];

    public function mount()
    {
        //
    }

    public function render()
    {
        $contracts = Contract::whereNotNull('created_at')
            ->when($this->q, function ($query) {
                return $query->where(function ($query) {
                    $query->where('contractId', 'like', '%' . $this->q . '%')
                        ->orwhere('contractData', 'like', '%' . $this->q . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC'); //->get()->toArray();
        // dd($contracts);
        $query = $contracts->toSql();
        $contracts = $contracts->paginate(5);
        return view('livewire.contracts', [
            'contracts' => $contracts,
            'query' => $query,
        ]);
    }

    public function updatingQ()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($field == $this->sortBy) {
            $this->sortAsc = !$this->sortAsc;
        }
        $this->sortBy = $field;
    }
}
