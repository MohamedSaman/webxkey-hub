<?php

namespace App\Livewire\Projects;

use App\Models\Client;
use App\Models\Project;
use App\Models\Proposal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Projects')]
class ProjectPipeline extends Component
{
    public string $search = '';

    // New project modal
    public bool $showNewProject = false;
    public int $step = 1;

    // Step 1
    public string $clientMode = 'existing';   // existing | new
    public ?int $clientId = null;
    public string $newClientName = '';
    public string $newClientAddress = '';
    public string $newClientPhone = '';
    public string $newClientEmail = '';
    public string $newClientContact = '';

    // Step 2
    public string $projectName = '';
    public string $projectType = 'system';
    public string $projectDescription = '';
    public string $agreementCode = '';

    public function render()
    {
        $projects = Project::with(['client', 'latestProposal', 'invoices'])
            ->when($this->search, function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('agreement_code', 'like', "%{$this->search}%")
                    ->orWhereHas('client', fn ($cq) => $cq->where('name', 'like', "%{$this->search}%"));
            })
            ->orderByDesc('created_at')
            ->get();

        $columns = [
            'pending'   => $projects->where('status', 'pending')->merge($projects->where('status', 'draft'))->values(),
            'approved'  => $projects->where('status', 'approved')->values(),
            'ongoing'   => $projects->where('status', 'ongoing')->values(),
            'completed' => $projects->where('status', 'completed')->values(),
            'cancelled' => $projects->where('status', 'cancelled')->values(),
        ];

        return view('livewire.projects.project-pipeline', [
            'columns'   => $columns,
            'projects'  => $projects,
            'clients'   => Client::orderBy('name')->get(),
        ]);
    }

    public function openNewProject(): void
    {
        $this->reset([
            'step', 'clientMode', 'clientId',
            'newClientName', 'newClientAddress', 'newClientPhone',
            'newClientEmail', 'newClientContact',
            'projectName', 'projectType', 'projectDescription',
        ]);
        $this->step = 1;
        $this->clientMode = 'existing';
        $this->projectType = 'system';
        $this->agreementCode = Project::generateAgreementCode();
        $this->showNewProject = true;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            if ($this->clientMode === 'existing') {
                $this->validate(['clientId' => 'required|exists:clients,id']);
            } else {
                $this->validate([
                    'newClientName'    => 'required|string|max:255',
                    'newClientAddress' => 'nullable|string',
                    'newClientPhone'   => 'nullable|string|max:50',
                    'newClientEmail'   => 'nullable|email|max:255',
                ]);
            }
            $this->step = 2;
        }
    }

    public function prevStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function createProject()
    {
        $this->validate([
            'projectName'   => 'required|string|max:255',
            'projectType'   => 'required|in:system,website',
            'agreementCode' => 'required|string|max:32',
        ]);

        if ($this->clientMode === 'new') {
            $client = Client::create([
                'name'           => $this->newClientName,
                'address'        => $this->newClientAddress ?: null,
                'phone'          => $this->newClientPhone ?: null,
                'email'          => $this->newClientEmail ?: null,
                'contact_person' => $this->newClientContact ?: null,
            ]);
            $clientId = $client->id;
        } else {
            $clientId = $this->clientId;
        }

        $project = Project::create([
            'client_id'      => $clientId,
            'name'           => $this->projectName,
            'type'           => $this->projectType,
            'description'    => $this->projectDescription ?: null,
            'agreement_code' => $this->agreementCode,
            'status'         => 'pending',
        ]);

        $client = Client::find($clientId);

        $proposal = Proposal::create([
            'project_id'    => $project->id,
            'date'          => now()->toDateString(),
            'subject'       => 'Agreement for ' . $project->name,
            'intro_text'    => "We are pleased to formalize our agreement with WebxKey Pvt Ltd for the design, development, and implementation of a customized {$project->name} for {$client?->name}.",
            'template_type' => $project->type,
            'status'        => 'draft',
        ]);

        return redirect()->route('proposals.show', $proposal);
    }
}
