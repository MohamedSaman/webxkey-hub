<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\Project;
use App\Models\Proposal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Clients')]
class ClientManager extends Component
{
    public string $search = '';

    // Modal state
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $address = '';
    public string $phone = '';
    public string $email = '';
    public string $contact_person = '';

    // New project flow
    public bool $showProjectModal = false;
    public ?int $projectClientId = null;
    public string $projectName = '';
    public string $projectType = 'system';
    public string $projectDescription = '';

    public function render()
    {
        $clients = Client::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")
                ->orWhere('phone', 'like', "%{$this->search}%"))
            ->withCount('projects')
            ->orderBy('name')
            ->get();

        return view('livewire.clients.client-manager', [
            'clients' => $clients,
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['editingId', 'name', 'address', 'phone', 'email', 'contact_person']);
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $client = Client::findOrFail($id);
        $this->editingId = $client->id;
        $this->name = $client->name;
        $this->address = $client->address ?? '';
        $this->phone = $client->phone ?? '';
        $this->email = $client->email ?? '';
        $this->contact_person = $client->contact_person ?? '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'nullable|string',
            'phone'          => 'nullable|string|max:50',
            'email'          => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
        ]);

        if ($this->editingId) {
            Client::find($this->editingId)?->update($data);
            session()->flash('message', 'Client updated.');
        } else {
            Client::create($data);
            session()->flash('message', 'Client created.');
        }

        $this->showModal = false;
        $this->reset(['editingId', 'name', 'address', 'phone', 'email', 'contact_person']);
    }

    public function delete(int $id): void
    {
        Client::findOrFail($id)->delete();
        session()->flash('message', 'Client deleted.');
    }

    public function openNewProject(int $clientId): void
    {
        $this->reset(['projectName', 'projectType', 'projectDescription']);
        $this->projectClientId = $clientId;
        $this->projectType = 'system';
        $this->showProjectModal = true;
    }

    public function createProject()
    {
        $data = $this->validate([
            'projectClientId'    => 'required|exists:clients,id',
            'projectName'        => 'required|string|max:255',
            'projectType'        => 'required|in:system,website',
            'projectDescription' => 'nullable|string',
        ]);

        $project = Project::create([
            'client_id'      => $data['projectClientId'],
            'name'           => $data['projectName'],
            'type'           => $data['projectType'],
            'description'    => $data['projectDescription'] ?? null,
            'agreement_code' => Project::generateAgreementCode(),
            'status'         => 'draft',
        ]);

        $client = Client::find($project->client_id);

        $proposal = Proposal::create([
            'project_id'     => $project->id,
            'date'           => now()->toDateString(),
            'subject'        => 'Agreement for ' . $project->name,
            'intro_text'     => "We are pleased to formalize our agreement with WebxKey Pvt Ltd for the design, development, and implementation of a customized {$project->name} for {$client?->name}.",
            'template_type'  => $project->type,
            'status'         => 'draft',
        ]);

        $this->showProjectModal = false;

        return redirect()->route('proposals.show', $proposal);
    }
}
