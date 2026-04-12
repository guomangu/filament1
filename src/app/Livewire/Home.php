<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Url;

class Home extends Component
{
    use \App\Traits\HandlesOfferActions;
    #[Url]
    public $search = '';
    public $lat;
    public $lng;
    public $locationName;

    public function mount()
    {
        if (auth()->check() && auth()->user()->coordinates && auth()->user()->location) {
            $this->lat = auth()->user()->coordinates['lat'];
            $this->lng = auth()->user()->coordinates['lng'];
            $this->locationName = auth()->user()->location;
        }
    }

    public function resetLocation()
    {
        $this->reset(['lat', 'lng', 'locationName']);
    }

    public function setLocation($lat, $lng, $name)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->locationName = $name;

        if (auth()->check()) {
            auth()->user()->update([
                'coordinates' => ['lat' => $lat, 'lng' => $lng],
                'location' => $name
            ]);
        }
    }

    public function render()
    {
        // 1. Fetch Circles
        $circleQuery = \App\Models\Circle::with([
            'owner.achievements.skill', 
            'owner.proches.achievements.skill',
            'members.user.achievements.skill', 
            'members.user.proches.achievements.skill',
            'achievements.skill'
        ]);

        // Circle Accessibility Logic
        $circleQuery->where(function($q) {
            $q->where('is_public', true);
            if (auth()->check()) {
                $myCircleIds = auth()->user()->joinedCircles()->pluck('circles.id')->toArray();
                $q->orWhereHas('members', function($mq) use ($myCircleIds) {
                    $mq->whereIn('circle_id', $myCircleIds);
                });
                $q->orWhere('owner_id', auth()->id());
            }
        });

        // 2. Search Logic
        if (!empty($this->search)) {
            $searchTerm = '%' . mb_strtolower($this->search) . '%';
            
            // Filter Circles
            $circleQuery->where(function($q) use ($searchTerm) {
                $q->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(description) LIKE ?', [$searchTerm])
                  ->orWhereRaw('LOWER(address) LIKE ?', [$searchTerm])
                  ->orWhereHas('owner', function($uq) use ($searchTerm) {
                      $uq->whereRaw('LOWER(name) LIKE ?', [$searchTerm])
                        ->orWhereHas('achievements.skill', fn($sq) => $sq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]));
                  })
                  ->orWhereHas('members.user.achievements.skill', fn($sq) => $sq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]))
                  ->orWhereHas('achievements.skill', fn($sq) => $sq->whereRaw('LOWER(name) LIKE ?', [$searchTerm]));
            });
        }

        $allResults = $circleQuery->get();

        // 4. Proximity & Trust Logic
        $viewer = auth()->user();
        $myCircleIds = $viewer ? $viewer->activeJoinedCircles->pluck('id') : collect();

        $formattedResults = $allResults->map(function($entity) use ($myCircleIds, $viewer) {
            $entity->is_circle = $entity instanceof \App\Models\Circle;
            $entity->is_mission = $entity instanceof \App\Models\Skill;

            if ($entity->is_circle) {
                $entity->proximity_type = 'global';
                $entity->proximity_level = 5;

                if ($myCircleIds->contains($entity->id)) {
                    $entity->proximity_type = 'direct';
                    $entity->proximity_level = 1;
                } elseif ($viewer) {
                    $myCities = $viewer->activeJoinedCircles->pluck('city')->filter()->unique();
                    if ($myCities->contains($entity->city)) {
                        $entity->proximity_type = 'city';
                        $entity->proximity_level = 3;
                    }
                }

                $entity->trustPath = $viewer ? $viewer->getTrustPathTo($entity) : [];
            } else {
                // Missions don't have proximity levels in the same way
                $entity->proximity_type = 'mission';
                $entity->proximity_level = 10;
                $entity->trustPath = [];
            }

            // Smart Distance Label (Fallback or primary if no trust)
            if (!empty($entity->coordinates) && (isset($entity->coordinates['lat']) && $entity->coordinates['lat'] != 0)) {
                if (isset($entity->distance)) {
                    $dist = $entity->distance;
                    if ($dist < 1) {
                        $entity->smart_distance = "À " . round($dist * 1000) . "m";
                    } elseif ($dist < 2) {
                        $entity->smart_distance = "Proche";
                    } else {
                        $entity->smart_distance = round($dist, 1) . " km";
                    }
                }
            } else {
                $entity->smart_distance = "Remote";
            }

            // Trust over distance logic:
            if ($entity->trust_label) {
                $entity->matching_context = $entity->trust_label;
            } else {
                $entity->matching_context = $entity->smart_distance;
            }

            // Matching Context (Simplified for high speed merge)
            if (!empty($this->search)) {
                $search = mb_strtolower($this->search);
                if ($entity->is_circle) {
                    if (stripos(mb_strtolower($entity->name), $search) !== false) $entity->matching_context = "Cercle trouvé";
                    else $entity->matching_context = "Expertise • " . ($entity->matching_context ?? $entity->smart_distance);
                } else {
                    if (stripos(mb_strtolower($entity->name), $search) !== false) $entity->matching_context = "Mission trouvée";
                    else $entity->matching_context = "Domaine • " . ($entity->matching_context ?? $entity->smart_distance);
                }
            } elseif (!$entity->matching_context) {
                $entity->matching_context = $entity->smart_distance;
            }

            return $entity;
        });

        $dynamicTitle = 'guuu.fr | Réseau de Confiance & Projets de Proximité';
        $dynamicDesc = 'Trouvez des experts vérifiés et soutenez des initiatives locales. guuu.fr connecte les talents via des cercles de confiance pour une collaboration transparente.';

        if ($this->search) {
            $dynamicTitle = '🔍 ' . ucfirst($this->search) . ' : Experts et Offres de Confiance | guuu.fr';
            $dynamicDesc = 'Recherche en cours pour "' . $this->search . '". Découvrez les meilleurs talents et opportunités locales correspondant à votre besoin sur guuu.fr.';
        } elseif ($this->locationName) {
            $dynamicTitle = '📍 À Proximité de ' . $this->locationName . ' | Initiatives & Talents | guuu.fr';
            $dynamicDesc = 'Explorez les cercles de confiance et offres de services à ' . $this->locationName . '. Connectez-vous avec votre communauté locale pour bâtir demain.';
        }

        return view('livewire.home', [
            'results' => $formattedResults,
            'achievements' => \App\Models\Achievement::with(['user', 'skill', 'circle'])->where('is_verified', true)->latest()->take(6)->get(),
        ])->layoutData([
            'title' => $dynamicTitle,
            'description' => $dynamicDesc,
        ]);
    }
}
