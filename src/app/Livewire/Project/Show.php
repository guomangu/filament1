<?php

namespace App\Livewire\Project;

use App\Models\Project;
use App\Models\ProjectReview;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class Show extends Component
{
    use WithFileUploads;
    use \App\Traits\HandlesOfferActions;

    public Project $project;
    
    // Review form (Note: some are now handled by trait)
    public $replyTo = null;

    // Message form
    public $message = '';
    public $lastMessageCount = 0;
    public $attachment = null;
    public $upload = null;
    public $guestName = '';
    public $guestContact = '';

    // Membership management
    public $userSearch = '';
    public $lastPendingCount = 0;
    public $showMemberManager = false;

    // Progressive management (Petit à Petit)
    public $offerTitle = '';
    public $offerDescription = '';
    public $offerImages = [];
    public $offerInfos = [['label' => '', 'title' => '']];
    
    // Editing states
    public $showOfferForm = false;
    public $editingOfferId = null;
    
    // Smart Skill Selector (Now for Project)
    public $selectedSkills = []; // Format: ['Skill name 1', 'Skill name 2']
    public $skillSearch = '';
    public $showProjectSkillForm = false;
    
    // Address Management
    public $address = '';
    public $isEditingAddress = false;

    // Réalisation specific
    public $additionalSkillName = '';
    public $showSkillTagForm = false;
    
    protected $isInitialLoad = true;

    public function mount(Project $project)
    {
        $this->project = $project;
        $this->lastMessageCount = $this->project->messages()->count();
        $this->lastPendingCount = $this->project->members()->where('status', 'pending')->count();
        $this->refresh();
        $this->selectedSkills = $this->project->skills->pluck('name')->toArray();
        $this->address = $this->project->address;

        $this->dispatchContext();
    }

    public function dispatchContext()
    {
        $items = collect([
            ['type' => 'project', 'id' => $this->project->id, 'name' => $this->project->title],
            ['type' => 'user', 'id' => $this->project->owner_id, 'name' => $this->project->owner->name],
        ]);
        
        foreach($this->project->activeMembers as $m) {
            $items->push(['type' => 'user', 'id' => $m->memberable_id, 'name' => $m->memberable->name]);
        }
        
        foreach($this->project->offers as $o) {
            $items->push(['type' => 'skill', 'id' => $o->skill_id ?? 0, 'name' => $o->title]);
        }
        
        foreach($this->project->skills as $s) {
            $items->push(['type' => 'skill', 'id' => $s->id, 'name' => $s->name]);
        }

        $this->dispatch('updateMessagingContext', items: $items->unique(fn($o) => $o['type'].$o['id'])->values()->toArray())->to(\App\Livewire\GlobalMessaging::class);
    }

    public function refresh()
    {
        try {
            $this->project->load([
                'owner',
                'activeMembers.memberable',
                'members.memberable',
                'offers' => fn($q) => $q->with(['skills', 'reviews.user', 'reviews.replies.user', 'informations']),
                'reviews.user',
                'reviews.replies.user',
                'messages' => fn($q) => $q->with('sender')->latest()->take(20),
                'informations'
            ]);

            $currentMessageCount = $this->project->messages()->count();
            $currentPendingCount = $this->project->members()->where('status', 'pending')->count();

            if (!$this->isInitialLoad && ($currentMessageCount !== $this->lastMessageCount || $currentPendingCount !== $this->lastPendingCount)) {
                $this->dispatch('project-updated');
                $this->lastMessageCount = $currentMessageCount;
                $this->lastPendingCount = $currentPendingCount;
            } else {
                $this->lastMessageCount = $currentMessageCount;
                $this->lastPendingCount = $currentPendingCount;
                $this->isInitialLoad = false;
            }
        } catch (\Exception $e) {
            // Silently fail for polling
        }
    }

    public function addSkill($name = null)
    {
        $name = $name ?: $this->skillSearch;
        if (empty($name)) return;
        
        $name = mb_convert_case(trim($name), MB_CASE_TITLE, "UTF-8");
        
        // Prevent duplicates (case-insensitive check)
        $existing = collect($this->selectedSkills)->map(fn($s) => mb_strtolower($s))->contains(mb_strtolower($name));
        
        if (!$existing) {
            $this->selectedSkills[] = $name;
        }
        $this->skillSearch = '';
    }

    public function removeSkill($name)
    {
        $this->selectedSkills = array_filter($this->selectedSkills, fn($s) => $s !== $name);
    }

    public function saveProjectSkills()
    {
        if (!$this->project->canManage(Auth::user())) return;

        $skillIds = [];
        foreach ($this->selectedSkills as $name) {
            $skill = \App\Models\Skill::firstOrCreate(
                ['name' => $name],
                ['slug' => \Illuminate\Support\Str::slug($name)]
            );
            $skillIds[] = $skill->id;
        }

        $this->project->skills()->sync($skillIds);
        $this->showProjectSkillForm = false;
        $this->project->refresh();
        session()->flash('success', 'Compétences du projet mises à jour.');
    }

    public function editOffer($id)
    {
        $offer = \App\Models\ProjectOffer::with('informations')->findOrFail($id);
        if (!$this->project->canManage(Auth::user())) return;

        $this->editingOfferId = $id;
        $this->offerTitle = $offer->title;
        $this->offerDescription = $offer->description;
        
        $this->offerInfos = $offer->informations->map(fn($info) => [
            'label' => $info->label,
            'title' => $info->title
        ])->toArray();
        
        if (empty($this->offerInfos)) {
            $this->offerInfos = [['label' => '', 'title' => '']];
        }

        $this->showOfferForm = true;
    }

    public function addOfferInfo()
    {
        $this->offerInfos[] = ['label' => '', 'title' => ''];
    }

    public function removeOfferInfo($index)
    {
        unset($this->offerInfos[$index]);
        $this->offerInfos = array_values($this->offerInfos);
    }

    public function deleteOffer($id)
    {
        $offer = \App\Models\ProjectOffer::findOrFail($id);
        if (!$this->project->canManage(Auth::user())) return;

        $offer->delete();
        $this->project->refresh();
        session()->flash('success', 'Offre supprimée.');
    }

    public function addOffer()
    {
        if (!$this->project->canManage(Auth::user())) return;

        $this->validate([
            'offerTitle' => 'required|min:3',
            'offerDescription' => 'nullable',
            'offerImages.*' => 'image|max:10240',
            'offerInfos.*.label' => 'nullable|string|max:50',
            'offerInfos.*.title' => 'nullable|required_with:offerInfos.*.label|string|max:255',
        ]);

        $imagePaths = [];
        if (!empty($this->offerImages)) {
            foreach ($this->offerImages as $image) {
                $imagePaths[] = $image->store('project-offers', 'public');
            }
        }

        $data = [
            'title' => $this->offerTitle,
            'description' => $this->offerDescription,
            'type' => 'offer',
        ];

        if (!empty($imagePaths)) {
            $data['images'] = $imagePaths;
        }

        $isUpdate = (bool)$this->editingOfferId;
        if ($this->editingOfferId) {
            $projectOffer = \App\Models\ProjectOffer::findOrFail($this->editingOfferId);
            if (!isset($data['images']) && $projectOffer->images) {
                unset($data['images']); 
            }
            $projectOffer->update($data);
        } else {
            $projectOffer = $this->project->allOffers()->create($data);
        }

        // Sync Information
        $projectOffer->informations()->delete();
        foreach ($this->offerInfos as $info) {
            if (!empty($info['title'])) {
                $projectOffer->informations()->create([
                    'label' => $info['label'],
                    'title' => $info['title'],
                    'author_id' => Auth::id(),
                ]);
            }
        }
        
        $this->offerTitle = '';
        $this->offerDescription = '';
        $this->offerInfos = [['label' => '', 'title' => '']];
        $this->offerImages = [];
        $this->showOfferForm = false;
        $this->editingOfferId = null;
        $this->project->refresh();
        session()->flash('success', $isUpdate ? 'Offre mise à jour !' : 'Offre ajoutée !');
    }
    public function getTeamMembers()
    {
        return $this->project->activeMembers()
            ->with('memberable')
            ->get()
            ->map(function($member) {
                if ($member->memberable instanceof \App\Models\User) {
                    $member->trustPath = auth()->check() ? auth()->user()->getTrustPathTo($member->memberable) : [];
                    $member->degree = count($member->trustPath) <= 2 ? 1 : (count($member->trustPath) <= 4 ? 2 : 3);
                } else {
                    $member->trustPath = [];
                    $member->degree = 3;
                }
                return $member;
            });
    }

    public function getNetworkExperts()
    {
        if (!auth()->check()) return collect();

        $me = auth()->user();
        $projectSkills = $this->project->allSkills()->pluck('name')->toArray();
        if (empty($projectSkills)) return collect();

        // Reusing the "Network" logic from Profile search but tailored for project skills
        $myCircleIds = $me->activeJoinedCircles->pluck('id');
        $firstDegreeUserIds = \App\Models\CircleMember::whereIn('circle_id', $myCircleIds)
            ->where('status', 'active')
            ->pluck('user_id')
            ->unique();

        $secondDegreeCircleIds = \App\Models\CircleMember::whereIn('user_id', $firstDegreeUserIds)
            ->where('status', 'active')
            ->pluck('circle_id')
            ->unique();

        $secondDegreeUserIds = \App\Models\CircleMember::whereIn('circle_id', $secondDegreeCircleIds)
            ->where('status', 'active')
            ->pluck('user_id')
            ->unique()
            ->diff($firstDegreeUserIds)
            ->diff([$me->id]);

        $networkUserIds = $firstDegreeUserIds->merge($secondDegreeUserIds)->unique()
            ->diff($this->project->activeMembers->pluck('memberable_id'));

        return \App\Models\User::whereIn('id', $networkUserIds)
            ->whereHas('achievements', function($q) use ($projectSkills) {
                $q->whereHas('skill', function($sq) use ($projectSkills) {
                    $sq->whereIn('name', $projectSkills);
                });
            })
            ->with(['achievements.skill', 'activeJoinedCircles'])
            ->get()
            ->map(function($u) use ($firstDegreeUserIds, $projectSkills) {
                $u->degree = $firstDegreeUserIds->contains($u->id) ? 1 : 2;
                $u->trustPath = auth()->user()->getTrustPathTo($u);
                
                // Find matching skill for reason
                $matchingSkill = $u->achievements->first(fn($a) => in_array($a->skill?->name, $projectSkills));
                $u->matchReason = $matchingSkill ? "Expertise: " . $matchingSkill->skill->name : "Compétences";
                
                return $u;
            })
            ->sortBy('degree')
            ->take(20);
    }

    public function toggleStatus()
    {
        if (!$this->project->canManage(Auth::user())) {
            session()->flash('error', 'Vous n\'avez pas la permission de modifier ce projet.');
            return;
        }

        $this->project->toggleStatus();
        session()->flash('success', 'Statut du projet mis à jour.');
        $this->project->refresh();
    }

    public function updateAddress()
    {
        if (!$this->project->canManage(Auth::user())) return;

        $this->validate([
            'address' => 'nullable|string|max:255',
        ]);

        $this->project->update([
            'address' => $this->address,
        ]);

        $this->isEditingAddress = false;
        $this->project->refresh();
        session()->flash('success', 'Localisation mise à jour.');
    }

    public function joinProject()
    {
        if ($this->project->isMember(Auth::user()) || $this->project->isPending(Auth::user())) {
            session()->flash('error', 'Vous avez déjà postulé ou êtes déjà membre.');
            return;
        }

        $this->project->addMember(Auth::user(), 'member', 'pending');
        session()->flash('success', 'Votre candidature a été envoyée !');
        $this->project->refresh();
    }

    public function inviteUser($userId)
    {
        if (!$this->project->canManage(Auth::user())) return;

        $user = \App\Models\User::findOrFail($userId);
        if ($this->project->isMember($user)) {
            session()->flash('error', 'Cet utilisateur est déjà membre.');
            return;
        }

        $this->project->addMember($user, 'member', 'invited');
        $this->userSearch = '';
        session()->flash('success', 'Invitation envoyée !');
        $this->project->refresh();
    }

    public function approveMember($memberId)
    {
        if (!$this->project->canManage(Auth::user())) return;

        $member = \App\Models\ProjectMember::findOrFail($memberId);
        $member->update(['status' => 'active']);
        session()->flash('success', 'Membre approuvé !');
        $this->project->refresh();
    }

    public function rejectMember($memberId)
    {
        if (!$this->project->canManage(Auth::user())) return;

        $member = \App\Models\ProjectMember::findOrFail($memberId);
        $member->delete();
        session()->flash('success', 'Demande refusée/annulée.');
        $this->project->refresh();
    }

    public function acceptInvitation()
    {
        $member = $this->project->members()
            ->where('memberable_type', \App\Models\User::class)
            ->where('memberable_id', Auth::id())
            ->where('status', 'invited')
            ->firstOrFail();

        $member->update(['status' => 'active']);
        session()->flash('success', 'Vous avez rejoint le projet !');
        $this->project->refresh();
    }

    public function getUserSuggestionsProperty()
    {
        if (strlen($this->userSearch) < 2) return collect();
        
        return \App\Models\User::where('name', 'like', '%' . $this->userSearch . '%')
            ->where('id', '!=', auth()->id())
            ->whereDoesntHave('projectMemberships', function($q) {
                $q->where('project_id', $this->project->id);
            })
            ->with('activeJoinedCircles')
            ->take(5)
            ->get();
    }

    public function leaveProject()
    {
        if ($this->project->isOwner(Auth::user())) {
            session()->flash('error', 'Le propriétaire ne peut pas quitter le projet.');
            return;
        }

        $this->project->removeMember(Auth::user());
        session()->flash('success', 'Vous avez quitté le projet.');
        $this->project->refresh();
    }

    public function setReplyTo($reviewId)
    {
        $this->replyTo = $reviewId;
    }

    public function sendMessage()
    {
        $rules = [
            'message' => 'required|min:2|max:1000',
            'upload' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:10240', // 10MB limit
        ];
        if (auth()->guest()) {
            $rules['guestName'] = 'required|min:2|max:50';
            $rules['guestContact'] = 'required|min:2|max:100';
        }
        $this->validate($rules);

        $metadata = $this->attachment ? ['attachment' => $this->attachment] : [];
        if (auth()->guest()) {
            $metadata['guest'] = [
                'name' => $this->guestName,
                'contact' => $this->guestContact,
            ];
        }

        if ($this->upload) {
            $path = $this->upload->store('attachments', 'public');
            $metadata['file'] = [
                'path' => $path,
                'name' => $this->upload->getClientOriginalName(),
                'type' => $this->upload->getMimeType(),
                'size' => $this->upload->getSize(),
            ];
        }

        \App\Models\Message::create([
            'circle_id' => null,
            'project_id' => $this->project->id,
            'sender_id' => auth()->id(),
            'content' => $this->message,
            'type' => 'chat',
            'metadata' => !empty($metadata) ? $metadata : null,
        ]);

        $this->message = '';
        $this->attachment = null;
        $this->upload = null;
        $this->guestName = '';
        $this->guestContact = '';
        $this->project->load('messages.sender');
        $this->dispatch('messageSent');
    }

    public function selectAttachment($type, $id, $name)
    {
        $this->attachment = [
            'type' => $type,
            'id' => $id,
            'name' => $name
        ];
    }

    public function removeAttachment()
    {
        $this->attachment = null;
    }

    public function addAdditionalSkill()
    {
        if (!$this->project->canManage(Auth::user())) return;
        
        $this->validate(['additionalSkillName' => 'required|min:2|max:50']);
        
        $skill = \App\Models\Skill::firstOrCreate(
            ['name' => $this->additionalSkillName],
            ['slug' => \Illuminate\Support\Str::slug($this->additionalSkillName)]
        );
        
        $this->project->skills()->syncWithoutDetaching([$skill->id]);
        $this->additionalSkillName = '';
        $this->showSkillTagForm = false;
        $this->project->refresh();
        session()->flash('success', 'Compétence ajoutée !');
    }

    public function lockRealisation()
    {
        if (!$this->project->canManage(Auth::user())) return;
        
        $this->project->update([
            'status' => 'verrouillée',
            'locked_at' => now(),
        ]);
        
        \App\Models\Message::create([
            'project_id' => $this->project->id,
            'sender_id' => Auth::id(),
            'content' => "🔒 La réalisation a été verrouillée. Les termes du contrat sont désormais fixes.",
            'type' => 'chat',
        ]);
        
        $this->project->refresh();
        session()->flash('success', 'Réalisation verrouillée !');
    }

    public function completeRealisation()
    {
        if (!$this->project->canManage(Auth::user())) return;
        
        $this->project->update([
            'status' => 'terminée',
            'realized_at' => now(),
            'is_open' => false,
        ]);
        
        \App\Models\Message::create([
            'project_id' => $this->project->id,
            'sender_id' => Auth::id(),
            'content' => "✅ La réalisation est officiellement terminée ! Merci à tous les participants.",
            'type' => 'chat',
        ]);
        
        $this->project->refresh();
        session()->flash('success', 'Réalisation terminée !');
    }

    public function cancelRealisation()
    {
        if (!$this->project->canManage(Auth::user())) return;
        
        $this->project->update([
            'status' => 'annulée',
            'is_open' => false,
        ]);
        
        \App\Models\Message::create([
            'project_id' => $this->project->id,
            'sender_id' => Auth::id(),
            'content' => "🚫 La réalisation a été annulée.",
            'type' => 'chat',
        ]);
        
        $this->project->refresh();
        session()->flash('success', 'Réalisation annulée.');
    }

    public function getSkillSuggestionsProperty()
    {
        if (strlen($this->skillSearch) < 1) return collect();

        $searchTerm = '%' . $this->skillSearch . '%';

        return \App\Models\Skill::where(function($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhereHas('achievements', function($aq) use ($searchTerm) {
                      $aq->where('title', 'like', $searchTerm);
                  });
            })
            ->whereNotIn('name', $this->selectedSkills)
            ->orderBy('name')
            ->take(8)
            ->get();
    }

    public function render()
    {
        $offerCount = $this->project->offers->count();
        $offersText = $offerCount > 0 ? ' (' . $offerCount . ' offres disponibles)' : '';
        $location = $this->project->city ? ' à ' . $this->project->city : '';

        return view('livewire.project.show')->layoutData([
            'title' => 'Projet : ' . $this->project->title . $location . $offersText . ' | guuu.fr',
            'description' => \Illuminate\Support\Str::limit('Découvrez le projet "' . $this->project->title . '"' . $location . '. ' . ($offerCount > 0 ? 'Consultez nos ' . $offerCount . ' offres de services. ' : '') . strip_tags($this->project->description), 160, '...'),
            'og_image' => $this->project->owner->avatar_url ?? 'https://ui-avatars.com/api/?name=' . urlencode($this->project->owner->name),
            'breadcrumbProject' => $this->project,
        ]);
    }
}
