<?php

namespace App\Livewire\Achievement;

use App\Models\Achievement;
use Livewire\Component;

class Show extends Component
{
    public Achievement $achievement;

    public function mount(Achievement $achievement)
    {
        $this->achievement = $achievement->load(['user', 'skill', 'proche', 'validations.user', 'informations']);
        
        $this->dispatchContext();
    }

    public function dispatchContext()
    {
        $items = collect([
            ['type' => 'achievement', 'id' => $this->achievement->id, 'name' => $this->achievement->title],
            ['type' => 'user', 'id' => $this->achievement->user_id, 'name' => $this->achievement->user->name],
            ['type' => 'skill', 'id' => $this->achievement->skill_id, 'name' => $this->achievement->skill->name ?? 'Expertise'],
        ]);

        if ($this->achievement->proche_id) {
            $items->push(['type' => 'user', 'id' => $this->achievement->proche->id, 'name' => $this->achievement->proche->name]);
        }

        $this->dispatch('updateMessagingContext', items: $items->unique(fn($o) => $o['type'].$o['id'])->values()->toArray())->to(\App\Livewire\GlobalMessaging::class);
    }

    public function render()
    {
        return view('livewire.achievement.show')->layoutData([
            'title' => 'Preuve : ' . $this->achievement->title . ' | guuu.fr',
            'description' => \Illuminate\Support\Str::limit($this->achievement->description, 160),
            'breadcrumbAchievement' => $this->achievement,
        ]);
    }
}
