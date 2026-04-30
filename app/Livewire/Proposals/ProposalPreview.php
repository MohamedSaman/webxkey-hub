<?php

namespace App\Livewire\Proposals;

use App\Models\Proposal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Proposal Preview')]
class ProposalPreview extends Component
{
    public Proposal $proposal;

    public function mount(Proposal $proposal): void
    {
        $this->proposal = $proposal->load(['project.client', 'modules.features', 'quotationItems']);
    }

    public function render()
    {
        return view('livewire.proposals.proposal-preview');
    }
}
