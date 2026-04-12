<?php

namespace App\Livewire\Mission;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Skill;
use App\Models\Project;
use App\Models\Message;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    public Skill $skill;
    public $title = '';
    public $description = '';
    public string $status = 'actuelle';
    public $realizedAt = '';
    public ?Project $draftProject = null;
    public array $selectedSkillIds = [];
    public $availableSkills = [];
    public bool $showCreationForm = false;
    public string $newSkillName = '';

    public function mount(Skill $skill)
    {
        $this->skill = $skill;
        $this->availableSkills = Skill::where('id', '!=', $skill->id)->orderBy('name')->get();
    }

    public function initDraft()
    {
        if (!Auth::check()) return;

        $this->cancelDraft(); // Clean any previous draft

        $this->draftProject = Project::create([
            'title' => 'Brouillon...',
            'description' => '',
            'owner_id' => Auth::id(),
            'skill_id' => $this->skill->id,
            'status' => 'brouillon',
            'metadata' => ['status' => 'brouillon'],
        ]);
        
        $this->showCreationForm = true;
        
        // As a draft, maybe don't add member yet or add him so he can see it if he refreshes (but we will delete on cancel)
        $this->draftProject->addMember(Auth::user(), 'admin');
    }

    public function cancelDraft()
    {
        if ($this->draftProject) {
            $this->draftProject->delete();
            $this->reset('draftProject');
        }
        $this->reset(['title', 'description', 'status', 'realizedAt', 'selectedSkillIds', 'showCreationForm']);
    }

    public function createRealisation()
    {
        if (!Auth::check()) return;

        $this->validate([
            'title' => 'required|min:3|max:255',
            'description' => 'nullable|max:1000',
            'status' => 'required|in:actuelle,verrouillée,terminée',
            'realizedAt' => 'required_if:status,terminée|nullable|date',
        ]);

        if ($this->draftProject) {
            $this->draftProject->update([
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'realized_at' => $this->status === 'terminée' ? $this->realizedAt : null,
                'metadata' => ['status' => $this->status],
            ]);
            $project = $this->draftProject;
        } else {
            $project = Project::create([
                'title' => $this->title,
                'description' => $this->description,
                'owner_id' => Auth::id(),
                'skill_id' => $this->skill->id,
                'status' => $this->status,
                'realized_at' => $this->status === 'terminée' ? $this->realizedAt : null,
                'metadata' => ['status' => $this->status],
            ]);

            $project->addMember(Auth::user(), 'admin');
        }

        // Create and sync skills
        $finalSkillIds = $this->selectedSkillIds;
        
        // Handle potentially comma separated new skills or single new skill
        if (!empty($this->newSkillName)) {
            $newSkills = array_map('trim', explode(',', $this->newSkillName));
            foreach ($newSkills as $name) {
                if (empty($name)) continue;
                $skill = Skill::firstOrCreate(
                    ['name' => mb_convert_case($name, MB_CASE_TITLE, "UTF-8")],
                    ['slug' => \Illuminate\Support\Str::slug($name)]
                );
                $finalSkillIds[] = $skill->id;
            }
        }

        $project->skills()->sync(array_unique($finalSkillIds));

        // Automatic creation of private conversation/first message
        Message::create([
            'project_id' => $project->id,
            'sender_id' => Auth::id(),
            'content' => "Début de la réalisation pour la mission : " . $this->skill->name . ". Définissons ensemble les termes.",
            'type' => 'chat',
        ]);

        session()->flash('success', 'Réalisation créée !');
        return redirect()->route('projects.show', $project);
    }

    public function render()
    {
        $currentRealisations = $this->skill->projects()
            ->whereIn('status', ['actuelle', 'verrouillée'])
            ->with(['owner', 'activeMembers.memberable'])
            ->latest()
            ->get();

        $projects = $this->skill->projects()
            ->where('status', 'terminée')
            ->with(['owner', 'activeMembers.memberable', 'reviews', 'skills'])
            ->latest()
            ->get();

        $achievements = \App\Models\Achievement::where('skill_id', $this->skill->id)
            ->where('title', '!=', '__SKELETON__')
            ->with(['user', 'proche', 'validations', 'informations', 'skill'])
            ->latest()
            ->get();

        $finishedRealisations = $projects->map(fn($p) => [
            'type' => 'project',
            'model' => $p,
            'date' => $p->realized_at ?? $p->created_at,
            'title' => $p->title,
            'description' => $p->description,
            'owner' => $p->owner,
            'activeMembers' => $p->activeMembers,
            'skills' => $p->skills,
            'id' => $p->id,
        ])->concat($achievements->map(fn($a) => [
            'type' => 'achievement',
            'model' => $a,
            'date' => $a->realized_at ?? $a->created_at,
            'title' => $a->title,
            'description' => $a->description,
            'owner' => $a->user,
            'activeMembers' => collect(), // Or map some members if needed
            'skills' => collect([$this->skill]),
            'id' => $a->id,
        ]))->sortByDesc('date');

        $topExpert = \App\Models\User::whereHas('achievements', function($q) {
                $q->where('skill_id', $this->skill->id)
                  ->where('title', '!=', '__SKELETON__');
            })
            ->with(['achievements.skill', 'informations'])
            ->orderByDesc('trust_score')
            ->first();

        return view('livewire.mission.show', [
            'currentRealisations' => $currentRealisations,
            'finishedRealisations' => $finishedRealisations,
            'topExpert' => $topExpert,
        ])->layoutData([
            'title' => 'Mission : ' . $this->skill->name . ' | guuu.fr',
            'breadcrumbSkill' => $this->skill,
        ]);
    }
}
