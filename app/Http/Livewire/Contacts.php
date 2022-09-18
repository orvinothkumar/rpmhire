<?php

namespace App\Http\Livewire;

use App\Models\Contact;
use Livewire\Component;
use Livewire\WithPagination;

class Contacts extends Component
{
    use WithPagination;
    public $q = '';
    public $sortBy = 'id';
    public $sortAsc = true;
    public $contact = array();

    protected $queryString = [
        'q' => ['except' => ''],
        'sortBy' => ['except' => 'id'],
        'sortAsc' => ['except' => true],
    ];

    protected $rules = [
        'contacts.contactId' => 'required',
        'contacts.contactData' => 'required',
        'contacts.status' => 'required',
    ];

    public function mount()
    {
        //
    }

    public function render()
    {
        $contacts = Contact::whereNotNull('created_at')
            ->when($this->q, function ($query) {
                return $query->where(function ($query) {
                    $query->where('contactId', 'like', '%' . $this->q . '%')
                        ->orwhere('contactData', 'like', '%' . $this->q . '%');
                });
            })
            ->orderBy($this->sortBy, $this->sortAsc ? 'ASC' : 'DESC'); //->get()->toArray();
        // dd($contacts);
        $query = $contacts->toSql();
        $contacts = $contacts->paginate(10);
        return view('livewire.contacts', [
            'contacts' => $contacts,
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
