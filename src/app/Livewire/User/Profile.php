<?php

namespace App\Livewire\User;

use App\Models\Project;
use App\Models\ProjectMember;
use Livewire\Component;

use Livewire\WithFileUploads;

class Profile extends Component
{
    use \App\Traits\HandlesOfferActions;
    use WithFileUploads;

    public \App\Models\User $user;
    // Proches and Skills management
    public bool $showProcheModal = false;
    public string $procheName = '';
    public bool $showCreateModal = false;
    public int $step = 1;
    public string $skillName = '';
    public string $proofTitle = '';
    public string $proofDescription = '';
    public ?int $selectedSkillId = null;
    public string $proofState = 'actuelle';
    public ?int $selectedProcheId = null;
    public $realizedAt = '';
    public ?\App\Models\Achievement $draftAchievement = null;
    public int $valCount = 0;
    public int $rejCount = 0;
    public array $selectedSkillIds = [];
    public $availableSkills = [];

    // Direct Tagging UX
    public ?int $taggingRealisationId = null;
    public ?string $taggingRealisationType = null;
    public string $searchSkill = '';

    // Validation Details
    public bool $showValidationModal = false;
    public ?\App\Models\Achievement $selectedAchievement = null;
    public string $validationComment = '';
    public ?string $votingType = null;
    public string $replyText = '';

    // Project Creation UX
    public bool $isCreatingProject = false;
    public string $projectType = '';
    public string $projectTitle = '';
    public ?\App\Models\Project $draftProject = null;

    // Editing properties
    public bool $showEditModal = false;
    public $editingId;
    public ?string $editingType = null;
    public string $editTitle = '';
    public string $editDescription = '';
    public $editDate;

    protected $rules = [
        'skillName' => 'required_if:step,1|min:2',
        'proofTitle' => 'required_if:step,2|min:3',
        'proofDescription' => 'required_if:step,2|min:10',
        'procheName' => 'required_if:showProcheModal,true|min:2',
        'proofState' => 'required_if:step,2|in:actuelle,verrouillée,terminée',
        'realizedAt' => 'required_if:proofState,terminée|nullable|date',
    ];

    public function mount(\App\Models\User $user)
    {
        $this->user = $user->load([
            'activeJoinedCircles.owner.achievements.skill',
            'activeJoinedCircles.activeMembers.user.achievements.skill', 
            'achievements.skill', 
            'achievements.circle', 
            'achievements.validations.user.achievements.skill',
            'achievements.validations.user.proches.achievements.skill',
            'proches.achievements.skill',
            'proches.achievements.validations.user.achievements.skill',
            'proches.achievements.validations.user.proches.achievements.skill',
            'validationsReceived'
        ]);

        $this->dispatchContext();

        $this->valCount = $this->user->validationsReceived->where('type', 'validate')->count();
        $this->rejCount = $this->user->validationsReceived->where('type', 'reject')->count();

        if (auth()->check() && auth()->user()->coordinates && auth()->user()->location) {
            $this->lat = auth()->user()->coordinates['lat'];
            $this->lng = auth()->user()->coordinates['lng'];
            $this->locationName = auth()->user()->location;
        }

        $this->availableSkills = \App\Models\Skill::orderBy('name')->get();
    }

    public function setLocation($lat, $lng, $name)
    {
        if (auth()->check()) {
            auth()->user()->update([
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'location' => $name
            ]);
        }
    }

    public function dispatchContext()
    {
        $items = collect([
            ['type' => 'user', 'id' => $this->user->id, 'name' => $this->user->name],
        ]);
        
        foreach($this->user->activeJoinedCircles as $c) {
            $items->push(['type' => 'circle', 'id' => $c->id, 'name' => $c->name]);
        }
        
        foreach($this->user->achievements as $a) {
            $items->push(['type' => 'skill', 'id' => $a->skill_id, 'name' => $a->skill->name]);
        }

        $this->dispatch('updateMessagingContext', items: $items->unique(fn($o) => $o['type'].$o['id'])->values()->toArray())->to(\App\Livewire\GlobalMessaging::class);
    }


    public function canEdit(): bool
    {
        if (!auth()->check()) return false;
        
        // Owner can edit
        if (auth()->id() === $this->user->id) return true;
        
        // Parent can edit Proche
        if ($this->user->is_managed && $this->user->parent_id === auth()->id()) return true;
        
        return false;
    }


    public function openCreateModal($procheId = null)
    {
        \Log::info('openCreateModal triggered for proche: ' . ($procheId ?? 'null') . ' by user: ' . auth()->id());
        if (!$this->canEdit()) {
            \Log::warning('openCreateModal denied for user: ' . auth()->id());
            return;
        }
        $this->cancelCreate();
        $this->selectedProcheId = $procheId;
        $this->showCreateModal = true;
    }

    public function addProofForSkill($skillName, $procheId = null)
    {
        if (!$this->canEdit()) return;
        $this->cancelCreate(); // Ensure any previous drafts are cleaned up

        $this->skillName = $skillName;
        $this->selectedProcheId = $procheId;
        
        $skill = \App\Models\Skill::where('name', $this->skillName)->first();
        if (!$skill) {
            $skill = \App\Models\Skill::create([
                'name' => $this->skillName,
                'slug' => \Illuminate\Support\Str::slug($this->skillName),
            ]);
        }
        
        $this->selectedSkillId = $skill->id;

        // Ensure we don't hold a legacy draft ID from a previous cancelled form
        $this->draftAchievement = null;

        $this->step = 2; // Direct to Proof step
        $this->showCreateModal = true;
    }

    public function cancelCreate()
    {
        if ($this->draftAchievement && $this->draftAchievement->id) {
            $this->draftAchievement->delete();
        }
        $this->reset(['step', 'skillName', 'selectedSkillId', 'selectedProcheId', 'proofTitle', 'proofDescription', 'proofState', 'realizedAt', 'draftAchievement', 'showCreateModal', 'selectedSkillIds']);
    }

    public function submitSkillOnly()
    {
        if (!$this->canEdit()) return;
        $this->validateOnly('skillName');

        $skill = \App\Models\Skill::firstOrCreate([
            'name' => $this->skillName,
        ], [
            'slug' => \Illuminate\Support\Str::slug($this->skillName),
        ]);

        \App\Models\Achievement::create([
            'user_id' => $this->selectedProcheId ? null : $this->user->id,
            'proche_id' => $this->selectedProcheId,
            'skill_id' => $skill->id,
            'title' => '__SKELETON__',
            'description' => 'Détails et preuves à venir...',
            'is_verified' => false,
        ]);

        $this->showCreateModal = false;
        $this->user->load('achievements.skill', 'achievements.circle');
        
        $this->dispatch('notify', [
            'message' => 'Compétence ajoutée au profil !',
            'type' => 'success'
        ]);
    }

    public function submitProof()
    {
        if (!$this->canEdit()) return;
        $this->validate();

        if ($this->draftAchievement) {
            $this->draftAchievement->update([
                'title' => $this->proofTitle,
                'description' => $this->proofDescription,
                'realized_at' => $this->proofState === 'terminée' ? $this->realizedAt : null,
                'metadata' => ['status' => $this->proofState],
            ]);
        } else {
            $achievement = \App\Models\Achievement::create([
                'user_id' => $this->selectedProcheId ? null : $this->user->id,
                'proche_id' => $this->selectedProcheId,
                'skill_id' => $this->selectedSkillId,
                'circle_id' => null, 
                'title' => $this->proofTitle,
                'description' => $this->proofDescription,
                'realized_at' => $this->proofState === 'terminée' ? $this->realizedAt : null,
                'is_verified' => false, 
                'metadata' => ['status' => $this->proofState],
            ]);
        }

        if ($this->draftAchievement) {
            $this->draftAchievement->skills()->sync($this->selectedSkillIds);
        } else {
            $achievement->skills()->sync($this->selectedSkillIds);
        }

        $this->reset('draftAchievement');
        $this->showCreateModal = false;
        $this->user->recalculateTrustScore(); // Might change if we had base points for adding proofs
        $this->user->load('achievements.skill', 'achievements.circle', 'proches.achievements.skill', 'achievements.informations');
        
        $this->dispatch('notify', [
            'message' => 'Preuve ajoutée avec succès !',
            'type' => 'success'
        ]);
    }

    public function initiateValidation(int $achievementId, string $type)
    {
        if (!auth()->check() || auth()->id() === $this->user->id) return;

        // Perform vote immediately
        \App\Models\AchievementValidation::updateOrCreate(
            ['user_id' => auth()->id(), 'achievement_id' => $achievementId],
            ['type' => $type]
        );

        $this->selectedAchievement = \App\Models\Achievement::with([
            'validations.user.achievements.skill',
            'validations.user.proches.achievements.skill'
        ])->find($achievementId);
        $this->votingType = $type; // Still useful to show "You voted [Type]" in modal
        $this->showValidationModal = true;
        
        $this->user->recalculateTrustScore();
        $this->user->load('achievements.validations.user', 'proches.achievements.validations.user');
        
        // Load my existing comment if any
        $myV = $this->selectedAchievement->validations->where('user_id', auth()->id())->first();
        $this->validationComment = $myV ? ($myV->comment ?? '') : '';
    }

    public function confirmValidation()
    {
        if (!$this->selectedAchievement) return;
        if (!auth()->check() || auth()->id() === $this->user->id) return;

        $v = \App\Models\AchievementValidation::where('user_id', auth()->id())
            ->where('achievement_id', $this->selectedAchievement->id)
            ->first();

        // Lock if reply exists
        if ($v && $v->reply) {
            $this->dispatch('notify', ['message' => 'Impossible de modifier un vote ayant reçu une réponse.', 'type' => 'error']);
            return;
        }

        if ($v) {
            $v->update(['comment' => $this->validationComment]);
        }

        $this->user->load('achievements.validations.user', 'proches.achievements.validations.user');
        
        $this->showValidationModal = false;
        $this->votingType = null;
        $this->validationComment = '';

        $this->dispatch('notify', ['message' => 'Commentaire enregistré !', 'type' => 'success']);
    }

    public function openValidationModal(int $achievementId)
    {
        $this->selectedAchievement = \App\Models\Achievement::with('validations.user')->find($achievementId);
        $this->votingType = null; // Read-only mode
        $this->showValidationModal = true;
        $this->replyText = ''; // Clear reply buffer
    }

    public function submitReply(int $validationId)
    {
        $v = \App\Models\AchievementValidation::with('achievement')->find($validationId);
        if (!$v || $v->achievement->user_id !== auth()->id()) return;
        if ($v->reply) return; // Already replied

        $v->update(['reply' => $this->replyText]);
        
        $this->replyText = '';
        $this->selectedAchievement = \App\Models\Achievement::with([
            'validations.user.achievements.skill',
            'validations.user.proches.achievements.skill'
        ])->find($v->achievement_id);
        $this->user->load([
            'achievements.validations.user.achievements.skill', 
            'achievements.validations.user.proches.achievements.skill',
            'proches.achievements.validations.user.achievements.skill',
            'proches.achievements.validations.user.proches.achievements.skill'
        ]);
        
        $this->dispatch('notify', ['message' => 'Réponse envoyée ! L\'échange est maintenant verrouillé.', 'type' => 'success']);
    }

    public function openProcheModal()
    {
        if (auth()->id() !== $this->user->id) return;
        $this->reset('procheName');
        $this->showProcheModal = true;
    }

    public function createProche()
    {
        if (auth()->id() !== $this->user->id) return;
        $this->validateOnly('procheName');

        \App\Models\Proche::create([
            'name' => $this->procheName,
            'parent_id' => auth()->id(),
        ]);

        $this->showProcheModal = false;
        $this->user->load('proches');

        $this->dispatch('notify', [
            'message' => 'Proche créé avec succès !',
            'type' => 'success'
        ]);
    }

    public function generateTransfer(int $procheId)
    {
        $proche = \App\Models\Proche::find($procheId);
        if (!$proche || $proche->parent_id !== auth()->id()) return;

        $proche->update([
            'transfer_token' => \Illuminate\Support\Str::random(40),
            'transfer_code' => strtoupper(\Illuminate\Support\Str::random(6)),
        ]);

        $this->user->load('proches');

        $this->dispatch('notify', [
            'message' => 'Code de transfert généré !',
            'type' => 'success'
        ]);
    }

    // Project creation logic is now handled in Home -> Mission -> Realisation flow

    public function render()
    {
        \Log::info('Profile render: showCreateModal=' . ($this->showCreateModal ? 'true' : 'false'));
        // 1. Members of my ACTIVE circles (excluding myself)
        $circleMemberIds = \App\Models\CircleMember::whereIn('circle_id', $this->user->activeJoinedCircles->pluck('id'))
            ->where('status', 'active')
            ->where('user_id', '!=', $this->user->id)
            ->pluck('user_id')
            ->unique();

        // 2. Experts from these circles (limit and sort by trust)
        $networkExperts = \App\Models\User::whereIn('id', $circleMemberIds)
            ->with(['achievements' => fn($q) => $q->where('title', '!=', '__SKELETON__')->with('skill')])
            ->get()
            ->filter(fn($u) => $u->achievements->count() > 0)
            ->sortByDesc('trust_score')
            ->take(20);

        // 3. Proches Achievements for networking
        $prochesAchievements = \App\Models\Achievement::whereIn('proche_id', $this->user->proches->pluck('id'))
            ->with(['skill', 'proche', 'informations', 'validations.user'])
            ->get();

        // 4. User Projects (owned or active member)
        try {
            $memberProjectIds = ProjectMember::where('memberable_type', \App\Models\User::class)
                ->where('memberable_id', $this->user->id)
                ->where('status', 'active')
                ->pluck('project_id');

            $allUserProjects = Project::where(function($q) use ($memberProjectIds) {
                    $q->where('owner_id', $this->user->id)
                      ->orWhereIn('id', $memberProjectIds);
                })
                ->whereNotNull('skill_id')
                ->with(['owner', 'activeMembers.memberable', 'skill', 'messages', 'informations', 'skills'])
                ->latest()
                ->get();
            
            $userProjects = $allUserProjects->take(20);
        } catch (\Exception $e) {
            $allUserProjects = collect([]);
            $userProjects = collect([]);
        }

        // Combine local and proches achievements
        $allAchievements = $this->user->achievements->merge($prochesAchievements);

        // Group everything by Skill Name
        $combinedGrouped = collect();

        // Add Achievements
        foreach ($allAchievements as $ach) {
            if (!$combinedGrouped->has($ach->skill->name)) {
                $combinedGrouped->put($ach->skill->name, collect());
            }
            $combinedGrouped->get($ach->skill->name)->push([
                'type' => 'achievement',
                'model' => $ach,
                'date' => $ach->realized_at ?? $ach->created_at,
            ]);
        }

        // Add Projects
        foreach ($allUserProjects as $proj) {
            if (!$combinedGrouped->has($proj->skill->name)) {
                $combinedGrouped->put($proj->skill->name, collect());
            }
            $combinedGrouped->get($proj->skill->name)->push([
                'type' => 'project',
                'model' => $proj,
                'date' => $proj->realized_at ?? $proj->created_at,
            ]);
        }

        // Sort each group by date desc
        $combinedGrouped = $combinedGrouped->map(function($items) {
            return $items->sortByDesc('date');
        });

        $userOffers = \App\Models\ProjectOffer::whereIn('project_id', $userProjects->pluck('id'))
            ->where('type', 'offer')
            ->with(['project', 'informations', 'reviews'])
            ->latest()
            ->take(20)
            ->get();

        $ownedProjectsCount = $this->user->ownedProjects()->count();
        $ownedOffersCount = \App\Models\ProjectOffer::whereHas('project', function($q) {
            $q->where('owner_id', $this->user->id);
        })->where('type', 'offer')->count();

        return view('livewire.user.profile', [
            'groupedAchievements' => $combinedGrouped,
            'networkExperts' => $networkExperts,
            'canEdit' => $this->canEdit(),
            'userProjects' => $userProjects,
            'userOffers' => $userOffers,
            'ownedProjectsCount' => $ownedProjectsCount,
            'ownedOffersCount' => $ownedOffersCount,
            'activeProject' => $this->user->activeProject(),
            'allAchievements' => $allAchievements,
        ])->layoutData([
            'title' => $this->user->name . ' | Expertises & Confiance (' . $this->user->trust_score . '%) | guuu.fr',
            'description' => \Illuminate\Support\Str::limit('Consultez le profil de ' . $this->user->name . ' : ' . $allAchievements->count() . ' compétences validées par la communauté. Score de confiance : ' . $this->user->trust_score . '%. Expertises principales : ' . $allAchievements->pluck('skill.name')->unique()->take(5)->implode(', '), 160, '...'),
            'og_image' => $this->user->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->user->name),
            'breadcrumbUser' => $this->user,
        ]);
    }

    public function startConversation()
    {
        if (!auth()->check()) return;
        
        $items = collect([
            ['type' => 'user', 'id' => $this->user->id, 'name' => $this->user->name]
        ]);
        $this->dispatch('updateMessagingContext', items: $items->unique(fn($o) => $o['type'].$o['id'])->values()->toArray())->to(\App\Livewire\GlobalMessaging::class);
        
        $this->dispatch('openConversationWith', userId: $this->user->id)->to(\App\Livewire\GlobalMessaging::class);
    }

    public function toggleProjectStatus(int $projectId)
    {
        $project = Project::findOrFail($projectId);
        if (!$project->canManage(auth()->user())) return;
        $project->toggleStatus();
    }

    public function addSkillToRealisation($skillName, $realisationId, $realisationType)
    {
        $skill = \App\Models\Skill::firstOrCreate(
            ['name' => $skillName],
            ['slug' => \Illuminate\Support\Str::slug($skillName)]
        );

        if ($realisationType === 'achievement') {
            $ach = \App\Models\Achievement::findOrFail($realisationId);
            // Allow if owner or parent of proche
            if ($ach->user_id === auth()->id() || ($ach->proche && $ach->proche->parent_id === auth()->id())) {
                $ach->skills()->syncWithoutDetaching([$skill->id]);
            }
        } else {
            $proj = \App\Models\Project::findOrFail($realisationId);
            if ($proj->canManage(auth()->user())) {
                $proj->skills()->syncWithoutDetaching([$skill->id]);
            }
        }

        $this->reset(['taggingRealisationId', 'taggingRealisationType', 'searchSkill']);
        $this->dispatch('notify', ['message' => 'Compétence ajoutée !', 'type' => 'success']);
    }

    public function initProjectCreation()
    {
        if (!$this->canEdit()) return;
        
        $this->cancelProjectCreation(); // Clean up

        $this->draftProject = \App\Models\Project::create([
            'title' => 'Brouillon...',
            'description' => '',
            'owner_id' => $this->user->id,
            'status' => 'brouillon',
            'metadata' => ['status' => 'brouillon'],
        ]);

        $this->draftProject->addMember(auth()->user(), 'admin');
        $this->isCreatingProject = true;
    }

    public function cancelProjectCreation()
    {
        if ($this->draftProject) {
            $this->draftProject->delete();
            $this->reset('draftProject');
        }
        $this->reset(['projectTitle', 'isCreatingProject']);
    }

    public function confirmProjectCreation()
    {
        if (!$this->canEdit()) return;
        
        $this->validate([
            'projectTitle' => 'required|min:3|max:255',
        ]);

        if ($this->draftProject) {
            $this->draftProject->update([
                'title' => $this->projectTitle,
                'status' => 'actuelle',
                'metadata' => ['status' => 'actuelle'],
            ]);
            $project = $this->draftProject;
        } else {
            $project = \App\Models\Project::create([
                'title' => $this->projectTitle,
                'owner_id' => $this->user->id,
                'status' => 'actuelle',
                'metadata' => ['status' => 'actuelle'],
            ]);
            $project->addMember(auth()->user(), 'admin');
        }

        // Create first message
        \App\Models\Message::create([
            'project_id' => $project->id,
            'sender_id' => auth()->id(),
            'content' => "Nouvelle réalisation lancée depuis le profil de " . $this->user->name . ".",
            'type' => 'chat',
        ]);

        $this->reset(['draftProject', 'projectTitle', 'isCreatingProject']);
        session()->flash('success', 'Réalisation lancée !');
        return redirect()->route('projects.show', $project);
    }

    public function joinProject(int $projectId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $project = Project::findOrFail($projectId);
        
        if ($project->isMember(auth()->user()) || $project->isOwner(auth()->user())) {
            $this->dispatch('notify', ['message' => 'Vous participez déjà à cette réalisation.', 'type' => 'info']);
            return;
        }

        $project->addMember(auth()->user(), 'member', 'active');
        
        $this->dispatch('notify', ['message' => 'Vous avez rejoint la réalisation !', 'type' => 'success']);
        
        // Optionally redirect to the project page
        return redirect()->route('projects.show', $project);
    }

    public function deleteProject(int $id)
    {
        $project = \App\Models\Project::findOrFail($id);
        if (!$project->canManage(auth()->user())) return;
        
        $project->delete();
        session()->flash('success', 'Réalisation supprimée.');
    }

    public function deleteAchievement(int $id)
    {
        $achievement = \App\Models\Achievement::findOrFail($id);
        if (!$this->canEdit() && $achievement->user_id !== auth()->id()) return;
        
        $achievement->delete();
        session()->flash('success', 'Expertise supprimée.');
    }

    public function editItem(string $type, int $id)
    {
        $this->editingType = $type;
        $this->editingId = $id;

        if ($type === 'project') {
            $item = \App\Models\Project::findOrFail($id);
            if (!$item->canManage(auth()->user())) return;
        } else {
            $item = \App\Models\Achievement::findOrFail($id);
            if (!$this->canEdit() && $item->user_id !== auth()->id()) return;
        }

        $this->editTitle = $item->title;
        $this->editDescription = $item->description;
        $this->editDate = $item->realized_at ? $item->realized_at->format('Y-m-d') : null;
        $this->showEditModal = true;
    }

    public function saveEdit()
    {
        $this->validate([
            'editTitle' => 'required|min:3|max:255',
            'editDescription' => 'nullable|max:2000',
            'editDate' => 'nullable|date',
        ]);

        if ($this->editingType === 'project') {
            $item = \App\Models\Project::findOrFail($this->editingId);
            if (!$item->canManage(auth()->user())) return;
        } else {
            $item = \App\Models\Achievement::findOrFail($this->editingId);
            if (!$this->canEdit() && $item->user_id !== auth()->id()) return;
        }

        $item->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'realized_at' => $this->editDate,
        ]);

        $this->cancelEdit();
        session()->flash('success', 'Modifications enregistrées.');
    }

    public function cancelEdit()
    {
        $this->reset(['showEditModal', 'editingId', 'editingType', 'editTitle', 'editDescription', 'editDate']);
    }
}
